@extends('layouts.auth')

@section('title', 'Buat Akun')

@section('foot-right')
    <span class="copy">&copy; {{ date('Y') }} SIMPUL &bull; Versi 2.4.0</span>
@endsection

@section('content')
<form class="login-form auth-form wide" id="registerForm" method="POST" action="{{ route('register.store') }}">
    @csrf
    <p class="kicker">PENDAFTARAN INSTANSI</p>
    <h1>Buat akun</h1>
    <p class="lead">Lengkapi data berikut. Administrator akan memverifikasi afiliasi instansi Anda.</p>

    <div class="field">
        <label for="full_name">Nama lengkap</label>
        <div class="input @error('full_name') is-invalid @enderror">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Masukkan nama sesuai identitas" maxlength="150" required autofocus autocomplete="name">
        </div>
        @error('full_name')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="instansi">Instansi / organisasi</label>
        <div class="input @error('instansi') is-invalid @enderror">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="12" height="18" rx="1.5"/><path d="M16 9h3a1 1 0 011 1v11H16M8 7h4M8 11h4M8 15h4"/></svg>
            <input type="text" id="instansi" name="instansi" value="{{ old('instansi') }}" placeholder="Contoh: Pemda Sumatera Selatan" maxlength="150" required autocomplete="organization">
        </div>
        @error('instansi')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="email">Alamat email dinas</label>
        <div class="input @error('email') is-invalid @enderror">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@instansi.go.id" maxlength="150" required autocomplete="email">
        </div>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="row-2">
        <div class="field">
            <label for="password">Kata sandi</label>
            <div class="input @error('password') is-invalid @enderror">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/><circle cx="12" cy="15.5" r="1"/></svg>
                <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" minlength="8" required autocomplete="new-password">
                <button type="button" class="eye" data-target="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Konfirmasi kata sandi</label>
            <div class="input">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/><circle cx="12" cy="15.5" r="1"/></svg>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" minlength="8" required autocomplete="new-password">
                <button type="button" class="eye" data-target="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>
    </div>

    <label class="check agree">
        <input type="checkbox" name="setuju" value="1" required @checked(old('setuju'))>
        <span></span>
        Saya menyetujui Ketentuan Layanan dan Kebijakan Privasi SIMPUL, serta memastikan data yang diberikan benar.
    </label>
    @error('setuju')<p class="field-error">{{ $message }}</p>@enderror

    <button type="submit" class="btn-login" id="btnLogin">
        <span>Buat akun instansi</span>
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </button>

    <p class="register">Sudah memiliki akun? <a class="link" href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
</form>
@endsection
