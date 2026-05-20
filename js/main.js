/**
 * BACOFAFL — Conservation Nature Cameroun
 * Main JavaScript
 */

'use strict';

/* ============================================================
   HERO CAROUSEL
   ============================================================ */
(function () {
  const slides = document.querySelectorAll('.hero-slide');
  const dots   = document.querySelectorAll('.hero-dot');
  if (!slides.length) return;

  let cur = 0, timer;

  function goTo(i) {
    slides[cur].classList.remove('active');
    dots[cur]?.classList.remove('active');
    cur = ((i % slides.length) + slides.length) % slides.length;
    slides[cur].classList.add('active');
    dots[cur]?.classList.add('active');
  }

  function next() { goTo(cur + 1); }
  function prev() { goTo(cur - 1); }
  function startTimer() { clearInterval(timer); timer = setInterval(next, 6000); }

  dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); startTimer(); }));
  document.querySelector('.hero-arrow.prev')?.addEventListener('click', () => { prev(); startTimer(); });
  document.querySelector('.hero-arrow.next')?.addEventListener('click', () => { next(); startTimer(); });

  // Touch swipe
  const hero = document.querySelector('.hero');
  if (hero) {
    let tx = 0;
    hero.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend',   e => {
      const d = tx - e.changedTouches[0].clientX;
      if (Math.abs(d) > 50) { d > 0 ? next() : prev(); startTimer(); }
    });
  }

  slides[0].classList.add('active');
  dots[0]?.classList.add('active');
  startTimer();
})();


/* ============================================================
   MOBILE MENU
   ============================================================ */
(function () {
  const burger = document.querySelector('.hamburger');
  const menu   = document.querySelector('.nav-menu');
  if (!burger || !menu) return;

  function close() {
    burger.classList.remove('active');
    menu.classList.remove('open');
    document.body.style.overflow = '';
  }

  burger.addEventListener('click', () => {
    const open = menu.classList.toggle('open');
    burger.classList.toggle('active', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });

  menu.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
  document.addEventListener('click', e => {
    if (!e.target.closest('.navbar') && menu.classList.contains('open')) close();
  });
})();


/* ============================================================
   STICKY HEADER
   ============================================================ */
(function () {
  const header = document.querySelector('.site-header');
  if (!header) return;
  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 30);
  }, { passive: true });
})();


/* ============================================================
   ACTIVE NAV LINK
   ============================================================ */
(function () {
  const page = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-link').forEach(a => {
    const href = a.getAttribute('href') || '';
    if (href === page || (page === '' && href === 'index.html')) a.classList.add('active');
  });
})();


/* ============================================================
   SCROLL REVEAL
   ============================================================ */
(function () {
  const els = document.querySelectorAll('.reveal, .reveal-l, .reveal-r');
  if (!els.length || !('IntersectionObserver' in window)) {
    els.forEach(el => el.classList.add('in'));
    return;
  }
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: 0.12 });
  els.forEach(el => io.observe(el));
})();


/* ============================================================
   STATS COUNTER
   ============================================================ */
(function () {
  const els = document.querySelectorAll('[data-count]');
  if (!els.length) return;

  function animate(el) {
    const target = +el.dataset.count;
    const dur    = 2000;
    const start  = performance.now();
    (function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      const e = 1 - Math.pow(1 - p, 3); // ease-out cubic
      el.textContent = Math.round(e * target).toLocaleString('fr-FR');
      if (p < 1) requestAnimationFrame(tick);
    })(start);
  }

  const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { animate(e.target); io.unobserve(e.target); } });
  }, { threshold: 0.5 });

  els.forEach(el => io.observe(el));
})();


/* ============================================================
   GALLERY FILTER
   ============================================================ */
(function () {
  const btns  = document.querySelectorAll('.filter-btn');
  const items = document.querySelectorAll('.gallery-grid .gi[data-cat]');
  if (!btns.length) return;

  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      btns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const cat = btn.dataset.filter;

      items.forEach(item => {
        const show = cat === 'all' || item.dataset.cat === cat;
        item.style.transition = 'opacity .3s, transform .3s';
        if (show) {
          item.style.display = '';
          requestAnimationFrame(() => { item.style.opacity = '1'; item.style.transform = 'scale(1)'; });
        } else {
          item.style.opacity = '0';
          item.style.transform = 'scale(0.9)';
          setTimeout(() => { item.style.display = 'none'; }, 300);
        }
      });
    });
  });
})();


