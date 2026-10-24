<?php
/**
 * GlobeTrek Adventures - Staff Tour Packages Management (CRUD)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('staff');
$user = current_user();

$action = clean($_GET['action'] ?? 'list');
$editId = (int)($_GET['id'] ?? 0);
$errors = [];

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $postAction = $_POST['post_action'] ?? '';

        if ($postAction === 'save_package') {
            $title = clean($_POST['title'] ?? '');
            $destId = (int)($_POST['destination_id'] ?? 0);
            $days = max(1, (int)($_POST['duration_days'] ?? 1));
            $nights = max(0, (int)($_POST['duration_nights'] ?? 0));
            $price = max(1, (float)($_POST['base_price'] ?? 0));
            $difficulty = clean($_POST['difficulty'] ?? 'Moderate');
            $category = clean($_POST['category'] ?? 'Adventure');
            $featured = isset($_POST['is_featured']) ? 1 : 0;
            $status = clean($_POST['status'] ?? 'Active');
            $shortDesc = clean($_POST['short_desc'] ?? '');
            $fullDesc = clean($_POST['full_desc'] ?? '');
            $pkgId = (int)($_POST['pkg_id'] ?? 0);

            if (empty($title)) $errors[] = 'Package title is required.';
            if ($destId <= 0) $errors[] = 'Please select a destination.';

            if (empty($errors)) {
                if ($pkgId > 0) {
                    // Update
                    db_query("
                        UPDATE tour_packages SET
                            title = ?, destination_id = ?, duration_days = ?, duration_nights = ?,
                            base_price = ?, difficulty = ?, category = ?, is_featured = ?,
                            short_desc = ?, full_desc = ?, status = ?
                        WHERE id = ?
                    ", [$title, $destId, $days, $nights, $price, $difficulty, $category, $featured, $shortDesc, $fullDesc, $status, $pkgId]);

                    log_audit_action($user['id'], 'UPDATE_PACKAGE', "Staff updated tour package #{$pkgId} ({$title})");
                    set_flash('success', "Tour package '{$title}' updated successfully.");
                } else {
                    // Insert
                    $newId = db_insert("
                        INSERT INTO tour_packages (
                            title, destination_id, duration_days, duration_nights, base_price,
                            difficulty, category, is_featured, short_desc, full_desc, status, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
                    ", [$title, $destId, $days, $nights, $price, $difficulty, $category, $featured, $shortDesc, $fullDesc, $status]);

                    log_audit_action($user['id'], 'CREATE_PACKAGE', "Staff created tour package #{$newId} ({$title})");
                    set_flash('success', "New tour package '{$title}' created successfully.");
                }
                header('Location: ' . BASE_URL . '/staff/manage-packages.php');
                exit;
            }
        } elseif ($postAction === 'delete_package') {
            $delId = (int)$_POST['pkg_id'];
            db_query("DELETE FROM tour_packages WHERE id = ?", [$delId]);
            log_audit_action($user['id'], 'DELETE_PACKAGE', "Staff deleted tour package #{$delId}");
            set_flash('info', 'Tour package deleted.');
            header('Location: ' . BASE_URL . '/staff/manage-packages.php');
            exit;
        } elseif ($postAction === 'toggle_status') {
            $tId = (int)$_POST['pkg_id'];
            $currentStatus = db_fetch_val("SELECT status FROM tour_packages WHERE id = ?", [$tId]);
            $newStatus = ($currentStatus === 'Active') ? 'Draft' : 'Active';
            db_query("UPDATE tour_packages SET status = ? WHERE id = ?", [$newStatus, $tId]);
            set_flash('success', "Package status changed to {$newStatus}.");
            header('Location: ' . BASE_URL . '/staff/manage-packages.php');
            exit;
        }
    }
}

// Fetch Dest Options
$destinations = db_fetch_all("SELECT * FROM destinations ORDER BY name ASC");

// Fetch Package for Edit
$editPackage = null;
if ($editId > 0) {
    $editPackage = db_fetch_one("SELECT * FROM tour_packages WHERE id = ?", [$editId]);
}

// Fetch All Packages for Table
$packagesList = db_fetch_all("
    SELECT p.*, d.name as destination_name,
           (SELECT COUNT(*) FROM bookings WHERE package_id = p.id) as total_bookings
    FROM tour_packages p
    JOIN destinations d ON p.destination_id = d.id
    ORDER BY p.id DESC
");

$pageTitle = 'Manage Tour Packages | Staff Desk';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Manage Tour Packages</h1>
        <p class="dashboard-subtitle">Create, edit, toggle availability, and configure itineraries.</p>
      </div>
      <div>
        <?php if ($action === 'create' || $editId > 0): ?>
          <a href="<?= BASE_URL ?>/staff/manage-packages.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Catalog</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/staff/manage-packages.php?action=create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create New Package</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul style="margin-left:1.2rem;">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($action === 'create' || $editPackage): ?>
      <!-- Create / Edit Form Card -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fa-solid fa-pen-to-square" style="color:var(--primary);"></i> 
            <?= $editPackage ? 'Edit Package: ' . htmlspecialchars($editPackage['title']) : 'Create New Tour Package' ?>
          </h3>
        </div>
        <div class="card-body">
          <form action="<?= BASE_URL ?>/staff/manage-packages.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="post_action" value="save_package">
            <input type="hidden" name="pkg_id" value="<?= $editPackage ? $editPackage['id'] : 0 ?>">

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Package Title <span style="color:var(--danger);">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. Ella Mountain Ridge Expedition" value="<?= htmlspecialchars($_POST['title'] ?? ($editPackage['title'] ?? '')) ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Destination <span style="color:var(--danger);">*</span></label>
                <select name="destination_id" class="form-control" required>
                  <option value="">-- Select Destination --</option>
                  <?php foreach ($destinations as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (($editPackage['destination_id'] ?? 0) == $d['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($d['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1.2rem;">
              <div class="form-group">
                <label class="form-label">Duration Days</label>
                <input type="number" name="duration_days" class="form-control" min="1" max="30" value="<?= $editPackage['duration_days'] ?? 3 ?>" required>
              </div>

              <div class="form-group">
                <label class="form-label">Duration Nights</label>
                <input type="number" name="duration_nights" class="form-control" min="0" max="30" value="<?= $editPackage['duration_nights'] ?? 2 ?>" required>
              </div>

              <div class="form-group">
                <label class="form-label">Base Price (USD/Person) <span style="color:var(--danger);">*</span></label>
                <input type="number" step="0.01" name="base_price" class="form-control" required value="<?= $editPackage['base_price'] ?? 250.00 ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                  <option value="Active" <?= (($editPackage['status'] ?? '') === 'Active') ? 'selected' : '' ?>>Active</option>
                  <option value="Draft" <?= (($editPackage['status'] ?? '') === 'Draft') ? 'selected' : '' ?>>Draft</option>
                  <option value="Archived" <?= (($editPackage['status'] ?? '') === 'Archived') ? 'selected' : '' ?>>Archived</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Tour Category</label>
                <select name="category" class="form-control">
                  <option value="Adventure" <?= (($editPackage['category'] ?? '') === 'Adventure') ? 'selected' : '' ?>>Adventure & Trekking</option>
                  <option value="Cultural" <?= (($editPackage['category'] ?? '') === 'Cultural') ? 'selected' : '' ?>>Cultural & Heritage</option>
                  <option value="Nature" <?= (($editPackage['category'] ?? '') === 'Nature') ? 'selected' : '' ?>>Nature & Tea Country</option>
                  <option value="Coastal" <?= (($editPackage['category'] ?? '') === 'Coastal') ? 'selected' : '' ?>>Coastal & Beach</option>
                  <option value="Wildlife" <?= (($editPackage['category'] ?? '') === 'Wildlife') ? 'selected' : '' ?>>Wildlife & Safari</option>
                  <option value="Honeymoon" <?= (($editPackage['category'] ?? '') === 'Honeymoon') ? 'selected' : '' ?>>Honeymoon & Romantic</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Activity Difficulty</label>
                <select name="difficulty" class="form-control">
                  <option value="Easy" <?= (($editPackage['difficulty'] ?? '') === 'Easy') ? 'selected' : '' ?>>Easy (Relaxed)</option>
                  <option value="Moderate" <?= (($editPackage['difficulty'] ?? '') === 'Moderate') ? 'selected' : '' ?>>Moderate (Active)</option>
                  <option value="Challenging" <?= (($editPackage['difficulty'] ?? '') === 'Challenging') ? 'selected' : '' ?>>Challenging</option>
                  <option value="Demanding" <?= (($editPackage['difficulty'] ?? '') === 'Demanding') ? 'selected' : '' ?>>Demanding (High Endurance)</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Short Summary (Shown on Catalog Cards)</label>
              <textarea name="short_desc" class="form-control" style="min-height:70px;" required><?= htmlspecialchars($editPackage['short_desc'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">Full Comprehensive Description</label>
              <textarea name="full_desc" class="form-control" style="min-height:120px;" required><?= htmlspecialchars($editPackage['full_desc'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom:1.8rem;">
              <label style="display:flex; align-items:center; gap:0.6rem; cursor:pointer; font-weight:600;">
                <input type="checkbox" name="is_featured" value="1" <?= (!empty($editPackage['is_featured'])) ? 'checked' : '' ?> style="width:18px; height:18px;">
                <span>Feature this package on homepage showcase</span>
              </label>
            </div>

            <div style="display:flex; gap:1rem;">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-floppy-disk"></i> <?= $editPackage ? 'Save Changes' : 'Publish Tour Package' ?>
              </button>
              <a href="<?= BASE_URL ?>/staff/manage-packages.php" class="btn btn-outline btn-lg">Cancel</a>
            </div>
          </form>
        </div>
      </div>

    <?php else: ?>
      <!-- Packages Directory Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fa-solid fa-box-archive" style="color:var(--primary);"></i> Active Tour Packages (<?= count($packagesList) ?>)</h3>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Destination</th>
                  <th>Duration</th>
                  <th>Base Price</th>
                  <th>Category</th>
                  <th>Bookings</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($packagesList as $p): ?>
                  <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td>
                      <strong><?= htmlspecialchars($p['title']) ?></strong>
                      <?php if ($p['is_featured']): ?>
                        <span style="font-size:0.7rem; background:#fef3c7; color:#d97706; padding:0.1rem 0.4rem; border-radius:4px; font-weight:700; margin-left:0.3rem;">Featured</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['destination_name']) ?></td>
                    <td><?= $p['duration_days'] ?>D / <?= $p['duration_nights'] ?>N</td>
                    <td><strong><?= format_price($p['base_price']) ?></strong></td>
                    <td><?= htmlspecialchars($p['category']) ?></td>
                    <td><span class="status-pill status-in-progress"><?= $p['total_bookings'] ?> bookings</span></td>
                    <td>
                      <form action="<?= BASE_URL ?>/staff/manage-packages.php" method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="post_action" value="toggle_status">
                        <input type="hidden" name="pkg_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="status-pill <?= ($p['status'] === 'Active') ? 'status-active' : 'status-pending' ?>" style="border:none; cursor:pointer;" title="Click to toggle">
                          <?= htmlspecialchars($p['status']) ?>
                        </button>
                      </form>
                    </td>
                    <td>
                      <div style="display:flex; gap:0.4rem;">
                        <a href="<?= BASE_URL ?>/staff/manage-packages.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="Edit">
                          <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form action="<?= BASE_URL ?>/staff/manage-packages.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this tour package?');" style="display:inline;">
                          <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                          <input type="hidden" name="post_action" value="delete_package">
                          <input type="hidden" name="pkg_id" value="<?= $p['id'] ?>">
                          <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
