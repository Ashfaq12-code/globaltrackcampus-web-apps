<?php
/**
 * GlobeTrek Adventures - Global Footer Template
 */
?>
  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-brand">GlobeTrek<span>Adventures</span></div>
          <p class="footer-desc">
            Sri Lanka's premier experiential adventure and cultural travel management platform. We deliver bespoke, sustainable, and unforgettable journeys across the Pearl of the Indian Ocean.
          </p>
          <div style="display:flex; gap:1rem; color:#94a3b8; font-size:1.2rem;">
            <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-twitter"></i></a>
            <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-tripadvisor"></i></a>
          </div>
        </div>

        <div>
          <h4 class="footer-heading">Top Destinations</h4>
          <ul class="footer-links">
            <li><a href="<?= BASE_URL ?>/tours.php?dest=Ella">Ella Highlands</a></li>
            <li><a href="<?= BASE_URL ?>/tours.php?dest=Kandy">Kandy Cultural City</a></li>
            <li><a href="<?= BASE_URL ?>/tours.php?dest=Sigiriya">Sigiriya Lion Rock</a></li>
            <li><a href="<?= BASE_URL ?>/tours.php?dest=Galle">Galle Dutch Fort</a></li>
            <li><a href="<?= BASE_URL ?>/tours.php?dest=Mirissa">Mirissa Whale Coast</a></li>
            <li><a href="<?= BASE_URL ?>/tours.php?dest=Yala">Yala Safari Park</a></li>
          </ul>
        </div>

        <div>
          <h4 class="footer-heading">Quick Links</h4>
          <ul class="footer-links">
            <li><a href="<?= BASE_URL ?>/tours.php">All Tour Packages</a></li>
            <li><a href="<?= BASE_URL ?>/accommodation.php">Hotel Partners</a></li>
            <li><a href="<?= BASE_URL ?>/transport.php">Transport Fleet</a></li>
            <li><a href="<?= BASE_URL ?>/travel-guides.php">Travel Guides</a></li>
            <li><a href="<?= BASE_URL ?>/about.php">About Our Team</a></li>
            <li><a href="<?= BASE_URL ?>/contact.php">Contact & Support</a></li>
          </ul>
        </div>

        <div>
          <h4 class="footer-heading">Stay Connected</h4>
          <p style="font-size:0.88rem; color:#94a3b8; margin-bottom:1rem;">
            Subscribe to our newsletter for exclusive Sri Lanka adventure deals and travel insider guides.
          </p>
          <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to GlobeTrek Adventures!');" style="display:flex; gap:0.5rem;">
            <input type="email" placeholder="Your email address" required style="padding:0.6rem 0.8rem; border-radius:var(--radius-sm); border:1px solid #475569; background:#1e293b; color:#fff; font-size:0.9rem; flex-grow:1;">
            <button type="submit" class="btn btn-accent btn-sm">Join</button>
          </form>
          <div style="margin-top:1.2rem; font-size:0.85rem; color:#94a3b8;">
            <i class="fa-solid fa-phone" style="color:var(--primary); margin-right:0.4rem;"></i> +94 11 234 5678<br>
            <i class="fa-solid fa-envelope" style="color:var(--primary); margin-right:0.4rem;"></i> info@globetrek.com
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <div>&copy; <?= date('Y') ?> GlobeTrek Adventures Ltd. All rights reserved. Registered Tourism Operator.</div>
        <div style="display:flex; gap:1.5rem;">
          <a href="#" style="color:#64748b;">Privacy Policy</a>
          <a href="#" style="color:#64748b;">Terms of Service</a>
          <a href="#" style="color:#64748b;">Security</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/booking.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/charts.js"></script>
</body>
</html>
