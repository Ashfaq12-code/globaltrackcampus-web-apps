<?php
/**
 * GlobeTrek Adventures - Tour Catalog & Search
 */
$pageTitle = 'Explore Tour Packages | GlobeTrek Adventures';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';

// Filter parameters from GET
$searchQuery = clean($_GET['q'] ?? '');
$category = clean($_GET['category'] ?? '');
$difficulty = clean($_GET['difficulty'] ?? '');
$destParam = clean($_GET['dest'] ?? '');
$duration = clean($_GET['duration'] ?? '');
$sortBy = clean($_GET['sort'] ?? 'featured');

// Build SQL Query
$sql = "
    SELECT p.*, d.name AS destination_name,
           (SELECT AVG(rating) FROM reviews WHERE package_id = p.id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE package_id = p.id) as review_count
    FROM tour_packages p
    JOIN destinations d ON p.destination_id = d.id
    WHERE p.status = 'Active'
";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (p.title LIKE ? OR d.name LIKE ? OR p.short_desc LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($destParam)) {
    $sql .= " AND d.name LIKE ?";
    $params[] = "%{$destParam}%";
}

if (!empty($category)) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
}

if (!empty($difficulty)) {
    $sql .= " AND p.difficulty = ?";
    $params[] = $difficulty;
}

if ($duration === '1-3') {
    $sql .= " AND p.duration_days BETWEEN 1 AND 3";
} elseif ($duration === '4-6') {
    $sql .= " AND p.duration_days BETWEEN 4 AND 6";
} elseif ($duration === '7+') {
    $sql .= " AND p.duration_days >= 7";
}

// Sorting
if ($sortBy === 'price_low') {
    $sql .= " ORDER BY p.base_price ASC";
} elseif ($sortBy === 'price_high') {
    $sql .= " ORDER BY p.base_price DESC";
} elseif ($sortBy === 'duration') {
    $sql .= " ORDER BY p.duration_days ASC";
} else {
    $sql .= " ORDER BY p.is_featured DESC, p.id ASC";
}

$packages = db_fetch_all($sql, $params);
$destinationsList = db_fetch_all("SELECT id, name FROM destinations ORDER BY name ASC");
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:2.5rem 0;">
  <div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
      <div>
        <h1 style="font-size:2.2rem; margin-bottom:0.3rem;">Tour Packages in Sri Lanka</h1>
        <p style="color:var(--text-muted); font-size:1rem;">
          Showing <?= count($packages) ?> handcrafted adventures and experiences.
        </p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/booking/customize.php" class="btn btn-accent"><i class="fa-solid fa-sliders"></i> Create Custom Trip</a>
      </div>
    </div>
  </div>
</div>

