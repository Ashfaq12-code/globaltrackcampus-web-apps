<?php
/**
 * GlobeTrek Adventures - Customer Profile Management
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('customer');
$user = current_user();

$errors = [];
$successMsg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_info') {
            $fullName = clean($_POST['full_name'] ?? '');
            $phone = clean($_POST['phone'] ?? '');
            $address = clean($_POST['address'] ?? '');

            if (empty($fullName)) {
                $errors[] = 'Full name is required.';
            } else {
                db_query("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?", [$fullName, $phone, $address, $user['id']]);
                $_SESSION['full_name'] = $fullName;
                $_SESSION['phone'] = $phone;
                log_audit_action($user['id'], 'UPDATE_PROFILE', "Customer updated profile info");
                set_flash('success', 'Profile information updated successfully.');
                header('Location: ' . BASE_URL . '/customer/profile.php');
                exit;
            }
        } elseif ($action === 'change_password') {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            $userRow = db_fetch_one("SELECT password_hash FROM users WHERE id = ?", [$user['id']]);
            if (!password_verify($currentPass, $userRow['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($newPass) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } elseif ($newPass !== $confirmPass) {
                $errors[] = 'New passwords do not match.';
            } else {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                db_query("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $user['id']]);
                log_audit_action($user['id'], 'CHANGE_PASSWORD', "Customer changed account password");
                set_flash('success', 'Password updated successfully.');
                header('Location: ' . BASE_URL . '/customer/profile.php');
                exit;
            }
        }
    }
}

// Fetch Fresh User Data
$userData = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user['id']]);

$pageTitle = 'Profile Settings | GlobeTrek Adventures';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Account & Profile Settings</h1>
        <p class="dashboard-subtitle">Manage your personal information and account security.</p>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger" style="margin-bottom:1.5rem;">
        <ul style="margin-left:1.2rem; font-size:0.9rem;">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="grid-2">
      <!-- Profile Info -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-user-pen" style="color:var(--primary);"></i> Personal Information</h3>
        </div>
        <div class="card-body">
          <form action="<?= BASE_URL ?>/customer/profile.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="update_info">

            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($userData['full_name']) ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address (Immutable)</label>
              <input type="email" class="form-control" disabled value="<?= htmlspecialchars($userData['email']) ?>" style="background:#f1f5f9; cursor:not-allowed;">
            </div>

            <div class="form-group">
              <label class="form-label">Phone / WhatsApp</label>
              <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" placeholder="+1 555 1234">
            </div>

            <div class="form-group">
              <label class="form-label">Country / Address</label>
              <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($userData['address'] ?? '') ?>" placeholder="United Kingdom">
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
            </button>
          </form>
        </div>
      </div>

      <!-- Security / Change Password -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-lock" style="color:var(--accent);"></i> Change Password</h3>
        </div>
        <div class="card-body">
          <form action="<?= BASE_URL ?>/customer/profile.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group">
              <label class="form-label">New Password (Min 6 chars)</label>
              <input type="password" name="new_password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-accent">
              <i class="fa-solid fa-key"></i> Update Password
            </button>
          </form>
        </div>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
