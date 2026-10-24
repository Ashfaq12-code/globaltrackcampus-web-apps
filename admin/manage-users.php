<?php
/**
 * GlobeTrek Adventures - Admin Customer Directory Management
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('admin');
$user = current_user();

// Handle User Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $action = $_POST['action'] ?? '';
        if ($action === 'toggle_status') {
            $targetId = (int)$_POST['user_id'];
            $currentActive = (int)db_fetch_val("SELECT is_active FROM users WHERE id = ?", [$targetId]);
            $newActive = ($currentActive === 1) ? 0 : 1;
            db_query("UPDATE users SET is_active = ? WHERE id = ?", [$newActive, $targetId]);
            log_audit_action($user['id'], 'TOGGLE_CUSTOMER_STATUS', "Admin changed customer #{$targetId} status to {$newActive}");
            set_flash('info', 'Customer account status updated.');
            header('Location: ' . BASE_URL . '/admin/manage-users.php');
            exit;
        }
    }
}

// Search
$search = clean($_GET['search'] ?? '');
$sql = "
    SELECT u.*,
           (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as total_bookings,
           (SELECT SUM(total_amount) FROM bookings WHERE user_id = u.id AND payment_status = 'Paid') as total_spend,
           (SELECT COUNT(*) FROM queries WHERE user_id = u.id) as query_count
    FROM users u
    WHERE u.role = 'customer'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.address LIKE ?)";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
}

$sql .= " ORDER BY u.id DESC";
$customers = db_fetch_all($sql, $params);

$pageTitle = 'Customer Directory | Admin Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Customer Information Management</h1>
        <p class="dashboard-subtitle">Monitor customer registration, lifetime trip volume, total expenditure, and communication history.</p>
      </div>
    </div>

    <!-- Search Form -->
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-body" style="padding:1.2rem;">
        <form action="<?= BASE_URL ?>/admin/manage-users.php" method="GET" style="display:grid; grid-template-columns:1fr auto; gap:1rem; align-items:center;">
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search customers by name, email, phone, or country...">
          <button type="submit" class="btn btn-primary" style="height:44px;"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
      </div>
    </div>

    <!-- Customer Directory Table -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-address-book" style="color:var(--primary);"></i> Registered Customer Accounts (<?= count($customers) ?>)</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Customer ID</th>
                <th>Full Name</th>
                <th>Email Address</th>
                <th>Phone</th>
                <th>Country / Location</th>
                <th>Total Bookings</th>
                <th>Lifetime Spend</th>
                <th>Inquiries</th>
                <th>Account Status</th>
                <th>Toggle</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($customers as $c): ?>
                <tr>
                  <td>#<?= $c['id'] ?></td>
                  <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                  <td><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a></td>
                  <td><?= htmlspecialchars($c['phone'] ?: 'N/A') ?></td>
                  <td><?= htmlspecialchars($c['address'] ?: 'International') ?></td>
                  <td><span class="status-pill status-in-progress"><?= $c['total_bookings'] ?> trips</span></td>
                  <td><strong style="color:var(--primary-dark);"><?= format_price($c['total_spend'] ?: 0) ?></strong></td>
                  <td><span class="status-pill status-pending"><?= $c['query_count'] ?></span></td>
                  <td>
                    <span class="status-pill <?= $c['is_active'] ? 'status-active' : 'status-failed' ?>">
                      <?= $c['is_active'] ? 'Active' : 'Suspended' ?>
                    </span>
                  </td>
                  <td>
                    <form action="<?= BASE_URL ?>/admin/manage-users.php" method="POST" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                      <button type="submit" class="btn btn-outline btn-sm">
                        <i class="fa-solid <?= $c['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i>
                      </button>
                    </form>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
