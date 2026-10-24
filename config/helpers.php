<?php
/**
 * GlobeTrek Adventures - Application Helpers & Utility Functions
 */

// Determine base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . $host . ($scriptName === '/' ? '' : $scriptName), '/');

// Normalize root path for BASE_URL
if (!defined('BASE_URL')) {
    // When called from subdirectories like /admin or /staff or /customer or /booking
    $docRoot = str_replace('\\', '/', dirname(__DIR__));
    $reqUri = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    
    // Simple robust BASE_URL fallback
    $pos = strpos($reqUri, '/public');
    if ($pos !== false) {
        $base = substr($reqUri, 0, $pos);
    } else {
        $base = '';
    }
    define('BASE_URL', $base);
}

/**
 * Sanitize user input
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency in USD
 */
function format_price($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Flash message helper
 */
function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * CSRF Protection
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Generate unique booking reference (e.g. GT-2026-00412)
 */
function generate_booking_code() {
    $year = date('Y');
    $rand = strtoupper(substr(uniqid(), -5));
    return "GT-{$year}-{$rand}";
}

/**
 * Dynamic Tour Price Calculator Formula
 * Formula:
 * Total = (Base_Price * Travelers) + (Hotel_Rate * Nights) + (Transport_Rate * Days) + AddOns_Total
 */
function calculate_tour_total($basePrice, $travelers, $durationNights, $durationDays, $hotelPricePerNight = 0, $transportPricePerDay = 0, $addOnsPrice = 0) {
    $travelers = max(1, (int)$travelers);
    $packagePortion = (float)$basePrice * $travelers;
    $hotelPortion = (float)$hotelPricePerNight * max(1, (int)$durationNights);
    $transportPortion = (float)$transportPricePerDay * max(1, (int)$durationDays);
    $addOnsPortion = (float)$addOnsPrice;

    return [
        'package_total' => $packagePortion,
        'hotel_total' => $hotelPortion,
        'transport_total' => $transportPortion,
        'addons_total' => $addOnsPortion,
        'grand_total' => $packagePortion + $hotelPortion + $transportPortion + $addOnsPortion
    ];
}

/**
 * Logs security audit actions
 */
function log_audit_action($userId, $action, $details = '') {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        db_insert(
            "INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) 
             VALUES (?, ?, ?, ?, datetime('now'))",
            [$userId, $action, $details, $ip]
        );
    } catch (Exception $e) {
        // Silently skip if logging table not ready during init
    }
}
