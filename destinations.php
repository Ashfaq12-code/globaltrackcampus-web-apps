<?php
/**
 * GlobeTrek Adventures - Destinations Explorer
 */
$pageTitle = 'Explore Sri Lanka Destinations | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';

$destinations = db_fetch_all("
    SELECT d.*, 
           (SELECT COUNT(*) FROM tour_packages WHERE destination_id = d.id AND status = 'Active') AS package_count
    FROM destinations d 
    ORDER BY d.id ASC
");
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:3rem 0;">
  <div class="container text-center" style="text-align:center; max-width:720px; margin:0 auto;">
    <span class="section-tag">Island Wonders</span>
    <h1 style="font-size:2.5rem; margin-bottom:0.8rem;">Explore Sri Lanka's Iconic Destinations</h1>
    <p style="color:var(--text-muted); font-size:1.05rem;">
      From the mist-shrouded peaks of Ella to ancient UNESCO rock citadels and golden southern whale coastlines.
    </p>
  </div>
</div>

<div class="container" style="padding-top:3.5rem; padding-bottom:5rem;">
  <div style="display:flex; flex-direction:column; gap:3rem;">
    <?php foreach ($destinations as $idx => $d): ?>
      <div style="display:grid; grid-template-columns:<?= ($idx % 2 === 0) ? '1.2fr 1fr' : '1fr 1.2fr' ?>; gap:2.5rem; align-items:center; background:#fff; border-radius:var(--radius-lg); border:1px solid var(--border); overflow:hidden; box-shadow:var(--shadow-sm);">
        
        <?php if ($idx % 2 === 0): ?>
          <div style="height:340px; overflow:hidden;">
            <img src="https://images.unsplash.com/photo-1546708973-b339540b5162?q=80&w=1000&auto=format&fit=crop" alt="<?= htmlspecialchars($d['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
          </div>
        <?php endif; ?>

        <div style="padding:2.5rem;">
          <div style="font-size:0.85rem; text-transform:uppercase; color:var(--primary); font-weight:700; margin-bottom:0.3rem;">
            <i class="fa-solid fa-map-location-dot"></i> <?= htmlspecialchars($d['region']) ?>
          </div>
          <h2 style="font-size:1.8rem; margin-bottom:0.8rem; color:var(--secondary);"><?= htmlspecialchars($d['name']) ?></h2>
          <p style="color:var(--text-main); font-size:0.95rem; line-height:1.7; margin-bottom:1.2rem;">
            <?= htmlspecialchars($d['description']) ?>
          </p>

          <div style="background:var(--bg-main); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1.5rem; font-size:0.88rem;">
            <div style="margin-bottom:0.3rem;"><strong><i class="fa-solid fa-camera" style="color:var(--accent);"></i> Top Attractions:</strong> <?= htmlspecialchars($d['highlights']) ?></div>
            <div><strong><i class="fa-solid fa-sun" style="color:var(--warning);"></i> Best Season:</strong> <?= htmlspecialchars($d['best_season']) ?></div>
          </div>

          <a href="<?= BASE_URL ?>/tours.php?dest=<?= urlencode($d['name']) ?>" class="btn btn-primary">
            <i class="fa-solid fa-compass"></i> View <?= $d['package_count'] ?> Packages in <?= htmlspecialchars($d['name']) ?>
          </a>
        </div>

        <?php if ($idx % 2 !== 0): ?>
          <div style="height:340px; overflow:hidden;">
            <img src="https://images.unsplash.com/photo-1546708973-b339540b5162?q=80&w=1000&auto=format&fit=crop" alt="<?= htmlspecialchars($d['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
          </div>
        <?php endif; ?>

      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
