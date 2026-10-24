<?php
/**
 * GlobeTrek Adventures - Admin Booking Oversight
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('admin');
$user = current_user();

// Handle Admin Overrides
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $bookingId = (int)$_POST['booking_id'];
        $bStatus = clean($_POST['booking_status'] ?? '');
        $pStatus = clean($_POST['payment_status'] ?? '');

        if (!empty($bStatus)) {
            db_query("UPDATE bookings SET booking_status = ?, payment_status = ? WHERE id = ?", [$bStatus, $pStatus, $bookingId]);
            $bkCode = db_fetch_val("SELECT booking_code FROM bookings WHERE id = ?", [$bookingId]);
            log_audit_action($user['id'], 'ADMIN_OVERRIDE_BOOKING', "Admin override for booking #{$bkCode} to {$bStatus}/{$pStatus}");
            set_flash('success', "Booking #{$bkCode} status updated by Administrator.");
            header('Location: ' . BASE_URL . '/admin/manage-bookings.php');
            exit;
        }
    }
}

// Search & Filter
$statusFilter = clean($_GET['status'] ?? 'all');
$search = clean($_GET['search'] ?? '');

$sql = "
    SELECT b.*, p.title as package_title, d.name as destination_name,
           u.full_name as customer_name, u.email as customer_email,
           pay.transaction_ref, pay.payment_method
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.id
    JOIN destinations d ON p.destination_id = d.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    WHERE 1=1
";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND b.booking_status = ?";
    $params[] = ucfirst($statusFilter);
}

if (!empty($search)) {
    $sql .= " AND (b.booking_code LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR p.title LIKE ?)";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
}

$sql .= " ORDER BY b.id DESC";
$bookings = db_fetch_all($sql, $params);

$pageTitle = 'All Bookings Oversight | Admin Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">System-Wide Booking Oversight</h1>
        <p class="dashboard-subtitle">Full administrative control over reservations, revenue attribution, and payment logs.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-primary"><i class="fa-solid fa-file-invoice-dollar"></i> Generate Financial Report</a>
      </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-body" style="padding:1.2rem;">
        <form action="<?= BASE_URL ?>/admin/manage-bookings.php" method="GET" style="display:grid; grid-template-columns:2fr 1.5fr auto; gap:1rem; align-items:center;">
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by Booking Ref, Customer Name, Email, or Package...">
          <select name="status" class="form-control">
            <option value="all">All Booking Statuses</option>
            <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= ($statusFilter === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
            <option value="in-progress" <?= ($statusFilter === 'in-progress') ? 'selected' : '' ?>>In-Progress</option>
            <option value="completed" <?= ($statusFilter === 'completed') ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= ($statusFilter === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
          </select>
          <button type="submit" class="btn btn-primary" style="height:44px;"><i class="fa-solid fa-filter"></i> Apply</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-database" style="color:var(--primary);"></i> Master Booking Log (<?= count($bookings) ?>)</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Booking Ref</th>
                <th>Customer</th>
                <th>Tour Package</th>
                <th>Travel Date</th>
                <th>Gross Total</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Admin Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $bk): ?>
                <tr>
                  <td>
                    <strong style="font-family:monospace;"><?= htmlspecialchars($bk['booking_code']) ?></strong><br>
                    <span style="font-size:0.75rem; color:var(--text-muted);"><?= date('d M Y', strtotime($bk['created_at'])) ?></span>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($bk['customer_name']) ?></strong><br>
                    <span style="font-size:0.78rem; color:var(--text-muted);"><?= htmlspecialchars($bk['customer_email']) ?></span>
                  </td>
                  <td>
                    <?= htmlspecialchars($bk['package_title']) ?><br>
                    <span style="font-size:0.75rem; color:var(--text-muted);"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($bk['destination_name']) ?> (<?= $bk['travelers_count'] ?> pax)</span>
                  </td>
                  <td><strong><?= date('d M Y', strtotime($bk['travel_date'])) ?></strong></td>
                  <td><strong style="color:var(--primary-dark); font-size:1.05rem;"><?= format_price($bk['total_amount']) ?></strong></td>
                  <td>
                    <span style="font-size:0.85rem; font-weight:600;"><?= htmlspecialchars($bk['payment_method'] ?: 'Card') ?></span><br>
                    <span class="status-pill status-<?= strtolower($bk['payment_status']) ?>" style="font-size:0.7rem;"><?= htmlspecialchars($bk['payment_status']) ?></span>
                  </td>
                  <td>
                    <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $bk['booking_status'])) ?>">
                      <?= htmlspecialchars($bk['booking_status']) ?>
                    </span>
                  </td>
                  <td>
                    <div style="display:flex; gap:0.4rem;">
                      <a href="<?= BASE_URL ?>/booking/confirmation.php?code=<?= urlencode($bk['booking_code']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Voucher">
                        <i class="fa-solid fa-ticket"></i>
                      </a>
                      <button type="button" class="btn btn-primary btn-sm" onclick='openAdminOverrideModal(<?= json_encode($bk) ?>)' title="Override Status">
                        <i class="fa-solid fa-sliders"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- Admin Override Modal -->
<div class="modal-backdrop" id="adminOverrideModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title" id="overrideModalTitle">Administrative Status Override</h3>
      <button type="button" class="modal-close" onclick="closeModal('adminOverrideModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/manage-bookings.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="booking_id" id="overrideBookingId">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Booking Lifecycle Status</label>
          <select name="booking_status" id="overrideBookingStatus" class="form-control">
            <option value="Pending">Pending</option>
            <option value="Confirmed">Confirmed</option>
            <option value="In-Progress">In-Progress</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Payment Gateway Status</label>
          <select name="payment_status" id="overridePaymentStatus" class="form-control">
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
            <option value="Refunded">Refunded</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('adminOverrideModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-shield-halved"></i> Save Override</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAdminOverrideModal(bk) {
  document.getElementById('overrideBookingId').value = bk.id;
  document.getElementById('overrideModalTitle').textContent = 'Admin Override: #' + bk.booking_code;
  document.getElementById('overrideBookingStatus').value = bk.booking_status;
  document.getElementById('overridePaymentStatus').value = bk.payment_status;
  openModal('adminOverrideModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
