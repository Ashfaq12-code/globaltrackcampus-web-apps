<?php
/**
 * GlobeTrek Adventures - Customer Registration Page
 * Aesthetic Split Hero/Form Layout with Real-time Validation
 */
$pageTitle = 'Create Free Account | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/customer/dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = clean($_POST['full_name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = clean($_POST['phone'] ?? '');
    $address = clean($_POST['address'] ?? '');

    if (empty($fullName)) $errors[] = 'Please enter your full name.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $reg = register_customer($fullName, $email, $password, $phone, $address);
        if ($reg['success']) {
            login_user($email, $password);
            set_flash('success', "Welcome to GlobeTrek Adventures, {$fullName}! Your account has been registered.");
            header('Location: ' . BASE_URL . '/customer/dashboard.php');
            exit;
        } else {
            $errors[] = $reg['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <style>
    body { padding-top: 0 !important; background: var(--bg-main); min-height: 100vh; }

    .register-page {
      display: grid;
      grid-template-columns: 1fr 1.15fr;
      min-height: 100vh;
    }

    /* Left: Hero Panel */
    .register-hero {
      background:
        linear-gradient(160deg, rgba(15,23,42,0.82) 0%, rgba(2,132,199,0.65) 55%, rgba(249,115,22,0.5) 100%),
        url('https://images.unsplash.com/photo-1546708973-b339540b5162?q=80&w=1400&auto=format&fit=crop') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3.5rem 3rem;
      text-align: center;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .register-hero::before {
      content: '';
      position: absolute;
      width: 500px; height: 500px;
      background: rgba(255,255,255,0.04);
      border-radius: 50%;
      top: -120px; right: -120px;
    }
    .register-hero::after {
      content: '';
      position: absolute;
      width: 300px; height: 300px;
      background: rgba(249,115,22,0.08);
      border-radius: 50%;
      bottom: -80px; left: -80px;
    }

    .register-hero-content { position: relative; z-index: 1; max-width: 440px; }

    .hero-logo-badge {
      width: 70px; height: 70px;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255,255,255,0.3);
      border-radius: 18px;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem;
      color: #fff;
      margin: 0 auto 1.2rem;
    }

    .hero-brand-name {
      font-family: 'Poppins', sans-serif;
      font-size: 2.1rem;
      font-weight: 800;
      color: #fff;
      margin-bottom: 0.3rem;
    }
    .hero-brand-name em { font-style: normal; color: #fbbf24; }
    .hero-tagline { font-size: 0.85rem; color: rgba(255,255,255,0.7); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 2rem; }
    
    .benefits-list {
      text-align: left;
      margin: 2rem 0;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .benefit-item {
      display: flex;
      align-items: center;
      gap: 0.8rem;
      font-size: 0.95rem;
      color: rgba(255,255,255,0.9);
      background: rgba(255,255,255,0.08);
      padding: 0.65rem 1rem;
      border-radius: 10px;
      backdrop-filter: blur(4px);
    }
    .benefit-item i { color: #fbbf24; font-size: 1.1rem; }

    /* Right: Form Panel */
    .register-form-panel {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem 3.5rem;
      background: #fff;
      overflow-y: auto;
    }

    .register-card { width: 100%; max-width: 480px; }

    .login-tabs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0;
      background: var(--bg-main);
      border-radius: var(--radius-md);
      padding: 4px;
      margin-bottom: 1.8rem;
    }
    .login-tab {
      padding: 0.55rem;
      font-size: 0.88rem;
      font-weight: 600;
      color: var(--text-muted);
      border: none;
      background: none;
      border-radius: calc(var(--radius-md) - 3px);
      cursor: pointer;
      transition: var(--transition);
    }
    .login-tab.active { background: #fff; color: var(--primary); box-shadow: var(--shadow-sm); }

    .panel-title { font-size: 1.6rem; font-weight: 800; color: var(--secondary); margin-bottom: 0.3rem; }
    .panel-subtitle { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.8rem; }

    @media (max-width: 900px) {
      .register-page { grid-template-columns: 1fr; }
      .register-hero { display: none; }
      .register-form-panel { padding: 2rem 1.5rem; }
    }
  </style>
</head>
<body>

<div class="register-page">

  <!-- ── Left Hero Panel ── -->
  <div class="register-hero">
    <div class="register-hero-content">
      <div class="hero-logo-badge">
        <i class="fa-solid fa-compass-drafting"></i>
      </div>
      <div class="hero-brand-name">Globe<em>Trek</em></div>
      <div class="hero-tagline">Adventures · Sri Lanka</div>

      <div class="benefits-list">
        <div class="benefit-item">
          <i class="fa-solid fa-circle-check"></i>
          <span>Instant booking with live confirmation vouchers</span>
        </div>
        <div class="benefit-item">
          <i class="fa-solid fa-sliders"></i>
          <span>Interactive trip customization & real-time pricing</span>
        </div>
        <div class="benefit-item">
          <i class="fa-solid fa-headset"></i>
          <span>Direct 24/7 support desk with operations staff</span>
        </div>
        <div class="benefit-item">
          <i class="fa-solid fa-shield-halved"></i>
          <span>Verified chauffeur drivers & eco-certified resorts</span>
        </div>
      </div>

      <p style="font-size:0.85rem; color:rgba(255,255,255,0.7); margin-top:1rem;">
        Join over 500+ happy global travelers exploring Sri Lanka.
      </p>
    </div>
  </div><!-- /register-hero -->

  <!-- ── Right Form Panel ── -->
  <div class="register-form-panel">
    <div class="register-card">

      <!-- Tab Switch: Login / Register -->
      <div class="login-tabs">
        <button class="login-tab" onclick="window.location='/login.php'">
          <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
        <button class="login-tab active" onclick="window.location='/register.php'">
          <i class="fa-solid fa-user-plus"></i> Create Account
        </button>
      </div>

      <h1 class="panel-title">Start Your Journey</h1>
      <p class="panel-subtitle">Create a free customer account in seconds.</p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
          <ul style="margin-left:1.2rem; font-size:0.88rem;">
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/register.php" method="POST">
        
        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-user" style="color:var(--primary);"></i> Full Name <span style="color:var(--danger);">*</span></label>
          <input type="text" name="full_name" class="form-control" required placeholder="e.g. Priyantha Silva" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-envelope" style="color:var(--primary);"></i> Email Address <span style="color:var(--danger);">*</span></label>
          <input type="email" name="email" class="form-control" required placeholder="e.g. yourname@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-lock" style="color:var(--primary);"></i> Password <span style="color:var(--danger);">*</span></label>
            <input type="password" name="password" id="regPassword" class="form-control" required placeholder="Min. 6 characters">
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-check-double" style="color:var(--primary);"></i> Confirm Password <span style="color:var(--danger);">*</span></label>
            <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control" required placeholder="Repeat password">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-phone" style="color:var(--primary);"></i> Contact Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+94 77 123 4567" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-earth-americas" style="color:var(--primary);"></i> Country / Location</label>
            <input type="text" name="address" class="form-control" placeholder="United Kingdom / Sri Lanka" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:0.8rem;">
          <i class="fa-solid fa-user-check"></i> Create My Account
        </button>

      </form>

      <p style="text-align:center; margin-top:1.5rem; font-size:0.88rem; color:var(--text-muted);">
        Already registered?
        <a href="<?= BASE_URL ?>/login.php" style="color:var(--primary); font-weight:600;">Sign In Here →</a>
      </p>

      <p style="text-align:center; margin-top:0.8rem; font-size:0.75rem; color:var(--text-light);">
        <i class="fa-solid fa-shield-halved" style="color:var(--success);"></i>
        Your data is encrypted and protected under our privacy policy.
      </p>

    </div><!-- /register-card -->
  </div><!-- /register-form-panel -->

</div><!-- /register-page -->

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
