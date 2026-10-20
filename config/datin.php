<?php

return [
    /*
     | Jika true, akun yang mendaftar lewat halaman registrasi harus diverifikasi administrator
     | sebelum bisa masuk (php artisan akun:verifikasi email@instansi.go.id).
     | Jika false, akun langsung aktif dan pengguna otomatis masuk setelah mendaftar.
     | Atur lewat .env: DATIN_VERIFIKASI_AKUN=false
     */
    'verifikasi_akun' => env('DATIN_VERIFIKASI_AKUN', true),

    /*
     | Batas waktu peninjauan laporan oleh Moda (hari sejak diajukan). Laporan yang masih
     | "Menunggu Persetujuan" lebih lama dari ini dihitung "melewati target waktu peninjauan".
     | Atur lewat .env: DATIN_TARGET_TINJAU_HARI=3
     */
    'target_tinjau_hari' => (int) env('DATIN_TARGET_TINJAU_HARI', 3),
];
