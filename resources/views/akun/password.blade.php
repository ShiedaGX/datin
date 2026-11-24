<<<<<<< HEAD
=======

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
@extends('layouts.app')

@section('title', 'Ubah Kata Sandi')
@section('heading', 'Akun Saya')
@section('subheading', 'Ubah kata sandi')

@section('content')
<<<<<<< HEAD
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
=======

<style>
    /* =========================================
       HALAMAN AKUN — DATIN
       ========================================= */

    .account-page {
        --account-primary: #174a85;
        --account-text: #1c3555;
        --account-muted: #718198;
        --account-border: #e4eaf2;

        width: 100%;
        max-width: 1180px;
        margin: 0;
        padding: 8px 0 32px;
        font-family: 'Inter', sans-serif;
    }

    .account-page *,
    .account-page *::before,
    .account-page *::after {
        box-sizing: border-box;
    }

    /* PAGE HEADER */

    .account-page-title {
        margin-bottom: 22px;
    }

    .account-page-title h2 {
        margin: 0 0 7px;
        color: var(--account-text);
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -.6px;
        line-height: 1.3;
    }

    .account-page-title p {
        margin: 0;
        color: var(--account-muted);
        font-size: 13px;
        line-height: 1.75;
    }

    /* MAIN CARD */

    .account-page form.akun-form {
        display: grid;
        grid-template-columns: minmax(260px, 32%) minmax(0, 68%);
        gap: 0;

        width: 100% !important;
        max-width: none !important;
        min-width: 0;

        margin: 0 !important;
        padding: 0 !important;

        background: #ffffff;
        border: 1px solid var(--account-border);
        border-radius: 16px;
        box-shadow: 0 5px 24px rgba(20, 48, 83, .045);
        overflow: hidden;
    }

    /* =========================================
       LEFT COLUMN — USER INFORMATION
       ========================================= */

    .account-left {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 34px 30px;
        background: #f8fafd;
        border-right: 1px solid var(--account-border);
    }

    .account-user-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 49px;
        height: 49px;
        margin-bottom: 20px;
        color: #2563a9;
        background: #e8f1fd;
        border-radius: 13px;
    }

    .account-user-icon svg {
        width: 24px;
        height: 24px;
    }

    .account-left h3 {
        margin: 0 0 6px;
        color: var(--account-text);
        font-size: 17px;
        font-weight: 800;
        letter-spacing: -.3px;
    }

    .account-left-desc {
        margin: 0 0 28px;
        color: var(--account-muted);
        font-size: 12px;
        line-height: 1.8;
    }

    /* USER DATA */

    .account-page .akun-info {
        display: block;
        width: 100%;
        margin: 0;
        padding: 0;
        background: transparent;
        border: 0;
    }

    .account-page .akun-info > div {
        display: block;
        width: 100%;
        margin: 0;
        padding: 0 0 17px;
        background: transparent;
        border: 0;
        border-bottom: 1px solid #e2e9f2;
        border-radius: 0;
    }

    .account-page .akun-info > div + div {
        padding-top: 17px;
    }

    .account-page .akun-info dt {
        margin: 0 0 7px;
        color: #8191a5;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1px;
    }

    .account-page .akun-info dd {
        margin: 0;
        color: #203b5e;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .account-role {
        display: inline-flex;
        align-items: center;
        padding: 5px 11px;
        color: #255d9e;
        background: #e6f0fd;
        border: 1px solid #d6e6fa;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 700;
    }

    /* LEFT FOOTER */

    .account-security {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-top: auto;
        padding-top: 30px;
    }

    .account-security svg {
        width: 19px;
        height: 19px;
        flex-shrink: 0;
        color: #3972b8;
    }

    .account-security p {
        margin: 0;
        color: #798ba3;
        font-size: 11px;
        line-height: 1.8;
    }

    .account-security strong {
        color: #345f93;
    }

    /* =========================================
       RIGHT COLUMN — PASSWORD FORM
       ========================================= */

    .account-right {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 35px 43px 30px;
        background: #ffffff;
    }

    .account-form-heading {
        padding-bottom: 23px;
        margin-bottom: 25px;
        border-bottom: 1px solid #edf0f5;
    }

    .account-form-heading h3 {
        margin: 0 0 7px;
        color: var(--account-text);
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.4px;
    }

    .account-form-heading p {
        margin: 0;
        color: var(--account-muted);
        font-size: 12px;
        line-height: 1.8;
    }

    /* FORM FIELDS */

    .account-fields {
        display: grid;
        gap: 21px;
        width: 100%;
        max-width: 560px;
    }

    .account-page .field {
        display: block;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .account-page .field label {
        display: block;
        margin: 0 0 9px;
        color: #304963;
        font-size: 12px;
        font-weight: 700;
    }

    .account-page .field input[type="password"],
    .account-page .field input[type="text"] {
        display: block;
        width: 100%;
        max-width: none;
        height: 46px;
        margin: 0;
        padding: 0 15px;

        color: #203b5c;
        background: #fbfcfe;
        border: 1px solid #dce5ef;
        border-radius: 9px;
        outline: none;

        font-family: inherit;
        font-size: 13px;
        font-weight: 500;

        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            background .2s ease;
    }

    .account-page .field input::placeholder {
        color: #a2afbf;
        font-weight: 400;
    }

    .account-page .field input:focus {
        background: #ffffff;
        border-color: #6496d6;
        box-shadow: 0 0 0 3px rgba(46, 110, 190, .09);
    }

    .account-page .field input.is-invalid {
        border-color: #d96b76;
        background: #fffafb;
    }

    .account-page .field .error {
        margin: 7px 0 0;
        color: #c34b58;
        font-size: 11px;
        line-height: 1.6;
    }

    .account-field-hint {
        margin: 7px 0 0;
        color: #8c9aac;
        font-size: 11px;
        line-height: 1.65;
    }

    /* SHOW PASSWORD */

    .account-page .akun-tampil {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        width: fit-content;
        margin-top: 20px;
        color: #536b86;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
    }

    .account-page .akun-tampil input {
        cursor: pointer;
    }

    /* ACTION BUTTONS */

    .account-page .actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 11px;

        width: 100%;
        max-width: none;
        margin: 32px 0 0;
        padding: 23px 0 0;

        border-top: 1px solid #edf0f5;
    }

    .account-page .actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 10px 22px;
        margin: 0;

        border-radius: 9px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        text-align: center;
        text-decoration: none;
        cursor: pointer;

        transition: background .2s ease, border-color .2s ease;
    }

    .account-page .actions .btn-outline {
        color: #536b85;
        background: #ffffff;
        border: 1px solid #d9e2ed;
    }

    .account-page .actions .btn-outline:hover {
        background: #f4f7fa;
        border-color: #c8d5e4;
    }

    .account-page .actions .btn-primary {
        color: #ffffff;
        background: #225b9e;
        border: 1px solid #225b9e;
    }

    .account-page .actions .btn-primary:hover {
        background: #194c88;
        border-color: #194c88;
    }

    /* =========================================
       RESPONSIVE
       ========================================= */

    @media (max-width: 950px) {
        .account-page form.akun-form {
            grid-template-columns: minmax(220px, 35%) minmax(0, 65%);
        }

        .account-left {
            padding: 28px 24px;
        }

        .account-right {
            padding: 29px 28px;
        }
    }

    @media (max-width: 700px) {
        .account-page {
            padding-top: 0;
        }

        .account-page form.akun-form {
            grid-template-columns: minmax(0, 1fr);
        }

        .account-left {
            padding: 24px;
            border-right: 0;
            border-bottom: 1px solid var(--account-border);
        }

        .account-left-desc {
            margin-bottom: 20px;
        }

        .account-security {
            margin-top: 16px;
            padding-top: 5px;
        }

        .account-right {
            padding: 27px 24px;
        }

        .account-form-heading {
            margin-bottom: 22px;
        }
    }

    @media (max-width: 420px) {
        .account-page-title h2 {
            font-size: 21px;
        }

        .account-left,
        .account-right {
            padding: 22px 18px;
        }

        .account-page .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .account-page .actions .btn {
            width: 100%;
            padding: 10px;
        }
    }
