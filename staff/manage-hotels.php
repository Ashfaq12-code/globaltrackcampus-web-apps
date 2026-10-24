<?php
/**
 * GlobeTrek Adventures - Staff Hotel & Accommodation Inventory Management
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('staff');
$user = current_user();

$errors = [];

// Handle Hotel CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_hotel') {
            $name = clean($_POST['name'] ?? '');
            $destId = (int)($_POST['destination_id'] ?? 0);
            $tier = clean($_POST['tier'] ?? 'Standard');
            $pricePerNight = (float)($_POST['price_per_night'] ?? 0.00);
            $description = clean($_POST['description'] ?? '');
            $amenities = clean($_POST['amenities'] ?? '');
            $hotelId = (int)($_POST['hotel_id'] ?? 0);

            if (empty($name)) $errors[] = 'Hotel name is required.';
            if ($destId <= 0) $errors[] = 'Destination is required.';

            if (empty($errors)) {
                if ($hotelId > 0) {
                    db_query("
                        UPDATE accommodations SET
                            name = ?, destination_id = ?, tier = ?, price_per_night = ?,
                            description = ?, amenities = ?
                        WHERE id = ?
                    ", [$name, $destId, $tier, $pricePerNight, $description, $amenities, $hotelId]);
                    set_flash('success', "Hotel '{$name}' updated.");
                } else {
                    db_insert("
                        INSERT INTO accommodations (name, destination_id, tier, price_per_night, description, amenities, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'Available')
                    ", [$name, $destId, $tier, $pricePerNight, $description, $amenities]);
                    set_flash('success', "Hotel '{$name}' added to inventory.");
                }
                header('Location: ' . BASE_URL . '/staff/manage-hotels.php');
                exit;
            }
        } elseif ($action === 'delete_hotel') {
            $hId = (int)$_POST['hotel_id'];
            db_query("DELETE FROM accommodations WHERE id = ?", [$hId]);
            set_flash('info', 'Hotel record removed.');
            header('Location: ' . BASE_URL . '/staff/manage-hotels.php');
            exit;
        }
    }
}

// Fetch Destinations
$destinations = db_fetch_all("SELECT * FROM destinations ORDER BY name ASC");

// Fetch All Hotels
$hotels = db_fetch_all("
    SELECT a.*, d.name as destination_name,
           (SELECT COUNT(*) FROM bookings WHERE accommodation_id = a.id) as booking_count
    FROM accommodations a
    JOIN destinations d ON a.destination_id = d.id
    ORDER BY a.destination_id ASC, a.price_per_night ASC
");

$pageTitle = 'Manage Accommodations | Staff Desk';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Partner Accommodations & Resorts</h1>
        <p class="dashboard-subtitle">Manage partner hotels, room tiers, nightly surcharges, and guest amenities.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary" onclick="openAddHotelModal()">
          <i class="fa-solid fa-plus"></i> Add Partner Hotel
        </button>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul style="margin-left:1.2rem;">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-hotel" style="color:var(--primary);"></i> Hotel Partners Directory (<?= count($hotels) ?>)</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Hotel Name</th>
                <th>Destination</th>
                <th>Tier</th>
                <th>Nightly Rate</th>
                <th>Key Amenities</th>
                <th>Reservations</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($hotels as $h): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($h['name']) ?></strong></td>
                  <td><i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> <?= htmlspecialchars($h['destination_name']) ?></td>
                  <td><span class="status-pill status-in-progress"><?= htmlspecialchars($h['tier']) ?></span></td>
                  <td><strong><?= ($h['price_per_night'] == 0) ? 'Included in Base' : '+' . format_price($h['price_per_night']) . ' / nt' ?></strong></td>
                  <td style="font-size:0.85rem; color:var(--text-muted); max-width:280px;"><?= htmlspecialchars($h['amenities']) ?></td>
                  <td><span class="status-pill status-confirmed"><?= $h['booking_count'] ?> used</span></td>
                  <td>
                    <div style="display:flex; gap:0.4rem;">
                      <button type="button" class="btn btn-outline btn-sm" onclick='openEditHotelModal(<?= json_encode($h) ?>)'>
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <form action="<?= BASE_URL ?>/staff/manage-hotels.php" method="POST" onsubmit="return confirm('Delete this hotel entry?');" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="delete_hotel">
                        <input type="hidden" name="hotel_id" value="<?= $h['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
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

  </main>
</div>

<!-- Hotel Add / Edit Modal -->
<div class="modal-backdrop" id="hotelModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title" id="hotelModalTitle">Add Partner Hotel</h3>
      <button type="button" class="modal-close" onclick="closeModal('hotelModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/staff/manage-hotels.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="save_hotel">
      <input type="hidden" name="hotel_id" id="hotelModalId" value="0">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Hotel / Resort Name <span style="color:var(--danger);">*</span></label>
          <input type="text" name="name" id="hotelName" class="form-control" required placeholder="e.g. Ella Grand Heritage Resort">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Destination</label>
            <select name="destination_id" id="hotelDest" class="form-control" required>
              <?php foreach ($destinations as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Accommodation Tier</label>
            <select name="tier" id="hotelTier" class="form-control">
              <option value="Standard">Standard (Eco Lodge)</option>
              <option value="Deluxe">Deluxe (Boutique Villa)</option>
              <option value="Luxury Resort">Luxury Resort (5-Star)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Surcharge Price per Night (USD)</label>
          <input type="number" step="0.01" name="price_per_night" id="hotelPrice" class="form-control" value="0.00" required>
        </div>

        <div class="form-group">
          <label class="form-label">Amenities (Comma-separated)</label>
          <input type="text" name="amenities" id="hotelAmenities" class="form-control" placeholder="Free Wi-Fi, Pool, Breakfast Included, Mountain View">
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" id="hotelDesc" class="form-control" placeholder="Describe resort features..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('hotelModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Hotel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddHotelModal() {
  document.getElementById('hotelModalTitle').textContent = 'Add Partner Hotel';
  document.getElementById('hotelModalId').value = 0;
  document.getElementById('hotelName').value = '';
  document.getElementById('hotelPrice').value = '0.00';
  document.getElementById('hotelAmenities').value = 'Free Wi-Fi, Breakfast Included';
  document.getElementById('hotelDesc').value = '';
  openModal('hotelModal');
}

function openEditHotelModal(h) {
  document.getElementById('hotelModalTitle').textContent = 'Edit Hotel: ' + h.name;
  document.getElementById('hotelModalId').value = h.id;
  document.getElementById('hotelName').value = h.name;
  document.getElementById('hotelDest').value = h.destination_id;
  document.getElementById('hotelTier').value = h.tier;
  document.getElementById('hotelPrice').value = h.price_per_night;
  document.getElementById('hotelAmenities').value = h.amenities;
  document.getElementById('hotelDesc').value = h.description;
  openModal('hotelModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
