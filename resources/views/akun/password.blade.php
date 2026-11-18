@extends('layouts.app')

@section('title', 'Ubah Kata Sandi')
@section('heading', 'Akun Saya')
@section('subheading', 'Ubah kata sandi')

@section('content')
<form class="box form akun-form" method="POST" action="{{ route('akun.password.update') }}" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="card-head">
        <h2>Ubah kata sandi</h2>
        <p>Akun Anda dibuat oleh administrator. Disarankan mengganti kata sandi awal dengan kata sandi yang hanya Anda ketahui.</p>
    </div>

    <dl class="akun-info">
        <div><dt>Nama</dt><dd>{{ auth()->user()->full_name }}</dd></div>
        <div><dt>Email</dt><dd>{{ auth()->user()->email }}</dd></div>
        <div><dt>Role</dt><dd>{{ auth()->user()->role->nama_role ?? '-' }}</dd></div>
    </dl>

    <div class="field">
        <label for="password_lama">Kata sandi saat ini</label>
        <input type="password" id="password_lama" name="password_lama" required autocomplete="current-password" class="@error('password_lama') is-invalid @enderror">
        @error('password_lama')<p class="error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="password">Kata sandi baru</label>
        <input type="password" id="password" name="password" minlength="8" required autocomplete="new-password" placeholder="Minimal 8 karakter" class="@error('password') is-invalid @enderror">
        @error('password')<p class="error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="password_confirmation">Konfirmasi kata sandi baru</label>
        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" required autocomplete="new-password" placeholder="Ulangi kata sandi baru">
        <p class="error" id="sandiBeda" hidden>Konfirmasi kata sandi baru tidak sama.</p>
    </div>

    <label class="check akun-tampil">
        <input type="checkbox" id="tampilSandi">
        <span></span>
        Tampilkan kata sandi
    </label>

    <div class="actions">
        <a class="btn btn-outline" href="{{ route('dashboard') }}">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan Kata Sandi</button>
    </div>
</form>
@endsection
