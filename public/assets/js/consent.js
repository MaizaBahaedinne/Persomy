(function () {
  'use strict';

  // Google Analytics 4 : chargé UNIQUEMENT après un accord explicite du visiteur.
  var body = document.body;
  var id = body.getAttribute('data-ga-id');
  if (!id || !/^G-[A-Z0-9]{6,14}$/.test(id)) {
    return;
  }

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

  function loadAnalytics() {
    if (loaded) { return; }
    loaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
    document.head.appendChild(s);
  }

  function disableAnalytics() {
    window['ga-disable-' + id] = true;
    // Supprime les cookies de mesure Google déjà déposés (_ga, _ga_XXXX).
    var host = location.hostname.split('.');
    var domains = ['', location.hostname];
    if (host.length > 2) { domains.push('.' + host.slice(-2).join('.')); } else { domains.push('.' + location.hostname); }
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
    if (value === 'granted') { loadAnalytics(); } else { disableAnalytics(); }
    var trigger = reopen[0];
    if (trigger) { trigger.focus(); }
  }

  if (banner) {
    banner.querySelectorAll('[data-consent-choice]').forEach(function (btn) {
      btn.addEventListener('click', function () { choose(btn.getAttribute('data-consent-choice')); });
    });
  }
  reopen.forEach(function (btn) {
    btn.hidden = false;
    btn.addEventListener('click', show);
  });

  var current = read();
  if (current === 'granted') {
    loadAnalytics();
  } else if (current === 'denied') {
    disableAnalytics();
  } else {
    show();
  }
})();
