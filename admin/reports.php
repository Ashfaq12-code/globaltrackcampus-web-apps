<?php
/**
 * GlobeTrek Adventures - Admin Sales & Financial Reports
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('admin');
$user = current_user();

// CSV Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=globetrek_sales_report_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Booking Ref', 'Customer Name', 'Customer Email', 'Tour Package', 'Destination', 'Travel Date', 'Travelers', 'Base Price', 'Hotel Surcharge', 'Transport Surcharge', 'Addons Price', 'Total Amount', 'Payment Status', 'Booking Status', 'Date Created']);

    $rows = db_fetch_all("
        SELECT b.booking_code, u.full_name, u.email, p.title, d.name as destination,
               b.travel_date, b.travelers_count, b.base_price, b.hotel_price, b.transport_price,
               b.addons_price, b.total_amount, b.payment_status, b.booking_status, b.created_at
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN tour_packages p ON b.package_id = p.id
        JOIN destinations d ON p.destination_id = d.id
        ORDER BY b.id DESC
    ");

    foreach ($rows as $r) {
        fputcsv($out, $r);
    }
    fclose($out);
    exit;
}

// Financial Metrics Breakdown
$grossRevenue = (float)db_fetch_val("SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'Paid'") ?: 0.00;
$confirmedTrips = (int)db_fetch_val("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('Confirmed', 'Completed')");
$avgOrderValue = (float)db_fetch_val("SELECT AVG(total_amount) FROM bookings WHERE payment_status = 'Paid'") ?: 0.00;
$totalCustomers = (int)db_fetch_val("SELECT COUNT(*) FROM users WHERE role = 'customer'");

// Destination Revenue Breakdown
$destRevenue = db_fetch_all("
    SELECT d.name as destination_name, 
           COUNT(b.id) as booking_count, 
           SUM(b.total_amount) as dest_revenue
    FROM destinations d
    JOIN tour_packages p ON p.destination_id = d.id
    LEFT JOIN bookings b ON b.package_id = p.id AND b.payment_status = 'Paid'
    GROUP BY d.id
    ORDER BY dest_revenue DESC
");

// Package Revenue Performance
$packageReports = db_fetch_all("
    SELECT p.title, p.category, d.name as destination_name,
           COUNT(b.id) as total_bookings,
           SUM(b.travelers_count) as total_guests,
           SUM(b.total_amount) as total_revenue
    FROM tour_packages p
    JOIN destinations d ON p.destination_id = d.id
    LEFT JOIN bookings b ON b.package_id = p.id AND b.payment_status = 'Paid'
    GROUP BY p.id
    ORDER BY total_revenue DESC
");

$pageTitle = 'Sales & Financial Reports | Admin Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Sales & Financial Reporting</h1>
        <p class="dashboard-subtitle">Real-time revenue metrics, regional market performance, and printable export summaries.</p>
      </div>
      <div style="display:flex; gap:0.8rem;">
        <a href="<?= BASE_URL ?>/admin/reports.php?export=csv" class="btn btn-accent">
          <i class="fa-solid fa-file-csv"></i> Export CSV Report
        </a>
        <button type="button" class="btn btn-outline" onclick="window.print()">
          <i class="fa-solid fa-print"></i> Print Report
        </button>
      </div>
    </div>

    <!-- Financial KPI Summary -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-dollar-sign"></i></div>
        <div class="stat-info">
          <span class="stat-label">Total Realized Revenue</span>
          <span class="stat-value"><?= format_price($grossRevenue) ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-chart-line"></i></div>
        <div class="stat-info">
          <span class="stat-label">Average Trip Value</span>
          <span class="stat-value"><?= format_price($avgOrderValue) ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-plane-departure"></i></div>
        <div class="stat-info">
          <span class="stat-label">Confirmed Expeditions</span>
          <span class="stat-value"><?= $confirmedTrips ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-users-viewfinder"></i></div>
        <div class="stat-info">
          <span class="stat-label">Total Customer Reach</span>
          <span class="stat-value"><?= $totalCustomers ?> Guests</span>
        </div>
      </div>
    </div>

    <!-- 2-Column: Destination Breakdown & Package Performance -->
    <div style="display:grid; grid-template-columns:1fr 1.6fr; gap:2rem; margin-bottom:2.5rem;">
      
      <!-- Regional Performance Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-map-location-dot" style="color:var(--primary);"></i> Regional Revenue Breakdown</h3>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Destination</th>
                  <th>Trips</th>
                  <th>Total Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($destRevenue as $dr): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($dr['destination_name']) ?></strong></td>
                    <td><?= (int)$dr['booking_count'] ?></td>
                    <td><strong style="color:var(--primary-dark);"><?= format_price($dr['dest_revenue'] ?: 0) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Package Detailed Performance Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-cubes" style="color:var(--accent);"></i> Package Popularity & Yield</h3>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Tour Package</th>
                  <th>Category</th>
                  <th>Total Guests</th>
                  <th>Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($packageReports as $pr): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($pr['title']) ?></strong></td>
                    <td><span class="status-pill status-in-progress"><?= htmlspecialchars($pr['category']) ?></span></td>
                    <td><?= (int)$pr['total_guests'] ?> pax</td>
                    <td><strong style="color:var(--primary-dark);"><?= format_price($pr['total_revenue'] ?: 0) ?></strong></td>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
