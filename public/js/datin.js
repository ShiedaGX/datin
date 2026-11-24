(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };


  /* ---------- Dialog konfirmasi (pengganti window.confirm) ---------- */
  var dlgAktif = null;
  function tampilKonfirmasi(o, ok) {
    if (dlgAktif) return;
    var ikon = {
      primary: '<path d="M5 12l4.5 4.5L19 7"/>',
      danger: '<path d="M12 8v5M12 16.5v.01"/><path d="M10.3 4.2L2.8 17.5A2 2 0 004.5 20.5h15a2 2 0 001.7-3L13.7 4.2a2 2 0 00-3.4 0z"/>',
      warning: '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/>'
    };
    var nada = o.nada || 'primary';
    var tpl = document.createElement('div');
    tpl.className = 'dlg-backdrop';
    tpl.innerHTML =
      '<div class="dlg dlg-' + nada + '" role="alertdialog" aria-modal="true" aria-labelledby="dlgJudul" aria-describedby="dlgPesan">' +
        '<div class="dlg-ikon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + (ikon[nada] || ikon.primary) + '</svg></div>' +
        '<h2 class="dlg-judul" id="dlgJudul"></h2>' +
        '<p class="dlg-pesan" id="dlgPesan"></p>' +
        (o.catatan ? '<p class="dlg-catatan"></p>' : '') +
        '<div class="dlg-aksi">' +
          '<button type="button" class="btn btn-light dlg-batal"></button>' +
          '<button type="button" class="btn dlg-ok ' + (nada === 'danger' ? 'btn-danger' : 'btn-primary') + '"></button>' +
        '</div>' +
      '</div>';
    $('.dlg-judul', tpl).textContent = o.judul || 'Konfirmasi';
    $('.dlg-pesan', tpl).textContent = o.pesan || '';
    if (o.catatan) $('.dlg-catatan', tpl).textContent = o.catatan;
    $('.dlg-batal', tpl).textContent = o.batal || 'Batal';
    $('.dlg-ok', tpl).textContent = o.ok || 'Ya, lanjutkan';

    var fokusSebelum = document.activeElement;
    function tutup(hasil) {
      document.removeEventListener('keydown', tombol, true);
      tpl.classList.remove('show');
      document.body.classList.remove('dlg-terbuka');
      setTimeout(function () { if (tpl.parentNode) tpl.parentNode.removeChild(tpl); }, 150);
      dlgAktif = null;
      if (fokusSebelum && fokusSebelum.focus) fokusSebelum.focus();
      if (hasil) ok();
    }
    function tombol(e) {
      if (e.key === 'Escape') { e.preventDefault(); tutup(false); }
      else if (e.key === 'Tab') {
        var f = $$('button', tpl);
        var i = f.indexOf(document.activeElement);
        e.preventDefault();
        f[(i + (e.shiftKey ? -1 : 1) + f.length) % f.length].focus();
      }
    }
    tpl.addEventListener('mousedown', function (e) { if (e.target === tpl) tutup(false); });
    $('.dlg-batal', tpl).addEventListener('click', function () { tutup(false); });
    $('.dlg-ok', tpl).addEventListener('click', function () { tutup(true); });
    document.addEventListener('keydown', tombol, true);
    document.body.appendChild(tpl);
    document.body.classList.add('dlg-terbuka');
    dlgAktif = tpl;
    requestAnimationFrame(function () { tpl.classList.add('show'); $('.dlg-ok', tpl).focus(); });
  }

  /* Tahan submit sampai pengguna menyetujui di dialog, lalu kirim ulang formulir yang sama */
  function konfirmasiSubmit(e, form, opsi) {
    if (form.__konfirmasiLolos) { form.__konfirmasiLolos = false; return true; }
    e.preventDefault();
    var pengirim = e.submitter || null;
    tampilKonfirmasi(opsi, function () {
      form.__konfirmasiLolos = true;
      if (form.requestSubmit) form.requestSubmit(pengirim); else form.submit();
    });
    return false;
  }

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
<<<<<<< HEAD
=======
  if (kab && kab.tagName !== 'SELECT') kab = null;   // form user: #id_kab_kota berupa input hidden (hasil saran Instansi), bukan dropdown
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54

  /* ---------- Revisi dari INTRAM: moda yang diminta disarankan, moda lain tetap boleh diajukan ---------- */
  var modaRadios = $$('input[name="id_moda"]'), modaHint = $('#modaHint'), modaAuto = false;
  function batasiModa() {
    if (!kab || !modaRadios.length) return;
    var o = kab.options[kab.selectedIndex];
    var diminta = o && o.dataset && o.dataset.revisi ? o.dataset.revisi.split(',') : [];
    var sudah = o && o.dataset && o.dataset.sudah ? o.dataset.sudah.split(',') : [];
    var namaModa = function (ids) {
      return modaRadios.filter(function (r) { return ids.indexOf(r.value) !== -1; })
        .map(function (r) { return r.nextElementSibling.textContent.trim(); }).join(', ');
    };
    modaRadios.forEach(function (r) { r.disabled = false; r.closest('.chip').classList.remove('is-off'); });
    if (!diminta.length) {
      if (modaAuto) { modaRadios.forEach(function (r) { r.checked = false; }); modaAuto = false; }  // batalkan pilihan otomatis
      if (modaHint) {
        modaHint.hidden = !sudah.length;
        if (sudah.length) modaHint.textContent = 'Daerah ini pernah diajukan untuk moda ' + namaModa(sudah) + '. Anda dapat mengajukan lagi; pengajuan sebelumnya tidak berubah.';
      }
      return;
    }
    var saran = modaRadios.filter(function (r) { return diminta.indexOf(r.value) !== -1; });
    if (saran.length && !modaRadios.some(function (r) { return r.checked; })) { saran[0].checked = true; modaAuto = true; }
    if (modaHint) {
      modaHint.textContent = 'INTRAM meminta Anda mengajukan untuk moda ' + namaModa(diminta) + '. Pengajuan moda sebelumnya tidak berubah.';
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
  var dataSektor = [];
  try { dataSektor = JSON.parse(($('#data-sektor') || {}).textContent || '[]'); } catch (e) { dataSektor = []; }
  var namaSektor = dataSektor.map(function (x) { return x.nama; });
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
      if (namaSektor.indexOf(el.value.trim()) !== -1) return;   // nama sektor yang memuat koma jangan dipecah
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

  /* ---------- Potensi: saran sektor ---------- */
  var sektorList = $('#sektorList');
  if (sektorList && potensi.length && dataSektor.length) {
    var sAktif = -1, sHasil = [], sInput = null;
    var sNorm = function (t) { return String(t).toLowerCase(); };

    var sTutup = function () {
      sektorList.hidden = true; sektorList.innerHTML = ''; sAktif = -1;
      if (sInput) { sInput.setAttribute('aria-expanded', 'false'); sInput.removeAttribute('aria-activedescendant'); }
    };
    var sCari = function (el) {
      var q = sNorm(el.value).trim();
      var dipakai = potensi.filter(function (o) { return o !== el; }).map(function (o) { return sNorm(o.value).trim(); });
      var out = [], kataCari = q === '' ? [] : q.split(/\s+/);
      dataSektor.forEach(function (d) {
        if (dipakai.indexOf(sNorm(d.nama)) !== -1) return;          // sektor yang sudah dipilih pada kolom lain
        var nama = sNorm(d.nama), kata = '', cocok = true;
        // setiap kata yang diketik harus cocok dengan nama sektor atau salah satu kata kuncinya
        kataCari.forEach(function (t) {
          if (nama.indexOf(t) !== -1) return;
          var k = d.kunci.filter(function (x) { return x.indexOf(t) !== -1; })[0];
          if (k) { kata = kata || k; } else { cocok = false; }
        });
        if (!cocok) return;
        out.push({ nama: d.nama, kata: kata, skor: nama.indexOf(q) === 0 ? 0 : (kata ? 2 : 1) });
      });
      if (q !== '') out.sort(function (a, b) { return a.skor - b.skor; });
      return out;
    };
    var sSelesaiAt = 0;
    var sPilih = function (item) {
      sSelesaiAt = Date.now();
      sInput.value = item.nama;
      sTutup();
      var berikut = potensi[potensi.indexOf(sInput) + 1];
      if (berikut && berikut.value.trim() === '') berikut.focus();
    };
    var sGambar = function (el) {
      sInput = el; sektorList.innerHTML = '';
      sHasil.forEach(function (it, i) {
        var li = document.createElement('li');
        li.id = 'sektor-opt-' + i; li.setAttribute('role', 'option'); li.setAttribute('aria-selected', 'false');
        var a = document.createElement('span'); a.textContent = it.nama;
        var b = document.createElement('small'); b.textContent = it.kata ? 'cocok: ' + it.kata : 'Sektor';
        li.appendChild(a); li.appendChild(b);
        li.addEventListener('mousedown', function (e) { e.preventDefault(); sPilih(it); });
        sektorList.appendChild(li);
      });
      // posisikan tepat di bawah kolom yang sedang diisi
      sektorList.style.top = (el.offsetTop + el.offsetHeight + 4) + 'px';
      sektorList.style.left = el.offsetLeft + 'px';
      sektorList.style.right = 'auto';
      sektorList.style.width = el.offsetWidth + 'px';
      sektorList.hidden = false; el.setAttribute('aria-expanded', 'true');
    };
    var sPerbarui = function (el) {
      sHasil = sCari(el);
      if (!sHasil.length) { sTutup(); return; }
      sAktif = -1; sGambar(el);
    };
    var sSorot = function () {
      Array.prototype.forEach.call(sektorList.children, function (li, i) {
        li.setAttribute('aria-selected', String(i === sAktif));
        if (i === sAktif) { sInput.setAttribute('aria-activedescendant', li.id); li.scrollIntoView({ block: 'nearest' }); }
      });
    };

    potensi.forEach(function (el) {
      el.setAttribute('role', 'combobox'); el.setAttribute('aria-autocomplete', 'list');
      el.setAttribute('aria-expanded', 'false'); el.setAttribute('aria-controls', 'sektorList');
      el.addEventListener('focus', function () { sPerbarui(el); });
      el.addEventListener('click', function () { if (Date.now() - sSelesaiAt > 400) sPerbarui(el); });   // abaikan click susulan setelah memilih saran
      el.addEventListener('input', function () { sPerbarui(el); });
      el.addEventListener('blur', function () { setTimeout(function () { if (document.activeElement !== el && sInput === el) sTutup(); }, 120); });   // jangan tutup daftar yang sudah berpindah ke kolom lain
      el.addEventListener('keydown', function (e) {
        if (sektorList.hidden || sInput !== el) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); sAktif = (sAktif + 1) % sHasil.length; sSorot(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); sAktif = (sAktif - 1 + sHasil.length) % sHasil.length; sSorot(); }
        else if (e.key === 'Enter' && sAktif >= 0) { e.preventDefault(); sPilih(sHasil[sAktif]); }
        else if (e.key === 'Escape') { e.stopPropagation(); sTutup(); }
      });
    });
  }

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

