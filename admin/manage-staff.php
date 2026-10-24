<?php
/**
 * GlobeTrek Adventures - Admin Staff Governance
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('admin');
$user = current_user();

$errors = [];

// Handle Staff CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create_staff') {
            $name = clean($_POST['full_name'] ?? '');
            $email = clean($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = clean($_POST['phone'] ?? '');
            $address = clean($_POST['address'] ?? '');

            if (empty($name)) $errors[] = 'Full name is required.';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
            if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

            $existing = db_fetch_one("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) $errors[] = 'An account with this email already exists.';

            if (empty($errors)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $newId = db_insert("
                    INSERT INTO users (full_name, email, password_hash, role, phone, address, is_active, created_at)
                    VALUES (?, ?, ?, 'staff', ?, ?, 1, datetime('now'))
                ", [$name, $email, $hash, $phone, $address]);

                log_audit_action($user['id'], 'CREATE_STAFF', "Admin created staff account #{$newId} ({$email})");
                set_flash('success', "Staff account for {$name} created successfully.");
                header('Location: ' . BASE_URL . '/admin/manage-staff.php');
                exit;
            }
        } elseif ($action === 'toggle_status') {
            $targetId = (int)$_POST['user_id'];
            if ($targetId !== (int)$user['id']) {
                $currentActive = (int)db_fetch_val("SELECT is_active FROM users WHERE id = ?", [$targetId]);
                $newActive = ($currentActive === 1) ? 0 : 1;
                db_query("UPDATE users SET is_active = ? WHERE id = ?", [$newActive, $targetId]);
                log_audit_action($user['id'], 'TOGGLE_USER_STATUS', "Admin toggled user #{$targetId} status to {$newActive}");
                set_flash('info', 'Staff account status updated.');
                header('Location: ' . BASE_URL . '/admin/manage-staff.php');
                exit;
            }
        } elseif ($action === 'delete_staff') {
            $targetId = (int)$_POST['user_id'];
            if ($targetId !== (int)$user['id']) {
                db_query("DELETE FROM users WHERE id = ? AND role = 'staff'", [$targetId]);
                log_audit_action($user['id'], 'DELETE_STAFF', "Admin deleted staff account #{$targetId}");
                set_flash('info', 'Staff account removed.');
                header('Location: ' . BASE_URL . '/admin/manage-staff.php');
                exit;
            }
        }
    }
}

// Fetch Staff & Admins
$staffMembers = db_fetch_all("
    SELECT u.*, 
           (SELECT COUNT(*) FROM queries WHERE replied_by = u.id) as queries_resolved
    FROM users u
    WHERE u.role IN ('staff', 'admin')
    ORDER BY u.role ASC, u.id ASC
");

$pageTitle = 'Manage Staff Accounts | Admin Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Staff & Personnel Administration</h1>
        <p class="dashboard-subtitle">Provision operational staff accounts, configure access privileges, and monitor operational activity.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary" onclick="openModal('addStaffModal')">
          <i class="fa-solid fa-user-plus"></i> Add New Staff Member
        </button>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul style="margin-left:1.2rem;">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-users-gear" style="color:var(--primary);"></i> Staff Accounts Directory (<?= count($staffMembers) ?>)</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Personnel Name</th>
                <th>Email Address</th>
                <th>Role</th>
                <th>Contact Phone</th>
                <th>Location / Hub</th>
                <th>Queries Resolved</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($staffMembers as $s): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($s['full_name']) ?></strong>
                    <?php if ($s['id'] == $user['id']): ?>
                      <span style="font-size:0.7rem; background:#fee2e2; color:#b91c1c; padding:0.1rem 0.4rem; border-radius:4px; font-weight:700; margin-left:0.3rem;">You</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($s['email']) ?></td>
                  <td>
                    <span class="role-tag <?= htmlspecialchars($s['role']) ?>">
                      <?= ucfirst($s['role']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($s['phone'] ?: 'N/A') ?></td>
                  <td><?= htmlspecialchars($s['address'] ?: 'Colombo HQ') ?></td>
                  <td><span class="status-pill status-in-progress"><?= $s['queries_resolved'] ?> tickets</span></td>
                  <td>
                    <span class="status-pill <?= $s['is_active'] ? 'status-active' : 'status-failed' ?>">
                      <?= $s['is_active'] ? 'Active' : 'Disabled' ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($s['id'] != $user['id']): ?>
                      <div style="display:flex; gap:0.4rem;">
                        <form action="<?= BASE_URL ?>/admin/manage-staff.php" method="POST" style="display:inline;">
                          <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                          <input type="hidden" name="action" value="toggle_status">
                          <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                          <button type="submit" class="btn btn-outline btn-sm" title="<?= $s['is_active'] ? 'Disable Account' : 'Enable Account' ?>">
                            <i class="fa-solid <?= $s['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                          </button>
                        </form>

                        <?php if ($s['role'] === 'staff'): ?>
                          <form action="<?= BASE_URL ?>/admin/manage-staff.php" method="POST" onsubmit="return confirm('Permanently remove this staff account?');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="delete_staff">
                            <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                              <i class="fa-solid fa-trash"></i>
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <span style="font-size:0.8rem; color:var(--text-light);">Current Session</span>
                    <?php endif; ?>
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

<!-- Add Staff Modal -->
<div class="modal-backdrop" id="addStaffModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">Provision New Staff Account</h3>
      <button type="button" class="modal-close" onclick="closeModal('addStaffModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/manage-staff.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="create_staff">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
          <input type="text" name="full_name" class="form-control" required placeholder="e.g. Priyantha Silva">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Work Email <span style="color:var(--danger);">*</span></label>
            <input type="email" name="email" class="form-control" required placeholder="priyantha@globetrek.com">
          </div>

          <div class="form-group">
            <label class="form-label">Temporary Password <span style="color:var(--danger);">*</span></label>
            <input type="password" name="password" class="form-control" required placeholder="Min 6 characters" value="staff123">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="+94 77 000 0000">
          </div>

          <div class="form-group">
            <label class="form-label">Station / Branch</label>
            <input type="text" name="address" class="form-control" placeholder="Kandy Hub / Ella Operations">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addStaffModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Create Account</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
