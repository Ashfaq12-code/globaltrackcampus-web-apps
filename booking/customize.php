<?php
/**
 * GlobeTrek Adventures - Bespoke Custom Trip Builder
 */
$pageTitle = 'Customize Your Sri Lanka Trip | GlobeTrek Adventures';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../includes/header.php';

$destinations = db_fetch_all("SELECT * FROM destinations ORDER BY name ASC");
$accommodations = db_fetch_all("SELECT * FROM accommodations ORDER BY tier ASC");
$transports = db_fetch_all("SELECT * FROM transport_options ORDER BY price_per_day ASC");
$addons = db_fetch_all("SELECT * FROM package_addons ORDER BY price ASC");
$packages = db_fetch_all("SELECT id, title, base_price, duration_days, duration_nights FROM tour_packages WHERE status = 'Active'");
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:3rem 0;">
  <div class="container" style="text-align:center; max-width:760px; margin:0 auto;">
    <span class="section-tag">Bespoke Travel Planner</span>
    <h1 style="font-size:2.5rem; margin-bottom:0.8rem;">Design Your Tailor-Made Sri Lanka Holiday</h1>
    <p style="color:var(--text-muted); font-size:1.05rem;">
      Select an existing tour template or customize your destinations, vehicle, hotel class, and optional activities from scratch.
    </p>
  </div>
</div>

