/* ============================================================
   Ashabesuffa Foundation - Main JavaScript
   Handles: mobile menu, language switching, RTL, forms,
            back-to-top, smooth scroll, year
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
  // ============================================================
  // 1. LANGUAGE SYSTEM
  // ============================================================
  const SUPPORTED_LANGS = ['en', 'ur', 'ar'];
  const DEFAULT_LANG = 'en';
  const STORAGE_KEY = 'ashabesuffa_lang';

  function getSavedLang() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      return SUPPORTED_LANGS.includes(saved) ? saved : DEFAULT_LANG;
    } catch (e) {
      return DEFAULT_LANG;
    }
  }

  function saveLang(lang) {
    try {
      localStorage.setItem(STORAGE_KEY, lang);
    } catch (e) {
      /* ignore */
    }
  }

  function applyLanguage(lang) {
    if (!window.translations || !window.translations[lang]) return;

    const dict = window.translations[lang];

    // Update <html> lang and dir
    document.documentElement.lang = lang;
    if (lang === 'ur' || lang === 'ar') {
      document.documentElement.dir = 'rtl';
    } else {
      document.documentElement.dir = 'ltr';
    }

    // Update body class for font family
    document.body.classList.remove('lang-en', 'lang-ur', 'lang-ar');
    document.body.classList.add('lang-' + lang);

    // Translate all elements with data-i18n
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      const key = el.getAttribute('data-i18n');
      if (dict[key]) {
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
          el.placeholder = dict[key];
        } else if (el.tagName === 'OPTION') {
          el.textContent = dict[key];
        } else {
          el.textContent = dict[key];
        }
      }
    });

    // Translate placeholders with data-i18n-placeholder
    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-placeholder');
      if (dict[key]) el.placeholder = dict[key];
    });

    // Update active state on language buttons
    document.querySelectorAll('.lang-btn').forEach(function (btn) {
      btn.classList.toggle('active', btn.dataset.lang === lang);
    });

    // Update page title if data attribute exists
    const titleKey = document.body.getAttribute('data-title-key');
    if (titleKey && dict[titleKey]) {
      document.title = dict[titleKey] + ' - ' + dict['brand.name'];
    }

    saveLang(lang);
  }

  // Attach click handlers to language buttons
  document.querySelectorAll('.lang-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const lang = this.dataset.lang;
      if (SUPPORTED_LANGS.includes(lang)) {
        applyLanguage(lang);
      }
    });
  });

  // Apply saved/default language on load
  applyLanguage(getSavedLang());

  // ============================================================
  // 2. MOBILE MENU TOGGLE
  // ============================================================
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', function () {
      navLinks.classList.toggle('active');
    });

    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navLinks.classList.remove('active');
      });
    });
  }

  // ============================================================
  // 3. CURRENT YEAR IN FOOTER
  // ============================================================
  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  // ============================================================
  // 4. FORM HANDLING
  // ============================================================
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const lang = document.documentElement.lang;
      const messages = {
        en: 'Thank you! Your message has been received. We will contact you soon.',
        ur: 'شکریہ! آپ کا پیغام موصول ہو گیا ہے۔ ہم جلد آپ سے رابطہ کریں گے۔',
        ar: 'شكراً لك! تم استلام رسالتك. سنتواصل معك قريباً.'
      };
      alert(messages[lang] || messages.en);
      this.reset();
    });
  }

  const admissionForm = document.getElementById('admissionForm');
  if (admissionForm) {
    admissionForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const lang = document.documentElement.lang;
      const messages = {
        en: 'Thank you! Your admission inquiry has been submitted. Our team will contact you shortly.',
        ur: 'شکریہ! آپ کی داخلہ انکوائری جمع ہو گئی ہے۔ ہماری ٹیم جلد آپ سے رابطہ کرے گی۔',
        ar: 'شكراً لك! تم إرسال استفسار القبول. سيتواصل معك فريقنا قريباً.'
      };
      alert(messages[lang] || messages.en);
      this.reset();
    });
  }

  // ============================================================
  // 5. SMOOTH SCROLL FOR ANCHOR LINKS
  // ============================================================
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ============================================================
  // 6. BACK TO TOP BUTTON
  // ============================================================
  const backToTop = document.getElementById('backToTop');
  if (backToTop) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 400) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    });

    backToTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ============================================================
  // 7. GALLERY LIGHTBOX (simple)
  // ============================================================
  document.querySelectorAll('.gallery-item').forEach(function (item) {
    item.addEventListener('click', function () {
      const img = this.querySelector('img');
      if (!img) return;
      const overlay = document.createElement('div');
      overlay.style.cssText =
        'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;display:grid;place-items:center;cursor:pointer;padding:2rem;';
      const bigImg = document.createElement('img');
      bigImg.src = img.src;
      bigImg.style.cssText =
        'max-width:95%;max-height:95%;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.5);';
      overlay.appendChild(bigImg);
      overlay.addEventListener('click', function () {
        document.body.removeChild(overlay);
      });
      document.body.appendChild(overlay);
    });
  });
});