/* ============================================================
   LIGHTBOX
   ============================================================ */
(function () {
  const lb = document.querySelector('.lightbox');
  if (!lb) return;

  const img  = lb.querySelector('.lb-img');
  const cap  = lb.querySelector('.lb-caption');
  const ctr  = lb.querySelector('.lb-counter');

  let pool = [], idx = 0;

  function open(items, i) {
    pool = items; idx = i;
    show();
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    lb.classList.remove('open');
    document.body.style.overflow = '';
  }

  function show() {
    const el     = pool[idx];
    const imgEl  = el.querySelector('img');
    const capEl  = el.querySelector('.gi-info p');
    img.src     = imgEl.src;
    img.alt     = imgEl.alt;
    if (cap) cap.textContent = capEl ? capEl.textContent : (imgEl.alt || '');
    if (ctr) ctr.textContent = `${idx + 1} / ${pool.length}`;
  }

  function goNext() { idx = (idx + 1) % pool.length; show(); }
  function goPrev() { idx = (idx - 1 + pool.length) % pool.length; show(); }

  lb.querySelector('.lb-close')?.addEventListener('click', close);
  lb.querySelector('.lb-next')?.addEventListener('click', goNext);
  lb.querySelector('.lb-prev')?.addEventListener('click', goPrev);
  lb.addEventListener('click', e => { if (e.target === lb) close(); });

  document.addEventListener('keydown', e => {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')     close();
    if (e.key === 'ArrowRight') goNext();
    if (e.key === 'ArrowLeft')  goPrev();
  });

  // Touch swipe inside lightbox
  let tx = 0;
  lb.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
  lb.addEventListener('touchend',   e => {
    const d = tx - e.changedTouches[0].clientX;
    if (Math.abs(d) > 50) { d > 0 ? goNext() : goPrev(); }
  });

  // Attach click handlers to every gallery item on the page
  document.querySelectorAll('.gallery-grid .gi, .gallery-mosaic .gal-item').forEach(item => {
    item.addEventListener('click', function () {
      const parent = this.closest('.gallery-grid, .gallery-mosaic');
      const all    = [...parent.querySelectorAll('.gi, .gal-item')].filter(el => el.style.display !== 'none');
      open(all, all.indexOf(this));
    });
  });
})();


/* ============================================================
   CONTACT FORM
   ============================================================ */
(function () {
  const form    = document.getElementById('contactForm');
  const success = document.getElementById('formSuccess');
  if (!form) return;

  form.addEventListener('submit', e => {
    e.preventDefault();
    let valid = true;

    form.querySelectorAll('[required]').forEach(f => {
      if (!f.value.trim()) {
        f.style.borderColor = '#e53e3e';
        valid = false;
        f.addEventListener('input', () => { f.style.borderColor = ''; }, { once: true });
      }
    });

    if (!valid) return;

    const btn = form.querySelector('button[type="submit"]');
    btn.textContent = 'Envoi en cours…';
    btn.disabled    = true;

    setTimeout(() => {
      form.style.display = 'none';
      if (success) success.style.display = 'block';
    }, 1400);
  });
})();


/* ============================================================
   BACK TO TOP
   ============================================================ */
(function () {
  const btn = document.querySelector('.btt');
  if (!btn) return;
  window.addEventListener('scroll', () => btn.classList.toggle('visible', scrollY > 400), { passive: true });
  btn.addEventListener('click', () => scrollTo({ top: 0, behavior: 'smooth' }));
})();


/* ============================================================
   SMOOTH SCROLL (anchor links)
   ============================================================ */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const t = document.querySelector(a.getAttribute('href'));
    if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
  });
});


/* ============================================================
   STAGGER DELAY HELPER
   ============================================================ */
document.querySelectorAll('.stagger > *').forEach((el, i) => {
  el.style.transitionDelay = `${i * 0.09}s`;
});
