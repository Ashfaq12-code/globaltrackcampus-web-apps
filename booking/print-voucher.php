<?php
/**
 * GlobeTrek Adventures - Printable Official Tour Voucher
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$bookingCode = clean($_GET['code'] ?? '');
if (empty($bookingCode)) {
    die("Invalid Booking Code.");
}

$booking = db_fetch_one("
    SELECT b.*, p.title as package_title, p.duration_days, p.duration_nights,
           d.name as destination_name,
           u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
           a.name as hotel_name, a.tier as hotel_tier,
           t.vehicle_name, t.vehicle_type,
           pay.transaction_ref, pay.payment_method, pay.paid_at
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.id
    JOIN destinations d ON p.destination_id = d.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN accommodations a ON b.accommodation_id = a.id
    LEFT JOIN transport_options t ON b.transport_id = t.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    WHERE b.booking_code = ?
", [$bookingCode]);

if (!$booking) {
    die("Booking not found.");
}

$addons = json_decode($booking['add_ons_json'] ?? '[]', true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Official Travel Voucher - <?= htmlspecialchars($booking['booking_code']) ?></title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; line-height: 1.5; padding: 2rem; }
    .voucher-box { max-width: 800px; margin: 0 auto; border: 2px solid #0284c7; border-radius: 12px; padding: 2.5rem; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
    .brand { font-size: 1.8rem; font-weight: 800; color: #0284c7; }
    .brand span { color: #f97316; }
    .badge { background: #0284c7; color: #fff; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 700; font-family: monospace; font-size: 1.1rem; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.3rem; }
    .info-row { display: flex; justify-content: space-between; font-size: 0.95rem; margin-bottom: 0.4rem; }
    .total-box { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1.2rem; margin-top: 1.5rem; }
    .total-amount { font-size: 1.5rem; font-weight: 800; color: #0284c7; text-align: right; }
    @media print {
      body { padding: 0; }
      .no-print { display: none; }
      .voucher-box { border: 1px solid #000; }
    }
  </style>
</head>
<body>

  <div class="no-print" style="text-align:center; margin-bottom:1.5rem;">
    <button onclick="window.print()" style="background:#0284c7; color:#fff; padding:0.6rem 1.5rem; font-size:1rem; border:none; border-radius:6px; cursor:pointer; font-weight:700;">
      Print Voucher / Save as PDF
    </button>
  </div>

  <div class="voucher-box">
    <div class="header">
      <div>
        <div class="brand">GlobeTrek<span>Adventures</span></div>
        <div style="font-size:0.85rem; color:#64748b;">Official Tour Service Voucher & Confirmation</div>
      </div>
      <div class="badge"><?= htmlspecialchars($booking['booking_code']) ?></div>
    </div>

    <div class="grid-2">
      <div>
        <div class="section-title">Lead Traveler Information</div>
        <div class="info-row"><span>Guest Name:</span> <strong><?= htmlspecialchars($booking['customer_name']) ?></strong></div>
        <div class="info-row"><span>Email:</span> <?= htmlspecialchars($booking['customer_email']) ?></div>
        <div class="info-row"><span>Phone:</span> <?= htmlspecialchars($booking['customer_phone'] ?: 'N/A') ?></div>
        <div class="info-row"><span>Total Travelers:</span> <strong><?= $booking['travelers_count'] ?> Person(s)</strong></div>
      </div>

      <div>
        <div class="section-title">Expedition Details</div>
        <div class="info-row"><span>Tour Package:</span> <strong><?= htmlspecialchars($booking['package_title']) ?></strong></div>
        <div class="info-row"><span>Destination:</span> <?= htmlspecialchars($booking['destination_name']) ?></div>
        <div class="info-row"><span>Duration:</span> <?= $booking['duration_days'] ?> Days / <?= $booking['duration_nights'] ?> Nights</div>
        <div class="info-row"><span>Travel Start Date:</span> <strong><?= date('d M Y', strtotime($booking['travel_date'])) ?></strong></div>
      </div>
    </div>

    <div class="grid-2">
      <div>
        <div class="section-title">Accommodation Arrangement</div>
        <div class="info-row"><span>Hotel / Lodge:</span> <strong><?= htmlspecialchars($booking['hotel_name'] ?: 'Standard Eco Chalet') ?></strong></div>
        <div class="info-row"><span>Standard:</span> <?= htmlspecialchars($booking['hotel_tier'] ?: 'Standard') ?></div>
        <div class="info-row"><span>Duration:</span> <?= $booking['duration_nights'] ?> Nights (Breakfast Included)</div>
      </div>

      <div>
        <div class="section-title">Transport & Chauffeur</div>
        <div class="info-row"><span>Vehicle:</span> <strong><?= htmlspecialchars($booking['vehicle_name'] ?: 'AC Private Sedan') ?></strong></div>
        <div class="info-row"><span>Service Type:</span> Private Chauffeur Guide (<?= $booking['duration_days'] ?> Days)</div>
        <div class="info-row"><span>Inclusions:</span> Fuel, Highway Tolls & Driver Lodging</div>
      </div>
    </div>

    <?php if (!empty($addons)): ?>
      <div style="margin-top:1rem;">
        <div class="section-title">Included Special Activities & Add-ons</div>
        <ul style="padding-left:1.5rem; margin-top:0.4rem; font-size:0.92rem;">
          <?php foreach ($addons as $adName => $adPrice): ?>
            <li><?= htmlspecialchars($adName) ?> (<?= format_price($adPrice) ?>)</li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="total-box">
      <div class="info-row"><span>Base Package Portion:</span> <span><?= format_price($booking['base_price']) ?></span></div>
      <div class="info-row"><span>Accommodation Upgrade:</span> <span><?= format_price($booking['hotel_price']) ?></span></div>
      <div class="info-row"><span>Transport Surcharge:</span> <span><?= format_price($booking['transport_price']) ?></span></div>
      <div class="info-row"><span>Add-ons Total:</span> <span><?= format_price($booking['addons_price']) ?></span></div>
      <div class="info-row" style="border-top:1px solid #cbd5e1; padding-top:0.6rem; margin-top:0.6rem;">
        <span style="font-weight:700; font-size:1.1rem;">Total Paid (USD):</span>
        <span class="total-amount"><?= format_price($booking['total_amount']) ?></span>
      </div>
      <div style="font-size:0.8rem; color:#64748b; margin-top:0.4rem;">
        Payment Status: <strong>PAID IN FULL</strong> &bull; TXN Ref: <?= htmlspecialchars($booking['transaction_ref'] ?: 'TXN-DIRECT') ?>
      </div>
    </div>

    <div style="margin-top:2rem; border-top:1px dashed #cbd5e1; padding-top:1rem; font-size:0.8rem; color:#64748b; display:flex; justify-content:space-between;">
      <div>GlobeTrek Adventures Ltd &bull; 24/7 Operations: +94 11 234 5678</div>
      <div>Please present this voucher to your chauffeur guide upon arrival.</div>
    </div>
  </div>

</body>
</html>
