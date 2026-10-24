<?php
/**
 * GlobeTrek Adventures - Staff Transport Fleet Management
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

require_login('staff');
$user = current_user();

$errors = [];

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_vehicle') {
            $name = clean($_POST['vehicle_name'] ?? '');
            $type = clean($_POST['vehicle_type'] ?? 'Private Sedan Car');
            $capacity = max(1, (int)($_POST['capacity'] ?? 4));
            $pricePerDay = max(0, (float)($_POST['price_per_day'] ?? 0));
            $features = clean($_POST['features'] ?? '');
            $vehicleId = (int)($_POST['vehicle_id'] ?? 0);

            if (empty($name)) $errors[] = 'Vehicle name is required.';

            if (empty($errors)) {
                if ($vehicleId > 0) {
                    db_query("
                        UPDATE transport_options SET
                            vehicle_name = ?, vehicle_type = ?, capacity = ?,
                            price_per_day = ?, features = ?
                        WHERE id = ?
                    ", [$name, $type, $capacity, $pricePerDay, $features, $vehicleId]);
                    set_flash('success', "Vehicle '{$name}' updated.");
                } else {
                    db_insert("
                        INSERT INTO transport_options (vehicle_name, vehicle_type, capacity, price_per_day, features, status)
                        VALUES (?, ?, ?, ?, ?, 'Available')
                    ", [$name, $type, $capacity, $pricePerDay, $features]);
                    set_flash('success', "New vehicle '{$name}' added to transport fleet.");
                }
                header('Location: ' . BASE_URL . '/staff/manage-transport.php');
                exit;
            }
        } elseif ($action === 'delete_vehicle') {
            $vId = (int)$_POST['vehicle_id'];
            db_query("DELETE FROM transport_options WHERE id = ?", [$vId]);
            set_flash('info', 'Vehicle removed from fleet.');
            header('Location: ' . BASE_URL . '/staff/manage-transport.php');
            exit;
        }
    }
}

$vehicles = db_fetch_all("
    SELECT t.*, 
           (SELECT COUNT(*) FROM bookings WHERE transport_id = t.id) as trips_assigned
    FROM transport_options t 
    ORDER BY t.price_per_day ASC
");

$pageTitle = 'Manage Transport Fleet | Staff Desk';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Transport Fleet & Chauffeurs</h1>
        <p class="dashboard-subtitle">Manage vehicle inventory, passenger capacities, daily rates, and chauffeur allocations.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary" onclick="openAddVehicleModal()">
          <i class="fa-solid fa-plus"></i> Add Vehicle to Fleet
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
        <h3 class="card-title"><i class="fa-solid fa-van-shuttle" style="color:var(--primary);"></i> Fleet Vehicles (<?= count($vehicles) ?>)</h3>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Vehicle Model</th>
                <th>Category</th>
                <th>Capacity</th>
                <th>Daily Rate</th>
                <th>Features & Driver</th>
                <th>Trips Assigned</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vehicles as $v): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($v['vehicle_name']) ?></strong></td>
                  <td><span class="status-pill status-in-progress"><?= htmlspecialchars($v['vehicle_type']) ?></span></td>
                  <td><i class="fa-solid fa-users"></i> <?= $v['capacity'] ?> Seats</td>
                  <td><strong><?= format_price($v['price_per_day']) ?> / day</strong></td>
                  <td style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($v['features']) ?></td>
                  <td><span class="status-pill status-confirmed"><?= $v['trips_assigned'] ?> bookings</span></td>
                  <td>
                    <div style="display:flex; gap:0.4rem;">
                      <button type="button" class="btn btn-outline btn-sm" onclick='openEditVehicleModal(<?= json_encode($v) ?>)'>
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <form action="<?= BASE_URL ?>/staff/manage-transport.php" method="POST" onsubmit="return confirm('Remove vehicle from fleet?');" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="delete_vehicle">
                        <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
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

<!-- Transport Modal -->
<div class="modal-backdrop" id="vehicleModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title" id="vehicleModalTitle">Add Fleet Vehicle</h3>
      <button type="button" class="modal-close" onclick="closeModal('vehicleModal')">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/staff/manage-transport.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="save_vehicle">
      <input type="hidden" name="vehicle_id" id="vehicleModalId" value="0">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Vehicle Name / Make <span style="color:var(--danger);">*</span></label>
          <input type="text" name="vehicle_name" id="vehicleName" class="form-control" required placeholder="e.g. Toyota KDH Super GL Luxury">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Vehicle Type</label>
            <select name="vehicle_type" id="vehicleType" class="form-control">
              <option value="Private Sedan Car">Private Sedan Car</option>
              <option value="AC Luxury Van">AC Luxury Van</option>
              <option value="4x4 Safari SUV">4x4 Safari SUV</option>
              <option value="Mini Tourist Coach">Mini Tourist Coach</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Seating Capacity</label>
            <input type="number" name="capacity" id="vehicleCapacity" class="form-control" min="1" max="50" value="4" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Daily Rental Rate (USD)</label>
          <input type="number" step="0.01" name="price_per_day" id="vehiclePrice" class="form-control" value="25.00" required>
        </div>

        <div class="form-group">
          <label class="form-label">Features & Chauffeur Details</label>
          <textarea name="features" id="vehicleFeatures" class="form-control" placeholder="e.g. Dual AC, English-speaking driver, fuel & tolls included..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('vehicleModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Vehicle</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddVehicleModal() {
  document.getElementById('vehicleModalTitle').textContent = 'Add Fleet Vehicle';
  document.getElementById('vehicleModalId').value = 0;
  document.getElementById('vehicleName').value = '';
  document.getElementById('vehicleCapacity').value = '4';
  document.getElementById('vehiclePrice').value = '25.00';
  document.getElementById('vehicleFeatures').value = 'Dual AC, Fuel Included, English Driver';
  openModal('vehicleModal');
}

function openEditVehicleModal(v) {
  document.getElementById('vehicleModalTitle').textContent = 'Edit Vehicle: ' + v.vehicle_name;
  document.getElementById('vehicleModalId').value = v.id;
  document.getElementById('vehicleName').value = v.vehicle_name;
  document.getElementById('vehicleType').value = v.vehicle_type;
  document.getElementById('vehicleCapacity').value = v.capacity;
  document.getElementById('vehiclePrice').value = v.price_per_day;
  document.getElementById('vehicleFeatures').value = v.features;
  openModal('vehicleModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
