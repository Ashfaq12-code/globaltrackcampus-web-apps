/**
 * GlobeTrek Adventures - Main Client JavaScript v2.0
 * Glassmorphism Navbar | Left Drawer | Hero Carousel | Scroll Animations
 * Username/Email Verification | Google Auth Display
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ================================================================
     1. NAVBAR: Glassmorphism scroll effect
  ================================================================ */
  const navbar = document.getElementById('mainNavbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 30) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }, { passive: true });
  }

  /* ================================================================
     2. USER DROPDOWN
  ================================================================ */
  const userDropdown = document.getElementById('userDropdown');
  const userBadgeBtn = document.getElementById('userBadgeBtn');
  const userDropdownMenu = document.getElementById('userDropdownMenu');

  if (userBadgeBtn && userDropdownMenu) {
    userBadgeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = userDropdownMenu.classList.toggle('open');
      userDropdown.classList.toggle('open', isOpen);
      userBadgeBtn.setAttribute('aria-expanded', isOpen);
    });

    document.addEventListener('click', (e) => {
      if (!userDropdown.contains(e.target)) {
        userDropdownMenu.classList.remove('open');
        userDropdown.classList.remove('open');
        userBadgeBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ================================================================
     3. LEFT SLIDE DRAWER
  ================================================================ */
  const hamburger    = document.getElementById('hamburgerBtn');
  const navDrawer    = document.getElementById('navDrawer');
  const drawerOverlay = document.getElementById('drawerOverlay');
  const drawerClose  = document.getElementById('drawerClose');

  function openDrawer() {
    navDrawer.classList.add('open');
    drawerOverlay.classList.add('open');
    hamburger.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    navDrawer.setAttribute('aria-hidden', 'false');
    drawerOverlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    navDrawer.classList.remove('open');
    drawerOverlay.classList.remove('open');
    hamburger.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    navDrawer.setAttribute('aria-hidden', 'true');
    drawerOverlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  if (hamburger) hamburger.addEventListener('click', openDrawer);
  if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
  if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);

  // Close drawer on ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDrawer();
  });

  /* ================================================================
     4. HERO AUTO-SWIPE CAROUSEL
  ================================================================ */
  const heroCarousel = document.querySelector('.hero-carousel');
  if (heroCarousel) {
    const slides   = heroCarousel.querySelectorAll('.hero-slide');
    const dots     = heroCarousel.querySelectorAll('.hero-dot');
    const prevBtn  = heroCarousel.querySelector('.hero-arrow-prev');
    const nextBtn  = heroCarousel.querySelector('.hero-arrow-next');

    let current  = 0;
    let timer    = null;
    const DELAY  = 5500; // ms between auto-slides

    function goTo(index) {
      slides[current].classList.remove('active');
      if (dots[current]) dots[current].classList.remove('active');

      current = (index + slides.length) % slides.length;

      slides[current].classList.add('active');
      if (dots[current]) dots[current].classList.add('active');

      // Re-trigger slide content animation
      const content = slides[current].querySelector('.slide-content');
      if (content) {
        content.querySelectorAll('[style*="animation"]').forEach(el => {
          el.style.animation = 'none';
          void el.offsetHeight; // reflow
          el.style.animation = '';
        });
      }
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startTimer() {
      clearInterval(timer);
      timer = setInterval(next, DELAY);
    }

    function resetTimer() {
      clearInterval(timer);
      startTimer();
    }

    if (prevBtn) prevBtn.addEventListener('click', () => { prev(); resetTimer(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { next(); resetTimer(); });

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => { goTo(i); resetTimer(); });
    });

    // Swipe touch support
    let touchStartX = 0;
    heroCarousel.addEventListener('touchstart', (e) => { touchStartX = e.changedTouches[0].clientX; }, { passive: true });
    heroCarousel.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); resetTimer(); }
    }, { passive: true });

    // Init first slide
    slides[0].classList.add('active');
    if (dots[0]) dots[0].classList.add('active');
    startTimer();

    // Pause on hover
    heroCarousel.addEventListener('mouseenter', () => clearInterval(timer));
    heroCarousel.addEventListener('mouseleave', startTimer);
  }

  /* ================================================================
     5. SCROLL REVEAL ANIMATIONS (Intersection Observer)
  ================================================================ */
  const animObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
        animObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.animate-on-scroll, .animate-left, .animate-right, .animate-scale')
    .forEach(el => animObserver.observe(el));

  /* ================================================================
     6. AUTO-DISMISS FLASH ALERTS
  ================================================================ */
  document.querySelectorAll('.alert').forEach(alert => {
    const closeBtn = alert.querySelector('.alert-close');
    if (closeBtn) closeBtn.addEventListener('click', () => dismissAlert(alert));
    setTimeout(() => dismissAlert(alert), 6000);
  });

  function dismissAlert(el) {
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateX(30px)';
    setTimeout(() => el.remove(), 500);
  }

  /* ================================================================
     7. LIVE TOUR FILTERING (tours.php)
  ================================================================ */
  const tourSearch   = document.getElementById('tourFilterInput');
  const categoryFil  = document.getElementById('categoryFilter');
  const difficultyFil= document.getElementById('difficultyFilter');
  const tourCards    = document.querySelectorAll('.tour-card-item');

  function filterTours() {
    if (!tourCards.length) return;
    const q    = (tourSearch ? tourSearch.value.toLowerCase() : '');
    const cat  = (categoryFil ? categoryFil.value : 'all');
    const diff = (difficultyFil ? difficultyFil.value : 'all');
    let visible = 0;
    tourCards.forEach(card => {
      const title = (card.dataset.title || '').toLowerCase();
      const dest  = (card.dataset.dest  || '').toLowerCase();
      const ccat  = (card.dataset.category   || '').toLowerCase();
      const cdiff = (card.dataset.difficulty || '').toLowerCase();
      const show  = (title.includes(q) || dest.includes(q))
                 && (cat  === 'all' || ccat  === cat.toLowerCase())
                 && (diff === 'all' || cdiff === diff.toLowerCase());
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    const noRes = document.getElementById('noToursFound');
    if (noRes) noRes.style.display = (visible === 0) ? 'block' : 'none';
  }

  if (tourSearch)    tourSearch.addEventListener('input', filterTours);
  if (categoryFil)   categoryFil.addEventListener('change', filterTours);
  if (difficultyFil) difficultyFil.addEventListener('change', filterTours);

}); /* END DOMContentLoaded */

