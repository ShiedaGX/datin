(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* Sidebar (mobile) */
  var sidebar = $('#sidebar'), backdrop = $('#backdrop'), menuBtn = $('#menuBtn');
  function toggleMenu(open) {
    if (!sidebar) return;
    sidebar.classList.toggle('open', open);
    backdrop.classList.toggle('show', open);
    menuBtn.setAttribute('aria-expanded', String(open));
  }
  if (menuBtn) menuBtn.addEventListener('click', function () { toggleMenu(!sidebar.classList.contains('open')); });
  if (backdrop) backdrop.addEventListener('click', function () { toggleMenu(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') toggleMenu(false); });

  /* Provinsi <-> Wilayah Regional */
  var prov = $('#id_provinsi'), wil = $('#id_wilayah');
  if (prov && wil) {
    var filterProvinsi = function () {
      var w = wil.value;
      $$('option', prov).forEach(function (o) {
        if (!o.value) return;
        var sembunyi = w !== '' && o.dataset.wilayah !== w;
        o.hidden = sembunyi;
        o.disabled = sembunyi;
      });
      var terpilih = prov.options[prov.selectedIndex];
      if (terpilih && terpilih.disabled) prov.value = '';
    };
    prov.addEventListener('change', function () {
      var o = prov.options[prov.selectedIndex];
      if (o && o.dataset.wilayah) wil.value = o.dataset.wilayah;   // wilayah otomatis mengikuti provinsi
    });
    wil.addEventListener('change', filterProvinsi);
    filterProvinsi();
  }

  /* Upload surat */
  var MAX = 5 * 1024 * 1024;
  $$('.btn-upload, .upload-name').forEach(function (el) {
    el.addEventListener('click', function () {
      var f = document.getElementById(el.dataset.target || el.dataset.for);
      if (f) f.click();
    });
  });
  $$('input[type=file][data-upload]').forEach(function (f) {
    f.addEventListener('change', function () {
      var label = $('.upload-name[data-for="' + f.id + '"]');
      var file = f.files[0];
      if (file && file.size > MAX) {
        alert('Ukuran file maksimal 5 MB.');
        f.value = '';
        label.value = '';
        return;
      }
      label.value = file ? file.name : '';
    });
  });

  /* Cegah submit ganda */
  var form = $('#formInput');
  if (form) {
    var btn = $('button[type=submit]', form);
    form.addEventListener('submit', function () {
      if (btn) { btn.disabled = true; btn.textContent = 'Menyimpan...'; }
    });
    window.addEventListener('pageshow', function () {
      if (btn) { btn.disabled = false; btn.textContent = 'Tambah Data'; }
    });
  }
})();
