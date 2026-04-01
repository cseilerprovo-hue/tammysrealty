/* ── NAV HAMBURGER ── */
document.addEventListener('DOMContentLoaded', () => {

  /* ── HAMBURGER MENU ──────────────────────────────────────────────── */
  const hamburger = document.querySelector('.hamburger');
  const navLinks  = document.querySelector('.nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
  }

  /* ── LEGACY FORM HANDLING (data-form attribute) ───────────────────── */
  document.querySelectorAll('form[data-form]').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const msg = form.querySelector('.success-msg');
      if (msg) {
        msg.style.display = 'block';
        form.reset();
        const firstRatings = form.querySelectorAll('input[type="radio"]');
        firstRatings.forEach(r => { if (r.dataset.default) r.checked = true; });
        setTimeout(() => msg.style.display = 'none', 7000);
        msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  });

  /* ── GENERAL CONTACT FORMS → contact.php ─────────────────────────── */
  // Handles: homeValueForm, relocationForm, quickContactForm, contactForm
  ['homeValueForm', 'relocationForm', 'quickContactForm', 'contactForm'].forEach(function(id) {
    const form = document.getElementById(id);
    if (!form) return;

    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const btn = this.querySelector('button[type="submit"]');
      const originalText = btn ? btn.textContent : '';
      if (btn) { btn.textContent = 'Sending...'; btn.disabled = true; }

      fetch('contact.php', {
        method: 'POST',
        body: new FormData(this)
      })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (res.success) {
          const successId = id.replace('Form', 'Success');
          const msg = document.getElementById(successId);
          if (msg) {
            msg.style.display = 'block';
            msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
          form.reset();
        } else {
          alert(res.message);
        }
      })
      .catch(function() {
        alert('Something went wrong. Please call Tammy directly at 385-327-9225.');
      })
      .finally(function() {
        if (btn) { btn.textContent = originalText; btn.disabled = false; }
      });
    });
  });

  /* ── RENTAL APPLICATION FORM → rental-application.php ────────────── */
  const rentalForm = document.getElementById('rentalForm');
  if (rentalForm) {
    rentalForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const btn = this.querySelector('button[type="submit"]');
      const originalText = btn ? btn.textContent : '';
      if (btn) { btn.textContent = 'Submitting...'; btn.disabled = true; }

      fetch('rental-application.php', {
        method: 'POST',
        body: new FormData(this)
      })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (res.success) {
          const msg = document.getElementById('rentalSuccess');
          if (msg) {
            msg.style.display = 'block';
            msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
          rentalForm.reset();
        } else {
          alert(res.message);
        }
      })
      .catch(function() {
        alert('Something went wrong. Please call Tammy directly at 385-327-9225.');
      })
      .finally(function() {
        if (btn) { btn.textContent = originalText; btn.disabled = false; }
      });
    });
  }

  /* ── GALLERY LIGHTBOX (simple) ── */
  const galleryImgs = document.querySelectorAll('.gallery img');
  if (galleryImgs.length) {
    const lb = document.createElement('div');
    lb.id = 'lightbox';
    lb.innerHTML = '<div class="lb-bg"></div><img/><button class="lb-close">✕</button><button class="lb-prev">‹</button><button class="lb-next">›</button>';
    document.body.appendChild(lb);
    let current = 0;
    const imgs = Array.from(galleryImgs);
    const lbImg = lb.querySelector('img');

    function open(i) {
      current = i;
      lbImg.src = imgs[i].src;
      lb.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    imgs.forEach((img, i) => img.addEventListener('click', () => open(i)));
    lb.querySelector('.lb-close').addEventListener('click', () => { lb.classList.remove('open'); document.body.style.overflow = ''; });
    lb.querySelector('.lb-bg').addEventListener('click', () => { lb.classList.remove('open'); document.body.style.overflow = ''; });
    lb.querySelector('.lb-prev').addEventListener('click', () => open((current - 1 + imgs.length) % imgs.length));
    lb.querySelector('.lb-next').addEventListener('click', () => open((current + 1) % imgs.length));
    document.addEventListener('keydown', e => {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'ArrowLeft') open((current - 1 + imgs.length) % imgs.length);
      if (e.key === 'ArrowRight') open((current + 1) % imgs.length);
      if (e.key === 'Escape') { lb.classList.remove('open'); document.body.style.overflow = ''; }
    });
  }
});