/* ================================================================
   8. USERNAME / EMAIL VERIFICATION WITH SOUND & GREEN TICK
================================================================ */
function setupLoginVerification() {
  const VALID_USERNAME   = 'malik3924';
  const VALID_ADMIN_EMAIL = 'prinzzjrmalik7@gmail.com';

  // Play a short success chime using Web Audio API
  function playSuccessChime() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.type = 'sine';
      osc.frequency.setValueAtTime(880, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.12);
      gain.gain.setValueAtTime(0.35, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.5);
    } catch(e) {}
  }

  function playErrorBuzz() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(180, ctx.currentTime);
      gain.gain.setValueAtTime(0.2, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.3);
      if (navigator.vibrate) navigator.vibrate([80, 40, 80]);
    } catch(e) {}
  }

  function setFieldState(input, suffix, valid) {
    input.classList.toggle('is-valid',   valid);
    input.classList.toggle('is-invalid', !valid);
    if (suffix) {
      suffix.className = 'input-suffix ' + (valid ? 'valid' : 'invalid');
      suffix.innerHTML = valid
        ? '<i class="fa-solid fa-circle-check"></i>'
        : '<i class="fa-solid fa-circle-xmark"></i>';
    }
  }

  // Username field verification
  const usernameInput  = document.getElementById('loginUsername');
  const usernameSuffix = document.getElementById('usernameSuffix');
  let usernameChimePlayed = false;

  if (usernameInput) {
    usernameInput.addEventListener('input', () => {
      const val = usernameInput.value.trim();
      if (val === '') {
        usernameInput.classList.remove('is-valid','is-invalid');
        if (usernameSuffix) usernameSuffix.className = 'input-suffix';
        usernameChimePlayed = false;
        return;
      }
      const isValid = val === VALID_USERNAME;
      setFieldState(usernameInput, usernameSuffix, isValid);
      if (isValid && !usernameChimePlayed) {
        playSuccessChime();
        usernameChimePlayed = true;
        showVerifyBadge('username-verify-badge', 'Administrator Malik — Verified ✓');
      } else if (!isValid) {
        usernameChimePlayed = false;
        hideVerifyBadge('username-verify-badge');
        if (val.length >= VALID_USERNAME.length) playErrorBuzz();
      }
    });
  }

  // Admin email verification
  const adminEmailInput  = document.getElementById('adminEmailInput');
  const adminEmailSuffix = document.getElementById('adminEmailSuffix');
  let emailChimePlayed = false;

  if (adminEmailInput) {
    adminEmailInput.addEventListener('input', () => {
      const val = adminEmailInput.value.trim().toLowerCase();
      if (val === '') {
        adminEmailInput.classList.remove('is-valid','is-invalid');
        if (adminEmailSuffix) adminEmailSuffix.className = 'input-suffix';
        emailChimePlayed = false;
        return;
      }
      const isValid = val === VALID_ADMIN_EMAIL;
      setFieldState(adminEmailInput, adminEmailSuffix, isValid);
      if (isValid && !emailChimePlayed) {
        playSuccessChime();
        emailChimePlayed = true;
        showVerifyBadge('email-verify-badge', 'prinzzjrmalik7@gmail.com — Authorized ✓');
        // Show Google Auth section
        const gaSection = document.getElementById('googleAuthSection');
        if (gaSection) {
          gaSection.style.display = 'block';
          gaSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      } else if (!isValid) {
        emailChimePlayed = false;
        hideVerifyBadge('email-verify-badge');
        if (val.includes('@') && val.length > 10) playErrorBuzz();
      }
    });
  }

  function showVerifyBadge(id, text) {
    const badge = document.getElementById(id);
    if (badge) {
      badge.textContent = text;
      badge.style.display = 'inline-flex';
      badge.style.animation = 'none';
      void badge.offsetHeight;
      badge.style.animation = '';
    }
  }
  function hideVerifyBadge(id) {
    const badge = document.getElementById(id);
    if (badge) badge.style.display = 'none';
  }
}

// Auto-init on login page
if (document.getElementById('loginUsername')) {
  setupLoginVerification();
}

/* ================================================================
   9. DEMO LOGIN AUTO-FILL
================================================================ */
function fillDemoLogin(identifier, password) {
  const uInput = document.getElementById('loginUsername');
  const pInput = document.getElementById('loginPassword');
  if (uInput && pInput) {
    uInput.value = identifier;
    pInput.value = password;
    uInput.dispatchEvent(new Event('input'));
  }
}

/* ================================================================
   10. MODAL HELPERS
================================================================ */
function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.add('show'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.remove('show'); document.body.style.overflow = ''; }
}
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', (e) => { if (e.target === m) closeModal(m.id); });
});
