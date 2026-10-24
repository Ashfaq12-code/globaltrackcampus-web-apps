<?php
/**
 * GlobeTrek Adventures - Interactive 4-Step Booking Wizard
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

$packageId = (int)($_GET['id'] ?? 0);
if ($packageId <= 0) {
    // Default to first active package if none specified
    $packageId = (int)db_fetch_val("SELECT id FROM tour_packages WHERE status = 'Active' ORDER BY is_featured DESC, id ASC LIMIT 1");
}

$pkg = db_fetch_one("
    SELECT p.*, d.name AS destination_name 
    FROM tour_packages p 
    JOIN destinations d ON p.destination_id = d.id 
    WHERE p.id = ? AND p.status = 'Active'
", [$packageId]);

if (!$pkg) {
    set_flash('danger', 'Selected tour package is not available.');
    header('Location: ' . BASE_URL . '/tours.php');
    exit;
}

$pageTitle = "Book Tour: " . $pkg['title'];
require_once __DIR__ . '/../includes/header.php';

$user = current_user();

// Fetch Accommodation Options for this destination
$accommodations = db_fetch_all("
    SELECT * FROM accommodations 
    WHERE destination_id = ? OR destination_id = 1 
    ORDER BY price_per_night ASC
", [$pkg['destination_id']]);

// Fetch Transport Options
$transports = db_fetch_all("SELECT * FROM transport_options ORDER BY price_per_day ASC");

// Fetch Optional Add-ons
$addons = db_fetch_all("SELECT * FROM package_addons ORDER BY price ASC");
?>

<div class="booking-page-wrap">
  <div class="container">
    
    <!-- Wizard Step Indicator -->
    <div class="wizard-steps">
      <div class="wizard-step-item active" data-step="1">
        <div class="wizard-circle">1</div>
        <div class="wizard-label">Tour & Dates</div>
      </div>
      <div class="wizard-step-item" data-step="2">
        <div class="wizard-circle">2</div>
        <div class="wizard-label">Customization</div>
      </div>
      <div class="wizard-step-item" data-step="3">
        <div class="wizard-circle">3</div>
        <div class="wizard-label">Traveler Info</div>
      </div>
      <div class="wizard-step-item" data-step="4">
        <div class="wizard-circle">4</div>
        <div class="wizard-label">Payment & Confirm</div>
      </div>
    </div>

    <!-- Booking Main Form -->
    <form action="<?= BASE_URL ?>/booking/process-booking.php" method="POST" id="bookingWizardForm" 
          data-base-price="<?= $pkg['base_price'] ?>"
          data-duration-days="<?= $pkg['duration_days'] ?>"
          data-duration-nights="<?= $pkg['duration_nights'] ?>">
      
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">

      <div class="booking-grid">
        
        <!-- Left: Wizard Step Panels -->
        <div class="booking-main-content">
          
          <!-- STEP 1: Tour Dates & Travelers -->
          <div class="wizard-step-panel" data-step="1">
            <h2 class="booking-section-title"><i class="fa-solid fa-calendar-days" style="color:var(--primary);"></i> Select Dates & Travelers</h2>
            <p class="booking-section-desc">Review your selected package and choose your departure schedule.</p>

            <div style="background:var(--bg-main); border:1px solid var(--border); border-radius:var(--radius-md); padding:1.5rem; margin-bottom:2rem; display:flex; gap:1.5rem; align-items:center;">
              <div style="width:110px; height:80px; border-radius:var(--radius-sm); overflow:hidden; flex-shrink:0;">
                <img src="https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?q=80&w=400&auto=format&fit=crop" style="width:100%; height:100%; object-fit:cover;">
              </div>
              <div>
                <span class="status-pill status-in-progress" style="margin-bottom:0.2rem;"><?= htmlspecialchars($pkg['destination_name']) ?></span>
                <h3 style="font-size:1.15rem; color:var(--secondary); margin-bottom:0.3rem;"><?= htmlspecialchars($pkg['title']) ?></h3>
                <div style="font-size:0.85rem; color:var(--text-muted);">
                  <i class="fa-solid fa-clock"></i> <?= $pkg['duration_days'] ?> Days / <?= $pkg['duration_nights'] ?> Nights &bull; Base Rate: <strong><?= format_price($pkg['base_price']) ?> / person</strong>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-calendar-day"></i> Preferred Travel Date <span style="color:var(--danger);">*</span></label>
                <input type="date" name="travel_date" id="travelDate" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
              </div>

              <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-users"></i> Number of Travelers <span style="color:var(--danger);">*</span></label>
                <input type="number" name="travelers_count" id="travelersCount" class="form-control" min="1" max="15" value="2" required>
              </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:2rem;">
              <button type="button" class="btn btn-primary btn-lg" onclick="goToBookingStep(2)">
                Continue to Customization <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>

          <!-- STEP 2: Customization (Hotel, Transport, Addons) -->
          <div class="wizard-step-panel" data-step="2" style="display:none;">
            <h2 class="booking-section-title"><i class="fa-solid fa-sliders" style="color:var(--accent);"></i> Customize Your Trip Options</h2>
            <p class="booking-section-desc">Upgrade your hotel tier, choose your preferred vehicle, and add exciting excursions.</p>

            <!-- 1. Accommodation Selection -->
            <h3 style="font-size:1.15rem; margin-bottom:1rem; color:var(--secondary);"><i class="fa-solid fa-hotel" style="color:var(--primary);"></i> Select Accommodation Tier (<?= $pkg['duration_nights'] ?> Nights)</h3>
            <div class="option-cards-grid">
              <?php foreach ($accommodations as $idx => $acc): ?>
                <label class="option-select-card hotel-card <?= ($idx === 0) ? 'selected' : '' ?>">
                  <div style="display:flex; align-items:center; gap:1rem;">
                    <input type="radio" name="accommodation_id" value="<?= $acc['id'] ?>" 
                           data-price="<?= $acc['price_per_night'] ?>" 
                           data-name="<?= htmlspecialchars($acc['name']) ?>"
                           <?= ($idx === 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                    <div class="option-info">
                      <span class="option-title"><?= htmlspecialchars($acc['name']) ?></span>
                      <span class="option-meta"><?= htmlspecialchars($acc['amenities']) ?></span>
                    </div>
                  </div>
                  <div class="option-price-tag">
                    <?= ($acc['price_per_night'] == 0) ? 'Included' : '+' . format_price($acc['price_per_night']) . ' / night' ?>
                  </div>
                </label>
              <?php endforeach; ?>
            </div>

            <!-- 2. Transport Selection -->
            <h3 style="font-size:1.15rem; margin-bottom:1rem; color:var(--secondary);"><i class="fa-solid fa-van-shuttle" style="color:var(--accent);"></i> Select Transportation (<?= $pkg['duration_days'] ?> Days)</h3>
            <div class="option-cards-grid">
              <?php foreach ($transports as $idx => $t): ?>
                <label class="option-select-card transport-card <?= ($idx === 0) ? 'selected' : '' ?>">
                  <div style="display:flex; align-items:center; gap:1rem;">
                    <input type="radio" name="transport_id" value="<?= $t['id'] ?>" 
                           data-price="<?= $t['price_per_day'] ?>" 
                           data-name="<?= htmlspecialchars($t['vehicle_name']) ?>"
                           <?= ($idx === 0) ? 'checked' : '' ?> style="width:18px; height:18px;">
                    <div class="option-info">
                      <span class="option-title"><?= htmlspecialchars($t['vehicle_name']) ?></span>
                      <span class="option-meta"><i class="fa-solid fa-users"></i> Capacity: <?= $t['capacity'] ?> Seats &bull; <?= htmlspecialchars($t['features']) ?></span>
                    </div>
                  </div>
                  <div class="option-price-tag">
                    +<?= format_price($t['price_per_day']) ?> / day
                  </div>
                </label>
              <?php endforeach; ?>
            </div>

            <!-- 3. Optional Add-ons -->
            <h3 style="font-size:1.15rem; margin-bottom:1rem; color:var(--secondary);"><i class="fa-solid fa-plus-circle" style="color:var(--success);"></i> Optional Experiences & Add-ons</h3>
            <div class="option-cards-grid">
              <?php foreach ($addons as $ad): ?>
                <label class="option-select-card">
                  <div style="display:flex; align-items:center; gap:1rem;">
                    <input type="checkbox" name="addons[]" value="<?= htmlspecialchars($ad['name']) ?>" 
                           data-price="<?= $ad['price'] ?>" style="width:18px; height:18px;">
                    <div class="option-info">
                      <span class="option-title"><?= htmlspecialchars($ad['name']) ?></span>
                      <span class="option-meta"><?= htmlspecialchars($ad['description']) ?></span>
                    </div>
                  </div>
                  <div class="option-price-tag">+<?= format_price($ad['price']) ?></div>
                </label>
              <?php endforeach; ?>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:2rem;">
              <button type="button" class="btn btn-outline btn-lg" onclick="goToBookingStep(1)">
                <i class="fa-solid fa-arrow-left"></i> Back
              </button>
              <button type="button" class="btn btn-primary btn-lg" onclick="goToBookingStep(3)">
                Continue to Traveler Details <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>

          <!-- STEP 3: Traveler Info -->
          <div class="wizard-step-panel" data-step="3" style="display:none;">
            <h2 class="booking-section-title"><i class="fa-solid fa-address-card" style="color:var(--primary);"></i> Primary Traveler Information</h2>
            <p class="booking-section-desc">Enter contact details for booking vouchers and confirmation dispatch.</p>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                <input type="text" name="guest_name" id="guestFullName" class="form-control" required placeholder="John Doe" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Email Address <span style="color:var(--danger);">*</span></label>
                <input type="email" name="guest_email" id="guestEmail" class="form-control" required placeholder="john@example.com" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Contact Phone / WhatsApp <span style="color:var(--danger);">*</span></label>
                <input type="text" name="guest_phone" class="form-control" required placeholder="+1 555 234 5678" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Country of Residence</label>
                <input type="text" name="guest_country" class="form-control" placeholder="e.g. United Kingdom" value="United States">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Special Requests / Dietary Requirements / Notes</label>
              <textarea name="custom_notes" class="form-control" placeholder="e.g. Vegetarian meals required, airport pickup at 9am, anniversary setup in hotel..."></textarea>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:2rem;">
              <button type="button" class="btn btn-outline btn-lg" onclick="goToBookingStep(2)">
                <i class="fa-solid fa-arrow-left"></i> Back
              </button>
              <button type="button" class="btn btn-primary btn-lg" onclick="goToBookingStep(4)">
                Proceed to Payment <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>

          <!-- STEP 4: Payment Simulation & Checkout -->
          <div class="wizard-step-panel" data-step="4" style="display:none;">
            <h2 class="booking-section-title"><i class="fa-solid fa-credit-card" style="color:var(--success);"></i> Simulated Payment Gateway</h2>
            <p class="booking-section-desc">Select your payment method. (Demonstration payment simulation with instant confirmation voucher).</p>

            <div class="payment-tabs">
              <div class="payment-tab-btn active" onclick="selectPaymentMethod('Credit Card')">
                <i class="fa-solid fa-credit-card" style="font-size:1.4rem; display:block; margin-bottom:0.4rem; color:var(--primary);"></i>
                Credit / Debit Card
              </div>
              <div class="payment-tab-btn" onclick="selectPaymentMethod('PayPal')">
                <i class="fa-brands fa-paypal" style="font-size:1.4rem; display:block; margin-bottom:0.4rem; color:#0284c7;"></i>
                PayPal Express
              </div>
              <div class="payment-tab-btn" onclick="selectPaymentMethod('Bank Transfer')">
                <i class="fa-solid fa-building-columns" style="font-size:1.4rem; display:block; margin-bottom:0.4rem; color:var(--accent);"></i>
                Bank Transfer
              </div>
            </div>

            <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="Credit Card">

            <!-- Simulated Card Inputs -->
            <div id="cardPaymentDetails">
              <div class="form-group">
                <label class="form-label">Cardholder Name</label>
                <input type="text" name="card_name" class="form-control" placeholder="John Doe" value="<?= htmlspecialchars($user['full_name'] ?? 'John Doe') ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Card Number</label>
                <input type="text" name="card_number" class="form-control" placeholder="4242 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242" value="4242 •••• •••• 4242">
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Expiry Date</label>
                  <input type="text" name="card_exp" class="form-control" placeholder="MM/YY" value="12/28">
                </div>

                <div class="form-group">
                  <label class="form-label">CVC / Security Code</label>
                  <input type="text" name="card_cvc" class="form-control" placeholder="CVC" value="888">
                </div>
              </div>
            </div>

            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-md); padding:1rem; margin:1.5rem 0; font-size:0.88rem; color:#166534; display:flex; align-items:center; gap:0.6rem;">
              <i class="fa-solid fa-lock" style="font-size:1.2rem;"></i>
              <span>256-Bit Encrypted Simulated Checkout. No real credit card will be billed during this university grading test.</span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:2rem;">
              <button type="button" class="btn btn-outline btn-lg" onclick="goToBookingStep(3)">
                <i class="fa-solid fa-arrow-left"></i> Back
              </button>
              <button type="submit" class="btn btn-accent btn-lg" style="font-size:1.1rem; padding:0.9rem 2.2rem;">
                <i class="fa-solid fa-circle-check"></i> Complete Booking & Pay Now
              </button>
            </div>
          </div>

        </div>

        <!-- Right: Live Real-Time Price Summary Card -->
        <div class="booking-summary-card">
          <h3 class="summary-title"><i class="fa-solid fa-receipt" style="color:var(--primary);"></i> Price Breakdown</h3>

          <div class="summary-item-row highlight">
            <span>Tour Package:</span>
            <span><?= htmlspecialchars($pkg['title']) ?></span>
          </div>

          <div class="summary-item-row">
            <span>Travelers:</span>
            <span id="sumTravelers">2 Person(s)</span>
          </div>

          <div class="summary-item-row">
            <span>Base Package Cost:</span>
            <span id="sumPackageTotal"><?= format_price($pkg['base_price'] * 2) ?></span>
          </div>

          <div class="summary-item-row">
            <span>Accommodation (<?= $pkg['duration_nights'] ?>N):</span>
            <span id="sumHotelTotal">$0.00</span>
          </div>

          <div class="summary-item-row">
            <span>Transport (<?= $pkg['duration_days'] ?>D):</span>
            <span id="sumTransportTotal">$75.00</span>
          </div>

          <div class="summary-item-row">
            <span>Optional Add-ons:</span>
            <span id="sumAddonsTotal">$0.00</span>
          </div>

          <div class="summary-total-row">
            <span class="total-label">Grand Total</span>
            <span class="total-amount" id="sumGrandTotal"><?= format_price($pkg['base_price'] * 2 + 75) ?></span>
          </div>

          <div style="margin-top:1.5rem; font-size:0.8rem; color:var(--text-light); text-align:center;">
            Includes all local government taxes, conservation entrance fees & guide allowances.
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

<script>
function selectPaymentMethod(method) {
  document.querySelectorAll('.payment-tab-btn').forEach(btn => btn.classList.remove('active'));
  event.currentTarget.classList.add('active');
  document.getElementById('selectedPaymentMethod').value = method;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
