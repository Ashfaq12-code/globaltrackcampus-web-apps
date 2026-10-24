<?php
/**
 * GlobeTrek Adventures - Customer Support Inquiries & Tickets
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('customer');
$user = current_user();

// Handle new query creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_query') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $subject = clean($_POST['subject'] ?? '');
        $message = clean($_POST['message'] ?? '');

        if (!empty($subject) && !empty($message)) {
            $qid = db_insert("
                INSERT INTO queries (user_id, guest_name, guest_email, subject, message, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'Pending', datetime('now'), datetime('now'))
            ", [$user['id'], $user['full_name'], $user['email'], $subject, $message]);

            log_audit_action($user['id'], 'SUBMIT_QUERY', "Customer created support query #{$qid}");
            set_flash('success', 'Your inquiry ticket has been submitted to travel management.');
            header('Location: ' . BASE_URL . '/customer/queries.php');
            exit;
        }
    }
}

$pageTitle = 'Support Inquiries | GlobeTrek Adventures';
require_once __DIR__ . '/../includes/header.php';

// Fetch Customer Queries
$queries = db_fetch_all("
    SELECT q.*, staff.full_name as staff_name 
    FROM queries q 
    LEFT JOIN users staff ON q.replied_by = staff.id 
    WHERE q.user_id = ? 
    ORDER BY q.id DESC
", [$user['id']]);
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Customer Support & Queries</h1>
        <p class="dashboard-subtitle">Communicate directly with our travel directors and operational staff.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary" onclick="openModal('newQueryModal')">
          <i class="fa-solid fa-paper-plane"></i> Submit New Query
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-comments" style="color:var(--primary);"></i> My Ticket Threads (<?= count($queries) ?>)</h3>
      </div>

      <div class="card-body">
        <?php if (!empty($queries)): ?>
          <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <?php foreach ($queries as $q): ?>
              <div style="background:var(--bg-main); border:1px solid var(--border); border-radius:var(--radius-md); padding:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.8rem; flex-wrap:wrap; gap:0.5rem;">
                  <div>
                    <span style="font-size:0.8rem; color:var(--text-muted); font-family:monospace;">Ticket #<?= $q['id'] ?></span>
                    <h4 style="font-size:1.15rem; color:var(--secondary); margin-top:0.2rem;"><?= htmlspecialchars($q['subject']) ?></h4>
                  </div>
                  <div>
                    <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $q['status'])) ?>">
                      <?= htmlspecialchars($q['status']) ?>
                    </span>
                  </div>
                </div>

                <!-- Customer Message -->
                <div style="background:#fff; border:1px solid var(--border); border-radius:var(--radius-sm); padding:1rem; margin-bottom:1rem; font-size:0.92rem; line-height:1.6;">
                  <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.4rem;">
                    <strong>You</strong> wrote on <?= date('d M Y, h:i A', strtotime($q['created_at'])) ?>:
                  </div>
                  <?= nl2br(htmlspecialchars($q['message'])) ?>
                </div>

                <!-- Staff Response (if any) -->
                <?php if (!empty($q['staff_reply'])): ?>
                  <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-sm); padding:1.2rem; font-size:0.92rem; line-height:1.6;">
                    <div style="font-size:0.8rem; color:#166534; font-weight:700; margin-bottom:0.4rem; display:flex; align-items:center; gap:0.4rem;">
                      <i class="fa-solid fa-user-tie"></i> Official Response from GlobeTrek Staff (<?= htmlspecialchars($q['staff_name'] ?: 'Operations Desk') ?>)
                      <span style="font-weight:400; color:var(--text-muted); font-size:0.75rem; margin-left:auto;"><?= date('d M Y, h:i A', strtotime($q['updated_at'])) ?></span>
                    </div>
                    <div style="color:var(--text-main);">
                      <?= nl2br(htmlspecialchars($q['staff_reply'])) ?>
                    </div>
                  </div>
                <?php else: ?>
                  <div style="font-size:0.85rem; color:var(--text-muted); font-style:italic;">
                    <i class="fa-solid fa-clock"></i> Our operations staff is reviewing your query. You will receive a reply here shortly.
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div style="text-align:center; padding:3rem;">
            <p style="color:var(--text-muted); margin-bottom:1rem;">You have not submitted any queries yet.</p>
            <button type="button" class="btn btn-primary" onclick="openModal('newQueryModal')">
              <i class="fa-solid fa-paper-plane"></i> Submit Your First Query
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>

<!-- New Query Modal -->
<div class="modal-backdrop" id="newQueryModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">Submit Inquiry to Travel Staff</h3>
      <button type="button" class="modal-close" onclick="closeModal('newQueryModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/customer/queries.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="new_query">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Subject / Topic <span style="color:var(--danger);">*</span></label>
          <input type="text" name="subject" class="form-control" required placeholder="e.g. Upgrade hotel room on Ella tour / Train tickets">
        </div>

        <div class="form-group">
          <label class="form-label">Query Message <span style="color:var(--danger);">*</span></label>
          <textarea name="message" class="form-control" style="min-height:130px;" required placeholder="Please explain your question or request in detail..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('newQueryModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send Query</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
