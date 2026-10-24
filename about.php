<?php
/**
 * GlobeTrek Adventures - About Us
 */
$pageTitle = 'About Us & Our Mission | GlobeTrek Adventures';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';
?>

<div style="background-color:#ffffff; border-bottom:1px solid var(--border); padding:3.5rem 0;">
  <div class="container" style="text-align:center; max-width:800px; margin:0 auto;">
    <span class="section-tag">Our Story & Heritage</span>
    <h1 style="font-size:2.8rem; margin-bottom:0.8rem;">Pioneering Authentic Experiential Travel</h1>
    <p style="color:var(--text-muted); font-size:1.1rem; line-height:1.7;">
      GlobeTrek Adventures was founded to transform tourism in Sri Lanka into immersive, personalized, and ecologically responsible experiences.
    </p>
  </div>
</div>

<div class="container" style="padding-top:3.5rem; padding-bottom:5rem;">
  
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:3rem; align-items:center; margin-bottom:4rem;">
    <div>
      <span class="section-tag">Who We Are</span>
      <h2 style="font-size:2rem; margin-bottom:1rem;">Crafting Journeys, Not Just Itineraries</h2>
      <p style="color:var(--text-main); font-size:1rem; line-height:1.8; margin-bottom:1rem;">
        From our headquarters in Colombo and regional coordination hubs in Kandy and Galle, GlobeTrek Adventures provides seamless end-to-end holiday planning. We empower international travelers to customize every facet of their Sri Lanka expedition—from cozy tea planter bungalows to luxury coastal villas, private chauffeur fleets, and accredited nature trackers.
      </p>
      <p style="color:var(--text-main); font-size:1rem; line-height:1.8;">
        Our team of seasoned tour directors and multilingual chauffeur guides bridge ancient historical legacy with modern comfort and uncompromising safety standards.
      </p>
    </div>
    <div style="border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-lg); height:380px;">
      <img src="https://images.unsplash.com/photo-1546708973-b339540b5162?q=80&w=1000&auto=format&fit=crop" alt="GlobeTrek Team" style="width:100%; height:100%; object-fit:cover;">
    </div>
  </div>

  <!-- Core Pillars -->
  <div class="section-header">
    <span class="section-tag">Our Guiding Values</span>
    <h2 class="section-title">The GlobeTrek Standard</h2>
  </div>

  <div class="grid-3" style="margin-bottom:4rem;">
    <div class="feature-box">
      <div class="feature-icon-wrap"><i class="fa-solid fa-leaf"></i></div>
      <h3 class="feature-title">Eco & Community Tourism</h3>
      <p class="feature-desc">We channel 5% of all expedition revenues directly into rural reforestation and sea turtle conservation along the southern coast.</p>
    </div>

    <div class="feature-box">
      <div class="feature-icon-wrap"><i class="fa-solid fa-handshake-angle"></i></div>
      <h3 class="feature-title">Transparent Pricing</h3>
      <p class="feature-desc">No hidden surcharges. Our dynamic price calculator allows travelers to see exact itemized costs for accommodation, transport, and excursions.</p>
    </div>

    <div class="feature-box">
      <div class="feature-icon-wrap"><i class="fa-solid fa-certificate"></i></div>
      <h3 class="feature-title">Certified Excellence</h3>
      <p class="feature-desc">Officially registered with the Sri Lanka Tourism Development Authority (SLTDA) with comprehensive passenger safety protocols.</p>
    </div>
  </div>

  <!-- Operations Leadership Team -->
  <div class="section-header">
    <span class="section-tag">Our People</span>
    <h2 class="section-title">Meet the Operations Team</h2>
  </div>

  <div class="grid-3">
    <div style="background:#fff; border-radius:var(--radius-md); border:1px solid var(--border); padding:1.8rem; text-align:center;">
      <div style="width:80px; height:80px; border-radius:var(--radius-full); background:var(--primary-light); color:var(--primary); font-size:2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; margin-bottom:1rem;">
        DP
      </div>
      <h4 style="font-size:1.15rem; margin-bottom:0.2rem;">Dhammika Perera</h4>
      <div style="font-size:0.85rem; color:var(--primary); font-weight:600; margin-bottom:0.8rem;">Head of Operations & Logistics</div>
      <p style="font-size:0.88rem; color:var(--text-muted);">15+ years coordinating high-altitude hill country trekking and cultural expeditions.</p>
    </div>

    <div style="background:#fff; border-radius:var(--radius-md); border:1px solid var(--border); padding:1.8rem; text-align:center;">
      <div style="width:80px; height:80px; border-radius:var(--radius-full); background:#fef3c7; color:#d97706; font-size:2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; margin-bottom:1rem;">
        KP
      </div>
      <h4 style="font-size:1.15rem; margin-bottom:0.2rem;">Kavinda Perera</h4>
      <div style="font-size:0.85rem; color:var(--accent); font-weight:600; margin-bottom:0.8rem;">Tour Director & Guide Coordinator</div>
      <p style="font-size:0.88rem; color:var(--text-muted);">Certified wildlife naturalist and master coordinator of Southern marine tours.</p>
    </div>

    <div style="background:#fff; border-radius:var(--radius-md); border:1px solid var(--border); padding:1.8rem; text-align:center;">
      <div style="width:80px; height:80px; border-radius:var(--radius-full); background:#ede9fe; color:#7c3aed; font-size:2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; margin-bottom:1rem;">
        AF
      </div>
      <h4 style="font-size:1.15rem; margin-bottom:0.2rem;">Ananya Fernando</h4>
      <div style="font-size:0.85rem; color:#7c3aed; font-weight:600; margin-bottom:0.8rem;">Customer Experience Lead</div>
      <p style="font-size:0.88rem; color:var(--text-muted);">Dedicated to ensuring 24/7 guest satisfaction, customized itineraries, and ticket support.</p>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
