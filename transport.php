<?php
/**
 * GlobeTrek Adventures - Transportation Fleet
 */
$pageTitle = 'Transportation Fleet & Chauffeurs | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';

$vehicles = db_fetch_all("SELECT * FROM transport_options ORDER BY price_per_day ASC");
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:3rem 0;">
  <div class="container" style="text-align:center; max-width:760px; margin:0 auto;">
    <span class="section-tag">Travel in Comfort</span>
    <h1 style="font-size:2.5rem; margin-bottom:0.8rem;">Private Transport & Chauffeur Fleet</h1>
    <p style="color:var(--text-muted); font-size:1.05rem;">
      Fully air-conditioned, commercially insured modern vehicles operated by licensed, English-speaking tourist chauffeur guides.
    </p>
  </div>
</div>

<div class="container" style="padding-top:3rem; padding-bottom:5rem;">
  <div class="grid-4">
    <?php foreach ($vehicles as $v): ?>
      <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
        <div style="height:180px; overflow:hidden; position:relative; background:#e2e8f0;">
          <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?q=80&w=800&auto=format&fit=crop" alt="<?= htmlspecialchars($v['vehicle_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
          <span style="position:absolute; top:0.8rem; right:0.8rem; background:rgba(15,23,42,0.85); color:#fff; font-size:0.75rem; font-weight:700; padding:0.2rem 0.6rem; border-radius:var(--radius-full);">
            <i class="fa-solid fa-users"></i> Up to <?= $v['capacity'] ?> Seats
          </span>
        </div>

        <div style="padding:1.4rem; flex-grow:1; display:flex; flex-direction:column;">
          <span style="font-size:0.75rem; font-weight:700; color:var(--primary); text-transform:uppercase; margin-bottom:0.2rem;">
            <?= htmlspecialchars($v['vehicle_type']) ?>
          </span>
          <h3 style="font-size:1.15rem; margin-bottom:0.6rem; color:var(--secondary);"><?= htmlspecialchars($v['vehicle_name']) ?></h3>
          
          <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem; line-height:1.5; flex-grow:1;">
            <?= htmlspecialchars($v['features']) ?>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:0.8rem; margin-top:auto;">
            <div>
              <span style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Daily Rate</span>
              <div style="font-size:1.2rem; font-weight:800; color:var(--primary-dark);"><?= format_price($v['price_per_day']) ?> <span style="font-size:0.75rem; font-weight:400; color:var(--text-muted);">/ day</span></div>
            </div>
            <a href="<?= BASE_URL ?>/booking/customize.php" class="btn btn-outline btn-sm">Add to Trip</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top:3rem; background:#fff; border-radius:var(--radius-md); border:1px solid var(--border); padding:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">
    <div>
      <h3 style="margin-bottom:0.4rem;">Need a Custom Vehicle or Airport Transfer?</h3>
      <p style="color:var(--text-muted); font-size:0.95rem;">We provide point-to-point airport pickups, luxury wedding coaches, and private 4x4 safari convoys.</p>
    </div>
    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-primary"><i class="fa-solid fa-envelope"></i> Request Vehicle Quote</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
