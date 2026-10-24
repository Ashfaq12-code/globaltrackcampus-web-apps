<?php
/**
 * GlobeTrek Adventures - Booking Confirmation & Voucher View
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

$bookingCode = clean($_GET['code'] ?? '');
if (empty($bookingCode)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Fetch Full Booking Details
$booking = db_fetch_one("
    SELECT b.*, p.title as package_title, p.duration_days, p.duration_nights,
           d.name as destination_name,
           u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
           a.name as hotel_name, a.tier as hotel_tier,
           t.vehicle_name, t.vehicle_type,
           pay.transaction_ref, pay.payment_method, pay.paid_at
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.id
    JOIN destinations d ON p.destination_id = d.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN accommodations a ON b.accommodation_id = a.id
    LEFT JOIN transport_options t ON b.transport_id = t.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    WHERE b.booking_code = ?
", [$bookingCode]);

if (!$booking) {
    set_flash('danger', 'Booking voucher not found.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = "Booking Confirmed: {$booking['booking_code']}";
require_once __DIR__ . '/../includes/header.php';

$addons = json_decode($booking['add_ons_json'] ?? '[]', true) ?: [];
?>

<div class="booking-page-wrap">
  <div class="container">
    
    <div class="voucher-card">
      <div class="voucher-header">
        <div class="voucher-icon">
          <i class="fa-solid fa-check"></i>
        </div>
        <h1 style="color:#fff; font-size:2rem; margin-bottom:0.3rem;">BOOKING CONFIRMED</h1>
        <p style="color:#dcfce7; font-size:1.05rem;">Thank you for choosing GlobeTrek Adventures!</p>
        <div class="voucher-code-badge"><?= htmlspecialchars($booking['booking_code']) ?></div>
      </div>

      <div class="voucher-body">
        
        <div class="voucher-details-grid">
          <div class="voucher-detail-box">
            <div class="voucher-label">Tour Package</div>
            <div class="voucher-val"><?= htmlspecialchars($booking['package_title']) ?></div>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.2rem;">
              <i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> <?= htmlspecialchars($booking['destination_name']) ?> (<?= $booking['duration_days'] ?>D / <?= $booking['duration_nights'] ?>N)
            </div>
          </div>

          <div class="voucher-detail-box">
            <div class="voucher-label">Travel Date & Guests</div>
            <div class="voucher-val"><?= date('l, F j, Y', strtotime($booking['travel_date'])) ?></div>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.2rem;">
              <i class="fa-solid fa-users" style="color:var(--accent);"></i> <?= $booking['travelers_count'] ?> Guest(s) Confirmed
            </div>
          </div>

          <div class="voucher-detail-box">
            <div class="voucher-label">Accommodation</div>
            <div class="voucher-val"><?= htmlspecialchars($booking['hotel_name'] ?: 'Standard Eco Lodge') ?></div>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.2rem;">
              Tier: <?= htmlspecialchars($booking['hotel_tier'] ?: 'Standard') ?> (<?= $booking['duration_nights'] ?> Nights)
            </div>
          </div>

          <div class="voucher-detail-box">
            <div class="voucher-label">Private Transport</div>
            <div class="voucher-val"><?= htmlspecialchars($booking['vehicle_name'] ?: 'Standard AC Sedan') ?></div>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.2rem;">
              <?= htmlspecialchars($booking['vehicle_type'] ?: 'Sedan') ?> &bull; Driver Guide Included
            </div>
          </div>
        </div>

        <?php if (!empty($addons)): ?>
          <div style="background:var(--bg-main); padding:1.2rem; border-radius:var(--radius-sm); margin-bottom:2rem;">
            <div class="voucher-label" style="margin-bottom:0.5rem;">Included Optional Add-ons</div>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:0.4rem; font-size:0.9rem;">
              <?php foreach ($addons as $adName => $adPrice): ?>
                <li><i class="fa-solid fa-circle-check" style="color:var(--success); margin-right:0.4rem;"></i> <?= htmlspecialchars($adName) ?> &mdash; <strong><?= format_price($adPrice) ?></strong></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Financial Summary -->
        <div style="border:1px solid var(--border); border-radius:var(--radius-md); padding:1.5rem; margin-bottom:2rem;">
          <h3 style="font-size:1.15rem; margin-bottom:1rem; color:var(--secondary);"><i class="fa-solid fa-receipt" style="color:var(--primary);"></i> Payment Receipt Breakdown</h3>
          <div class="summary-item-row">
            <span>Package Base (<?= $booking['travelers_count'] ?> pax):</span>
            <span><?= format_price($booking['base_price']) ?></span>
          </div>
          <div class="summary-item-row">
            <span>Hotel Surcharge:</span>
            <span><?= format_price($booking['hotel_price']) ?></span>
          </div>
          <div class="summary-item-row">
            <span>Transport Surcharge:</span>
            <span><?= format_price($booking['transport_price']) ?></span>
          </div>
          <div class="summary-item-row">
            <span>Add-ons Total:</span>
            <span><?= format_price($booking['addons_price']) ?></span>
          </div>
          <div class="summary-total-row">
            <span class="total-label">Total Amount Paid</span>
            <span class="total-amount"><?= format_price($booking['total_amount']) ?></span>
          </div>
          <div style="margin-top:0.8rem; font-size:0.82rem; color:var(--text-muted);">
            Payment Method: <strong><?= htmlspecialchars($booking['payment_method'] ?: 'Credit Card') ?></strong> &bull; TXN Ref: <?= htmlspecialchars($booking['transaction_ref'] ?: 'N/A') ?> &bull; Status: <span class="status-pill status-paid">Paid in Full</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/booking/print-voucher.php?code=<?= urlencode($booking['booking_code']) ?>" target="_blank" class="btn btn-outline btn-lg">
            <i class="fa-solid fa-print"></i> Print Official Voucher
          </a>
          <a href="<?= BASE_URL ?>/customer/dashboard.php" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-gauge-high"></i> Go to Customer Dashboard
          </a>
        </div>

      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
