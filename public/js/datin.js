(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---------- Sidebar (mobile) ---------- */
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

  /* ---------- Wilayah -> Provinsi -> Kabupaten/Kota (dropdown bertingkat) ---------- */
  var prov = $('#id_provinsi'), wil = $('#id_wilayah'), kab = $('#id_kab_kota');

  function filterProvinsi() {
    if (!prov || !wil) return;
    var w = wil.value;
    $$('option', prov).forEach(function (o) {
      if (!o.value) return;
      var sembunyi = w !== '' && o.dataset.wilayah !== w;
      o.hidden = sembunyi;
      o.disabled = sembunyi;
    });
    var terpilih = prov.options[prov.selectedIndex];
    if (terpilih && terpilih.disabled) prov.value = '';
  }

  function filterKab() {
    if (!kab) return;
    var p = prov ? prov.value : '';
    kab.disabled = (p === '');
    $$('option', kab).forEach(function (o) {
      if (!o.value) return;
      var sembunyi = (p === '' || o.dataset.provinsi !== p);
      o.hidden = sembunyi;
      o.disabled = sembunyi || o.dataset.terkunci === '1';   // yang sudah diajukan tetap terkunci
    });
    var terpilih = kab.options[kab.selectedIndex];
    if (terpilih && terpilih.value && terpilih.disabled) kab.value = '';
  }

  if (prov && wil) {
    prov.addEventListener('change', function () {
      var o = prov.options[prov.selectedIndex];
      if (o && o.dataset.wilayah) wil.value = o.dataset.wilayah;   // wilayah mengikuti provinsi
      kab && (kab.value = '');
      filterKab();
    });
    wil.addEventListener('change', function () { filterProvinsi(); if (kab) kab.value = ''; filterKab(); });
    filterProvinsi();
  }
  filterKab();

  /* ---------- Potensi: satu potensi per kolom ---------- */
  var potensi = $$('input[name="potensi[]"]');
  var hint = $('#potensiHint');
  var hintAsli = hint ? hint.textContent : '';
  var hintTimer;
  function kabarkan(teks, peringatan) {
    if (!hint) return;
    hint.textContent = teks;
    hint.classList.toggle('is-warn', !!peringatan);
    clearTimeout(hintTimer);
    hintTimer = setTimeout(function () { hint.textContent = hintAsli; hint.classList.remove('is-warn'); }, 5000);
  }
  potensi.forEach(function (el, i) {
    el.addEventListener('input', function () {
      if (!/[,;\n\r]/.test(el.value)) return;
      // Dipaste/diketik berisi beberapa butir: sisakan satu di kolom ini, sebar sisanya ke kolom kosong berikutnya
      var bagian = el.value.split(/[,;\n\r]+/).map(function (x) { return x.trim(); }).filter(Boolean);
      el.value = bagian.shift() || '';
      var idx = i + 1, sisa = [];
      bagian.forEach(function (b) {
        while (idx < potensi.length && potensi[idx].value.trim() !== '') idx++;
        if (idx < potensi.length) { potensi[idx].value = b; idx++; } else { sisa.push(b); }
      });
      if (sisa.length) {
        kabarkan('Semua kolom sudah terisi. Tidak dimasukkan: ' + sisa.join(', '), true);
      } else if (bagian.length) {
        kabarkan('Setiap kolom hanya untuk satu potensi. Sisanya dipindahkan ke kolom berikutnya.', false);
      } else {
        kabarkan('Setiap kolom hanya untuk satu potensi.', false);
      }
    });
  });

  /* ---------- Kebutuhan konektivitas: saran nama kabupaten/kota ---------- */
  var ta = $('#kebutuhan_konektivitas'), list = $('#acList'), dataEl = $('#data-kabkota');
  if (ta && list && dataEl) {
    var semua = [];
    try { semua = JSON.parse(dataEl.textContent); } catch (e) { semua = []; }
    var hasil = [], aktif = -1;

    var norm = function (s) { return String(s).toLowerCase(); };

    function tokenAktif() {
      var v = ta.value, pos = ta.selectionStart == null ? v.length : ta.selectionStart, mulai = 0, akhir = v.length, i, j;
      for (i = pos - 1; i >= 0; i--) { if (v[i] === ',' || v[i] === '\n') { mulai = i + 1; break; } }
      for (j = pos; j < v.length; j++) { if (v[j] === ',' || v[j] === '\n') { akhir = j; break; } }
      return { mulai: mulai, akhir: akhir, teks: v.slice(mulai, akhir).trim() };
    }

    function cari(t) {
      var q = norm(t.teks);
      var lain = ta.value.slice(0, t.mulai) + ',' + ta.value.slice(t.akhir);          // teks di luar token yang sedang diketik
      var sudah = lain.split(/[,\n]+/).map(function (x) { return norm(x.trim()); }).filter(Boolean);
      var asal = kab ? kab.value : '';
      var out = [];
      semua.forEach(function (k) {
        if (String(k.id) === asal) return;                                            // tidak menyarankan daerah asal sendiri
        var n = norm(k.nama);
        if (sudah.indexOf(n) !== -1) return;                                          // sudah ada di daftar
        var inti = n.replace(/^(kab\.?|kabupaten|kota)\s+/, '');
        var skor = (n.indexOf(q) === 0 || inti.indexOf(q) === 0) ? 0 : (n.indexOf(q) !== -1 ? 1 : -1);
        if (skor >= 0) out.push({ k: k, skor: skor });
      });
      out.sort(function (a, b) { return a.skor - b.skor || a.k.nama.localeCompare(b.k.nama, 'id'); });
      return out.slice(0, 8).map(function (o) { return o.k; });
    }

    function tutup() {
      list.hidden = true; list.innerHTML = ''; aktif = -1;
      ta.setAttribute('aria-expanded', 'false'); ta.removeAttribute('aria-activedescendant');
    }

    function gambar() {
      list.innerHTML = '';
      hasil.forEach(function (k, i) {
        var li = document.createElement('li');
        li.id = 'ac-opt-' + i; li.setAttribute('role', 'option'); li.setAttribute('aria-selected', 'false');
        var a = document.createElement('span'); a.textContent = k.nama;
        var b = document.createElement('small'); b.textContent = k.provinsi;
        li.appendChild(a); li.appendChild(b);
        li.addEventListener('mousedown', function (e) { e.preventDefault(); pilih(k); });   // cegah textarea kehilangan fokus
        list.appendChild(li);
      });
      list.hidden = false;
      ta.setAttribute('aria-expanded', 'true');
    }

    function sorot() {
      $$('li', list).forEach(function (li, i) {
        li.setAttribute('aria-selected', String(i === aktif));
        if (i === aktif) { ta.setAttribute('aria-activedescendant', li.id); li.scrollIntoView({ block: 'nearest' }); }
      });
    }

    function perbarui() {
      var t = tokenAktif();
      if (t.teks.length < 2) { tutup(); return; }
      hasil = cari(t);
      if (!hasil.length) { tutup(); return; }
      aktif = -1;
      gambar();
    }

    function pilih(k) {
      var v = ta.value, t = tokenAktif();
      var depan = v.slice(0, t.mulai).replace(/[ \t]+$/, ''), belakang = v.slice(t.akhir);
      var pemisah = (depan === '' || /\n$/.test(depan)) ? '' : ' ';
      var tambah = belakang.trim() === '' ? ', ' : '';                                // siap mengetik daerah berikutnya
      var awal = depan + pemisah + k.nama + tambah;
      ta.value = awal + belakang;
      ta.setSelectionRange(awal.length, awal.length);
      ta.focus();
      tutup();
    }

    ta.addEventListener('input', perbarui);
    ta.addEventListener('click', perbarui);
    ta.addEventListener('blur', function () { setTimeout(tutup, 120); });
    ta.addEventListener('keydown', function (e) {
      if (list.hidden) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); aktif = (aktif + 1) % hasil.length; sorot(); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); aktif = (aktif - 1 + hasil.length) % hasil.length; sorot(); }
      else if (e.key === 'Enter' && aktif >= 0) { e.preventDefault(); pilih(hasil[aktif]); }
      else if (e.key === 'Escape') { e.stopPropagation(); tutup(); }
    });
  }

  /* ---------- Form user: cakupan & hak akses menyesuaikan role ---------- */
  var peran = $('#id_role'), dataRole = $('#data-role');
  if (peran && dataRole) {
    var info = {};
    try { info = JSON.parse(dataRole.textContent); } catch (e) { info = {}; }
    var panel = $('#roleInfo'), daftar = $('#roleInfoList'), catatan = $('#roleInfoCatatan'), tandaWajib = $('#wajibInstansi');

    var sesuaikan = function () {
      var r = info[peran.value];
      var butuh = r ? r.syarat : [];

      $$('[data-butuh]').forEach(function (wadah) {
        var perlu = butuh.indexOf(wadah.dataset.butuh) !== -1;
        wadah.hidden = !perlu;
        $$('input,select', wadah).forEach(function (el) { el.disabled = !perlu; el.required = perlu; });
      });
      $$('[data-wajib]').forEach(function (el) { el.required = butuh.indexOf(el.dataset.wajib) !== -1; });
      if (tandaWajib) tandaWajib.hidden = butuh.indexOf('instansi') === -1;

      if (!r) { panel.hidden = true; return; }
      daftar.innerHTML = '';
      if (r.super) {
        catatan.textContent = 'Super Admin memiliki seluruh hak akses dan dapat mengelola akun.';
      } else {
        r.hak.forEach(function (nama) {
          var li = document.createElement('li');
          li.textContent = nama;
          daftar.appendChild(li);
        });
        catatan.textContent = r.hak.length
          ? 'Hak akses diatur per role lewat tombol "Atur hak akses" di halaman Data User.'
          : 'Role ini belum memiliki hak akses.';
      }
      panel.hidden = false;
    };
    peran.addEventListener('change', sesuaikan);
    sesuaikan();
  }

  /* ---------- Upload surat ---------- */
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

  /* ---------- Filter otomatis & konfirmasi hapus ---------- */
  $$('[data-autosubmit]').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!window.confirm(f.dataset.confirm)) e.preventDefault();
    });
  });

  /* ---------- Cegah submit ganda ---------- */
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
