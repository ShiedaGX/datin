```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">

    <title>Masuk ke SIMPUL</title>

    vite(['resources/css/app.css', 'resources/css/login.css'])
</head>

<body>

<div class="masuk-ke-SIMPUL">

    {{-- PANEL INSTITUSI --}}
    <div class="panel-institusi">

        {{-- IDENTITAS APLIKASI --}}
        <div class="identitas-aplikasi">

            <div class="lambang">
                <img
                    class="rute"
                    src="{{ asset('img/rute.svg') }}"
                    alt="Logo"
                >
            </div>

            <div class="nama-aplikasi">
                <div class="nama">
                    Subsidi transportasi
                </div>

                <div class="deskripsi">
                    TRANSPORTASI BERSUBSIDI
                </div>
            </div>

        </div>


        {{-- PESAN PLATFORM --}}
        <div class="pesan-platform">

            <div class="salinan">

                <div class="div">

                    <div class="penanda"></div>

                    <div class="label">
                        PLATFORM DATA NASIONAL
                    </div>

                </div>

                <p class="judul">
                    Data terhubung untuk layanan transportasi yang lebih tepat.
                </p>

                <p class="text-wrapper">
                    Kelola pemetaan, validasi, dan analisis transportasi
                    bersubsidi dalam satu ruang kerja yang aman.
                </p>

            </div>


            {{-- STATUS JARINGAN --}}
            <div class="peta-konektivitas">

                <div class="keterangan">
                    JARINGAN LAYANAN TERINTEGRASI
                </div>

                <div class="status-jaringan">

                    <div class="status"></div>

                    <div class="label-2">
                        Sistem terhubung
                    </div>

                </div>

            </div>


            {{-- CAKUPAN PLATFORM --}}
            <div class="cakupan-platform">

                <div class="indikator">

                    <div class="nilai">
                        29
                    </div>

                    <div class="label-3">
                        provinsi tercakup
                    </div>

                </div>


                <div class="indikator">

                    <div class="nilai">
                        189
                    </div>

                    <div class="label-3">
                        wilayah terpetakan
                    </div>

                </div>


                <div class="indikator">

                    <div class="nilai">
                        96%
                    </div>

                    <div class="label-3">
                        kelengkapan data
                    </div>

                </div>

            </div>

        </div>


        {{-- INFORMASI KEAMANAN --}}
        <div class="keterangan-keamanan">

            <div class="shield-check">

                <img
                    class="vector"
                    src="{{ asset('img/vector-2.svg') }}"
                    alt="Keamanan"
                >

            </div>

            <p class="p">
                Akses terbatas untuk instansi dan mitra terverifikasi
            </p>

        </div>

    </div>


    {{-- AREA AUTENTIKASI --}}
    <div class="area-autentikasi">

        {{-- HEADER --}}
        <div class="div-2">

            <div class="div">

                <div class="status-2"></div>

                <div class="text-wrapper-2">
                    Portal Data Transportasi Bersubsidi
                </div>

            </div>

            <div class="text-wrapper-2">
                Bahasa Indonesia
            </div>

        </div>


        {{-- KONTEN LOGIN --}}
        <div class="konten-masuk">

            {{-- PENGANTAR --}}
            <div class="pengantar">

                <div class="penanda-2">
                    AKSES AKUN
                </div>

                <div class="judul-2">
                    Selamat datang kembali
                </div>

                <p class="deskripsi-2">
                    Masuk menggunakan akun instansi Anda
                    untuk melanjutkan pengelolaan data.
                </p>

            </div>


            {{-- FORM LOGIN --}}
            <form
                class="formulir-masuk"
                method="POST"
                action="{{ route('login') }}"
            >

                @csrf


                {{-- EMAIL --}}
                <div class="bidang-formulir">

                    <div class="kepala-bidang">

                        <div class="label-4">
                            Alamat email
                        </div>

                    </div>

                    <div class="masukan">

                        <div class="vector-wrapper">

                            <img
                                class="img"
                                src="{{ asset('img/vector.svg') }}"
                                alt="Email"
                            >

                        </div>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@instansi.go.id"
                            autocomplete="email"
                            required
                        >

                    </div>

                    @error('email')
                        <span class="error-message">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- PASSWORD --}}
                <div class="bidang-formulir">

                    <div class="kepala-bidang">

                        <div class="label-4">
                            Kata sandi
                        </div>

                    </div>

                    <div class="masukan">

                        <div class="vector-wrapper">

                            <img
                                class="vector-2"
                                src="{{ asset('img/vector-3.svg') }}"
                                alt="Password"
                            >

                        </div>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="vector-wrapper"
                            onclick="togglePassword()"
                            aria-label="Tampilkan password"
                        >

                            <img
                                class="vector-3"
                                src="{{ asset('img/image.svg') }}"
                                alt="Tampilkan password"
                            >

                        </button>

                    </div>

                    @error('password')
                        <span class="error-message">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- INGAT SAYA & LUPA PASSWORD --}}
                <div class="div-2">

                    <label class="pilihan">

                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                        >

                        <span class="kotak"></span>

                        <span class="label-5">
                            Ingat saya
                        </span>

                    </label>


                    <a
                        href="{{ route('password.request') }}"
                        class="lupa-kata-sandi"
                    >
                        Lupa kata sandi?
                    </a>

                </div>


                {{-- TOMBOL LOGIN --}}
                <button
                    type="submit"
                    class="aksi-utama"
                >

                    <div class="label-6">
                        Masuk ke SIMPUL
                    </div>

                    <div class="vector-wrapper">

                        <img
                            class="vector-4"
                            src="{{ asset('img/vector-4.svg') }}"
                            alt=""
                        >

                    </div>

                </button>

            </form>


            {{-- PENDAFTARAN --}}
            <div class="arah-pendaftaran">

                <div class="pertanyaan">
                    Belum memiliki akun?
                </div>

                <a
                    href="{{ route('register') }}"
                    class="tautan"
                >
                    Daftar akun instansi
                </a>

            </div>


            {{-- INFORMASI KEAMANAN --}}
            <div class="informasi-keamanan">

                <div class="lock-keyhole">

                    <img
                        class="vector-5"
                        src="{{ asset('img/vector-5.svg') }}"
                        alt="Keamanan"
                    >

                </div>

                <p class="keterangan-2">
                    Koneksi terenkripsi. Informasi akun Anda dilindungi
                    sesuai kebijakan keamanan data pemerintah.
                </p>

            </div>

        </div>


        {{-- FOOTER --}}
        <div class="div-2">

            <div class="bantuan">

                <div class="pertanyaan-2">
                    Butuh bantuan akses?
                </div>

                <a
                    href="#"
                    class="tautan-2"
                >
                    Hubungi administrator
                </a>

            </div>

            <p class="hak-cipta">
                © {{ date('Y') }} SIMPUL • Versi 2.4.0
            </p>

        </div>

    </div>

</div>


{{-- PASSWORD TOGGLE --}}
<script>
    function togglePassword() {
        const password = document.getElementById('password');

        if (password.type === 'password') {
            password.type = 'text';
        } else {
            password.type = 'password';
        }
    }
</script>

</body>
</html>
```
