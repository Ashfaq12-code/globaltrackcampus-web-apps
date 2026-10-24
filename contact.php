<?php
/**
 * GlobeTrek Adventures - Contact & Customer Query Submission
 */
$pageTitle = 'Contact Us & Send Query | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security verification failed. Please refresh and try again.';
    }

    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $subject = clean($_POST['subject'] ?? '');
    $message = clean($_POST['message'] ?? '');

    if (empty($name)) $errors[] = 'Please enter your full name.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (empty($subject)) $errors[] = 'Please enter an inquiry subject.';
    if (empty($message)) $errors[] = 'Please enter your message or question.';

    if (empty($errors)) {
        $userId = $user ? $user['id'] : null;
        $queryId = db_insert("
            INSERT INTO queries (user_id, guest_name, guest_email, subject, message, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'Pending', datetime('now'), datetime('now'))
        ", [$userId, $name, $email, $subject, $message]);

        log_audit_action($userId, 'SUBMIT_QUERY', "Customer query #{$queryId} submitted: {$subject}");

        set_flash('success', 'Thank you! Your query has been submitted successfully (Ticket #' . $queryId . '). Our staff team will review and respond promptly.');
        header('Location: ' . BASE_URL . '/contact.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:3rem 0;">
  <div class="container" style="text-align:center; max-width:760px; margin:0 auto;">
    <span class="section-tag">Get in Touch</span>
    <h1 style="font-size:2.5rem; margin-bottom:0.8rem;">Contact Travel Management</h1>
    <p style="color:var(--text-muted); font-size:1.05rem;">
      Have a question about a package, require bespoke group travel, or need assistance? Send us a message and our operations staff will assist you.
    </p>
  </div>
</div>

<div class="container" style="padding-top:3.5rem; padding-bottom:5rem;">
  <div style="display:grid; grid-template-columns:1fr 1.3fr; gap:3.5rem; align-items:start;">
    
    <!-- Contact Information Info Box -->
    <div>
      <h2 style="font-size:1.6rem; margin-bottom:1.2rem; color:var(--secondary);">Our Headquarters & Support Hubs</h2>
      <p style="color:var(--text-muted); line-height:1.7; margin-bottom:2rem;">
        Whether you are planning months in advance or currently traveling on one of our island tours, our dedicated support team is available 24/7.
      </p>

      <div style="display:flex; flex-direction:column; gap:1.5rem; margin-bottom:2.5rem;">
        <div style="display:flex; gap:1.2rem; align-items:flex-start;">
          <div style="width:48px; height:48px; border-radius:var(--radius-md); background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
            <i class="fa-solid fa-location-dot"></i>
          </div>
          <div>
            <div style="font-weight:700; color:var(--secondary); font-size:1.05rem;">Main Office</div>
            <div style="color:var(--text-muted); font-size:0.92rem;">Level 4, Galle Face Tower, Colombo 03, Sri Lanka</div>
          </div>
        </div>

        <div style="display:flex; gap:1.2rem; align-items:flex-start;">
          <div style="width:48px; height:48px; border-radius:var(--radius-md); background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
            <i class="fa-solid fa-phone"></i>
          </div>
          <div>
            <div style="font-weight:700; color:var(--secondary); font-size:1.05rem;">Phone & WhatsApp</div>
            <div style="color:var(--text-muted); font-size:0.92rem;">+94 11 234 5678 / +94 77 123 4567</div>
          </div>
        </div>

        <div style="display:flex; gap:1.2rem; align-items:flex-start;">
          <div style="width:48px; height:48px; border-radius:var(--radius-md); background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
            <i class="fa-solid fa-envelope"></i>
          </div>
          <div>
            <div style="font-weight:700; color:var(--secondary); font-size:1.05rem;">Email Inquiries</div>
            <div style="color:var(--text-muted); font-size:0.92rem;">info@globetrek.com / support@globetrek.com</div>
          </div>
        </div>
      </div>

      <div style="background:#fff; border-radius:var(--radius-md); border:1px solid var(--border); padding:1.5rem;">
        <h4 style="margin-bottom:0.5rem; font-size:1.05rem;"><i class="fa-solid fa-clock" style="color:var(--primary);"></i> Operations Hours</h4>
        <p style="font-size:0.9rem; color:var(--text-muted); line-height:1.6;">
          <strong>Monday &ndash; Sunday:</strong> 24 Hours Active Operations & Airport Dispatch. Customer desk responses within 2 hours.
        </p>
      </div>
    </div>

    <!-- Contact Form Box -->
    <div style="background:#fff; border-radius:var(--radius-lg); border:1px solid var(--border); padding:2.5rem; box-shadow:var(--shadow-md);">
      <h3 style="font-size:1.5rem; margin-bottom:0.5rem; color:var(--secondary);">Send a Customer Query</h3>
      <p style="color:var(--text-muted); font-size:0.92rem; margin-bottom:1.8rem;">
        Submit your question directly to our operations and staff management system.
      </p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
          <ul style="margin-left:1.2rem; font-size:0.9rem;">
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/contact.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Your Full Name <span style="color:var(--danger);">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? ($user['full_name'] ?? '')) ?>" required placeholder="John Doe">
          </div>

          <div class="form-group">
            <label class="form-label">Your Email Address <span style="color:var(--danger);">*</span></label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? ($user['email'] ?? '')) ?>" required placeholder="john@example.com">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Subject / Topic <span style="color:var(--danger);">*</span></label>
          <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required placeholder="e.g. Airport pickup for Ella package / Custom safari request">
        </div>

        <div class="form-group">
          <label class="form-label">Your Message / Query Details <span style="color:var(--danger);">*</span></label>
          <textarea name="message" class="form-control" style="min-height:140px;" required placeholder="Tell us how we can assist you..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
          <i class="fa-solid fa-paper-plane"></i> Submit Query to Management
        </button>
      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
