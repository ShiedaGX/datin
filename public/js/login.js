(function () {
  'use strict';
  var pw = document.getElementById('password');
  var toggle = document.getElementById('togglePw');
  if (pw && toggle) {
    toggle.addEventListener('click', function () {
      var show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(show));
      toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
  }

  var form = document.getElementById('loginForm');
  var btn = document.getElementById('btnLogin');
  if (form && btn) {
    form.addEventListener('submit', function () {
      btn.disabled = true;
      btn.firstElementChild.textContent = 'Memproses...';
    });
    window.addEventListener('pageshow', function () {
      btn.disabled = false;
      btn.firstElementChild.textContent = 'Masuk';
    });
  }
})();
