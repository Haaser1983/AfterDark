(function () {
  'use strict';

  // New book: fill the web address from the title until it's edited by hand.
  var title = document.querySelector('[data-title]');
  var slug = document.querySelector('[data-slug]');
  var preview = document.querySelector('[data-slug-preview]');
  if (title && slug) {
    var touched = slug.value !== '';
    var slugify = function (s) {
      return s.toLowerCase().replace(/['’]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    };
    var update = function () { if (preview) preview.textContent = slug.value || 'your-title'; };
    title.addEventListener('input', function () { if (!touched) { slug.value = slugify(title.value); update(); } });
    slug.addEventListener('input', function () { touched = true; slug.value = slugify(slug.value); update(); });
  }

  // Cast rows.
  var list = document.querySelector('[data-chars]');
  var tpl = document.querySelector('[data-char-template]');
  var add = document.querySelector('[data-add-char]');
  if (list && tpl && add) {
    var next = parseInt(list.getAttribute('data-next'), 10) || 0;
    add.addEventListener('click', function () {
      var html = tpl.innerHTML.replace(/999/g, String(next++));
      var wrap = document.createElement('div');
      wrap.innerHTML = html.trim();
      var row = wrap.firstElementChild;
      list.appendChild(row);
      var first = row.querySelector('input[type="text"]');
      if (first) first.focus();
    });
    list.addEventListener('click', function (ev) {
      var btn = ev.target.closest('[data-remove-char]');
      if (!btn) return;
      var row = btn.closest('[data-char]');
      if (row) { row.remove(); add.focus(); }
    });
  }

  // Keep each color picker and its text box in step.
  document.addEventListener('input', function (ev) {
    var t = ev.target;
    var pair = t.closest && t.closest('.colorpair');
    if (!pair) return;
    var text = pair.querySelector('[data-color-text]');
    var picker = pair.querySelector('[data-color-picker]');
    if (!text || !picker) return;
    if (t === picker) text.value = picker.value.toUpperCase();
    if (t === text && /^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value;
  });
})();
