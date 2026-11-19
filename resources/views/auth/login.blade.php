@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
@php
    // Nomor WhatsApp administrator. Ganti angka di bawah ini (format 62..., tanpa + dan spasi).
    // Opsional: bila ADMIN_WA diisi di .env (lewat config/services.php), nilai itu yang dipakai.
    $nomorAdmin = '6288808929725';
    $wa = preg_replace('/\D/', '', (string) (config('services.admin_wa') ?: $nomorAdmin));
    if (str_starts_with($wa, '0')) {
        $wa = '62' . substr($wa, 1);
    }
    $waBase = 'https://wa.me/' . $wa;

    $pesanLupa = "Halo Admin, saya lupa kata sandi akun Portal Data Transportasi Bersubsidi. Mohon bantuan untuk mengatur ulang kata sandi.";
    $pesanAkun = "Halo Admin, saya belum memiliki akun Portal Data Transportasi Bersubsidi dan ingin meminta dibuatkan akun.\n\nNama:\nInstansi:\nEmail instansi:";
@endphp

<form class="login-form auth-form" id="loginForm" method="POST" action="{{ route('login.attempt') }}">
    @csrf
    <p class="kicker">AKSES AKUN</p>
    <h1>Selamat datang</h1>
    <p class="lead">Masuk menggunakan akun instansi Anda untuk melanjutkan pengelolaan data.</p>

    @if (session('status'))
        <div class="alert alert-ok" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="field">
        <label for="email">Alamat email</label>
        <div class="input @error('email') is-invalid @enderror">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@instansi.go.id" required autofocus autocomplete="email">
        </div>
    </div>

    <div class="field">
        <label for="password">Kata sandi</label>
        <div class="input">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/><circle cx="12" cy="15.5" r="1"/></svg>
            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
            <button type="button" class="eye" data-target="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
    </div>

    <div class="row">
        <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span></span>Ingat saya</label>
        {{-- Lupa kata sandi: buka WhatsApp administrator (email yang sudah diketik ikut ditulis di pesan) --}}
        <a class="link" id="waLupa" href="{{ $waBase }}?text={{ rawurlencode($pesanLupa) }}"
           data-pesan="{{ $pesanLupa }}" target="_blank" rel="noopener noreferrer"
           title="Hubungi administrator melalui WhatsApp">Lupa kata sandi?</a>
    </div>

    <button type="submit" class="btn-login" id="btnLogin">
        <span>Masuk</span>
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </button>

    <p class="register">Belum memiliki akun? Akun dibuat oleh administrator.
        <a class="link" href="{{ $waBase }}?text={{ rawurlencode($pesanAkun) }}" target="_blank" rel="noopener noreferrer"
           title="Hubungi administrator melalui WhatsApp">Hubungi administrator</a>
    </p>

    <p class="secure">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/><circle cx="12" cy="15.5" r="1"/></svg>
        <span>Koneksi terenkripsi. Informasi akun Anda dilindungi sesuai kebijakan keamanan data pemerintah.</span>
    </p>
</form>

<script>
(() => {
    const link = document.getElementById('waLupa');
    const email = document.getElementById('email');
    if (!link || !email) return;

    // Saat diklik, sertakan email yang sudah diketik pengguna agar admin tahu akun mana.
    link.addEventListener('click', () => {
        const base = link.href.split('?')[0];
        const isi = email.value.trim();
        const pesan = link.dataset.pesan + (isi ? '\n\nEmail akun: ' + isi : '');
        link.href = base + '?text=' + encodeURIComponent(pesan);
    });
})();
</script>
@endsection
