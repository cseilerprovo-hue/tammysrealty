/* ── NAV HAMBURGER ── */
document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
  }

  /* ── FORM HANDLING ── */
  document.querySelectorAll('form[data-form]').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const msg = form.querySelector('.success-msg');
      if (msg) {
        msg.style.display = 'block';
        form.reset();
        // reset radio defaults
        const firstRatings = form.querySelectorAll('input[type="radio"]');
        firstRatings.forEach(r => { if (r.dataset.default) r.checked = true; });
        setTimeout(() => msg.style.display = 'none', 7000);
        msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  });

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
