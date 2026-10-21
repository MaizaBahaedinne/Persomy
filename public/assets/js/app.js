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

  // Apparition douce au défilement (désactivée si l'utilisateur réduit les animations).
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reduce && 'IntersectionObserver' in window) {
    var targets = document.querySelectorAll('.card, .step, .section-head, .cta-inner');
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
          // Retire les classes une fois l'animation terminée (préserve les effets au survol).
          setTimeout(function (el) { el.classList.remove('reveal', 'is-visible'); }, 800, entry.target);
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
