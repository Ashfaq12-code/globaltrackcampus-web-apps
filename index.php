<?php
/**
 * GlobeTrek Adventures - Homepage
 */
$pageTitle = 'Explore Sri Lanka with GlobeTrek Adventures | Premier Tours & Experiences';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';

// Fetch Featured Packages
$featuredPackages = db_fetch_all("
    SELECT p.*, d.name AS destination_name,
           (SELECT AVG(rating) FROM reviews WHERE package_id = p.id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE package_id = p.id) as review_count
    FROM tour_packages p
    JOIN destinations d ON p.destination_id = d.id
    WHERE p.status = 'Active' AND p.is_featured = 1
    ORDER BY p.id ASC
    LIMIT 6
");

// Fetch Destinations
$destinations = db_fetch_all("SELECT * FROM destinations ORDER BY id ASC LIMIT 6");

// Fetch Reviews for Testimonials
$testimonials = db_fetch_all("
    SELECT r.*, u.full_name, u.avatar, p.title as package_title
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN tour_packages p ON r.package_id = p.id
    WHERE r.is_approved = 1
    ORDER BY r.id DESC
    LIMIT 3
");
?>

<!-- ===== HERO AUTO-SWIPE CAROUSEL ===== -->
<section class="hero-carousel" aria-label="Featured Destinations">

  <div class="hero-slides">

    <!-- Slide 1: Ella Highlands -->
    <div class="hero-slide"
         style="background-image: url('https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?q=80&w=1800&auto=format&fit=crop');">
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <span class="slide-tag"><i class="fa-solid fa-mountain-sun"></i> Ella Highlands · Sri Lanka</span>
        <h1 class="slide-title">Explore the <span>Misty Mountains</span> of Ella</h1>
        <p class="slide-desc">Hike the Nine Arch Bridge trails, ride the scenic mountain train, and wake up to breathtaking valley sunrise views.</p>
        <div class="slide-actions">
          <a href="<?= BASE_URL ?>/tours.php?dest=Ella" class="btn btn-accent btn-lg"><i class="fa-solid fa-compass"></i> Explore Ella Tours</a>
          <a href="<?= BASE_URL ?>/booking/customize.php" class="btn btn-glass btn-lg"><i class="fa-solid fa-sliders"></i> Customize Trip</a>
        </div>
      </div>
    </div>

    <!-- Slide 2: Sigiriya Ancient Rock -->
    <div class="hero-slide"
         style="background-image: url('https://images.unsplash.com/photo-1555400038-63f5ba517a47?q=80&w=1800&auto=format&fit=crop');">
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <span class="slide-tag"><i class="fa-solid fa-landmark"></i> Sigiriya · UNESCO Heritage</span>
        <h1 class="slide-title">Discover <span>Ancient Wonders</span> at Sigiriya</h1>
        <p class="slide-desc">Ascend the 5th-century Lion Rock fortress rising 200 metres above the jungle. A UNESCO World Heritage marvel awaiting you.</p>
        <div class="slide-actions">
          <a href="<?= BASE_URL ?>/tours.php?dest=Sigiriya" class="btn btn-accent btn-lg"><i class="fa-solid fa-compass"></i> Sigiriya Packages</a>
          <a href="<?= BASE_URL ?>/tours.php" class="btn btn-glass btn-lg"><i class="fa-solid fa-map-location-dot"></i> View All Tours</a>
        </div>
      </div>
    </div>

    <!-- Slide 3: Mirissa Coastal Paradise -->
    <div class="hero-slide"
         style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1800&auto=format&fit=crop');">
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <span class="slide-tag"><i class="fa-solid fa-water"></i> Mirissa · South Coast</span>
        <h1 class="slide-title">Coastal <span>Paradise</span> &amp; Blue Whale Magic</h1>
        <p class="slide-desc">Experience world-famous blue whale watching at dawn, surf turquoise waves, and unwind on golden palm-fringed beaches.</p>
        <div class="slide-actions">
          <a href="<?= BASE_URL ?>/tours.php?dest=Mirissa" class="btn btn-accent btn-lg"><i class="fa-solid fa-compass"></i> Coastal Tours</a>
          <a href="<?= BASE_URL ?>/contact.php" class="btn btn-glass btn-lg"><i class="fa-solid fa-envelope"></i> Ask an Expert</a>
        </div>
      </div>
    </div>

  </div><!-- /hero-slides -->

  <!-- Navigation Arrows -->
  <button class="hero-arrow hero-arrow-prev" aria-label="Previous slide">
    <i class="fa-solid fa-chevron-left"></i>
  </button>
  <button class="hero-arrow hero-arrow-next" aria-label="Next slide">
    <i class="fa-solid fa-chevron-right"></i>
  </button>

  <!-- Dot Indicators -->
  <div class="hero-dots" role="tablist" aria-label="Slide indicators">
    <button class="hero-dot" aria-label="Slide 1"></button>
    <button class="hero-dot" aria-label="Slide 2"></button>
    <button class="hero-dot" aria-label="Slide 3"></button>
  </div>

</section><!-- /hero-carousel -->

<!-- Search Bar Box -->
<div class="container">
  <div class="search-card">
    <form action="<?= BASE_URL ?>/tours.php" method="GET" class="search-form">
      <div class="search-group">
        <label><i class="fa-solid fa-location-dot"></i> Destination or Keyword</label>
        <input type="text" name="q" class="search-input" placeholder="e.g. Ella, Kandy, Sigiriya...">
      </div>
      <div class="search-group">
        <label><i class="fa-solid fa-layer-group"></i> Tour Category</label>
        <select name="category" class="search-select">
          <option value="">All Categories</option>
          <option value="Adventure">Adventure & Hiking</option>
          <option value="Cultural">Cultural & Heritage</option>
          <option value="Nature">Nature & Tea Country</option>
          <option value="Coastal">Coastal & Marine</option>
          <option value="Wildlife">Wildlife & Safari</option>
        </select>
      </div>
      <div class="search-group">
        <label><i class="fa-solid fa-clock"></i> Duration</label>
        <select name="duration" class="search-select">
          <option value="">Any Duration</option>
          <option value="1-3">1 - 3 Days</option>
          <option value="4-6">4 - 6 Days</option>
          <option value="7+">7+ Days Expedition</option>
        </select>
      </div>
      <div>
        <button type="submit" class="btn btn-primary btn-block" style="height:44px; margin-top:18px;">
          <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Featured Tour Packages Section -->
<section class="section" style="padding-top:1rem;">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Handcrafted Journeys</span>
      <h2 class="section-title">Popular Tour Packages</h2>
      <p class="section-desc">
        Explore our highest-rated guided packages with customizable accommodations, transport choices, and flexible itineraries.
      </p>
    </div>

    <div class="grid-3">
      <?php foreach ($featuredPackages as $pkg): ?>
        <div class="tour-card">
          <div class="tour-card-img-wrap">
            <img src="https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?q=80&w=800&auto=format&fit=crop" alt="<?= htmlspecialchars($pkg['title']) ?>" class="tour-card-img">
            <span class="tour-badge"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($pkg['destination_name']) ?></span>
            <?php if ($pkg['is_featured']): ?>
              <span class="tour-featured-badge"><i class="fa-solid fa-star"></i> Featured</span>
            <?php endif; ?>
          </div>
          <div class="tour-card-body">
            <div class="tour-meta">
              <span><i class="fa-solid fa-calendar-days" style="color:var(--primary);"></i> <?= $pkg['duration_days'] ?>D / <?= $pkg['duration_nights'] ?>N</span>
              <span><i class="fa-solid fa-person-hiking" style="color:var(--accent);"></i> <?= htmlspecialchars($pkg['difficulty']) ?></span>
              <span><i class="fa-solid fa-star" style="color:var(--warning);"></i> <?= number_format($pkg['avg_rating'] ?: 5.0, 1) ?> (<?= $pkg['review_count'] ?: 1 ?>)</span>
            </div>
            <h3 class="tour-title">
              <a href="<?= BASE_URL ?>/tour-details.php?id=<?= $pkg['id'] ?>"><?= htmlspecialchars($pkg['title']) ?></a>
            </h3>
            <p class="tour-desc"><?= htmlspecialchars($pkg['short_desc']) ?></p>
            <div class="tour-card-footer">
              <div class="tour-price-box">
                <span class="tour-price-label">From / Person</span>
                <span class="tour-price-val"><?= format_price($pkg['base_price']) ?></span>
              </div>
              <div style="display:flex; gap:0.5rem;">
                <a href="<?= BASE_URL ?>/tour-details.php?id=<?= $pkg['id'] ?>" class="btn btn-outline btn-sm">Details</a>
                <a href="<?= BASE_URL ?>/booking/book.php?id=<?= $pkg['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-bolt"></i> Book Now</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center; margin-top:3rem;">
      <a href="<?= BASE_URL ?>/tours.php" class="btn btn-outline btn-lg"><i class="fa-solid fa-list-check"></i> View All Tour Packages</a>
    </div>
  </div>
</section>

<!-- Top Destinations Section -->
<section class="section" style="background-color:#ffffff;">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Island Wonders</span>
      <h2 class="section-title">Top Destinations</h2>
      <p class="section-desc">From misty mountain gaps to golden palm-fringed coastlines, discover the breathtaking regions of Sri Lanka.</p>
    </div>

    <div class="grid-3">
      <?php foreach ($destinations as $dest): ?>
        <a href="<?= BASE_URL ?>/tours.php?dest=<?= urlencode($dest['name']) ?>" class="dest-card">
          <img src="https://images.unsplash.com/photo-1546708973-b339540b5162?q=80&w=800&auto=format&fit=crop" alt="<?= htmlspecialchars($dest['name']) ?>" class="dest-card-img">
          <div class="dest-card-overlay">
            <h3 class="dest-title"><?= htmlspecialchars($dest['name']) ?></h3>
            <div class="dest-region"><i class="fa-solid fa-map-pin"></i> <?= htmlspecialchars($dest['region']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Why Choose GlobeTrek Section -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Why GlobeTrek</span>
      <h2 class="section-title">Why Travel With GlobeTrek Adventures</h2>
      <p class="section-desc">We combine authentic local knowledge with seamless modern technology to craft unforgettable holidays.</p>
    </div>

    <div class="grid-4">
      <div class="feature-box">
        <div class="feature-icon-wrap"><i class="fa-solid fa-mountain-sun"></i></div>
        <h4 class="feature-title">Tailor-Made Customization</h4>
        <p class="feature-desc">Choose your preferred hotel tiers, transport vehicles, and unique optional activities with live pricing.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-wrap"><i class="fa-solid fa-shield-halved"></i></div>
        <h4 class="feature-title">Verified Quality & Safety</h4>
        <p class="feature-desc">All hotels, licensed chauffeur drivers, and wildlife safari trackers meet stringent quality benchmarks.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-wrap"><i class="fa-solid fa-headset"></i></div>
        <h4 class="feature-title">24/7 Dedicated Support</h4>
        <p class="feature-desc">Submit queries and communicate directly with our operations staff anytime via your customer portal.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-wrap"><i class="fa-solid fa-seedling"></i></div>
        <h4 class="feature-title">Sustainable Tourism</h4>
        <p class="feature-desc">We actively support local community homestays, wildlife conservation funds, and eco-certified chalets.</p>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="section" style="background-color:#ffffff;">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Traveler Stories</span>
      <h2 class="section-title">What Our Guests Say</h2>
      <p class="section-desc">Real experiences from travelers who explored Sri Lanka with us.</p>
    </div>

    <div class="grid-3">
      <?php foreach ($testimonials as $t): ?>
        <div style="background:var(--bg-main); padding:2rem; border-radius:var(--radius-md); border:1px solid var(--border); display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="color:var(--warning); margin-bottom:0.8rem; font-size:1.1rem;">
              <?php for ($i=0; $i<$t['rating']; $i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
            </div>
            <p style="font-size:0.95rem; color:var(--text-main); font-style:italic; margin-bottom:1.5rem; line-height:1.6;">
              "<?= htmlspecialchars($t['comment']) ?>"
            </p>
          </div>
          <div style="display:flex; align-items:center; gap:0.8rem; border-top:1px solid var(--border); padding-top:1rem;">
            <div style="width:42px; height:42px; border-radius:var(--radius-full); background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">
              <?= strtoupper(substr($t['full_name'], 0, 1)) ?>
            </div>
            <div>
              <div style="font-weight:700; font-size:0.95rem; color:var(--secondary);"><?= htmlspecialchars($t['full_name']) ?></div>
              <div style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($t['package_title']) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<section style="background:linear-gradient(135deg, var(--primary-dark), #0f172a); color:#fff; padding:4.5rem 0; text-align:center;">
  <div class="container" style="max-width:760px;">
    <h2 style="color:#fff; font-size:2.4rem; margin-bottom:1rem;">Ready to Craft Your Dream Sri Lanka Holiday?</h2>
    <p style="font-size:1.1rem; color:#cbd5e1; margin-bottom:2rem;">
      Whether you wish to hike the peaks of Ella, experience cultural heritage in Kandy, or relax by the ocean in Galle, our team is ready to assist.
    </p>
    <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/booking/customize.php" class="btn btn-accent btn-lg"><i class="fa-solid fa-sliders"></i> Customize Your Itinerary</a>
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline btn-lg" style="color:#fff; border-color:#fff;"><i class="fa-solid fa-envelope"></i> Send Us an Inquiry</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
