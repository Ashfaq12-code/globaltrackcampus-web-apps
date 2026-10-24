<?php
/**
 * GlobeTrek Adventures - Staff Customer Query & Ticketing Desk
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('staff');
$user = current_user();

// Handle Staff Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Session expired. Please try again.');
    } else {
        $queryId = (int)$_POST['query_id'];
        $reply = clean($_POST['staff_reply'] ?? '');
        $newStatus = clean($_POST['status'] ?? 'Answered');

        if (!empty($reply)) {
            db_query("
                UPDATE queries SET
                    staff_reply = ?,
                    status = ?,
                    replied_by = ?,
                    updated_at = datetime('now')
                WHERE id = ?
            ", [$reply, $newStatus, $user['id'], $queryId]);

            log_audit_action($user['id'], 'REPLY_QUERY', "Staff replied to ticket #{$queryId}");
            set_flash('success', "Response submitted for ticket #{$queryId}.");
            header('Location: ' . BASE_URL . '/staff/customer-queries.php');
            exit;
        }
    }
}

// Fetch all queries
$statusFilter = clean($_GET['status'] ?? 'all');
$sql = "
    SELECT q.*, staff.full_name as replied_by_name, u.email as registered_email
    FROM queries q
    LEFT JOIN users staff ON q.replied_by = staff.id
    LEFT JOIN users u ON q.user_id = u.id
    WHERE 1=1
";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND q.status = ?";
    $params[] = ucfirst($statusFilter);
}

$sql .= " ORDER BY CASE WHEN q.status = 'Pending' THEN 1 WHEN q.status = 'In Review' THEN 2 ELSE 3 END, q.id DESC";
$queries = db_fetch_all($sql, $params);

$pageTitle = 'Customer Support Desk | Staff Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Customer Support Desk & Inquiries</h1>
        <p class="dashboard-subtitle">Answer customer queries, provide custom tour quotes, and resolve guest requests.</p>
      </div>
    </div>

    <!-- Status Tabs -->
    <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/staff/customer-queries.php?status=all" class="btn <?= ($statusFilter === 'all') ? 'btn-primary' : 'btn-outline' ?> btn-sm">All Tickets</a>
      <a href="<?= BASE_URL ?>/staff/customer-queries.php?status=pending" class="btn <?= ($statusFilter === 'pending') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Pending Action</a>
      <a href="<?= BASE_URL ?>/staff/customer-queries.php?status=in-review" class="btn <?= ($statusFilter === 'in-review') ? 'btn-primary' : 'btn-outline' ?> btn-sm">In Review</a>
      <a href="<?= BASE_URL ?>/staff/customer-queries.php?status=answered" class="btn <?= ($statusFilter === 'answered') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Answered</a>
    </div>

    <!-- Queries Grid / Accordion -->
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
      <?php if (!empty($queries)): ?>
        <?php foreach ($queries as $q): ?>
          <div class="card" style="margin-bottom:0;">
            <div class="card-header" style="background:#f8fafc;">
              <div style="display:flex; align-items:center; gap:0.8rem; flex-wrap:wrap;">
                <strong style="font-family:monospace; font-size:0.95rem;">Ticket #<?= $q['id'] ?></strong>
                <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $q['status'])) ?>">
                  <?= htmlspecialchars($q['status']) ?>
                </span>
                <span style="font-weight:700; color:var(--secondary); font-size:1.05rem;"><?= htmlspecialchars($q['subject']) ?></span>
              </div>
              <div style="font-size:0.82rem; color:var(--text-muted);">
                <i class="fa-solid fa-calendar"></i> <?= date('d M Y, h:i A', strtotime($q['created_at'])) ?>
              </div>
            </div>

            <div class="card-body">
              <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:2rem; margin-bottom:1.2rem;">
                <div>
                  <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.3rem;">
                    <strong>Guest Details:</strong> <?= htmlspecialchars($q['guest_name']) ?> &bull; <a href="mailto:<?= htmlspecialchars($q['guest_email']) ?>"><?= htmlspecialchars($q['guest_email']) ?></a>
                    <?= $q['user_id'] ? ' <span style="color:var(--success); font-weight:700;">(Registered Customer)</span>' : ' <span style="color:var(--text-muted);">(Guest)</span>' ?>
                  </div>

                  <div style="background:var(--bg-main); padding:1rem; border-radius:var(--radius-sm); font-size:0.95rem; line-height:1.6; border-left:3px solid var(--primary); margin-top:0.6rem;">
                    <?= nl2br(htmlspecialchars($q['message'])) ?>
                  </div>
                </div>

                <div>
                  <?php if (!empty($q['staff_reply'])): ?>
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-sm); padding:1rem; font-size:0.9rem;">
                      <div style="font-weight:700; color:#166534; font-size:0.82rem; margin-bottom:0.4rem;">
                        <i class="fa-solid fa-check"></i> Answered by <?= htmlspecialchars($q['replied_by_name'] ?: 'Staff') ?>
                        <span style="font-weight:400; color:var(--text-muted); font-size:0.75rem; float:right;"><?= date('d M, h:i A', strtotime($q['updated_at'])) ?></span>
                      </div>
                      <div style="color:var(--text-main); line-height:1.5;">
                        <?= nl2br(htmlspecialchars($q['staff_reply'])) ?>
                      </div>
                    </div>
                  <?php else: ?>
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:var(--radius-sm); padding:1rem; text-align:center;">
                      <p style="font-size:0.88rem; color:#92400e; margin-bottom:0.8rem;">This ticket is awaiting staff response.</p>
                      <button type="button" class="btn btn-primary btn-sm" onclick='openReplyModal(<?= json_encode($q) ?>)'>
                        <i class="fa-solid fa-reply"></i> Write Response
                      </button>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <?php if (!empty($q['staff_reply'])): ?>
                <div style="display:flex; justify-content:flex-end;">
                  <button type="button" class="btn btn-outline btn-sm" onclick='openReplyModal(<?= json_encode($q) ?>)'>
                    <i class="fa-solid fa-pen-to-square"></i> Edit Response
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="card">
          <div class="card-body" style="text-align:center; padding:3rem; color:var(--text-muted);">
            No customer inquiries found in this category.
          </div>
        </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<!-- Reply Modal -->
<div class="modal-backdrop" id="replyModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title" id="replyModalTitle">Answer Customer Query</h3>
      <button type="button" class="modal-close" onclick="closeModal('replyModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/staff/customer-queries.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="query_id" id="replyQueryId">

      <div class="modal-body">
        <div style="background:var(--bg-main); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1.2rem; font-size:0.88rem;">
          <div style="margin-bottom:0.3rem;"><strong>Customer:</strong> <span id="replyGuestName"></span></div>
          <div style="margin-bottom:0.3rem;"><strong>Subject:</strong> <span id="replySubject"></span></div>
          <div style="color:var(--text-muted); font-style:italic;" id="replyGuestMsg"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Official Staff Reply <span style="color:var(--danger);">*</span></label>
          <textarea name="staff_reply" id="replyContent" class="form-control" style="min-height:140px;" required placeholder="Dear traveler, thank you for reaching out..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Ticket Status</label>
          <select name="status" id="replyStatus" class="form-control">
            <option value="Answered">Answered (Resolved)</option>
            <option value="In Review">In Review (Ongoing Communication)</option>
            <option value="Closed">Closed</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('replyModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send Reply</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReplyModal(q) {
  document.getElementById('replyQueryId').value = q.id;
  document.getElementById('replyModalTitle').textContent = 'Answer Ticket #' + q.id;
  document.getElementById('replyGuestName').textContent = q.guest_name + ' (' + q.guest_email + ')';
  document.getElementById('replySubject').textContent = q.subject;
  document.getElementById('replyGuestMsg').textContent = '"' + q.message + '"';
  document.getElementById('replyContent').value = q.staff_reply || '';
  document.getElementById('replyStatus').value = (q.status === 'Pending') ? 'Answered' : q.status;
  openModal('replyModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
