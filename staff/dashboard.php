<?php
/**
 * GlobeTrek Adventures - Staff Operations Dashboard
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('staff');
$user = current_user();

$pageTitle = 'Staff Operations Dashboard | GlobeTrek Adventures';
require_once __DIR__ . '/../includes/header.php';

// Quick KPIs
$totalBookings = (int)db_fetch_val("SELECT COUNT(*) FROM bookings");
$pendingBookings = (int)db_fetch_val("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'");
$confirmedBookings = (int)db_fetch_val("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Confirmed'");
$pendingQueries = (int)db_fetch_val("SELECT COUNT(*) FROM queries WHERE status = 'Pending'");
$activePackages = (int)db_fetch_val("SELECT COUNT(*) FROM tour_packages WHERE status = 'Active'");

// Recent Bookings
$recentBookings = db_fetch_all("
    SELECT b.*, p.title as package_title, u.full_name as customer_name, u.email as customer_email,
           d.name as destination_name
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.id
    JOIN users u ON b.user_id = u.id
    JOIN destinations d ON p.destination_id = d.id
    ORDER BY b.id DESC
    LIMIT 6
");

// Pending Customer Queries
$unansweredQueries = db_fetch_all("
    SELECT * FROM queries WHERE status = 'Pending' ORDER BY id DESC LIMIT 4
");
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Operations & Staff Desk</h1>
        <p class="dashboard-subtitle">Coordinate tour packages, customer bookings, hotels, vehicles, and inquiries.</p>
      </div>
      <div style="display:flex; gap:0.8rem;">
        <a href="<?= BASE_URL ?>/staff/manage-packages.php?action=create" class="btn btn-accent"><i class="fa-solid fa-plus"></i> Add New Package</a>
        <a href="<?= BASE_URL ?>/staff/manage-bookings.php" class="btn btn-primary"><i class="fa-solid fa-calendar-check"></i> Manage Bookings</a>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="stat-info">
          <span class="stat-label">Total Bookings</span>
          <span class="stat-value"><?= $totalBookings ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="stat-info">
          <span class="stat-label">Pending Confirmation</span>
          <span class="stat-value"><?= $pendingBookings ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-box-archive"></i></div>
        <div class="stat-info">
          <span class="stat-label">Active Tour Packages</span>
          <span class="stat-value"><?= $activePackages ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-headset"></i></div>
        <div class="stat-info">
          <span class="stat-label">Pending Inquiries</span>
          <span class="stat-value"><?= $pendingQueries ?></span>
        </div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1.6fr 1fr; gap:2rem;">
      
      <!-- Left: Recent Bookings Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-clipboard-list" style="color:var(--primary);"></i> Recent Customer Bookings</h3>
          <a href="<?= BASE_URL ?>/staff/manage-bookings.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>Customer</th>
                  <th>Tour Package</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentBookings as $bk): ?>
                  <tr>
                    <td><strong style="font-family:monospace;"><?= htmlspecialchars($bk['booking_code']) ?></strong></td>
                    <td>
                      <strong><?= htmlspecialchars($bk['customer_name']) ?></strong><br>
                      <span style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($bk['customer_email']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($bk['package_title']) ?></td>
                    <td><?= date('d M Y', strtotime($bk['travel_date'])) ?></td>
                    <td>
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $bk['booking_status'])) ?>">
                        <?= htmlspecialchars($bk['booking_status']) ?>
                      </span>
                    </td>
                    <td>
                      <a href="<?= BASE_URL ?>/staff/manage-bookings.php?id=<?= $bk['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right: Pending Support Inquiries -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-circle-question" style="color:var(--accent);"></i> Unanswered Inquiries</h3>
          <a href="<?= BASE_URL ?>/staff/customer-queries.php" class="btn btn-outline btn-sm">Desk</a>
        </div>
        <div class="card-body">
          <?php if (!empty($unansweredQueries)): ?>
            <div style="display:flex; flex-direction:column; gap:1rem;">
              <?php foreach ($unansweredQueries as $uq): ?>
                <div style="background:var(--bg-main); border:1px solid var(--border); border-radius:var(--radius-sm); padding:1rem;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                    <strong style="font-size:0.9rem; color:var(--secondary);"><?= htmlspecialchars($uq['guest_name']) ?></strong>
                    <span style="font-size:0.75rem; color:var(--text-muted);"><?= date('d M', strtotime($uq['created_at'])) ?></span>
                  </div>
                  <div style="font-size:0.85rem; font-weight:600; color:var(--primary); margin-bottom:0.4rem;"><?= htmlspecialchars($uq['subject']) ?></div>
                  <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.8rem; line-height:1.4;">
                    <?= substr(htmlspecialchars($uq['message']), 0, 90) ?>...
                  </p>
                  <a href="<?= BASE_URL ?>/staff/customer-queries.php?reply_id=<?= $uq['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-reply"></i> Reply to Guest
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="text-align:center; padding:2rem 1rem; color:var(--text-muted);">
              <i class="fa-solid fa-circle-check" style="color:var(--success); font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
              All customer queries have been answered!
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
