<?php
/**
 * GlobeTrek Adventures - Customer My Bookings
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('customer');
$user = current_user();

// Handle Booking Cancellation Action
if (isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $bookingId = (int)$_POST['booking_id'];
        $bk = db_fetch_one("SELECT * FROM bookings WHERE id = ? AND user_id = ?", [$bookingId, $user['id']]);
        if ($bk && ($bk['booking_status'] === 'Pending' || $bk['booking_status'] === 'Confirmed')) {
            db_query("UPDATE bookings SET booking_status = 'Cancelled' WHERE id = ?", [$bookingId]);
            log_audit_action($user['id'], 'CANCEL_BOOKING', "Customer cancelled booking #{$bk['booking_code']}");
            set_flash('info', "Booking #{$bk['booking_code']} has been cancelled.");
            header('Location: ' . BASE_URL . '/customer/my-bookings.php');
            exit;
        }
    }
}

// Handle Review Submission
if (isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $pkgId = (int)$_POST['package_id'];
        $rating = max(1, min(5, (int)$_POST['rating']));
        $comment = clean($_POST['comment'] ?? '');

        if (!empty($comment)) {
            db_insert("
                INSERT INTO reviews (package_id, user_id, rating, comment, is_approved, created_at)
                VALUES (?, ?, ?, ?, 1, datetime('now'))
            ", [$pkgId, $user['id'], $rating, $comment]);

            set_flash('success', 'Thank you for your review! It is now published.');
            header('Location: ' . BASE_URL . '/customer/my-bookings.php');
            exit;
        }
    }
}

$pageTitle = 'My Bookings | GlobeTrek Adventures';
require_once __DIR__ . '/../includes/header.php';

// Fetch Bookings with status filter
$statusFilter = clean($_GET['status'] ?? 'all');
$sql = "
    SELECT b.*, p.title as package_title, d.name as destination_name,
           a.name as hotel_name, t.vehicle_name
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.id
    JOIN destinations d ON p.destination_id = d.id
    LEFT JOIN accommodations a ON b.accommodation_id = a.id
    LEFT JOIN transport_options t ON b.transport_id = t.id
    WHERE b.user_id = ?
";
$params = [$user['id']];

if ($statusFilter !== 'all') {
    $sql .= " AND b.booking_status = ?";
    $params[] = ucfirst($statusFilter);
}

$sql .= " ORDER BY b.id DESC";
$bookings = db_fetch_all($sql, $params);
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">My Travel Bookings</h1>
        <p class="dashboard-subtitle">Track, view vouchers, and manage your holiday reservations.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/tours.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Book Another Tour</a>
      </div>
    </div>

    <!-- Status Tabs -->
    <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/customer/my-bookings.php?status=all" class="btn <?= ($statusFilter === 'all') ? 'btn-primary' : 'btn-outline' ?> btn-sm">All Bookings</a>
      <a href="<?= BASE_URL ?>/customer/my-bookings.php?status=confirmed" class="btn <?= ($statusFilter === 'confirmed') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Confirmed</a>
      <a href="<?= BASE_URL ?>/customer/my-bookings.php?status=in-progress" class="btn <?= ($statusFilter === 'in-progress') ? 'btn-primary' : 'btn-outline' ?> btn-sm">In-Progress</a>
      <a href="<?= BASE_URL ?>/customer/my-bookings.php?status=completed" class="btn <?= ($statusFilter === 'completed') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Completed</a>
      <a href="<?= BASE_URL ?>/customer/my-bookings.php?status=cancelled" class="btn <?= ($statusFilter === 'cancelled') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Cancelled</a>
    </div>

    <div class="card">
      <div class="card-body" style="padding:0;">
        <?php if (!empty($bookings)): ?>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Booking Ref</th>
                  <th>Tour Package</th>
                  <th>Departure Date</th>
                  <th>Travelers</th>
                  <th>Hotel Tier</th>
                  <th>Transport</th>
                  <th>Total Amount</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bookings as $bk): ?>
                  <tr>
                    <td><strong style="font-family:monospace;"><?= htmlspecialchars($bk['booking_code']) ?></strong></td>
                    <td>
                      <strong><?= htmlspecialchars($bk['package_title']) ?></strong><br>
                      <span style="font-size:0.8rem; color:var(--text-muted);"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($bk['destination_name']) ?></span>
                    </td>
                    <td><?= date('d M Y', strtotime($bk['travel_date'])) ?></td>
                    <td><?= $bk['travelers_count'] ?> pax</td>
                    <td><?= htmlspecialchars($bk['hotel_name'] ?: 'Standard Lodge') ?></td>
                    <td><?= htmlspecialchars($bk['vehicle_name'] ?: 'AC Sedan') ?></td>
                    <td><strong style="color:var(--primary-dark);"><?= format_price($bk['total_amount']) ?></strong></td>
                    <td>
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $bk['booking_status'])) ?>">
                        <?= htmlspecialchars($bk['booking_status']) ?>
                      </span>
                    </td>
                    <td>
                      <div style="display:flex; gap:0.4rem; align-items:center;">
                        <a href="<?= BASE_URL ?>/booking/confirmation.php?code=<?= urlencode($bk['booking_code']) ?>" class="btn btn-outline btn-sm" title="View Voucher">
                          <i class="fa-solid fa-ticket"></i> Voucher
                        </a>

                        <?php if ($bk['booking_status'] === 'Confirmed' || $bk['booking_status'] === 'Pending'): ?>
                          <form action="<?= BASE_URL ?>/customer/my-bookings.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="cancel_booking">
                            <input type="hidden" name="booking_id" value="<?= $bk['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Cancel Booking">
                              <i class="fa-solid fa-ban"></i>
                            </button>
                          </form>
                        <?php endif; ?>

                        <?php if ($bk['booking_status'] === 'Completed'): ?>
                          <button type="button" class="btn btn-primary btn-sm" onclick="openReviewModal(<?= $bk['package_id'] ?>, '<?= addslashes($bk['package_title']) ?>')">
                            <i class="fa-solid fa-star"></i> Review
                          </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div style="text-align:center; padding:3rem;">
            <p style="color:var(--text-muted); margin-bottom:1rem;">No bookings found in this view.</p>
            <a href="<?= BASE_URL ?>/tours.php" class="btn btn-primary">Browse All Tours</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>

<!-- Review Submission Modal -->
<div class="modal-backdrop" id="reviewModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title" id="reviewModalTitle">Leave a Review</h3>
      <button type="button" class="modal-close" onclick="closeModal('reviewModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/customer/my-bookings.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="submit_review">
      <input type="hidden" name="package_id" id="reviewPackageId" value="1">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Rating</label>
          <select name="rating" class="form-control" required>
            <option value="5">5 Stars - Outstanding Experience</option>
            <option value="4">4 Stars - Very Good</option>
            <option value="3">3 Stars - Average</option>
            <option value="2">2 Stars - Below Expectations</option>
            <option value="1">1 Star - Unsatisfactory</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Your Review & Feedback</label>
          <textarea name="comment" class="form-control" required placeholder="Describe your experience with the driver, accommodations, and scenery..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('reviewModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publish Review</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReviewModal(pkgId, pkgTitle) {
  document.getElementById('reviewPackageId').value = pkgId;
  document.getElementById('reviewModalTitle').textContent = 'Leave a Review for ' + pkgTitle;
  openModal('reviewModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
