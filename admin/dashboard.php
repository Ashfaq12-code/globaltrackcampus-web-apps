<?php
/**
 * GlobeTrek Adventures - Executive Admin Dashboard
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('admin');
$user = current_user();

$pageTitle = 'Executive Admin Dashboard | GlobeTrek Adventures';
require_once __DIR__ . '/../includes/header.php';

// Executive KPIs
$totalRevenue = (float)db_fetch_val("SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'Paid'") ?: 0.00;
$totalBookings = (int)db_fetch_val("SELECT COUNT(*) FROM bookings");
$totalCustomers = (int)db_fetch_val("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$totalStaff = (int)db_fetch_val("SELECT COUNT(*) FROM users WHERE role = 'staff'");

// Data for Package Performance Chart
$topPackages = db_fetch_all("
    SELECT p.title, COUNT(b.id) as booking_count, SUM(b.total_amount) as package_revenue
    FROM tour_packages p
    LEFT JOIN bookings b ON b.package_id = p.id AND b.payment_status = 'Paid'
    GROUP BY p.id
    ORDER BY booking_count DESC
    LIMIT 5
");

$chartLabels = [];
$chartData = [];
foreach ($topPackages as $tp) {
    $chartLabels[] = substr($tp['title'], 0, 14) . '..';
    $chartData[] = (int)$tp['booking_count'];
}

// Recent System Audit Logs
$recentLogs = db_fetch_all("
    SELECT a.*, u.full_name as user_name, u.role as user_role
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.id DESC
    LIMIT 6
");
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">System Governance & Executive Dashboard</h1>
        <p class="dashboard-subtitle">Platform revenue analytics, operational throughput, staff governance, and security audit.</p>
      </div>
      <div style="display:flex; gap:0.8rem;">
        <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-primary"><i class="fa-solid fa-file-invoice-dollar"></i> Sales Reports</a>
        <a href="<?= BASE_URL ?>/admin/manage-staff.php" class="btn btn-accent"><i class="fa-solid fa-user-shield"></i> Manage Staff</a>
      </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-sack-dollar"></i></div>
        <div class="stat-info">
          <span class="stat-label">Gross Revenue</span>
          <span class="stat-value"><?= format_price($totalRevenue) ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info">
          <span class="stat-label">Total Reservations</span>
          <span class="stat-value"><?= $totalBookings ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info">
          <span class="stat-label">Registered Customers</span>
          <span class="stat-value"><?= $totalCustomers ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-id-badge"></i></div>
        <div class="stat-info">
          <span class="stat-label">Active Staff Accounts</span>
          <span class="stat-value"><?= $totalStaff ?></span>
        </div>
      </div>
    </div>

    <!-- Analytics Charts Section -->
    <div style="display:grid; grid-template-columns:1.4fr 1fr; gap:2rem; margin-bottom:2.2rem;">
      
      <!-- Revenue Trend Line Chart -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-chart-line" style="color:var(--primary);"></i> Revenue Performance ($ USD)</h3>
        </div>
        <div class="card-body">
          <canvas id="revenueLineChart" style="width:100%; height:260px;"></canvas>
        </div>
      </div>

      <!-- Bookings by Package Bar Chart -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-chart-simple" style="color:var(--accent);"></i> Top Package Popularity</h3>
        </div>
        <div class="card-body">
          <canvas id="packagesBarChart" style="width:100%; height:260px;"></canvas>
        </div>
      </div>

    </div>

    <!-- Bottom: Top Performing Packages & Live Security Audit Log -->
    <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:2rem;">
      
      <!-- Top Packages Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-ranking-star" style="color:var(--primary);"></i> Package Revenue Summary</h3>
          <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-outline btn-sm">Full Report</a>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Package Name</th>
                  <th>Reservations</th>
                  <th>Total Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($topPackages as $tp): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($tp['title']) ?></strong></td>
                    <td><span class="status-pill status-in-progress"><?= (int)$tp['booking_count'] ?> trips</span></td>
                    <td><strong style="color:var(--primary-dark);"><?= format_price($tp['package_revenue'] ?: 0) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Security Audit Trail -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-shield-halved" style="color:var(--danger);"></i> Security Audit Trail</h3>
          <a href="<?= BASE_URL ?>/admin/security.php" class="btn btn-outline btn-sm">Audit Log</a>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Action</th>
                  <th>User</th>
                  <th>Timestamp</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentLogs as $log): ?>
                  <tr>
                    <td>
                      <span class="role-tag <?= htmlspecialchars($log['user_role'] ?: 'customer') ?>">
                        <?= htmlspecialchars($log['action']) ?>
                      </span><br>
                      <span style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($log['details']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($log['user_name'] ?: 'Guest / System') ?></td>
                    <td style="font-size:0.78rem; color:var(--text-muted);"><?= date('H:i:s, d M', strtotime($log['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Render Canvas Charts
  const months = ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'];
  const revenues = [2100, 3400, 4800, 5600, 6900, <?= round($totalRevenue) ?>];
  GlobeCharts.drawLineChart('revenueLineChart', months, revenues, '$');

  const pkgLabels = <?= json_encode($chartLabels) ?>;
  const pkgValues = <?= json_encode($chartData) ?>;
  GlobeCharts.drawBarChart('packagesBarChart', pkgLabels.length ? pkgLabels : ['Ella', 'Kandy', 'Sigiriya'], pkgValues.length ? pkgValues : [4, 3, 5]);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
