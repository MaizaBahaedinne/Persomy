(function () {
  'use strict';

  // Menu mobile : replié uniquement si le JavaScript fonctionne.
  var header = document.querySelector('[data-header]');
  var toggle = document.querySelector('[data-nav-toggle]');
  if (header && toggle) {
    header.classList.add('js-nav');
    toggle.addEventListener('click', function () {
      var open = header.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && header.classList.contains('is-open')) {
        header.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });
  }

  // Sélecteur de langue : se ferme au clic extérieur et avec Échap.
  var langMenu = document.querySelector('[data-lang-menu]');
  if (langMenu) {
    document.addEventListener('click', function (event) {
      if (langMenu.open && !langMenu.contains(event.target)) { langMenu.open = false; }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && langMenu.open) {
        langMenu.open = false;
        langMenu.querySelector('summary').focus();
      }
    });
  }

  // Apparition douce au défilement (désactivée si l'utilisateur réduit les animations).
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // En-tête : ombre dès que la page défile.
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Halo qui suit le pointeur sur les cartes de services (souris uniquement).
  if (!reduce && window.matchMedia && window.matchMedia('(hover: hover)').matches) {
    document.querySelectorAll('.service-card').forEach(function (card) {
      card.addEventListener('pointermove', function (event) {
        var rect = card.getBoundingClientRect();
        card.style.setProperty('--mx', (event.clientX - rect.left) + 'px');
        card.style.setProperty('--my', (event.clientY - rect.top) + 'px');
      });
    });
  }
  if (!reduce && 'IntersectionObserver' in window) {
    var targets = document.querySelectorAll('.card, .step, .section-head, .cta-inner, .empty-state, .story, .mission, .value, .city-card');
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
          // Retire les classes une fois l'animation terminée (préserve les effets au survol).
          setTimeout(function (el) { el.classList.remove('reveal', 'is-visible'); }, 1300, entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    targets.forEach(function (el) {
      var rect = el.getBoundingClientRect();
      if (rect.top > window.innerHeight) {
        el.classList.add('reveal');
        observer.observe(el);
      }
    });
  }

  // Place le focus sur le résumé d'erreurs du formulaire.
  var summary = document.querySelector('[data-autofocus]');
  if (summary) {
    summary.focus();
  }
})();
