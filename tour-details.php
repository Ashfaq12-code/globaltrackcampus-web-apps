<?php
/**
 * GlobeTrek Adventures - Tour Package Detailed Itinerary
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$packageId = (int)($_GET['id'] ?? 0);
if ($packageId <= 0) {
    set_flash('danger', 'Tour package not found.');
    header('Location: ' . BASE_URL . '/tours.php');
    exit;
}

// Fetch Package Details
$pkg = db_fetch_one("
    SELECT p.*, d.name AS destination_name, d.region AS destination_region, d.description AS destination_desc
    FROM tour_packages p
    JOIN destinations d ON p.destination_id = d.id
    WHERE p.id = ? AND p.status = 'Active'
", [$packageId]);

if (!$pkg) {
    set_flash('danger', 'Tour package not found or is currently inactive.');
    header('Location: ' . BASE_URL . '/tours.php');
    exit;
}

$pageTitle = $pkg['title'];
require_once __DIR__ . '/includes/header.php';

// Fetch Day-by-day Itinerary
$itineraries = db_fetch_all("
    SELECT * FROM package_itineraries 
    WHERE package_id = ? 
    ORDER BY day_number ASC
", [$packageId]);

// Fetch Accommodations for this destination
$accommodations = db_fetch_all("
    SELECT * FROM accommodations 
    WHERE destination_id = ? OR destination_id = 1
    ORDER BY price_per_night ASC
", [$pkg['destination_id']]);

// Fetch Transport Options
$transports = db_fetch_all("SELECT * FROM transport_options ORDER BY price_per_day ASC");

// Fetch Reviews
$reviews = db_fetch_all("
    SELECT r.*, u.full_name, u.avatar 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.package_id = ? AND r.is_approved = 1 
    ORDER BY r.id DESC
", [$packageId]);

$avgRating = db_fetch_val("SELECT AVG(rating) FROM reviews WHERE package_id = ? AND is_approved = 1", [$packageId]) ?: 5.0;
?>

<!-- Package Header Title & Breadcrumb -->
<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:2rem 0;">
  <div class="container">
    <div style="display:flex; align-items:center; gap:0.5rem; color:var(--text-muted); font-size:0.88rem; margin-bottom:0.8rem;">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
      <a href="<?= BASE_URL ?>/tours.php">Tours</a>
      <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
      <span style="color:var(--secondary); font-weight:600;"><?= htmlspecialchars($pkg['destination_name']) ?></span>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.5rem;">
      <div>
        <div style="display:flex; gap:0.6rem; margin-bottom:0.6rem; flex-wrap:wrap;">
          <span class="status-pill status-in-progress"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($pkg['destination_name']) ?></span>
          <span class="status-pill status-active"><i class="fa-solid fa-layer-group"></i> <?= htmlspecialchars($pkg['category']) ?></span>
          <span class="status-pill status-pending"><i class="fa-solid fa-person-hiking"></i> <?= htmlspecialchars($pkg['difficulty']) ?></span>
        </div>
        <h1 style="font-size:2.4rem; color:var(--secondary); line-height:1.2;"><?= htmlspecialchars($pkg['title']) ?></h1>
      </div>
      
      <div style="text-align:right;">
        <div style="font-size:0.85rem; color:var(--text-muted); text-transform:uppercase;">Price per person</div>
        <div style="font-size:2.2rem; font-weight:800; color:var(--primary-dark);"><?= format_price($pkg['base_price']) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="container" style="padding-top:2.5rem; padding-bottom:5rem;">
  <div style="display:grid; grid-template-columns:1fr 380px; gap:2.5rem; align-items:start;">
    
    <!-- Left Main Content -->
    <div>
      <!-- Hero Image -->
      <div style="border-radius:var(--radius-lg); overflow:hidden; height:400px; margin-bottom:2.5rem; box-shadow:var(--shadow-md);">
        <img src="https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?q=80&w=1200&auto=format&fit=crop" alt="<?= htmlspecialchars($pkg['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
      </div>

      <!-- Highlights Matrix -->
      <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1rem; background:#fff; padding:1.5rem; border-radius:var(--radius-md); border:1px solid var(--border); margin-bottom:2.5rem; text-align:center;">
        <div>
          <div style="font-size:1.4rem; color:var(--primary); margin-bottom:0.3rem;"><i class="fa-solid fa-clock"></i></div>
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Duration</div>
          <div style="font-weight:700; color:var(--secondary);"><?= $pkg['duration_days'] ?> Days / <?= $pkg['duration_nights'] ?> Nights</div>
        </div>
        <div>
          <div style="font-size:1.4rem; color:var(--accent); margin-bottom:0.3rem;"><i class="fa-solid fa-person-hiking"></i></div>
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Difficulty</div>
          <div style="font-weight:700; color:var(--secondary);"><?= htmlspecialchars($pkg['difficulty']) ?></div>
        </div>
        <div>
          <div style="font-size:1.4rem; color:var(--success); margin-bottom:0.3rem;"><i class="fa-solid fa-users"></i></div>
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Group Size</div>
          <div style="font-weight:700; color:var(--secondary);">1 - 12 Persons</div>
        </div>
        <div>
          <div style="font-size:1.4rem; color:var(--warning); margin-bottom:0.3rem;"><i class="fa-solid fa-star"></i></div>
          <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Rating</div>
          <div style="font-weight:700; color:var(--secondary);"><?= number_format($avgRating, 1) ?> / 5.0 (<?= count($reviews) ?>)</div>
        </div>
      </div>

      <!-- Tour Overview -->
      <div style="background:#fff; padding:2rem; border-radius:var(--radius-md); border:1px solid var(--border); margin-bottom:2.5rem;">
        <h2 style="font-size:1.5rem; margin-bottom:1rem;"><i class="fa-solid fa-book-open" style="color:var(--primary);"></i> Tour Overview</h2>
        <p style="color:var(--text-main); line-height:1.8; font-size:1.02rem; margin-bottom:1.5rem;">
          <?= nl2br(htmlspecialchars($pkg['full_desc'])) ?>
        </p>
        <div style="background:var(--bg-main); padding:1.2rem; border-radius:var(--radius-sm); border-left:4px solid var(--primary);">
          <strong>Region Highlights:</strong> <?= htmlspecialchars($pkg['destination_region']) ?> &mdash; <?= htmlspecialchars($pkg['destination_desc']) ?>
        </div>
      </div>

      <!-- Day-by-Day Itinerary -->
      <div style="background:#fff; padding:2rem; border-radius:var(--radius-md); border:1px solid var(--border); margin-bottom:2.5rem;">
        <h2 style="font-size:1.5rem; margin-bottom:1.5rem;"><i class="fa-solid fa-route" style="color:var(--primary);"></i> Detailed Itinerary</h2>
        
        <?php if (!empty($itineraries)): ?>
          <div style="display:flex; flex-direction:column; gap:1.5rem; position:relative; padding-left:1.5rem; border-left:2px solid var(--primary-light);">
            <?php foreach ($itineraries as $it): ?>
              <div style="position:relative;">
                <div style="position:absolute; left:-32px; top:0; width:22px; height:22px; border-radius:var(--radius-full); background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700;">
                  <?= $it['day_number'] ?>
                </div>
                <h3 style="font-size:1.15rem; color:var(--secondary); margin-bottom:0.4rem;">
                  Day <?= $it['day_number'] ?>: <?= htmlspecialchars($it['title']) ?>
                </h3>
                <p style="color:var(--text-muted); line-height:1.6; font-size:0.95rem;">
                  <?= htmlspecialchars($it['description']) ?>
                </p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="color:var(--text-muted);">Custom daily itinerary will be crafted by our travel specialists upon booking.</p>
        <?php endif; ?>
      </div>

      <!-- Inclusions & Exclusions -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2.5rem;">
        <div style="background:#fff; padding:1.8rem; border-radius:var(--radius-md); border:1px solid var(--border);">
          <h3 style="color:#16a34a; font-size:1.2rem; margin-bottom:1rem;"><i class="fa-solid fa-circle-check"></i> What's Included</h3>
          <ul style="list-style:none; display:flex; flex-direction:column; gap:0.6rem; font-size:0.92rem; color:var(--text-main);">
            <li><i class="fa-solid fa-check" style="color:#16a34a; margin-right:0.4rem;"></i> Handpicked Accommodation with breakfast</li>
            <li><i class="fa-solid fa-check" style="color:#16a34a; margin-right:0.4rem;"></i> Private AC Vehicle & licensed driver</li>
            <li><i class="fa-solid fa-check" style="color:#16a34a; margin-right:0.4rem;"></i> English-speaking certified mountain/cultural guide</li>
            <li><i class="fa-solid fa-check" style="color:#16a34a; margin-right:0.4rem;"></i> Fuel, highway tolls, and driver allowances</li>
            <li><i class="fa-solid fa-check" style="color:#16a34a; margin-right:0.4rem;"></i> 24/7 dedicated operational assistance</li>
          </ul>
        </div>

        <div style="background:#fff; padding:1.8rem; border-radius:var(--radius-md); border:1px solid var(--border);">
          <h3 style="color:#dc2626; font-size:1.2rem; margin-bottom:1rem;"><i class="fa-solid fa-circle-xmark"></i> What's Excluded</h3>
          <ul style="list-style:none; display:flex; flex-direction:column; gap:0.6rem; font-size:0.92rem; color:var(--text-main);">
            <li><i class="fa-solid fa-xmark" style="color:#dc2626; margin-right:0.4rem;"></i> International airfare and visa fees</li>
            <li><i class="fa-solid fa-xmark" style="color:#dc2626; margin-right:0.4rem;"></i> Personal expenses and alcoholic beverages</li>
            <li><i class="fa-solid fa-xmark" style="color:#dc2626; margin-right:0.4rem;"></i> Gratuities / tips for drivers & trackers</li>
            <li><i class="fa-solid fa-xmark" style="color:#dc2626; margin-right:0.4rem;"></i> Optional activity add-ons (selected at checkout)</li>
          </ul>
        </div>
      </div>

      <!-- Traveler Reviews -->
      <div style="background:#fff; padding:2rem; border-radius:var(--radius-md); border:1px solid var(--border);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
          <h2 style="font-size:1.4rem;"><i class="fa-solid fa-comments" style="color:var(--primary);"></i> Traveler Reviews (<?= count($reviews) ?>)</h2>
          <div style="color:var(--warning); font-size:1.1rem; font-weight:700;">
            <i class="fa-solid fa-star"></i> <?= number_format($avgRating, 1) ?> / 5.0
          </div>
        </div>

        <?php if (!empty($reviews)): ?>
          <div style="display:flex; flex-direction:column; gap:1.2rem;">
            <?php foreach ($reviews as $rev): ?>
              <div style="border-bottom:1px solid var(--border); padding-bottom:1.2rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                  <div style="font-weight:700; color:var(--secondary);"><?= htmlspecialchars($rev['full_name']) ?></div>
                  <div style="color:var(--warning); font-size:0.9rem;">
                    <?php for ($i=0; $i<$rev['rating']; $i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
                  </div>
                </div>
                <p style="color:var(--text-muted); font-size:0.92rem;"><?= htmlspecialchars($rev['comment']) ?></p>
                <div style="font-size:0.75rem; color:var(--text-light); margin-top:0.3rem;"><?= date('F j, Y', strtotime($rev['created_at'])) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="color:var(--text-muted);">No reviews submitted yet for this package.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Booking Call-To-Action Box -->
    <div style="position:sticky; top:96px;">
      <div class="booking-summary-card">
        <h3 class="summary-title" style="display:flex; justify-content:space-between; align-items:center;">
          <span>Book This Adventure</span>
          <span style="font-size:0.8rem; background:var(--primary-light); color:var(--primary); padding:0.2rem 0.6rem; border-radius:var(--radius-full);">Instant Confirmation</span>
        </h3>

        <div style="margin-bottom:1.5rem;">
          <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.2rem;">Starting from</div>
          <div style="font-size:2rem; font-weight:800; color:var(--primary-dark);"><?= format_price($pkg['base_price']) ?> <span style="font-size:0.9rem; font-weight:400; color:var(--text-muted);">/ person</span></div>
        </div>

        <ul style="list-style:none; font-size:0.9rem; color:var(--text-muted); margin-bottom:1.8rem; display:flex; flex-direction:column; gap:0.5rem;">
          <li><i class="fa-solid fa-check" style="color:var(--success); margin-right:0.4rem;"></i> Free cancellation up to 7 days before</li>
          <li><i class="fa-solid fa-check" style="color:var(--success); margin-right:0.4rem;"></i> Customizable hotel & vehicle upgrades</li>
          <li><i class="fa-solid fa-check" style="color:var(--success); margin-right:0.4rem;"></i> Real-time payment simulation</li>
        </ul>

        <a href="<?= BASE_URL ?>/booking/book.php?id=<?= $pkg['id'] ?>" class="btn btn-primary btn-block btn-lg" style="margin-bottom:0.8rem;">
          <i class="fa-solid fa-bolt"></i> Book Now
        </a>

        <a href="<?= BASE_URL ?>/booking/customize.php?package_id=<?= $pkg['id'] ?>" class="btn btn-outline btn-block">
          <i class="fa-solid fa-sliders"></i> Customize Itinerary
        </a>

        <div style="margin-top:1.5rem; text-align:center; font-size:0.82rem; color:var(--text-light);">
          <i class="fa-solid fa-shield-halved" style="color:var(--success);"></i> 100% Safe & Secure Booking
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
