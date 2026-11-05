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

  /* ---------- Revisi dari INTRAM: moda dibatasi pada moda yang diminta ---------- */
  var modaRadios = $$('input[name="id_moda"]'), modaHint = $('#modaHint'), modaAuto = false, btnKirim = $('#formInput button[type=submit]');
  function batasiModa() {
    if (!kab || !modaRadios.length) return;
    var o = kab.options[kab.selectedIndex];
    var diminta = o && o.dataset && o.dataset.revisi ? o.dataset.revisi.split(',') : [];
    var sudah = o && o.dataset && o.dataset.sudah ? o.dataset.sudah.split(',') : [];
    var terkunciUlang = !diminta.length && sudah.length > 0;   // sudah diajukan dan belum ada permintaan revisi INTRAM
    if (btnKirim) btnKirim.disabled = terkunciUlang;
    modaRadios.forEach(function (r) {
      var boleh = terkunciUlang ? false : (!diminta.length || diminta.indexOf(r.value) !== -1);
      r.disabled = !boleh;
      r.closest('.chip').classList.toggle('is-off', !boleh);
      if (!boleh && r.checked) r.checked = false;
    });
    if (terkunciUlang) {
      if (modaHint) {
        modaHint.textContent = 'Daerah ini sudah diajukan untuk moda ' + modaRadios.filter(function (r) { return sudah.indexOf(r.value) !== -1; })
          .map(function (r) { return r.nextElementSibling.textContent.trim(); }).join(', ') + '. Pengajuan ulang dapat dilakukan setelah INTRAM meminta revisi.';
        modaHint.hidden = false;
      }
      modaAuto = false;
      return;
    }
    if (!diminta.length) {
      if (modaHint) modaHint.hidden = true;
      if (modaAuto) { modaRadios.forEach(function (r) { r.checked = false; }); modaAuto = false; }  // batalkan pilihan otomatis
      return;
    }
    var aktif = modaRadios.filter(function (r) { return !r.disabled; });
    if (aktif.length && !modaRadios.some(function (r) { return r.checked; })) { aktif[0].checked = true; modaAuto = true; }
    if (modaHint) {
      modaHint.textContent = 'INTRAM meminta Anda mengajukan untuk moda ' + aktif.map(function (r) { return r.nextElementSibling.textContent.trim(); }).join(', ')
        + '. Pengajuan moda sebelumnya tidak berubah.';
      modaHint.hidden = false;
    }
  }

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
    batasiModa();
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
  if (kab) kab.addEventListener('change', batasiModa);
  function formKosong() {
    var kb = $('#kebutuhan_konektivitas'), kl = $('#klasifikasi_pemda');
    return !(kb && kb.value.trim()) && !(kl && kl.value) &&
      !$$('input[name="potensi[]"]').some(function (i) { return i.value.trim(); }) &&
      !$$('input[name="keterangan[]"]').some(function (c) { return c.checked; });
  }
  if (kab) kab.addEventListener('change', function () {   // daerah yang diminta direvisi: buka form yang terisi dari pengajuan sebelumnya
    var o = kab.options[kab.selectedIndex];
    if (o && o.dataset && o.dataset.isi && location.search.indexOf('kab=') === -1 && formKosong()) location.href = o.dataset.isi;
  });
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

  /* ---------- Review laporan (Moda): checklist & keputusan ---------- */
  var fk = $('#formKeputusan');
  if (fk) {
    var cek = $$('input[name="periksa[]"]', fk);
    var hitung = $('#cekHitung'), setuju = $('#btnSetuju'), catatan = $('#catatan');

    var perbaruiCek = function () {
      var n = cek.filter(function (c) { return c.checked; }).length;
      if (hitung) {
        hitung.textContent = n + ' dari ' + cek.length + ' pemeriksaan telah dikonfirmasi';
        hitung.classList.toggle('ok', cek.length > 0 && n === cek.length);
      }
      if (setuju) setuju.disabled = n !== cek.length;       // Setujui hanya aktif bila semua pemeriksaan dikonfirmasi
    };
    cek.forEach(function (c) { c.addEventListener('change', perbaruiCek); });
    if (cek.length) perbaruiCek();

    // Catatan wajib untuk Minta Revisi / Tolak
    ['btnRevisi', 'btnTolak'].forEach(function (id) {
      var b = document.getElementById(id);
      if (b && catatan) b.addEventListener('click', function () { catatan.required = true; });
    });
    $$('[data-wajib-catatan]', fk).forEach(function (b) {
      if (catatan) b.addEventListener('click', function () { catatan.required = true; });
    });
    if (setuju && catatan) setuju.addEventListener('click', function () { catatan.required = false; });

    fk.addEventListener('submit', function (e) {
      var v = e.submitter ? e.submitter.value : '';
      var pesan = { setuju: 'Setujui laporan ini?', revisi: 'Kembalikan laporan ini untuk direvisi?', perbaikan: 'Kembalikan surat ini untuk diperbaiki?', tolak: 'Tolak laporan ini?' }[v];
      if (pesan && !window.confirm(pesan + ' Keputusan akan tercatat permanen.')) e.preventDefault();
    });
  }

  /* ---------- Surat CHR: unggah, ringkasan, dan kirim ---------- */
  var fs = $('#formSurat');
  if (fs) {
    var MAKS_BYTE = (parseInt(fs.dataset.maksMb, 10) || 10) * 1024 * 1024;
    var slots = $$('.sr-slot', fs);
    var baru = {};   // kode slot -> File yang baru dipilih (belum disimpan)

    var fmt = function (b) {
      return b >= 1048576 ? (b / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';
    };
    var adaDiServer = function (slot) { return slot.dataset.adaNama !== undefined; };

    var perbaruiSlot = function (slot) {
      var f = baru[slot.dataset.slot], ada = adaDiServer(slot);
      var fAda = $('.sr-file-ada', slot), fBaru = $('.sr-file-baru', slot), drop = $('.sr-drop', slot), st = $('[data-state]', slot);
      fAda.hidden = !(ada && !f);
      fBaru.hidden = !f;
      drop.hidden = !!(ada || f);
      if (f) {
        $('[data-nama]', fBaru).textContent = f.name;
        $('[data-meta]', fBaru).textContent = 'PDF · ' + fmt(f.size) + ' · Belum disimpan';
      }
      st.textContent = f ? 'Siap disimpan' : (ada ? 'Terunggah' : 'Belum diunggah');
      st.classList.toggle('is-ok', !!(f || ada));
    };

    var hitung = function () {
      var n = 0, total = 0, wajib = 0, wajibOk = 0;
      slots.forEach(function (s) {
        var f = baru[s.dataset.slot], ada = adaDiServer(s), wj = s.dataset.wajib === '1';
        var ukuran = f ? f.size : (ada ? (parseInt(s.dataset.adaUkuran, 10) || 0) : 0);
        var ada_ = !!(f || ada);
        if (ada_) { n++; total += ukuran; }
        if (wj) { wajib++; if (ada_) wajibOk++; }
        var li = $('#srList li[data-slot="' + s.dataset.slot + '"]');
        if (li) {
          li.classList.toggle('ok', ada_);
          var ket = $('[data-ket]', li);
          if (ket) ket.textContent = ada_ ? '' : 'Belum diunggah · ' + (wj ? 'Wajib' : 'Opsional');
        }
      });

      var lengkap = wajibOk === wajib;
      $('#srCount').textContent = n + ' surat · Total ' + (total ? fmt(total) : '0 KB');
      $('#srBar').style.width = (wajib ? Math.round(wajibOk / wajib * 100) : 100) + '%';
      $('#srBarLabel').textContent = lengkap ? 'Surat wajib lengkap' : 'Surat wajib belum lengkap (' + wajibOk + '/' + wajib + ')';
      $('#srSiap').classList.toggle('is-belum', !lengkap);
      $('#srSiapChip').textContent = lengkap ? 'Siap dikirim' : 'Belum lengkap';
      $('#srSiapTeks').textContent = lengkap ? 'Surat sudah siap dikirim' : 'Unggah ' + (wajib - wajibOk) + ' surat wajib lagi';
      $('#srFooterJudul').textContent = lengkap ? 'Siap mengirim ' + n + ' surat ke User INTRAM' : 'Lengkapi surat wajib untuk mengirim';
      $('#btnKirim').disabled = !lengkap;
    };

    slots.forEach(function (slot) {
      var kode = slot.dataset.slot, input = $('input[type=file]', slot), drop = $('.sr-drop', slot);

      var terima = function (file) {
        if (!file) return;
        if (!/\.pdf$/i.test(file.name) || (file.type && file.type !== 'application/pdf')) {
          window.alert('Berkas harus berformat PDF.'); input.value = ''; return;
        }
        if (file.size > MAKS_BYTE) {
          window.alert('Ukuran berkas maksimal ' + (MAKS_BYTE / 1048576) + ' MB.'); input.value = ''; return;
        }
        baru[kode] = file;
        perbaruiSlot(slot); hitung();
      };

      $$('[data-pilih],[data-ganti]', slot).forEach(function (b) { b.addEventListener('click', function () { input.click(); }); });
      input.addEventListener('change', function () {
        if (input.files && input.files[0]) { terima(input.files[0]); }
        else { delete baru[kode]; perbaruiSlot(slot); hitung(); }
      });
      $$('[data-batal]', slot).forEach(function (b) {
        b.addEventListener('click', function () { input.value = ''; delete baru[kode]; perbaruiSlot(slot); hitung(); });
      });

      // Tarik dan lepas berkas
      ['dragenter', 'dragover'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
      });
      drop.addEventListener('dragleave', function () { drop.classList.remove('is-over'); });
      drop.addEventListener('drop', function (e) {
        e.preventDefault(); drop.classList.remove('is-over');
        var f = e.dataTransfer && e.dataTransfer.files[0];
        if (!f) return;
        try { var dt = new DataTransfer(); dt.items.add(f); input.files = dt.files; }
        catch (err) { window.alert('Browser Anda belum mendukung tarik-dan-lepas. Gunakan tombol Pilih berkas.'); return; }
        terima(f);
      });
    });

    var ta = $('#srCatatan'), ctr = $('#srCounter');
    if (ta && ctr) {
      var hitungKarakter = function () { ctr.textContent = ta.value.length.toLocaleString('id-ID') + ' / 1.000 karakter'; };
      ta.addEventListener('input', hitungKarakter);
      hitungKarakter();
    }

    fs.addEventListener('submit', function (e) {
      var v = e.submitter ? e.submitter.value : '';
      if (v === 'kirim' && !window.confirm('Kirim surat ke User INTRAM? Surat yang sudah dikirim tidak dapat diubah.')) e.preventDefault();
    });

    slots.forEach(perbaruiSlot);
    hitung();
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
      var maksMb = parseInt(f.dataset.maksMb, 10) || 5;
      if (file && f.hasAttribute('data-pdf') && !/\.pdf$/i.test(file.name)) {
        alert('Berkas harus berformat PDF.');
        f.value = '';
        label.value = '';
        return;
      }
      if (file && file.size > maksMb * 1024 * 1024) {
        alert('Ukuran file maksimal ' + maksMb + ' MB.');
        f.value = '';
        label.value = '';
        return;
      }
      label.value = file ? file.name : '';
    });
  });

  /* ---------- Notifikasi (lonceng) ---------- */
  (function () {
    var btn = $('#notifBtn'), panel = $('#notifPanel');
    if (!btn || !panel) return;
    function tutup() { panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var buka = panel.hidden;
      panel.hidden = !buka;
      btn.setAttribute('aria-expanded', buka ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) { if (!panel.contains(e.target)) tutup(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutup(); });
  })();

  /* ---------- Konfirmasi lewat tombol submit (data-konfirmasi) ---------- */
  $$('form').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      var pesan = e.submitter && e.submitter.dataset ? e.submitter.dataset.konfirmasi : '';
      if (pesan && !window.confirm(pesan)) e.preventDefault();
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
      batasiModa();
    });
  }
})();
