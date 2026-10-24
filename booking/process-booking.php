<?php
/**
 * GlobeTrek Adventures - Booking Transaction Processor
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/tours.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Session expired. Please try booking again.');
    header('Location: ' . BASE_URL . '/tours.php');
    exit;
}

$packageId = (int)($_POST['package_id'] ?? 0);
$travelDate = clean($_POST['travel_date'] ?? '');
$travelers = max(1, (int)($_POST['travelers_count'] ?? 1));
$accommodationId = (int)($_POST['accommodation_id'] ?? 0);
$transportId = (int)($_POST['transport_id'] ?? 0);
$customNotes = clean($_POST['custom_notes'] ?? '');
$paymentMethod = clean($_POST['payment_method'] ?? 'Credit Card');

// Traveler Contact Details
$guestName = clean($_POST['guest_name'] ?? '');
$guestEmail = clean($_POST['guest_email'] ?? '');
$guestPhone = clean($_POST['guest_phone'] ?? '');

// Fetch Package from DB
$pkg = db_fetch_one("SELECT * FROM tour_packages WHERE id = ? AND status = 'Active'", [$packageId]);
if (!$pkg) {
    set_flash('danger', 'Invalid tour package.');
    header('Location: ' . BASE_URL . '/tours.php');
    exit;
}

// Check or register user if not logged in
$user = current_user();
$userId = null;

if ($user) {
    $userId = $user['id'];
} else {
    // Check if email already exists in DB
    $existingUser = db_fetch_one("SELECT id FROM users WHERE email = ?", [$guestEmail]);
    if ($existingUser) {
        $userId = $existingUser['id'];
    } else {
        // Auto-create customer account
        $tempPass = bin2hex(random_bytes(4));
        $hash = password_hash($tempPass, PASSWORD_DEFAULT);
        $userId = db_insert("
            INSERT INTO users (full_name, email, password_hash, role, phone, is_active, created_at)
            VALUES (?, ?, ?, 'customer', ?, 1, datetime('now'))
        ", [$guestName, $guestEmail, $hash, $guestPhone]);
    }
    // Auto-login session for user
    $_SESSION['user_id'] = $userId;
    $_SESSION['full_name'] = $guestName;
    $_SESSION['email'] = $guestEmail;
    $_SESSION['role'] = 'customer';
}

// Calculate Hotel Cost
$hotelRate = 0.00;
if ($accommodationId > 0) {
    $hotel = db_fetch_one("SELECT price_per_night FROM accommodations WHERE id = ?", [$accommodationId]);
    if ($hotel) $hotelRate = (float)$hotel['price_per_night'];
}
$hotelTotal = $hotelRate * (int)$pkg['duration_nights'];

// Calculate Transport Cost
$transportRate = 0.00;
if ($transportId > 0) {
    $trans = db_fetch_one("SELECT price_per_day FROM transport_options WHERE id = ?", [$transportId]);
    if ($trans) $transportRate = (float)$trans['price_per_day'];
}
$transportTotal = $transportRate * (int)$pkg['duration_days'];

// Calculate Add-ons
$selectedAddons = $_POST['addons'] ?? [];
$addonsList = [];
$addonsTotal = 0.00;
if (is_array($selectedAddons) && !empty($selectedAddons)) {
    foreach ($selectedAddons as $addonName) {
        $adRow = db_fetch_one("SELECT name, price FROM package_addons WHERE name = ?", [$addonName]);
        if ($adRow) {
            $addonsList[$adRow['name']] = (float)$adRow['price'];
            $addonsTotal += (float)$adRow['price'];
        }
    }
}

// Base Package Portion
$packagePortion = (float)$pkg['base_price'] * $travelers;

// Total Price Computation
$grandTotal = $packagePortion + $hotelTotal + $transportTotal + $addonsTotal;

// Generate Unique Booking Code
$bookingCode = generate_booking_code();

// Begin Transaction in DB
$pdo = get_db_connection();
$pdo->beginTransaction();

try {
    // 1. Insert Booking Record
    $stmtBk = $pdo->prepare("
        INSERT INTO bookings (
            booking_code, user_id, package_id, travel_date, travelers_count,
            accommodation_id, transport_id, add_ons_json, custom_notes,
            base_price, hotel_price, transport_price, addons_price, total_amount,
            payment_status, booking_status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', 'Confirmed', datetime('now'))
    ");

    $stmtBk->execute([
        $bookingCode, $userId, $packageId, $travelDate, $travelers,
        $accommodationId ?: null, $transportId ?: null, json_encode($addonsList), $customNotes,
        $packagePortion, $hotelTotal, $transportTotal, $addonsTotal, $grandTotal
    ]);

    $bookingId = $pdo->lastInsertId();

    // 2. Insert Simulated Payment Record
    $txnRef = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
    $stmtPay = $pdo->prepare("
        INSERT INTO payments (
            booking_id, user_id, transaction_ref, payment_method, amount, payment_status, card_last4, paid_at
        ) VALUES (?, ?, ?, ?, ?, 'Completed', '4242', datetime('now'))
    ");
    $stmtPay->execute([$bookingId, $userId, $txnRef, $paymentMethod, $grandTotal]);

    // 3. Security Audit Log
    log_audit_action($userId, 'CREATE_BOOKING', "Booking {$bookingCode} confirmed for {$pkg['title']}, amount: {$grandTotal}");

    $pdo->commit();

    // Flash success and redirect to confirmation
    set_flash('success', "Congratulations! Your booking #{$bookingCode} is confirmed.");
    header('Location: ' . BASE_URL . '/booking/confirmation.php?code=' . urlencode($bookingCode));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    set_flash('danger', 'Booking transaction failed: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/booking/book.php?id=' . $packageId);
    exit;
}
