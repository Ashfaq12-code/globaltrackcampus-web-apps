<?php
/**
 * GlobeTrek Adventures - Customer Dashboard
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('customer');
$user = current_user();

$pageTitle = 'Customer Dashboard | GlobeTrek Adventures';
require_once __DIR__ . '/../includes/header.php';

// Calculate Customer KPI Stats
$totalBookings = (int)db_fetch_val("SELECT COUNT(*) FROM bookings WHERE user_id = ?", [$user['id']]);
$upcomingTrips = (int)db_fetch_val("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND travel_date >= date('now') AND booking_status IN ('Confirmed', 'In-Progress')", [$user['id']]);
$completedTrips = (int)db_fetch_val("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND booking_status = 'Completed'", [$user['id']]);
$totalSpent = (float)db_fetch_val("SELECT SUM(total_amount) FROM bookings WHERE user_id = ? AND payment_status = 'Paid'", [$user['id']]) ?: 0.00;
$activeQueries = (int)db_fetch_val("SELECT COUNT(*) FROM queries WHERE user_id = ? AND status != 'Closed'", [$user['id']]);

// Fetch Recent Bookings
$recentBookings = db_fetch_all("
    SELECT b.*, p.title as package_title, d.name as destination_name
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.id
    JOIN destinations d ON p.destination_id = d.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
    LIMIT 5
", [$user['id']]);
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</h1>
        <p class="dashboard-subtitle">Manage your travel bookings, customized itineraries, and inquiries.</p>
      </div>
      <div style="display:flex; gap:0.8rem;">
        <a href="<?= BASE_URL ?>/booking/customize.php" class="btn btn-accent"><i class="fa-solid fa-sliders"></i> Plan Custom Trip</a>
        <a href="<?= BASE_URL ?>/tours.php" class="btn btn-primary"><i class="fa-solid fa-compass"></i> Explore Tours</a>
      </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-suitcase-rolling"></i></div>
        <div class="stat-info">
          <span class="stat-label">Total Bookings</span>
          <span class="stat-value"><?= $totalBookings ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info">
          <span class="stat-label">Upcoming Trips</span>
          <span class="stat-value"><?= $upcomingTrips ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <div class="stat-info">
          <span class="stat-label">Total Travel Spent</span>
          <span class="stat-value"><?= format_price($totalSpent) ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-circle-question"></i></div>
        <div class="stat-info">
          <span class="stat-label">Active Inquiries</span>
          <span class="stat-value"><?= $activeQueries ?></span>
        </div>
      </div>
    </div>

    <!-- Recent Bookings Card -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--primary);"></i> Recent Bookings & Expeditions</h3>
        <a href="<?= BASE_URL ?>/customer/my-bookings.php" class="btn btn-outline btn-sm">View All Bookings</a>
      </div>

      <div class="card-body" style="padding:0;">
        <?php if (!empty($recentBookings)): ?>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Booking Ref</th>
                  <th>Tour Package</th>
                  <th>Destination</th>
                  <th>Travel Date</th>
                  <th>Travelers</th>
                  <th>Total Paid</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentBookings as $bk): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($bk['booking_code']) ?></strong></td>
                    <td><?= htmlspecialchars($bk['package_title']) ?></td>
                    <td><i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> <?= htmlspecialchars($bk['destination_name']) ?></td>
                    <td><?= date('d M Y', strtotime($bk['travel_date'])) ?></td>
                    <td><?= $bk['travelers_count'] ?> pax</td>
                    <td><strong><?= format_price($bk['total_amount']) ?></strong></td>
                    <td>
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $bk['booking_status'])) ?>">
                        <?= htmlspecialchars($bk['booking_status']) ?>
                      </span>
                    </td>
                    <td>
                      <a href="<?= BASE_URL ?>/booking/confirmation.php?code=<?= urlencode($bk['booking_code']) ?>" class="btn btn-outline btn-sm" title="View Voucher">
                        <i class="fa-solid fa-ticket"></i> Voucher
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div style="text-align:center; padding:3rem;">
            <p style="color:var(--text-muted); margin-bottom:1rem;">You don't have any bookings yet. Start your Sri Lanka adventure today!</p>
            <a href="<?= BASE_URL ?>/tours.php" class="btn btn-primary"><i class="fa-solid fa-compass"></i> Browse Tour Packages</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
