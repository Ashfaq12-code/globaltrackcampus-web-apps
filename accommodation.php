<?php
/**
 * GlobeTrek Adventures - Accommodation & Hotel Partners
 */
$pageTitle = 'Partner Accommodations & Resorts | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';

$hotels = db_fetch_all("
    SELECT a.*, d.name as destination_name 
    FROM accommodations a 
    JOIN destinations d ON a.destination_id = d.id 
    ORDER BY a.tier ASC, a.price_per_night ASC
");
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:3rem 0;">
  <div class="container" style="text-align:center; max-width:760px; margin:0 auto;">
    <span class="section-tag">Handpicked Stays</span>
    <h1 style="font-size:2.5rem; margin-bottom:0.8rem;">Accommodations & Resort Partners</h1>
    <p style="color:var(--text-muted); font-size:1.05rem;">
      From charming mountain eco-lodges to 5-star colonial heritage palaces and oceanfront villas.
    </p>
  </div>
</div>

<div class="container" style="padding-top:3rem; padding-bottom:5rem;">
  <div class="grid-3">
    <?php foreach ($hotels as $h): ?>
      <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
        <div style="height:200px; overflow:hidden; position:relative;">
          <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=800&auto=format&fit=crop" alt="<?= htmlspecialchars($h['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
          <span style="position:absolute; top:1rem; right:1rem; background:var(--primary); color:#fff; font-size:0.75rem; font-weight:700; padding:0.25rem 0.65rem; border-radius:var(--radius-full);">
            <?= htmlspecialchars($h['tier']) ?>
          </span>
        </div>
        
        <div style="padding:1.5rem; flex-grow:1; display:flex; flex-direction:column;">
          <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.3rem;">
            <i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> <?= htmlspecialchars($h['destination_name']) ?>
          </div>
          <h3 style="font-size:1.2rem; margin-bottom:0.6rem; color:var(--secondary);"><?= htmlspecialchars($h['name']) ?></h3>
          <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.6; margin-bottom:1rem; flex-grow:1;">
            <?= htmlspecialchars($h['description']) ?>
          </p>

          <div style="background:var(--bg-main); padding:0.8rem; border-radius:var(--radius-sm); font-size:0.82rem; color:var(--text-main); margin-bottom:1.2rem;">
            <strong>Amenities:</strong> <?= htmlspecialchars($h['amenities']) ?>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:1rem; margin-top:auto;">
            <div>
              <span style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Rate</span>
              <div style="font-size:1.25rem; font-weight:800; color:var(--primary-dark);">
                <?= ($h['price_per_night'] == 0) ? 'Included in Base' : '+' . format_price($h['price_per_night']) . ' / night' ?>
              </div>
            </div>
            <a href="<?= BASE_URL ?>/booking/customize.php" class="btn btn-outline btn-sm">Select in Customizer</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
