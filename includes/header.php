<?php
/**
 * GlobeTrek Adventures - Global Header & Navigation
 * Glassmorphism navbar | Left-slide mobile drawer | Role-based menus
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

$user = current_user();
$pageTitle = $pageTitle ?? 'Explore Sri Lanka | GlobeTrek Adventures';
$currentPage = basename($_SERVER['PHP_SELF']);
function isActivePage($pages) {
    global $currentPage;
    return in_array($currentPage, (array)$pages) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/booking.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<!-- ========== NAVBAR ========== -->
<header class="navbar" id="mainNavbar">
  <div class="nav-container">

    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>/index.php" class="nav-brand" aria-label="GlobeTrek Home">
      <div class="brand-icon">
        <i class="fa-solid fa-compass-drafting"></i>
      </div>
      <div class="brand-text-wrap">
        <span class="brand-main">Globe<em>Trek</em></span>
        <span class="brand-tagline">Adventures · Sri Lanka</span>
      </div>
    </a>

    <!-- Desktop Nav Links -->
    <nav class="nav-links-desktop" aria-label="Primary Navigation">
      <a href="<?= BASE_URL ?>/index.php"           class="nav-link <?= isActivePage('index.php') ?>">Home</a>
      <a href="<?= BASE_URL ?>/tours.php"            class="nav-link <?= isActivePage(['tours.php','tour-details.php']) ?>">Tours</a>
      <a href="<?= BASE_URL ?>/destinations.php"     class="nav-link <?= isActivePage('destinations.php') ?>">Destinations</a>
      <a href="<?= BASE_URL ?>/accommodation.php"    class="nav-link <?= isActivePage('accommodation.php') ?>">Stay</a>
      <a href="<?= BASE_URL ?>/transport.php"        class="nav-link <?= isActivePage('transport.php') ?>">Transport</a>
      <a href="<?= BASE_URL ?>/travel-guides.php"    class="nav-link <?= isActivePage('travel-guides.php') ?>">Guides</a>
      <a href="<?= BASE_URL ?>/about.php"            class="nav-link <?= isActivePage('about.php') ?>">About</a>
      <a href="<?= BASE_URL ?>/contact.php"          class="nav-link <?= isActivePage('contact.php') ?>">Contact</a>
    </nav>

    <!-- Right Actions -->
    <div class="nav-actions">
      <?php if ($user): ?>
        <div class="user-dropdown" id="userDropdown">
          <button class="user-badge-btn" id="userBadgeBtn" type="button" aria-expanded="false">
            <div class="user-avatar-circle">
              <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
            <span class="user-name-short"><?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?></span>
            <span class="role-tag <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
            <i class="fa-solid fa-chevron-down dropdown-caret"></i>
          </button>
          <div class="dropdown-menu" id="userDropdownMenu">
            <div class="dropdown-user-info">
              <div class="dropdown-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
              <div>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                <small><?= htmlspecialchars($user['email']) ?></small>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <?php if ($user['role'] === 'admin'): ?>
              <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fa-solid fa-chart-pie"></i> Admin Dashboard</a>
              <a href="<?= BASE_URL ?>/admin/manage-staff.php"><i class="fa-solid fa-users-gear"></i> Manage Staff</a>
              <a href="<?= BASE_URL ?>/admin/manage-users.php"><i class="fa-solid fa-address-book"></i> Customers</a>
              <a href="<?= BASE_URL ?>/admin/reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a>
              <a href="<?= BASE_URL ?>/admin/security.php"><i class="fa-solid fa-shield-halved"></i> Security</a>
            <?php elseif ($user['role'] === 'staff'): ?>
              <a href="<?= BASE_URL ?>/staff/dashboard.php"><i class="fa-solid fa-gauge"></i> Staff Dashboard</a>
              <a href="<?= BASE_URL ?>/staff/manage-packages.php"><i class="fa-solid fa-box-archive"></i> Packages</a>
              <a href="<?= BASE_URL ?>/staff/manage-bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
              <a href="<?= BASE_URL ?>/staff/customer-queries.php"><i class="fa-solid fa-comments"></i> Inquiries</a>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/customer/dashboard.php"><i class="fa-solid fa-gauge-high"></i> My Dashboard</a>
              <a href="<?= BASE_URL ?>/customer/my-bookings.php"><i class="fa-solid fa-suitcase-rolling"></i> My Bookings</a>
              <a href="<?= BASE_URL ?>/customer/queries.php"><i class="fa-solid fa-circle-question"></i> Inquiries</a>
              <a href="<?= BASE_URL ?>/customer/profile.php"><i class="fa-solid fa-user-pen"></i> Profile</a>
            <?php endif; ?>
            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/logout.php" class="dropdown-logout"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn-nav-login">Login</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn-nav-register">Get Started</a>
      <?php endif; ?>

      <!-- Hamburger (Mobile) -->
      <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- ========== MOBILE OVERLAY ========== -->
<div class="drawer-overlay" id="drawerOverlay" aria-hidden="true"></div>

<!-- ========== LEFT SLIDE DRAWER ========== -->
<aside class="nav-drawer" id="navDrawer" aria-label="Mobile Navigation" aria-hidden="true">
  
  <!-- Drawer Header -->
  <div class="drawer-header">
    <a href="<?= BASE_URL ?>/index.php" class="drawer-brand">
      <div class="brand-icon sm"><i class="fa-solid fa-compass-drafting"></i></div>
      <div>
        <span class="brand-main">Globe<em>Trek</em></span>
        <span class="brand-tagline">Adventures</span>
      </div>
    </a>
    <button class="drawer-close" id="drawerClose" aria-label="Close menu">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <?php if ($user): ?>
  <div class="drawer-user-card">
    <div class="drawer-user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
    <div>
      <strong><?= htmlspecialchars($user['full_name']) ?></strong>
      <span class="role-tag <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
    </div>
  </div>
  <?php endif; ?>

  <!-- Drawer Nav Links -->
  <nav class="drawer-nav" aria-label="Mobile Navigation Links">
    <span class="drawer-section-label">Main Pages</span>
    <a href="<?= BASE_URL ?>/index.php"        class="drawer-link <?= isActivePage('index.php') ?>"><i class="fa-solid fa-house"></i> Home</a>
    <a href="<?= BASE_URL ?>/tours.php"         class="drawer-link <?= isActivePage(['tours.php','tour-details.php']) ?>"><i class="fa-solid fa-map-location-dot"></i> Tour Packages</a>
    <a href="<?= BASE_URL ?>/destinations.php"  class="drawer-link <?= isActivePage('destinations.php') ?>"><i class="fa-solid fa-earth-asia"></i> Destinations</a>
    <a href="<?= BASE_URL ?>/accommodation.php" class="drawer-link <?= isActivePage('accommodation.php') ?>"><i class="fa-solid fa-hotel"></i> Accommodation</a>
    <a href="<?= BASE_URL ?>/transport.php"     class="drawer-link <?= isActivePage('transport.php') ?>"><i class="fa-solid fa-van-shuttle"></i> Transport</a>
    <a href="<?= BASE_URL ?>/travel-guides.php" class="drawer-link <?= isActivePage('travel-guides.php') ?>"><i class="fa-solid fa-book-open"></i> Travel Guides</a>
    <a href="<?= BASE_URL ?>/about.php"         class="drawer-link <?= isActivePage('about.php') ?>"><i class="fa-solid fa-circle-info"></i> About Us</a>
    <a href="<?= BASE_URL ?>/contact.php"       class="drawer-link <?= isActivePage('contact.php') ?>"><i class="fa-solid fa-envelope"></i> Contact</a>

    <?php if ($user): ?>
    <span class="drawer-section-label" style="margin-top:1.2rem;">My Account</span>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/admin/dashboard.php"     class="drawer-link"><i class="fa-solid fa-chart-pie"></i> Admin Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/manage-staff.php"  class="drawer-link"><i class="fa-solid fa-users-gear"></i> Manage Staff</a>
        <a href="<?= BASE_URL ?>/admin/reports.php"       class="drawer-link"><i class="fa-solid fa-file-lines"></i> Reports</a>
        <a href="<?= BASE_URL ?>/admin/security.php"      class="drawer-link"><i class="fa-solid fa-shield-halved"></i> Security</a>
      <?php elseif ($user['role'] === 'staff'): ?>
        <a href="<?= BASE_URL ?>/staff/dashboard.php"           class="drawer-link"><i class="fa-solid fa-gauge"></i> Staff Dashboard</a>
        <a href="<?= BASE_URL ?>/staff/manage-packages.php"     class="drawer-link"><i class="fa-solid fa-box-archive"></i> Packages</a>
        <a href="<?= BASE_URL ?>/staff/manage-bookings.php"     class="drawer-link"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
        <a href="<?= BASE_URL ?>/staff/customer-queries.php"    class="drawer-link"><i class="fa-solid fa-comments"></i> Inquiries</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/customer/dashboard.php"  class="drawer-link"><i class="fa-solid fa-gauge-high"></i> My Dashboard</a>
        <a href="<?= BASE_URL ?>/customer/my-bookings.php" class="drawer-link"><i class="fa-solid fa-suitcase-rolling"></i> My Bookings</a>
        <a href="<?= BASE_URL ?>/customer/queries.php"    class="drawer-link"><i class="fa-solid fa-circle-question"></i> Inquiries</a>
        <a href="<?= BASE_URL ?>/customer/profile.php"    class="drawer-link"><i class="fa-solid fa-user-pen"></i> Profile</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/logout.php" class="drawer-link" style="color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
    <?php else: ?>
    <span class="drawer-section-label" style="margin-top:1.2rem;">Account</span>
      <a href="<?= BASE_URL ?>/login.php"    class="drawer-link"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
      <a href="<?= BASE_URL ?>/register.php" class="drawer-link"><i class="fa-solid fa-user-plus"></i> Register</a>
    <?php endif; ?>
  </nav>

  <div class="drawer-footer">
    <p>🌿 Eco-certified travel agency</p>
    <p>📞 +94 11 234 5678</p>
  </div>
</aside>

<!-- Flash Alerts -->
<div class="flash-alerts-container">
  <?php require_once __DIR__ . '/alerts.php'; ?>
</div>