<div class="container" style="padding-top:3rem; padding-bottom:5rem;">
  <div class="booking-grid">
    
    <!-- Customizer Form -->
    <div class="booking-main-content">
      <h2 class="booking-section-title"><i class="fa-solid fa-wand-magic-sparkles" style="color:var(--accent);"></i> Custom Trip Configurator</h2>
      <p class="booking-section-desc">Select your desired journey preferences to calculate your instant quote.</p>

      <div class="form-group">
        <label class="form-label"><i class="fa-solid fa-compass"></i> Select Base Itinerary Template</label>
        <select id="customBasePackage" class="form-control" onchange="onPackageSelectChange()">
          <?php foreach ($packages as $p): ?>
            <option value="<?= $p['id'] ?>" data-price="<?= $p['base_price'] ?>" data-days="<?= $p['duration_days'] ?>" data-nights="<?= $p['duration_nights'] ?>">
              <?= htmlspecialchars($p['title']) ?> (<?= $p['duration_days'] ?>D/<?= $p['duration_nights'] ?>N &mdash; <?= format_price($p['base_price']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-calendar-days"></i> Travel Start Date</label>
          <input type="date" id="customDate" class="form-control" value="<?= date('Y-m-d', strtotime('+20 days')) ?>">
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-users"></i> Number of Guests</label>
          <input type="number" id="customTravelers" class="form-control" min="1" max="20" value="2" oninput="updateCustomTotal()">
        </div>
      </div>

      <!-- Hotel Tier -->
      <h3 style="font-size:1.15rem; margin:1.8rem 0 1rem; color:var(--secondary);"><i class="fa-solid fa-hotel" style="color:var(--primary);"></i> Preferred Accommodation Standard</h3>
      <div class="option-cards-grid">
        <?php foreach ($accommodations as $idx => $acc): ?>
          <label class="option-select-card custom-hotel <?= ($idx === 0) ? 'selected' : '' ?>">
            <div style="display:flex; align-items:center; gap:1rem;">
              <input type="radio" name="custom_hotel" value="<?= $acc['id'] ?>" data-rate="<?= $acc['price_per_night'] ?>" <?= ($idx === 0) ? 'checked' : '' ?> onchange="updateCustomTotal()">
              <div class="option-info">
                <span class="option-title"><?= htmlspecialchars($acc['name']) ?></span>
                <span class="option-meta"><?= htmlspecialchars($acc['amenities']) ?></span>
              </div>
            </div>
            <div class="option-price-tag"><?= ($acc['price_per_night'] == 0) ? 'Included' : '+' . format_price($acc['price_per_night']) . '/nt' ?></div>
          </label>
        <?php endforeach; ?>
      </div>

      <!-- Transport -->
      <h3 style="font-size:1.15rem; margin:1.8rem 0 1rem; color:var(--secondary);"><i class="fa-solid fa-car" style="color:var(--accent);"></i> Private Vehicle Choice</h3>
      <div class="option-cards-grid">
        <?php foreach ($transports as $idx => $t): ?>
          <label class="option-select-card custom-transport <?= ($idx === 0) ? 'selected' : '' ?>">
            <div style="display:flex; align-items:center; gap:1rem;">
              <input type="radio" name="custom_trans" value="<?= $t['id'] ?>" data-rate="<?= $t['price_per_day'] ?>" <?= ($idx === 0) ? 'checked' : '' ?> onchange="updateCustomTotal()">
              <div class="option-info">
                <span class="option-title"><?= htmlspecialchars($t['vehicle_name']) ?></span>
                <span class="option-meta"><?= htmlspecialchars($t['features']) ?></span>
              </div>
            </div>
            <div class="option-price-tag">+<?= format_price($t['price_per_day']) ?>/day</div>
          </label>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:2rem;">
        <button type="button" class="btn btn-accent btn-lg btn-block" onclick="proceedWithCustomTrip()">
          <i class="fa-solid fa-bolt"></i> Proceed to Book This Custom Itinerary
        </button>
      </div>
    </div>

    <!-- Right Live Calculator Box -->
    <div class="booking-summary-card">
      <h3 class="summary-title"><i class="fa-solid fa-calculator" style="color:var(--primary);"></i> Custom Estimate</h3>

      <div class="summary-item-row highlight">
        <span>Guests:</span>
        <span id="custGuests">2 Persons</span>
      </div>

      <div class="summary-item-row">
        <span>Package Base:</span>
        <span id="custBase">$500.00</span>
      </div>

      <div class="summary-item-row">
        <span>Hotel Upgrade:</span>
        <span id="custHotel">$0.00</span>
      </div>

      <div class="summary-item-row">
        <span>Vehicle Daily:</span>
        <span id="custTransport">$75.00</span>
      </div>

      <div class="summary-total-row">
        <span class="total-label">Estimated Total</span>
        <span class="total-amount" id="custTotal">$575.00</span>
      </div>

      <div style="margin-top:1.5rem; background:var(--bg-main); padding:1rem; border-radius:var(--radius-sm); font-size:0.85rem; color:var(--text-muted);">
        <i class="fa-solid fa-circle-check" style="color:var(--success);"></i> Free adjustments & concierge planning included.
      </div>
    </div>

  </div>
</div>

<script>
function onPackageSelectChange() {
  updateCustomTotal();
}

function updateCustomTotal() {
  const pkgSelect = document.getElementById('customBasePackage');
  const selectedOpt = pkgSelect.options[pkgSelect.selectedIndex];
  const basePrice = parseFloat(selectedOpt.dataset.price || 0);
  const days = parseInt(selectedOpt.dataset.days || 3);
  const nights = parseInt(selectedOpt.dataset.nights || 2);

  const travelers = Math.max(1, parseInt(document.getElementById('customTravelers').value || 1));

  let hotelRate = 0;
  document.querySelectorAll('input[name="custom_hotel"]').forEach(r => {
    if (r.checked) {
      hotelRate = parseFloat(r.dataset.rate || 0);
      r.closest('.option-select-card').classList.add('selected');
    } else {
      r.closest('.option-select-card').classList.remove('selected');
    }
  });

  let transRate = 0;
  document.querySelectorAll('input[name="custom_trans"]').forEach(r => {
    if (r.checked) {
      transRate = parseFloat(r.dataset.rate || 0);
      r.closest('.option-select-card').classList.add('selected');
    } else {
      r.closest('.option-select-card').classList.remove('selected');
    }
  });

  const baseTotal = basePrice * travelers;
  const hotelTotal = hotelRate * nights;
  const transTotal = transRate * days;
  const grandTotal = baseTotal + hotelTotal + transTotal;

  document.getElementById('custGuests').textContent = `${travelers} Person(s)`;
  document.getElementById('custBase').textContent = `$${baseTotal.toFixed(2)}`;
  document.getElementById('custHotel').textContent = `$${hotelTotal.toFixed(2)}`;
  document.getElementById('custTransport').textContent = `$${transTotal.toFixed(2)}`;
  document.getElementById('custTotal').textContent = `$${grandTotal.toFixed(2)}`;
}

function proceedWithCustomTrip() {
  const pkgId = document.getElementById('customBasePackage').value;
  window.location.href = '<?= BASE_URL ?>/booking/book.php?id=' + pkgId;
}

document.addEventListener('DOMContentLoaded', updateCustomTotal);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
