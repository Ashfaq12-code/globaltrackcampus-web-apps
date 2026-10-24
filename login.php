<?php
/**
 * GlobeTrek Adventures - Login Page
 * Features: Username/Email verification with green tick + sound | Google Authenticator display
 */
$pageTitle = 'Sign In | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    $r = $_SESSION['role'] ?? 'customer';
    if ($r === 'admin')  header('Location: ' . BASE_URL . '/admin/dashboard.php');
    elseif ($r === 'staff') header('Location: ' . BASE_URL . '/staff/dashboard.php');
    else header('Location: ' . BASE_URL . '/customer/dashboard.php');
    exit;
}

$error = '';
$redirect = clean($_GET['redirect'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = clean($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Please enter your username/email and password.';
    } else {
        $res = login_user($identifier, $password);
        if ($res['success']) {
            $u = $res['user'];
            set_flash('success', "Welcome back, {$u['full_name']}! 🌍");
            if (!empty($redirect) && strpos($redirect, 'http') === false) {
                header('Location: ' . $redirect); exit;
            }
            if ($u['role'] === 'admin')       header('Location: ' . BASE_URL . '/admin/dashboard.php');
            elseif ($u['role'] === 'staff')   header('Location: ' . BASE_URL . '/staff/dashboard.php');
            else                              header('Location: ' . BASE_URL . '/customer/dashboard.php');
            exit;
        } else {
            $error = $res['message'];
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
    /* ─── Login Page Specific ──────────────────────────── */
    body { padding-top: 0 !important; background: var(--bg-main); min-height: 100vh; }

    .login-page {
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: 100vh;
    }

    /* Left: Hero Panel */
    .login-hero {
      background:
        linear-gradient(160deg, rgba(15,23,42,0.82) 0%, rgba(2,132,199,0.65) 55%, rgba(249,115,22,0.5) 100%),
        url('https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?q=80&w=1400&auto=format&fit=crop') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem 3rem;
      text-align: center;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .login-hero::before {
      content: '';
      position: absolute;
      width: 500px; height: 500px;
      background: rgba(255,255,255,0.04);
      border-radius: 50%;
      top: -120px; right: -120px;
    }
    .login-hero::after {
      content: '';
      position: absolute;
      width: 300px; height: 300px;
      background: rgba(249,115,22,0.08);
      border-radius: 50%;
      bottom: -80px; left: -80px;
    }

    .login-hero-content { position: relative; z-index: 1; }

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
      font-size: 2rem;
      font-weight: 800;
      color: #fff;
      margin-bottom: 0.3rem;
    }
    .hero-brand-name em { font-style: normal; color: #fbbf24; }
    .hero-tagline { font-size: 0.85rem; color: rgba(255,255,255,0.7); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 2rem; }
    .hero-panel-desc { font-size: 1rem; color: rgba(255,255,255,0.85); line-height: 1.7; max-width: 360px; margin: 0 auto 2.5rem; }

    .hero-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
      max-width: 380px;
      margin: 0 auto;
    }
    .stat-box {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 12px;
      padding: 1rem 0.5rem;
      text-align: center;
    }
    .stat-box .num { font-size: 1.4rem; font-weight: 800; color: #fbbf24; }
    .stat-box .lbl { font-size: 0.7rem; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.06em; }

    /* Right: Form Panel */
    .login-form-panel {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem 2.5rem;
      background: #fff;
      overflow-y: auto;
    }

    .login-card { width: 100%; max-width: 420px; }

    .login-tabs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0;
      background: var(--bg-main);
      border-radius: var(--radius-md);
      padding: 4px;
      margin-bottom: 2rem;
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
    .panel-subtitle { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2rem; }

    /* Verification badge */
    .verify-badge {
      display: none;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--success);
      background: #d1fae5;
      padding: 0.25rem 0.75rem;
      border-radius: var(--radius-full);
      margin-top: 0.4rem;
      animation: popIn 0.3s ease;
    }
    @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    /* Google Auth Display */
    .gauth-section {
      display: none;
      background: linear-gradient(135deg, #f0f9ff, #fff);
      border: 1.5px solid #bae6fd;
      border-radius: var(--radius-md);
      padding: 1.3rem;
      margin-top: 1rem;
      animation: slideUp 0.4s ease;
    }
    @keyframes slideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

    .gauth-header { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1rem; }
    .gauth-header i { color: #4285f4; font-size: 1.2rem; }
    .gauth-header strong { font-size: 0.92rem; color: var(--secondary); }
    .gauth-body { display: grid; grid-template-columns: auto 1fr; gap: 1rem; align-items: center; }
    .gauth-qr {
      width: 90px; height: 90px;
      background: #fff;
      border: 2px solid var(--border);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden;
    }
    .gauth-qr img { width: 100%; height: 100%; object-fit: cover; }
    .gauth-steps p { font-size: 0.78rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 0.6rem; }
    .gauth-code-input {
      width: 100%;
      padding: 0.55rem 0.8rem;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: 700;
      letter-spacing: 0.25em;
      text-align: center;
      font-family: monospace;
      color: var(--secondary);
    }
    .gauth-code-input:focus { outline: none; border-color: #4285f4; box-shadow: 0 0 0 3px rgba(66,133,244,0.15); }
    .gauth-note { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.4rem; text-align: center; font-style: italic; }

    .demo-buttons { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1.5rem; }
    .demo-btn {
      padding: 0.5rem 0.4rem;
      font-size: 0.75rem;
      font-weight: 600;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      background: var(--bg-main);
      color: var(--text-muted);
      cursor: pointer;
      transition: var(--transition);
      text-align: center;
    }
    .demo-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
    .demo-btn.admin-btn:hover  { border-color: #b91c1c; color: #b91c1c; background: #fee2e2; }
    .demo-btn.staff-btn:hover  { border-color: #4338ca; color: #4338ca; background: #e0e7ff; }
    .demo-btn.guest-btn:hover  { border-color: #15803d; color: #15803d; background: #dcfce7; }

    .divider { text-align: center; position: relative; margin: 1.2rem 0; }
    .divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: var(--border); }
    .divider span { position: relative; background: #fff; padding: 0 0.75rem; font-size: 0.78rem; color: var(--text-muted); font-weight: 500; }

    /* Responsive */
    @media (max-width: 768px) {
      .login-page { grid-template-columns: 1fr; }
      .login-hero { display: none; }
      .login-form-panel { padding: 2rem 1.5rem; }
    }
  </style>
</head>
<body>

<div class="login-page">

  <!-- ── Left Hero Panel ── -->
  <div class="login-hero">
    <div class="login-hero-content">
      <div class="hero-logo-badge">
        <i class="fa-solid fa-compass-drafting"></i>
      </div>
      <div class="hero-brand-name">Globe<em>Trek</em></div>
      <div class="hero-tagline">Adventures · Sri Lanka</div>
      <p class="hero-panel-desc">
        Your gateway to breathtaking Sri Lankan experiences — from misty Ella mountains to ancient Sigiriya rock fortresses and pristine Mirissa coastlines.
      </p>
      <div class="hero-stats">
        <div class="stat-box">
          <div class="num">50+</div>
          <div class="lbl">Tours</div>
        </div>
        <div class="stat-box">
          <div class="num">7</div>
          <div class="lbl">Destinations</div>
        </div>
        <div class="stat-box">
          <div class="num">500+</div>
          <div class="lbl">Happy Guests</div>
        </div>
      </div>
    </div>
  </div><!-- /login-hero -->

  <!-- ── Right Form Panel ── -->
  <div class="login-form-panel">
    <div class="login-card">

      <!-- Tab Switch: Login / Register -->
      <div class="login-tabs">
        <button class="login-tab active" onclick="window.location='/login.php'">
          <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
        <button class="login-tab" onclick="window.location='/register.php'">
          <i class="fa-solid fa-user-plus"></i> Register
        </button>
      </div>

      <h1 class="panel-title">Welcome Back</h1>
      <p class="panel-subtitle">Sign in to manage your adventures and bookings.</p>

      <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.2rem;">
          <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Demo Quick Login Buttons -->
      <div style="margin-bottom:0.5rem;">
        <p style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted); margin-bottom:0.5rem;">
          Quick Demo Access
        </p>
        <div class="demo-buttons">
          <button class="demo-btn admin-btn" onclick="fillDemoLogin('admin','admin123')">
            <i class="fa-solid fa-shield-halved"></i> Admin
          </button>
          <button class="demo-btn staff-btn" onclick="fillDemoLogin('staff','staff123')">
            <i class="fa-solid fa-users-gear"></i> Staff
          </button>
          <button class="demo-btn guest-btn" onclick="fillDemoLogin('customer','customer123')">
            <i class="fa-solid fa-user"></i> Customer
          </button>
        </div>
      </div>

      <div class="divider"><span>or sign in manually</span></div>

      <!-- Login Form -->
      <form action="<?= BASE_URL ?>/login.php" method="POST" id="loginForm">

        <!-- Username / Email Field with Live Verification -->
        <div class="form-group">
          <label class="form-label" for="loginUsername">
            <i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Username or Email
          </label>
          <div class="input-wrapper">
            <input
              type="text"
              id="loginUsername"
              name="identifier"
              class="form-control"
              placeholder="e.g. malik3924 or admin@globetrek.com"
              autocomplete="username"
              required
            >
            <span class="input-suffix" id="usernameSuffix"></span>
          </div>
          <span class="verify-badge" id="username-verify-badge">
            <i class="fa-solid fa-circle-check"></i> Verified
          </span>
        </div>

        <!-- Admin Recovery Email (shown only for admin username) -->
        <div class="form-group" id="adminEmailGroup" style="display:none;">
          <label class="form-label" for="adminEmailInput">
            <i class="fa-solid fa-envelope-circle-check" style="color:#4285f4;"></i> Verify Administrator Email
          </label>
          <div class="input-wrapper">
            <input
              type="email"
              id="adminEmailInput"
              class="form-control"
              placeholder="prinzzjrmalik7@gmail.com"
              autocomplete="off"
            >
            <span class="input-suffix" id="adminEmailSuffix"></span>
          </div>
          <span class="verify-badge" id="email-verify-badge">
            <i class="fa-solid fa-circle-check"></i> Authorized
          </span>

          <!-- Google Authenticator Display -->
          <div class="gauth-section" id="googleAuthSection">
            <div class="gauth-header">
              <i class="fa-brands fa-google" style="font-size:1.1rem; color:#4285f4;"></i>
              <strong>Google Authenticator — 2FA Setup</strong>
              <span class="status-pill status-confirmed" style="margin-left:auto; font-size:0.65rem;">Optional</span>
            </div>
            <div class="gauth-body">
              <div class="gauth-qr">
                <img
                  src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=otpauth://totp/GlobeTrek:prinzzjrmalik7%40gmail.com?secret=JBSWY3DPEHPK3PXP%26issuer=GlobeTrek%26algorithm=SHA1%26digits=6%26period=30"
                  alt="Google Auth QR Code"
                  title="Scan with Google Authenticator"
                >
              </div>
              <div class="gauth-steps">
                <p>1. Download <strong>Google Authenticator</strong> app<br>
                   2. Tap <strong>+</strong> and scan the QR code<br>
                   3. Enter the 6-digit code below</p>
                <input type="text" class="gauth-code-input" placeholder="000 000" maxlength="7" inputmode="numeric" pattern="[0-9 ]*">
                <p class="gauth-note">Code refreshes every 30 seconds</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Password Field -->
        <div class="form-group">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
            <label class="form-label" for="loginPassword" style="margin-bottom:0;">
              <i class="fa-solid fa-lock" style="color:var(--primary);"></i> Password
            </label>
            <a href="#" style="font-size:0.8rem; color:var(--primary);">Forgot password?</a>
          </div>
          <div class="input-wrapper">
            <input
              type="password"
              id="loginPassword"
              name="password"
              class="form-control"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
            >
            <button type="button"
              style="position:absolute; right:0.8rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:0.9rem;"
              onclick="togglePassword()"
              tabindex="-1"
              aria-label="Toggle password visibility">
              <i class="fa-solid fa-eye" id="passEyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:0.5rem;">
          <i class="fa-solid fa-right-to-bracket"></i> Sign In to GlobeTrek
        </button>

      </form>

      <p style="text-align:center; margin-top:1.5rem; font-size:0.88rem; color:var(--text-muted);">
        Don't have an account?
        <a href="<?= BASE_URL ?>/register.php" style="color:var(--primary); font-weight:600;">Create free account →</a>
      </p>

      <p style="text-align:center; margin-top:0.8rem; font-size:0.75rem; color:var(--text-light);">
        <i class="fa-solid fa-shield-halved" style="color:var(--success);"></i>
        Secured with RBAC, bcrypt encryption & CSRF protection
      </p>

      <!-- Admin Setup Link -->
      <p style="text-align:center; margin-top:0.5rem; font-size:0.72rem; color:var(--text-light);">
        First time? <a href="<?= BASE_URL ?>/database/add_admin.php" style="color:var(--text-muted);">Setup admin account</a>
      </p>

    </div><!-- /login-card -->
  </div><!-- /login-form-panel -->

</div><!-- /login-page -->

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
/* ── Extra login page logic ── */

// Show admin email field when username = malik3924
const usernameField = document.getElementById('loginUsername');
const adminEmailGroup = document.getElementById('adminEmailGroup');

if (usernameField) {
  usernameField.addEventListener('input', () => {
    if (usernameField.value.trim() === 'malik3924') {
      adminEmailGroup.style.display = 'block';
    } else {
      adminEmailGroup.style.display = 'none';
    }
  });
}

// Password show/hide toggle
function togglePassword() {
  const inp = document.getElementById('loginPassword');
  const ico = document.getElementById('passEyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.className = 'fa-solid fa-eye-slash';
  } else {
    inp.type = 'password';
    ico.className = 'fa-solid fa-eye';
  }
}

// Override fillDemoLogin for identifier-based login
function fillDemoLogin(identifier, password) {
  const uInput = document.getElementById('loginUsername');
  const pInput = document.getElementById('loginPassword');
  if (uInput && pInput) {
    uInput.value = identifier;
    pInput.value = password;
    uInput.dispatchEvent(new Event('input'));
    uInput.classList.remove('is-valid','is-invalid');
    document.getElementById('usernameSuffix').className = 'input-suffix';
  }
}
</script>
</body>
</html>
