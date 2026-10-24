<?php
/**
 * GlobeTrek Adventures - Unified Dashboard Sidebar
 */
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

$user = current_user();
if (!$user) return;

$currentScript = basename($_SERVER['PHP_SELF']);
$userRole = $user['role'];

// Fetch quick badges
$pendingQueriesCount = 0;
$pendingBookingsCount = 0;
try {
    if ($userRole === 'staff' || $userRole === 'admin') {
        $pendingQueriesCount = (int)db_fetch_val("SELECT COUNT(*) FROM queries WHERE status = 'Pending'");
        $pendingBookingsCount = (int)db_fetch_val("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'");
    }
} catch (Exception $e) {}
?>
<aside class="dashboard-sidebar">
  <div class="sidebar-user">
    <div class="sidebar-avatar">
      <i class="fa-solid <?= ($userRole === 'admin') ? 'fa-shield-halved' : (($userRole === 'staff') ? 'fa-user-tie' : 'fa-user') ?>"></i>
    </div>
    <div class="sidebar-user-info">
      <div class="sidebar-user-name"><?= htmlspecialchars($user['full_name']) ?></div>
      <span class="role-tag <?= htmlspecialchars($userRole) ?>"><?= ucfirst($userRole) ?></span>
    </div>
  </div>

  <ul class="sidebar-menu">
    <?php if ($userRole === 'admin'): ?>
      <!-- Admin Menu -->
      <li>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="sidebar-link <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-chart-pie"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/admin/manage-staff.php" class="sidebar-link <?= ($currentScript === 'manage-staff.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-users-gear"></i>
          <span>Manage Staff</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/admin/manage-users.php" class="sidebar-link <?= ($currentScript === 'manage-users.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-address-book"></i>
          <span>Manage Customers</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/admin/manage-bookings.php" class="sidebar-link <?= ($currentScript === 'manage-bookings.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-calendar-check"></i>
          <span>All Bookings</span>
          <?php if ($pendingBookingsCount > 0): ?>
            <span class="badge"><?= $pendingBookingsCount ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/admin/reports.php" class="sidebar-link <?= ($currentScript === 'reports.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-file-invoice-dollar"></i>
          <span>Sales Reports</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/admin/security.php" class="sidebar-link <?= ($currentScript === 'security.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-lock"></i>
          <span>System Security</span>
        </a>
      </li>

    <?php elseif ($userRole === 'staff'): ?>
      <!-- Staff Menu -->
      <li>
        <a href="<?= BASE_URL ?>/staff/dashboard.php" class="sidebar-link <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-gauge"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/staff/manage-packages.php" class="sidebar-link <?= ($currentScript === 'manage-packages.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-box-archive"></i>
          <span>Manage Packages</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/staff/manage-bookings.php" class="sidebar-link <?= ($currentScript === 'manage-bookings.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-calendar-check"></i>
          <span>Manage Bookings</span>
          <?php if ($pendingBookingsCount > 0): ?>
            <span class="badge"><?= $pendingBookingsCount ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/staff/manage-hotels.php" class="sidebar-link <?= ($currentScript === 'manage-hotels.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-hotel"></i>
          <span>Hotels / Rooms</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/staff/manage-transport.php" class="sidebar-link <?= ($currentScript === 'manage-transport.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-van-shuttle"></i>
          <span>Transport Fleet</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/staff/customer-queries.php" class="sidebar-link <?= ($currentScript === 'customer-queries.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-comments"></i>
          <span>Inquiries</span>
          <?php if ($pendingQueriesCount > 0): ?>
            <span class="badge"><?= $pendingQueriesCount ?></span>
          <?php endif; ?>
        </a>
      </li>

    <?php else: ?>
      <!-- Customer Menu -->
      <li>
        <a href="<?= BASE_URL ?>/customer/dashboard.php" class="sidebar-link <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-gauge-high"></i>
          <span>My Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/customer/my-bookings.php" class="sidebar-link <?= ($currentScript === 'my-bookings.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-suitcase-rolling"></i>
          <span>My Bookings</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/customer/queries.php" class="sidebar-link <?= ($currentScript === 'queries.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-circle-question"></i>
          <span>My Inquiries</span>
        </a>
      </li>
      <li>
        <a href="<?= BASE_URL ?>/customer/profile.php" class="sidebar-link <?= ($currentScript === 'profile.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-user-pen"></i>
          <span>Profile Settings</span>
        </a>
      </li>
    <?php endif; ?>

    <li style="margin-top:auto; padding-top:1.5rem; border-top:1px solid var(--border);">
      <a href="<?= BASE_URL ?>/index.php" class="sidebar-link">
        <i class="fa-solid fa-globe"></i>
        <span>Public Website</span>
      </a>
    </li>
    <li>
      <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link" style="color:var(--danger);">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </li>
  </ul>
</aside>