<div class="container" style="padding-top:2.5rem; padding-bottom:5rem;">
  <div style="display:grid; grid-template-columns:280px 1fr; gap:2.5rem; align-items:start;">
    
    <!-- Left Filter Sidebar -->
    <div style="background:#ffffff; border:1px solid var(--border); border-radius:var(--radius-md); padding:1.5rem; box-shadow:var(--shadow-sm); position:sticky; top:96px;">
      <h3 style="font-size:1.15rem; margin-bottom:1.2rem; padding-bottom:0.6rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
        <span><i class="fa-solid fa-filter" style="color:var(--primary);"></i> Filters</span>
        <a href="<?= BASE_URL ?>/tours.php" style="font-size:0.8rem; color:var(--text-muted); font-weight:500;">Reset All</a>
      </h3>

      <form action="<?= BASE_URL ?>/tours.php" method="GET" id="catalogFilterForm">
        <!-- Keyword / Live filter -->
        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-magnifying-glass"></i> Live Keyword Filter</label>
          <input type="text" id="tourFilterInput" name="q" value="<?= htmlspecialchars($searchQuery) ?>" class="form-control" placeholder="Search tours...">
        </div>

        <!-- Category -->
        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-layer-group"></i> Category</label>
          <select name="category" id="categoryFilter" class="form-control">
            <option value="all">All Categories</option>
            <option value="Adventure" <?= ($category === 'Adventure') ? 'selected' : '' ?>>Adventure & Trekking</option>
            <option value="Cultural" <?= ($category === 'Cultural') ? 'selected' : '' ?>>Cultural & Heritage</option>
            <option value="Nature" <?= ($category === 'Nature') ? 'selected' : '' ?>>Nature & Highlands</option>
            <option value="Coastal" <?= ($category === 'Coastal') ? 'selected' : '' ?>>Coastal & Marine</option>
            <option value="Wildlife" <?= ($category === 'Wildlife') ? 'selected' : '' ?>>Wildlife & Safari</option>
          </select>
        </div>

        <!-- Difficulty -->
        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-person-hiking"></i> Activity Difficulty</label>
          <select name="difficulty" id="difficultyFilter" class="form-control">
            <option value="all">Any Difficulty</option>
            <option value="Easy" <?= ($difficulty === 'Easy') ? 'selected' : '' ?>>Easy (Relaxed)</option>
            <option value="Moderate" <?= ($difficulty === 'Moderate') ? 'selected' : '' ?>>Moderate (Active)</option>
            <option value="Challenging" <?= ($difficulty === 'Challenging') ? 'selected' : '' ?>>Challenging</option>
          </select>
        </div>

        <!-- Duration -->
        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-calendar"></i> Duration</label>
          <select name="duration" class="form-control">
            <option value="">Any Duration</option>
            <option value="1-3" <?= ($duration === '1-3') ? 'selected' : '' ?>>1 - 3 Days</option>
            <option value="4-6" <?= ($duration === '4-6') ? 'selected' : '' ?>>4 - 6 Days</option>
            <option value="7+" <?= ($duration === '7+') ? 'selected' : '' ?>>7+ Days Expedition</option>
          </select>
        </div>

        <!-- Sort By -->
        <div class="form-group">
          <label class="form-label"><i class="fa-solid fa-arrow-down-short-wide"></i> Sort Results</label>
          <select name="sort" class="form-control">
            <option value="featured" <?= ($sortBy === 'featured') ? 'selected' : '' ?>>Featured First</option>
            <option value="price_low" <?= ($sortBy === 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_high" <?= ($sortBy === 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="duration" <?= ($sortBy === 'duration') ? 'selected' : '' ?>>Duration: Shortest</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;">
          <i class="fa-solid fa-arrows-rotate"></i> Apply Filters
        </button>
      </form>
    </div>

    <!-- Right Tour Cards Grid -->
    <div>
      <div id="noToursFound" style="display:<?= empty($packages) ? 'block' : 'none' ?>; text-align:center; padding:4rem 2rem; background:#fff; border-radius:var(--radius-md); border:1px solid var(--border);">
        <i class="fa-solid fa-compass" style="font-size:3rem; color:var(--text-muted); margin-bottom:1rem;"></i>
        <h3 style="margin-bottom:0.5rem;">No Tour Packages Found</h3>
        <p style="color:var(--text-muted); margin-bottom:1.5rem;">Try adjusting your filters or search keywords.</p>
        <a href="<?= BASE_URL ?>/tours.php" class="btn btn-primary">View All Tours</a>
      </div>

      <div class="grid-3" id="toursGridContainer">
        <?php foreach ($packages as $pkg): ?>
          <div class="tour-card tour-card-item" 
               data-title="<?= strtolower(htmlspecialchars($pkg['title'])) ?>"
               data-dest="<?= strtolower(htmlspecialchars($pkg['destination_name'])) ?>"
               data-category="<?= strtolower(htmlspecialchars($pkg['category'])) ?>"
               data-difficulty="<?= strtolower(htmlspecialchars($pkg['difficulty'])) ?>">
            
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
                <div style="display:flex; gap:0.4rem;">
                  <a href="<?= BASE_URL ?>/tour-details.php?id=<?= $pkg['id'] ?>" class="btn btn-outline btn-sm">Details</a>
                  <a href="<?= BASE_URL ?>/booking/book.php?id=<?= $pkg['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-bolt"></i> Book</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
