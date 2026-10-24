<?php
/**
 * GlobeTrek Adventures - Admin Security, Audit Logs & System Management
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('admin');
$user = current_user();

// Handle Database Reset / Re-seed Action (for university grading test)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reseed_db') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        // Execute init_db.php internally
        include __DIR__ . '/../database/init_db.php';
        log_audit_action($user['id'], 'DATABASE_RESET', 'Administrator reset and re-seeded the database');
        set_flash('success', 'Database has been successfully re-initialized and freshly seeded with demo records!');
        header('Location: ' . BASE_URL . '/admin/security.php');
        exit;
    }
}

// Fetch Audit Logs
$logs = db_fetch_all("
    SELECT a.*, u.full_name, u.email, u.role
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.id DESC
    LIMIT 50
");

// System Health Checks
$dbSize = file_exists(DB_FILE) ? round(filesize(DB_FILE) / 1024, 2) . ' KB' : 'N/A';
$phpVersion = PHP_VERSION;
$pdoDrivers = implode(', ', PDO::getAvailableDrivers());

$pageTitle = 'Security & Audit Logs | Admin Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">System Security & Governance Audit</h1>
        <p class="dashboard-subtitle">Monitor operational audit trails, track login events, verify database integrity, and run system maintenance.</p>
      </div>
    </div>

    <!-- Security Status Grid -->
    <div class="stats-grid" style="margin-bottom:2rem;">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-lock"></i></div>
        <div class="stat-info">
          <span class="stat-label">Security Protocol</span>
          <span class="stat-value" style="font-size:1.2rem; color:var(--success);">RBAC & CSRF Active</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-database"></i></div>
        <div class="stat-info">
          <span class="stat-label">Database Storage</span>
          <span class="stat-value" style="font-size:1.2rem; color:var(--primary);"><?= $dbSize ?> (SQLite PDO)</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-server"></i></div>
        <div class="stat-info">
          <span class="stat-label">PHP Engine</span>
          <span class="stat-value" style="font-size:1.2rem; color:var(--secondary);"><?= $phpVersion ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-clock-rotate-left"></i></div>
        <div class="stat-info">
          <span class="stat-label">Logged Audit Events</span>
          <span class="stat-value" style="font-size:1.2rem; color:#7c3aed;"><?= count($logs) ?> Events</span>
        </div>
      </div>
    </div>

    <!-- Reset / Examination Tool Box -->
    <div class="card" style="border:1px solid #fde68a; background:#fffbeb; margin-bottom:2rem;">
      <div class="card-body" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem;">
        <div>
          <h3 style="color:#92400e; font-size:1.15rem; margin-bottom:0.3rem;"><i class="fa-solid fa-triangle-exclamation"></i> Examiner Database Reset & Seeder Tool</h3>
          <p style="color:#b45309; font-size:0.9rem; margin-bottom:0;">
            Use this tool to reset all tables and repopulate authentic Sri Lankan demo data, bookings, users, and staff accounts.
          </p>
        </div>
        <form action="<?= BASE_URL ?>/admin/security.php" method="POST" onsubmit="return confirm('Warning: This will re-seed the entire database to default demo state. Proceed?');">
          <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
          <input type="hidden" name="action" value="reseed_db">
          <button type="submit" class="btn btn-accent"><i class="fa-solid fa-rotate"></i> Reset & Re-Seed Database</button>
        </form>
      </div>
    </div>

    <!-- Audit Log Trail Table -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-list-ol" style="color:var(--primary);"></i> Real-Time Audit Log Trail</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Event ID</th>
                <th>Action Type</th>
                <th>User / Initiator</th>
                <th>Role</th>
                <th>Activity Details</th>
                <th>IP Address</th>
                <th>Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
                <tr>
                  <td>#<?= $l['id'] ?></td>
                  <td>
                    <span class="role-tag <?= htmlspecialchars($l['role'] ?: 'customer') ?>">
                      <?= htmlspecialchars($l['action']) ?>
                    </span>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($l['full_name'] ?: 'Guest / Visitor') ?></strong><br>
                    <span style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($l['email'] ?: 'Unauthenticated') ?></span>
                  </td>
                  <td><span class="status-pill status-in-progress"><?= ucfirst($l['role'] ?: 'guest') ?></span></td>
                  <td style="font-size:0.88rem; color:var(--text-main);"><?= htmlspecialchars($l['details']) ?></td>
                  <td><code style="font-size:0.8rem; background:var(--bg-main); padding:0.15rem 0.4rem; border-radius:4px;"><?= htmlspecialchars($l['ip_address'] ?: '127.0.0.1') ?></code></td>
                  <td style="font-size:0.8rem; color:var(--text-muted);"><?= date('Y-m-d H:i:s', strtotime($l['created_at'])) ?></td>
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
