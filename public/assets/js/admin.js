(function () {
  'use strict';

  // Confirmation avant les actions destructrices (formulaires marqués data-confirm).
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (form && form.hasAttribute && form.hasAttribute('data-confirm')) {
      if (!window.confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
      }
    }
  });

  var summary = document.querySelector('[data-autofocus]');
  if (summary) {
    summary.focus();
  }
})();
