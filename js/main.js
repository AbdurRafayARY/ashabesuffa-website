document.addEventListener('DOMContentLoaded', function () {
  // Mobile menu
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');
  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => navLinks.classList.toggle('active'));
    navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('active')));
  }

  // Year
  const yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // Back to top
  const backToTop = document.getElementById('backToTop');
  if (backToTop) {
    window.addEventListener('scroll', () => {
      backToTop.classList.toggle('visible', window.scrollY > 400);
    });
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // Gallery lightbox
  document.querySelectorAll('.gallery-item').forEach(item => {
    item.addEventListener('click', () => {
      const img = item.querySelector('img');
      if (!img) return;
      const overlay = document.createElement('div');
      overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;display:grid;place-items:center;cursor:pointer;padding:2rem;';
      const big = document.createElement('img');
      big.src = img.src;
      big.style.cssText = 'max-width:95%;max-height:95%;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.5);';
      overlay.appendChild(big);
      overlay.addEventListener('click', () => document.body.removeChild(overlay));
      document.body.appendChild(overlay);
    });
  });

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const id = a.getAttribute('href');
      if (id === '#') return;
      const target = document.querySelector(id);
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

  // Simple form validation
  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', e => {
      let valid = true;
      form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) { valid = false; field.style.borderColor = '#ef4444'; }
        else field.style.borderColor = '';
      });
      if (!valid) { e.preventDefault(); alert('Please fill all required fields.'); }
    });
  });
});