<<<<<<< HEAD
=======
  /* ---------- Form user: saran Instansi (kab/kota) dari server ---------- */
  var inInst = $('#instansi'), listInst = $('#instansiList'), idKab = $('#id_kab_kota');
  if (inInst && listInst && idKab && inInst.dataset.url) {
    var instHasil = [], instAktif = -1, instTimer = null, instSeq = 0;

    var tutupInst = function () {
      listInst.hidden = true; listInst.innerHTML = ''; instAktif = -1;
      inInst.setAttribute('aria-expanded', 'false'); inInst.removeAttribute('aria-activedescendant');
    };
    var tandaiInst = function (i) {
      var li = $$('li', listInst);
      li.forEach(function (el, n) { el.setAttribute('aria-selected', n === i ? 'true' : 'false'); });
      instAktif = i;
      if (li[i]) { inInst.setAttribute('aria-activedescendant', li[i].id); li[i].scrollIntoView({ block: 'nearest' }); }
    };
    var pilihInst = function (s) {
      inInst.value = s.value;
      idKab.value = s.id_kab_kota;                         // ID inilah yang dikirim & divalidasi server
      var w = $('#id_wilayah'); if (w && s.id_wilayah) w.value = s.id_wilayah;
      inInst.classList.remove('is-invalid');
      tutupInst();
    };
    var gambarInst = function () {
      listInst.innerHTML = '';
      if (!instHasil.length) { tutupInst(); return; }
      instHasil.forEach(function (s, i) {
        var li = document.createElement('li');
        li.id = 'instansi-opt-' + i; li.setAttribute('role', 'option'); li.setAttribute('aria-selected', 'false');
        var a = document.createElement('span'); a.textContent = s.value;
        var b = document.createElement('small'); b.textContent = s.provinsi;
        li.appendChild(a); li.appendChild(b);
        li.addEventListener('mousedown', function (e) { e.preventDefault(); pilihInst(s); });
        listInst.appendChild(li);
      });
      listInst.hidden = false;
      inInst.setAttribute('aria-expanded', 'true');
    };

    inInst.addEventListener('input', function () {
      idKab.value = '';                                    // teks diubah: pilihan sebelumnya tidak berlaku lagi
      clearTimeout(instTimer);
      var q = inInst.value.trim();
      if (q.length < 2) { instSeq++; tutupInst(); return; }
      instTimer = setTimeout(function () {
        var n = ++instSeq;
        fetch(inInst.dataset.url + '?q=' + encodeURIComponent(q), {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.ok ? r.json() : []; }).then(function (d) {
          if (n !== instSeq) return;                       // abaikan respons yang sudah usang
          instHasil = Array.isArray(d) ? d : []; instAktif = -1; gambarInst();
        }).catch(tutupInst);
      }, 200);
    });
    inInst.addEventListener('keydown', function (e) {
      if (listInst.hidden || !instHasil.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); tandaiInst((instAktif + 1) % instHasil.length); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); tandaiInst((instAktif - 1 + instHasil.length) % instHasil.length); }
      else if (e.key === 'Enter' && instAktif >= 0) { e.preventDefault(); pilihInst(instHasil[instAktif]); }
      else if (e.key === 'Escape') { tutupInst(); }
    });
    inInst.addEventListener('blur', function () { setTimeout(tutupInst, 150); });
  }

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
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
      var opsi = {
        setuju:    { judul: 'Setujui laporan ini?', pesan: 'Laporan akan ditandai disetujui dan diteruskan ke tahap berikutnya.', ok: 'Ya, setujui', nada: 'primary' },
        revisi:    { judul: 'Minta revisi laporan?', pesan: 'Laporan dikembalikan kepada pengirim untuk diperbaiki sesuai catatan Anda.', ok: 'Ya, minta revisi', nada: 'warning' },
        perbaikan: { judul: 'Kembalikan surat?', pesan: 'Surat dikembalikan untuk diperbaiki sesuai catatan Anda.', ok: 'Ya, kembalikan', nada: 'warning' },
        tolak:     { judul: 'Tolak laporan ini?', pesan: 'Laporan akan ditolak dan pengirim diberi tahu.', ok: 'Ya, tolak', nada: 'danger' }
      }[v];
      if (opsi) {
        opsi.catatan = 'Keputusan akan tercatat permanen pada riwayat persetujuan.';
        konfirmasiSubmit(e, fk, opsi);
      }
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
      var n = 0, total = 0, wajib = 0, wajibOk = 0, tanpaNomor = 0;
      slots.forEach(function (s) {
        var f = baru[s.dataset.slot], ada = adaDiServer(s), wj = s.dataset.wajib === '1';
        var ukuran = f ? f.size : (ada ? (parseInt(s.dataset.adaUkuran, 10) || 0) : 0);
        var ada_ = !!(f || ada);
        if (ada_) { n++; total += ukuran; }
        var nomorEl = $('.sr-nomor input', s), nomorOk = !nomorEl || nomorEl.value.trim() !== '';
        if (ada_ && !nomorOk) tanpaNomor++;
        if (wj) { wajib++; if (ada_ && nomorOk) wajibOk++; }
        var li = $('#srList li[data-slot="' + s.dataset.slot + '"]');
        if (li) {
          li.classList.toggle('ok', ada_ && nomorOk);
          var ket = $('[data-ket]', li);
          if (ket) ket.textContent = ada_ ? (nomorOk ? '' : 'Nomor surat belum diisi') : 'Belum diunggah · ' + (wj ? 'Wajib' : 'Opsional');
        }
      });

      var lengkap = wajibOk === wajib && tanpaNomor === 0;
      $('#srCount').textContent = n + ' surat · Total ' + (total ? fmt(total) : '0 KB');
      $('#srBar').style.width = (wajib ? Math.round(wajibOk / wajib * 100) : 100) + '%';
      $('#srBarLabel').textContent = wajibOk === wajib ? 'Surat wajib lengkap' : 'Surat wajib belum lengkap (' + wajibOk + '/' + wajib + ')';
      $('#srSiap').classList.toggle('is-belum', !lengkap);
      $('#srSiapChip').textContent = lengkap ? 'Siap dikirim' : 'Belum lengkap';
      $('#srSiapTeks').textContent = lengkap ? 'Surat sudah siap dikirim' : (wajibOk < wajib ? 'Lengkapi ' + (wajib - wajibOk) + ' surat wajib (berkas dan nomor surat)' : 'Isi nomor surat pada setiap berkas');
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
      if (v === 'kirim') konfirmasiSubmit(e, fs, { judul: 'Kirim surat ke User INTRAM?', pesan: 'Surat yang sudah dikirim tidak dapat diubah lagi.', ok: 'Ya, kirim', nada: 'primary' });
    });

    $$('.sr-nomor input', fs).forEach(function (el) { el.addEventListener('input', hitung); });
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

  /* ---------- Ubah kata sandi: tampilkan sandi & cek konfirmasi ---------- */
  (function () {
    var tampil = $('#tampilSandi'), baru = $('#password'), konfirm = $('#password_confirmation'), pesan = $('#sandiBeda');
    if (!tampil || !baru || !konfirm) return;
    var kolom = [$('#password_lama'), baru, konfirm];
    tampil.addEventListener('change', function () {
      kolom.forEach(function (k) { if (k) k.type = tampil.checked ? 'text' : 'password'; });
    });
    var cek = function () {
      var beda = konfirm.value !== '' && baru.value !== konfirm.value;
      konfirm.setCustomValidity(beda ? 'Konfirmasi kata sandi baru tidak sama.' : '');
      if (pesan) pesan.hidden = !beda;
    };
    baru.addEventListener('input', cek);
    konfirm.addEventListener('input', cek);
  })();

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
      if (pesan) konfirmasiSubmit(e, f, { judul: 'Konfirmasi', pesan: pesan, ok: 'Ya, lanjutkan', nada: 'warning' });
    });
  });

  /* ---------- Filter otomatis & konfirmasi hapus ---------- */
  $$('[data-autosubmit]').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      konfirmasiSubmit(e, f, { judul: 'Hapus data?', pesan: f.dataset.confirm, ok: 'Ya, hapus', nada: 'danger' });
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
