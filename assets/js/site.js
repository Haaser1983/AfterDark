(function () {
  'use strict';

  function store(key, value) {
    try {
      if (value === undefined) return window.localStorage.getItem(key);
      window.localStorage.setItem(key, value);
    } catch (e) { return null; }
    return null;
  }

  // Home: the strip of worlds. Hover or focus opens a world; tap opens it on touch screens.
  var strip = document.querySelector('[data-worlds]');
  if (strip) {
    var worlds = Array.prototype.slice.call(strip.querySelectorAll('[data-world]'));
    var activate = function (w) {
      worlds.forEach(function (o) { o.classList.toggle('is-active', o === w); });
    };
    worlds.forEach(function (w) {
      w.addEventListener('mouseenter', function () { activate(w); });
      w.addEventListener('focusin', function () { activate(w); });
      w.addEventListener('click', function (ev) {
        if (!w.classList.contains('is-active') && window.matchMedia('(min-width: 760px)').matches) {
          ev.preventDefault();
          activate(w);
        }
      });
    });
  }

  // Spoiler gates: remembered per book once a reader opens them.
  document.querySelectorAll('[data-spoilers]').forEach(function (box) {
    var key = 'spoilers:' + box.getAttribute('data-spoilers');
    var btn = box.querySelector('[data-spoiler-open]');
    var content = box.querySelector('.spoilers__content');
    var open = function () {
      box.classList.add('is-open');
      content.hidden = false;
      if (btn) btn.setAttribute('aria-expanded', 'true');
    };
    if (store(key) === '1') open();
    if (btn) btn.addEventListener('click', function () {
      store(key, '1');
      open();
      content.setAttribute('tabindex', '-1');
      content.focus();
    });
  });

  // Optional 18+ gate.
  var gate = document.getElementById('agegate');
  if (gate && store('agegate') !== 'yes') {
    gate.hidden = false;
    var yes = gate.querySelector('[data-agegate-yes]');
    if (yes) {
      yes.focus();
      yes.addEventListener('click', function () { store('agegate', 'yes'); gate.hidden = true; });
    }
  }
})();
