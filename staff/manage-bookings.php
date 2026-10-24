<?php
/**
 * GlobeTrek Adventures - Staff Booking Operations Management
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('staff');
$user = current_user();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Session expired. Please try again.');
    } else {
        $bookingId = (int)$_POST['booking_id'];
        $newStatus = clean($_POST['booking_status'] ?? '');
        $payStatus = clean($_POST['payment_status'] ?? '');

        if (!empty($newStatus)) {
            db_query("UPDATE bookings SET booking_status = ?, payment_status = ? WHERE id = ?", [$newStatus, $payStatus, $bookingId]);
            $bkCode = db_fetch_val("SELECT booking_code FROM bookings WHERE id = ?", [$bookingId]);
            log_audit_action($user['id'], 'UPDATE_BOOKING_STATUS', "Staff updated booking #{$bkCode} status to {$newStatus} / {$payStatus}");
            set_flash('success', "Booking #{$bkCode} status successfully updated.");
            header('Location: ' . BASE_URL . '/staff/manage-bookings.php');
            exit;
        }
    }
}

// Search & Filter
$statusFilter = clean($_GET['status'] ?? 'all');
$search = clean($_GET['search'] ?? '');

$sql = "
    SELECT b.*, p.title as package_title, d.name as destination_name,
           u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
           a.name as hotel_name, t.vehicle_name
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.id
    JOIN destinations d ON p.destination_id = d.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN accommodations a ON b.accommodation_id = a.id
    LEFT JOIN transport_options t ON b.transport_id = t.id
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
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY b.id DESC";
$bookings = db_fetch_all($sql, $params);

$pageTitle = 'Manage Bookings | Staff Desk';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Manage Customer Bookings</h1>
        <p class="dashboard-subtitle">Monitor travel schedules, update statuses, and verify payments.</p>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-body" style="padding:1.2rem;">
        <form action="<?= BASE_URL ?>/staff/manage-bookings.php" method="GET" style="display:grid; grid-template-columns:2fr 1.5fr auto; gap:1rem; align-items:center;">
          <div class="form-group" style="margin-bottom:0;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by Booking Ref, Customer Name, Email, or Package...">
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <select name="status" class="form-control">
              <option value="all">All Booking Statuses</option>
              <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
              <option value="confirmed" <?= ($statusFilter === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
              <option value="in-progress" <?= ($statusFilter === 'in-progress') ? 'selected' : '' ?>>In-Progress</option>
              <option value="completed" <?= ($statusFilter === 'completed') ? 'selected' : '' ?>>Completed</option>
              <option value="cancelled" <?= ($statusFilter === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary" style="height:44px;">
            <i class="fa-solid fa-filter"></i> Filter
          </button>
        </form>
      </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-list-check" style="color:var(--primary);"></i> Reservations List (<?= count($bookings) ?>)</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <?php if (!empty($bookings)): ?>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Booking Ref</th>
                  <th>Customer</th>
                  <th>Tour Package</th>
                  <th>Travel Date</th>
                  <th>Pax</th>
                  <th>Total</th>
                  <th>Payment</th>
                  <th>Booking Status</th>
                  <th>Update</th>
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
                      <span style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($bk['customer_phone'] ?: $bk['customer_email']) ?></span>
                    </td>
                    <td>
                      <?= htmlspecialchars($bk['package_title']) ?><br>
                      <span style="font-size:0.75rem; color:var(--text-muted);">
                        Hotel: <?= htmlspecialchars($bk['hotel_name'] ?: 'Standard') ?> &bull; Trans: <?= htmlspecialchars($bk['vehicle_name'] ?: 'Sedan') ?>
                      </span>
                    </td>
                    <td><strong><?= date('d M Y', strtotime($bk['travel_date'])) ?></strong></td>
                    <td><?= $bk['travelers_count'] ?></td>
                    <td><strong style="color:var(--primary-dark);"><?= format_price($bk['total_amount']) ?></strong></td>
                    <td>
                      <span class="status-pill status-<?= strtolower($bk['payment_status']) ?>">
                        <?= htmlspecialchars($bk['payment_status']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $bk['booking_status'])) ?>">
                        <?= htmlspecialchars($bk['booking_status']) ?>
                      </span>
                    </td>
                    <td>
                      <button type="button" class="btn btn-outline btn-sm" onclick='openEditBookingModal(<?= json_encode($bk) ?>)'>
                        <i class="fa-solid fa-pen-to-square"></i> Status
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div style="text-align:center; padding:3rem; color:var(--text-muted);">
            No reservations found matching your criteria.
          </div>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>

<!-- Edit Booking Status Modal -->
<div class="modal-backdrop" id="editBookingModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title" id="bookingModalRef">Update Booking Status</h3>
      <button type="button" class="modal-close" onclick="closeModal('editBookingModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/staff/manage-bookings.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="booking_id" id="modalBookingId">

      <div class="modal-body">
        <div style="background:var(--bg-main); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1.2rem; font-size:0.9rem;">
          <div style="margin-bottom:0.3rem;"><strong>Customer:</strong> <span id="modalCustomerName"></span></div>
          <div style="margin-bottom:0.3rem;"><strong>Package:</strong> <span id="modalPackageTitle"></span></div>
          <div><strong>Total:</strong> <span id="modalTotalAmount" style="color:var(--primary-dark); font-weight:700;"></span></div>
        </div>

        <div class="form-group">
          <label class="form-label">Booking Workflow Status</label>
          <select name="booking_status" id="modalBookingStatus" class="form-control" required>
            <option value="Pending">Pending (Awaiting Review)</option>
            <option value="Confirmed">Confirmed (Chauffeur & Hotel Allocated)</option>
            <option value="In-Progress">In-Progress (Guest Currently on Tour)</option>
            <option value="Completed">Completed (Tour Concluded)</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Payment Status</label>
          <select name="payment_status" id="modalPaymentStatus" class="form-control" required>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
            <option value="Refunded">Refunded</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('editBookingModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Update Reservation</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditBookingModal(bk) {
  document.getElementById('modalBookingId').value = bk.id;
  document.getElementById('bookingModalRef').textContent = 'Update Booking #' + bk.booking_code;
  document.getElementById('modalCustomerName').textContent = bk.customer_name + ' (' + bk.customer_email + ')';
  document.getElementById('modalPackageTitle').textContent = bk.package_title + ' (' + bk.travel_date + ')';
  document.getElementById('modalTotalAmount').textContent = '$' + parseFloat(bk.total_amount).toFixed(2);
  document.getElementById('modalBookingStatus').value = bk.booking_status;
  document.getElementById('modalPaymentStatus').value = bk.payment_status;
  openModal('editBookingModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
