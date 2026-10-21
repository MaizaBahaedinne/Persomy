(function () {
  'use strict';

  // Google Analytics 4 + Google Consent Mode v2.
  //  - Tout est REFUSÉ par défaut (analytics_storage, ad_storage, ad_user_data, ad_personalization).
  //  - Mode « basic »    : aucun script Google n'est chargé avant l'accord du visiteur.
  //  - Mode « advanced » : gtag.js est chargé dès l'arrivée, en mode refusé (pings sans cookie).
  //  - Le choix est mémorisé (localStorage) et modifiable via « Gérer les cookies ».
  var body = document.body;
  var id = body.getAttribute('data-ga-id');
  if (!id || !/^G-[A-Z0-9]{6,14}$/.test(id)) {
    return;
  }
  var advanced = body.getAttribute('data-consent-mode') === 'advanced';

  var KEY = 'persomy_consent';
  var banner = document.querySelector('[data-consent]');
  var reopen = document.querySelectorAll('[data-consent-open]');
  var loaded = false;

  function read() {
    try { return window.localStorage.getItem(KEY); } catch (e) { return null; }
  }
  function write(value) {
    try { window.localStorage.setItem(KEY, value); } catch (e) { /* stockage indisponible */ }
  }

  window.dataLayer = window.dataLayer || [];
  window.gtag = function () { window.dataLayer.push(arguments); };

  function state(value) {
    return {
      analytics_storage: value,
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied'
    };
  }

  // 1) Valeurs par défaut : refusé. Doit précéder toute commande « config ».
  var defaults = state('denied');
  if (advanced) { defaults.wait_for_update = 500; }
  window.gtag('consent', 'default', defaults);
  window.gtag('set', 'ads_data_redaction', true);

  // 2) Si un choix antérieur existe, il est appliqué avant la configuration.
  var stored = read();
  if (stored === 'granted') {
    window.gtag('consent', 'update', state('granted'));
  }

  function loadGtag() {
    if (loaded) { return; }
    loaded = true;
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
    document.head.appendChild(s);
  }

  function purgeCookies() {
    var parts = location.hostname.split('.');
    var domains = ['', location.hostname, '.' + location.hostname];
    if (parts.length > 2) { domains.push('.' + parts.slice(-2).join('.')); }
    document.cookie.split(';').forEach(function (c) {
      var name = c.split('=')[0].trim();
      if (name === '_ga' || name.indexOf('_ga_') === 0 || name === '_gid') {
        domains.forEach(function (d) {
          document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + (d ? '; domain=' + d : '');
        });
      }
    });
  }

  function show() {
    if (!banner) { return; }
    banner.hidden = false;
    var first = banner.querySelector('button');
    if (first) { first.focus({ preventScroll: true }); }
  }
  function hide() {
    if (banner) { banner.hidden = true; }
  }

  function choose(value) {
    write(value);
    hide();
    window.gtag('consent', 'update', state(value));
    if (value === 'granted') {
      loadGtag();
    } else {
      purgeCookies();
    }
    if (reopen[0]) { reopen[0].focus(); }
  }

  if (banner) {
    banner.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') { choose('denied'); }
    });
    banner.querySelectorAll('[data-consent-choice]').forEach(function (btn) {
      btn.addEventListener('click', function () { choose(btn.getAttribute('data-consent-choice')); });
    });
  }
  reopen.forEach(function (btn) {
    btn.hidden = false;
    btn.addEventListener('click', show);
  });

  // 3) Chargement initial.
  if (stored === 'granted' || advanced) {
    loadGtag();
  }
  if (stored === 'denied') {
    purgeCookies();
  } else if (stored !== 'granted') {
    show();
  }
})();
