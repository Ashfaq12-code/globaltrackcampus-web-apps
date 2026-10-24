<?php
/**
 * GlobeTrek Adventures - Travel Guides & Tips
 */
$pageTitle = 'Sri Lanka Travel Guides & Insider Tips | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';

$guides = db_fetch_all("SELECT * FROM travel_guides ORDER BY id ASC");
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:3rem 0;">
  <div class="container" style="text-align:center; max-width:760px; margin:0 auto;">
    <span class="section-tag">Essential Knowledge</span>
    <h1 style="font-size:2.5rem; margin-bottom:0.8rem;">Sri Lanka Travel Guides & Insider Advice</h1>
    <p style="color:var(--text-muted); font-size:1.05rem;">
      Practical insights on local visa protocols, scenic mountain train schedules, monsoon seasons, and cultural temple etiquette.
    </p>
  </div>
</div>

<div class="container" style="padding-top:3rem; padding-bottom:5rem;">
  <div class="grid-3">
    <?php foreach ($guides as $g): ?>
      <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
        <div style="height:200px; overflow:hidden; position:relative;">
          <img src="https://images.unsplash.com/photo-1546708973-b339540b5162?q=80&w=800&auto=format&fit=crop" alt="<?= htmlspecialchars($g['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
          <span style="position:absolute; top:0.8rem; left:0.8rem; background:rgba(15,23,42,0.85); color:#fff; font-size:0.75rem; font-weight:700; padding:0.2rem 0.6rem; border-radius:var(--radius-full);">
            <i class="fa-solid fa-clock"></i> <?= htmlspecialchars($g['read_time']) ?>
          </span>
        </div>

        <div style="padding:1.6rem; flex-grow:1; display:flex; flex-direction:column;">
          <span style="font-size:0.78rem; font-weight:700; color:var(--primary); text-transform:uppercase; margin-bottom:0.3rem;">
            <?= htmlspecialchars($g['category']) ?> &bull; <?= htmlspecialchars($g['destination']) ?>
          </span>
          <h3 style="font-size:1.2rem; margin-bottom:0.8rem; color:var(--secondary); line-height:1.35;"><?= htmlspecialchars($g['title']) ?></h3>
          <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.6; margin-bottom:1.2rem;">
            <?= htmlspecialchars($g['snippet']) ?>
          </p>
          <div style="background:var(--bg-main); padding:1rem; border-radius:var(--radius-sm); font-size:0.88rem; color:var(--text-main); margin-bottom:1.2rem; line-height:1.6; border-left:3px solid var(--primary);">
            <?= htmlspecialchars($g['content']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
