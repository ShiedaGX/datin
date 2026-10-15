(function () {
  'use strict';

  /* Tampilkan/sembunyikan kata sandi (tombol mata) */
  document.querySelectorAll('.eye[data-target]').forEach(function (btn) {
    var input = document.getElementById(btn.dataset.target);
    if (!input) return;
    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', String(show));
      btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
  });

  /* Konfirmasi kata sandi harus sama */
  var pw = document.getElementById('password');
  var pw2 = document.getElementById('password_confirmation');
  if (pw && pw2) {
    var cek = function () {
      pw2.setCustomValidity(pw2.value !== '' && pw.value !== pw2.value ? 'Konfirmasi kata sandi tidak sama.' : '');
    };
    pw.addEventListener('input', cek);
    pw2.addEventListener('input', cek);
  }

  /* Cegah submit ganda */
  document.querySelectorAll('form.auth-form').forEach(function (form) {
    var btn = form.querySelector('.btn-login');
    if (!btn) return;
    var label = btn.firstElementChild;
    var asli = label.textContent;
    form.addEventListener('submit', function () {
      btn.disabled = true;
      label.textContent = 'Memproses...';
    });
    window.addEventListener('pageshow', function () {
      btn.disabled = false;
      label.textContent = asli;
    });
  });
})();
