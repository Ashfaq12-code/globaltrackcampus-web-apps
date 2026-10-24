<?php
/**
 * GlobeTrek Adventures - Admin User Setup & Username Column Migration
 * Visit: http://localhost:8000/database/add_admin.php
 */
require_once __DIR__ . '/../config/db.php';

$log = [];

// Step 1: Add username column if not exists
try {
    db_query("ALTER TABLE users ADD COLUMN username VARCHAR(50)");
    $log[] = "✅ Added 'username' column to users table.";
} catch (Exception $e) {
    $log[] = "ℹ️ Username column already exists (skipped).";
}

// Step 2: Add admin_email column if not exists
try {
    db_query("ALTER TABLE users ADD COLUMN admin_email VARCHAR(255)");
    $log[] = "✅ Added 'admin_email' column.";
} catch (Exception $e) {
    $log[] = "ℹ️ Admin_email column already exists (skipped).";
}

// Step 3: Create or update the malik3924 admin account
$existing = db_fetch_one("SELECT id FROM users WHERE email = 'malik3924@globetrek.lk' OR username = 'malik3924'");
if (!$existing) {
    $hash = password_hash('GT@Admin2026!', PASSWORD_DEFAULT);
    $id = db_insert(
        "INSERT INTO users (full_name, email, username, admin_email, password_hash, role, phone, address, is_active, created_at)
         VALUES ('Administrator Malik', 'malik3924@globetrek.lk', 'malik3924', 'prinzzjrmalik7@gmail.com', ?, 'admin', '+94771234567', 'Colombo Headquarters, Sri Lanka', 1, datetime('now'))",
        [$hash]
    );
    $log[] = "✅ Admin account created: username=malik3924, email=malik3924@globetrek.lk, ID=#{$id}";
    $log[] = "🔐 Default password: GT@Admin2026! (Change this after first login)";
} else {
    // Update existing with username and admin_email
    $hash = password_hash('GT@Admin2026!', PASSWORD_DEFAULT);
    db_query(
        "UPDATE users SET username='malik3924', admin_email='prinzzjrmalik7@gmail.com', password_hash=?, role='admin', is_active=1 WHERE id=?",
        [$hash, $existing['id']]
    );
    $log[] = "✅ Admin account updated: username=malik3924, admin_email=prinzzjrmalik7@gmail.com";
    $log[] = "🔐 Password reset to: GT@Admin2026!";
}

// Step 4: Set usernames for existing demo accounts
$demos = [
    ['email' => 'admin@globetrek.com',    'username' => 'admin'],
    ['email' => 'staff@globetrek.com',    'username' => 'staff'],
    ['email' => 'customer@globetrek.com', 'username' => 'customer'],
];
foreach ($demos as $d) {
    db_query("UPDATE users SET username=? WHERE email=? AND (username IS NULL OR username='')", [$d['username'], $d['email']]);
}
$log[] = "✅ Demo account usernames set (admin / staff / customer).";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Setup | GlobeTrek</title>
<style>
  body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 2rem; }
  h2 { color: #38bdf8; margin-bottom: 1.5rem; }
  .log-line { padding: 0.5rem 0.8rem; margin: 0.3rem 0; background: rgba(255,255,255,0.05); border-radius: 6px; border-left: 3px solid #10b981; }
  .btn { display:inline-block; margin-top:1.5rem; padding:0.7rem 1.5rem; background:#0284c7; color:#fff; border-radius:8px; text-decoration:none; font-weight:700; }
  table { border-collapse: collapse; margin-top:1.5rem; width:100%; }
  th, td { padding: 0.6rem 1rem; border: 1px solid rgba(255,255,255,0.1); text-align:left; }
  th { background: rgba(2, 132, 199, 0.3); color: #38bdf8; }
</style>
</head>
<body>
  <h2>🌍 GlobeTrek Adventures - Admin Account Setup</h2>
  
  <?php foreach ($log as $line): ?>
    <div class="log-line"><?= htmlspecialchars($line) ?></div>
  <?php endforeach; ?>

  <table>
    <tr><th>Credential</th><th>Value</th></tr>
    <tr><td>Username</td><td><strong>malik3924</strong></td></tr>
    <tr><td>Email</td><td>malik3924@globetrek.lk</td></tr>
    <tr><td>Admin Email (Google Auth)</td><td>prinzzjrmalik7@gmail.com</td></tr>
    <tr><td>Password</td><td><strong>GT@Admin2026!</strong></td></tr>
    <tr><td>Role</td><td>Administrator</td></tr>
  </table>

  <a href="/login.php" class="btn">→ Go to Login Page</a>
</body>
</html>