</style>


<div class="account-page">

    {{-- ========================================
         PAGE TITLE
         ======================================== --}}

    <div class="account-page-title">

        <h2>Pengaturan Akun</h2>

        <p>
            Kelola informasi akun dan perbarui kata sandi
            untuk menjaga keamanan akses Anda ke sistem.
        </p>

    </div>


    {{-- ========================================
         MAIN FORM
         ======================================== --}}

    <form
        class="box form akun-form"
        method="POST"
        action="{{ route('akun.password.update') }}"
        autocomplete="off"
    >

        @csrf
        @method('PUT')


        {{-- ========================================
             LEFT — INFORMASI AKUN
             ======================================== --}}

        <section class="account-left">

            <div class="account-user-icon" aria-hidden="true">

                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">

                    <circle cx="12" cy="8" r="4"/>

                    <path d="M4 21v-2a8 8 0 0116 0v2"/>

                </svg>

            </div>


            <h3>Informasi Pengguna</h3>

            <p class="account-left-desc">
                Berikut adalah identitas akun
                yang sedang Anda gunakan.
            </p>


            {{-- ========================================
                 DATA PENGGUNA
                 ======================================== --}}

            <dl class="akun-info">

                {{-- NAMA PENGGUNA --}}
                <div>
                    <dt>Nama Pengguna</dt>

                    <dd>
                        {{ auth()->user()->full_name }}
                    </dd>
                </div>


                {{-- ALAMAT EMAIL --}}
                <div>
                    <dt>Alamat Email</dt>

                    <dd>
                        {{ auth()->user()->email }}
                    </dd>
                </div>


                {{-- HAK AKSES --}}
                <div>
                    <dt>Hak Akses</dt>

                    <dd>
                        <span class="account-role">
                            {{ auth()->user()->role->nama_role ?? '-' }}
                        </span>
                    </dd>
                </div>


                {{-- ASAL PEMDA --}}
                @if (
                    !auth()->user()->isSuperAdmin() &&
                    !auth()->user()->isModa() &&
                    !auth()->user()->isIntram()
                )

                    <div>
                        <dt>Asal Pemda</dt>

                        <dd>
                            {{ auth()->user()->kabKota?->nama_lengkap ?? 'Belum ditentukan' }}
                        </dd>
                    </div>

                @endif

            </dl>


            {{-- ========================================
                 INFORMASI KEAMANAN
                 ======================================== --}}

            <div class="account-security">

                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">

                    <path d="M12 3l8 4v5c0 5-3.5 8-8 10-4.5-2-8-5-8-10V7l8-4z"/>

                    <path d="M9 12l2 2 4-4"/>

                </svg>

                <p>
                    <strong>Keamanan akun</strong>
                    <br>
                    Jangan bagikan kata sandi kepada siapa pun.
                    Gunakan kata sandi yang unik dan sulit ditebak.
                </p>

            </div>

        </section>


        {{-- ========================================
             RIGHT — UBAH KATA SANDI
             ======================================== --}}

        <section class="account-right">

            <div class="account-form-heading">

                <h3>Ubah Kata Sandi</h3>

                <p>
                    Masukkan kata sandi saat ini,
                    kemudian buat kata sandi baru
                    untuk memperbarui keamanan akun Anda.
                </p>

            </div>


            <div class="account-fields">

                {{-- KATA SANDI SAAT INI --}}

                <div class="field">

                    <label for="password_lama">
                        Kata sandi saat ini
                    </label>

                    <input
                        type="password"
                        id="password_lama"
                        name="password_lama"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi saat ini"
                        class="@error('password_lama') is-invalid @enderror"
                    >

                    @error('password_lama')
                        <p class="error">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- KATA SANDI BARU --}}

                <div class="field">

                    <label for="password">
                        Kata sandi baru
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="@error('password') is-invalid @enderror"
                    >

                    <p class="account-field-hint">
                        Gunakan minimal 8 karakter.
                        Kombinasikan huruf, angka, dan simbol
                        untuk keamanan tambahan.
                    </p>

                    @error('password')
                        <p class="error">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- KONFIRMASI KATA SANDI --}}

                <div class="field">

                    <label for="password_confirmation">
                        Konfirmasi kata sandi baru
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi baru"
                    >

                    <p
                        class="error"
                        id="sandiBeda"
                        hidden
                    >
                        Konfirmasi kata sandi baru tidak sama.
                    </p>

                </div>

            </div>


            {{-- ========================================
                 TAMPILKAN KATA SANDI
                 ======================================== --}}

            <label class="check akun-tampil">

                <input
                    type="checkbox"
                    id="tampilSandi"
                >

                <span></span>

                Tampilkan kata sandi

            </label>


            {{-- ========================================
                 TOMBOL AKSI
                 ======================================== --}}

            <div class="actions">

                <a
                    class="btn btn-outline"
                    href="{{ route('dashboard') }}"
                >
                    Batal
                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan Kata Sandi
                </button>

            </div>

        </section>

    </form>

</div>

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
@endsection
