
@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')

@php
    // Nomor WhatsApp administrator
    $nomorAdmin = '6288808929725';

    $wa = preg_replace(
        '/\D/',
        '',
        (string) (config('services.admin_wa') ?: $nomorAdmin)
    );

    if (str_starts_with($wa, '0')) {
        $wa = '62' . substr($wa, 1);
    }

    $waBase = 'https://wa.me/' . $wa;

    $pesanLupa = "Halo Admin, saya lupa kata sandi akun Sistem Informasi Transportasi Bersubsidi INTRAM. Mohon bantuan untuk mengatur ulang kata sandi.";

    $pesanAkun = "Halo Admin, saya belum memiliki akun Sistem Informasi Transportasi Bersubsidi INTRAM dan ingin meminta dibuatkan akun.\n\nNama:\nInstansi:\nEmail instansi:";
@endphp

{{-- JUDUL LOGIN --}}
<div class="login-heading">

    <div class="login-eyebrow">
        <span class="eyebrow-line"></span>
        PORTAL AKSES INSTANSI
    </div>

    <h1>
        Selamat datang<span class="heading-dot">.</span>
    </h1>

    <p>
        Masuk menggunakan akun instansi Anda untuk mengakses
        Sistem Informasi Transportasi Bersubsidi INTRAM.
    </p>

</div>

{{-- PESAN STATUS --}}
@if (session('status'))
    <div class="auth-alert auth-alert-success" role="status">
        {{ session('status') }}
    </div>
@endif

{{-- PESAN ERROR --}}
@if ($errors->any())
    <div class="auth-alert auth-alert-error" role="alert">
        {{ $errors->first() }}
    </div>
@endif

{{-- FORM LOGIN --}}
<form
    class="login-form auth-form"
    id="loginForm"
    method="POST"
    action="{{ route('login.attempt') }}"
>

    @csrf

    {{-- EMAIL --}}
    <div class="form-field">

        <label for="email">
            Alamat Email
        </label>

        <div class="input-wrap @error('email') is-invalid @enderror">

            <svg
                viewBox="0 0 24 24"
                width="22"
                height="22"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <rect x="3" y="5" width="18" height="14" rx="2"/>
                <path d="M3 7l9 6 9-6"/>
            </svg>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="nama@instansi.go.id"
                autocomplete="email"
                required
                autofocus
            >

        </div>

    </div>

    {{-- PASSWORD --}}
    <div class="form-field">

        <label for="password">
            Kata Sandi
        </label>

        <div class="input-wrap">

            <svg
                viewBox="0 0 24 24"
                width="22"
                height="22"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <rect x="5" y="11" width="14" height="9" rx="2"/>
                <path d="M8 11V8a4 4 0 018 0v3"/>
                <circle cx="12" cy="15.5" r="1"/>
            </svg>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan kata sandi"
                autocomplete="current-password"
                required
            >

            <button
                type="button"
                class="eye password-toggle"
                data-target="password"
                aria-label="Tampilkan kata sandi"
                aria-pressed="false"
            >

                <svg
                    viewBox="0 0 24 24"
                    width="22"
                    height="22"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>

            </button>

        </div>

    </div>

    {{-- REMEMBER ME & LUPA PASSWORD --}}
    <div class="form-options">

        <label class="remember-label">

            <input
                type="checkbox"
                name="remember"
                value="1"
                @checked(old('remember'))
            >

            <span>
                Ingat saya
            </span>

        </label>

        <a
            class="forgot-link"
            id="waLupa"
            href="{{ $waBase }}?text={{ rawurlencode($pesanLupa) }}"
            data-pesan="{{ $pesanLupa }}"
            target="_blank"
            rel="noopener noreferrer"
        >
            Lupa kata sandi?
        </a>

    </div>

    {{-- TOMBOL LOGIN --}}
    <button
        type="submit"
        class="btn-login"
        id="btnLogin"
    >

        <span>
            Masuk ke Dashboard
        </span>

        <svg
            viewBox="0 0 24 24"
            width="22"
            height="22"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M5 12h14M13 6l6 6-6 6"/>
        </svg>

    </button>

    {{-- BANTUAN ADMINISTRATOR --}}
    <div class="register-box">

        <div class="register-icon">

            <svg
                viewBox="0 0 24 24"
                width="23"
                height="23"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <circle cx="12" cy="8" r="4"/>
                <path d="M5 21v-2a7 7 0 0114 0v2"/>
            </svg>

        </div>

        <div>

            <strong>
                Belum memiliki akun?
            </strong>

            <p>
                Pendaftaran akun dikelola oleh administrator.

                <a
                    href="{{ $waBase }}?text={{ rawurlencode($pesanAkun) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Hubungi administrator
                </a>
            </p>

        </div>

    </div>

    {{-- INFORMASI KEAMANAN --}}
    <div class="security-note">

        <svg
            viewBox="0 0 24 24"
            width="20"
            height="20"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/>
            <path d="M9 12l2 2 4-4"/>
        </svg>

        <span>
            Akses terbatas untuk pengguna yang memiliki otorisasi.
        </span>

    </div>

</form>

<script>
(() => {

    // WhatsApp lupa kata sandi
    const link = document.getElementById('waLupa');
    const email = document.getElementById('email');

    if (link && email) {
        link.addEventListener('click', () => {

            const base = link.href.split('?')[0];
            const isi = email.value.trim();

            const pesan = link.dataset.pesan +
                (isi ? '\n\nEmail akun: ' + isi : '');

            link.href = base + '?text=' + encodeURIComponent(pesan);

        });
    }

    // Tampilkan atau sembunyikan kata sandi
    document.querySelectorAll('.password-toggle').forEach(button => {

        button.addEventListener('click', () => {

            const target = document.getElementById(
                button.dataset.target
            );

            if (!target) return;

            const show = target.type === 'password';

            target.type = show ? 'text' : 'password';

            button.setAttribute(
                'aria-pressed',
                String(show)
            );

            button.setAttribute(
                'aria-label',
                show
                    ? 'Sembunyikan kata sandi'
                    : 'Tampilkan kata sandi'
            );

        });

    });

})();
</script>

@endsection
