<?php

use App\Http\Controllers\Admin\HakAksesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SektorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MasterSatuanDataController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataWilayahController;
use App\Http\Controllers\InputDataController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\PetaGeospasialController;
use App\Http\Controllers\FasilitasTransportasiController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\SuratController;

use App\Http\Middleware\AkunAktif;
use App\Http\Middleware\HanyaRole;
use App\Http\Middleware\HanyaSuperAdmin;
use App\Http\Middleware\PunyaHakAkses;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

});

Route::middleware(['auth', AkunAktif::class])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.attempt');

});


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', AkunAktif::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/', fn () => redirect()->route('dashboard'));

    // Input data pengajuan: butuh hak akses "pemda.unggah" (bawaan: role Pemda; Super Admin selalu boleh)
    Route::middleware(PunyaHakAkses::class . ':pemda.unggah')->group(function () {
        Route::get('/input-data', [InputDataController::class, 'create'])->name('input-data.create');
        Route::post('/input-data', [InputDataController::class, 'store'])->name('input-data.store');
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    // Permohonan: khusus role Moda (dan hak akses "laporan.tinjau"). Laporan dibatasi per moda di controller.
    Route::middleware([HanyaRole::class . ':Moda', PunyaHakAkses::class . ':laporan.tinjau'])
        ->prefix('permohonan')->name('permohonan.')->group(function () {
            Route::get('/', [PermohonanController::class, 'index'])->name('index');
            Route::get('export', [PermohonanController::class, 'export'])->name('export');
            Route::get('{rute}/export', [PermohonanController::class, 'exportDetail'])->whereNumber('rute')->name('export-detail');
            Route::get('{rute}', [PermohonanController::class, 'show'])->whereNumber('rute')->name('show');
            Route::post('{rute}/keputusan', [PermohonanController::class, 'keputusan'])->whereNumber('rute')->name('keputusan');
            Route::get('{rute}/dokumen/{dokumen}', [PermohonanController::class, 'dokumen'])->whereNumber(['rute', 'dokumen'])->name('dokumen');
        });

    // Surat CHR: Moda mengunggah surat untuk laporan Pemda yang sudah disetujui, lalu mengirimkannya ke INTRAM
    Route::middleware(HanyaRole::class . ':Moda')->prefix('surat')->name('surat.')->group(function () {
        Route::get('/', [SuratController::class, 'index'])->middleware(PunyaHakAkses::class . ':chr.pantau')->name('index');
        Route::get('laporan/{rute}', [SuratController::class, 'laporan'])->whereNumber('rute')->middleware(PunyaHakAkses::class . ':chr.pantau')->name('laporan');
        Route::post('laporan/{rute}', [SuratController::class, 'simpan'])->whereNumber('rute')->middleware(PunyaHakAkses::class . ':chr.unggah')->name('simpan');
        Route::delete('dokumen/{dokumen}', [SuratController::class, 'hapus'])->whereNumber('dokumen')->middleware(PunyaHakAkses::class . ':chr.unggah')->name('hapus');
    });
    // Approval Surat CHR dan Hasil Unggahan: khusus INTRAM
    Route::middleware(HanyaRole::class . ':Intram')->group(function () {
        Route::prefix('approval')->name('approval.')->middleware(PunyaHakAkses::class . ':chr.tinjau')->group(function () {
            Route::get('/', [ApprovalController::class, 'index'])->name('index');
            Route::post('{pengajuan}/mulai', [ApprovalController::class, 'mulai'])->whereNumber('pengajuan')->name('mulai');
            Route::post('{pengajuan}/keputusan', [ApprovalController::class, 'keputusan'])->whereNumber('pengajuan')->name('keputusan');
            Route::post('{pengajuan}/revisi-pemda', [ApprovalController::class, 'revisiPemda'])->whereNumber('pengajuan')->name('revisi');
        });

        Route::prefix('hasil-unggahan')->name('hasil.')->group(function () {
            Route::get('/', [RekomendasiController::class, 'index'])->middleware(PunyaHakAkses::class . ':cakupan.lihat_unggahan')->name('index');
            Route::get('{rute}', [RekomendasiController::class, 'show'])->whereNumber('rute')->middleware(PunyaHakAkses::class . ':cakupan.lihat_unggahan')->name('show');
            Route::post('{rute}', [RekomendasiController::class, 'simpan'])->whereNumber('rute')->middleware(PunyaHakAkses::class . ':rekomendasi_teknis.unggah')->name('simpan');
        });
    });
    // Surat Rekomendasi Teknis: INTRAM, Super Admin, Moda yang sama, dan Pemda pemilik laporan (diperiksa di controller)
    Route::get('/rekomendasi-teknis/{rekomendasi}', [RekomendasiController::class, 'dokumen'])->whereNumber('rekomendasi')->name('rekomendasi.dokumen');

    // Pratinjau/unduh berkas: pemilik, atau INTRAM/Super Admin untuk surat yang sudah dikirim (diperiksa di controller)
    Route::get('/surat/dokumen/{dokumen}', [SuratController::class, 'dokumen'])->whereNumber('dokumen')->name('surat.dokumen');

    // Khusus Super Admin
    Route::prefix('admin')->name('admin.')->middleware(HanyaSuperAdmin::class)->group(function () {
        Route::get('user/export', [UserController::class, 'export'])->name('user.export');
        Route::get('user', [UserController::class, 'index'])->name('user.index');
        Route::get('user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('user', [UserController::class, 'store'])->name('user.store');
        Route::get('user/{user}', [UserController::class, 'show'])->whereNumber('user')->name('user.show');
        Route::get('user/{user}/edit', [UserController::class, 'edit'])->whereNumber('user')->name('user.edit');
        Route::put('user/{user}', [UserController::class, 'update'])->whereNumber('user')->name('user.update');
        Route::delete('user/{user}', [UserController::class, 'destroy'])->whereNumber('user')->name('user.destroy');

        Route::get('role/{role}/hak-akses', [HakAksesController::class, 'edit'])->whereNumber('role')->name('role.hak-akses.edit');
        Route::put('role/{role}/hak-akses', [HakAksesController::class, 'update'])->whereNumber('role')->name('role.hak-akses.update');
    });

    // Akun saya: semua role dapat mengganti kata sandinya sendiri
    Route::get('/akun/password', [AkunController::class, 'password'])->name('akun.password');
    Route::put('/akun/password', [AkunController::class, 'ubahPassword'])->middleware('throttle:6,1')->name('akun.password.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    /*
    |--------------------------------------------------------------------------
    | PETA KONEKTIVITAS REGIONAL
    |--------------------------------------------------------------------------
    */

    Route::get('/peta-konektivitas', [PetaController::class, 'index'])
        ->name('peta.index');

    Route::get('/peta-geospasial', [PetaGeospasialController::class, 'index'])
        ->name('peta.geospasial');

    Route::get('/peta-geospasial/data/{layer}', [PetaGeospasialController::class, 'data'])
        ->name('peta.geospasial.data');


    Route::get(
        '/peta-geospasial/terminal-osm/geojson',
        [\App\Http\Controllers\TerminalOsmController::class, 'geojson']
    )->name('peta.terminal-osm.geojson');



    // Data fasilitas referensi dapat dikelola hanya oleh Super Admin.
    Route::get('/fasilitas-transportasi', [FasilitasTransportasiController::class, 'index'])->name('fasilitas.index');
    Route::get('/fasilitas-transportasi/tambah', [FasilitasTransportasiController::class, 'create'])->name('fasilitas.create');
    Route::post('/fasilitas-transportasi', [FasilitasTransportasiController::class, 'store'])->name('fasilitas.store');
    Route::get('/fasilitas-transportasi/{fasilitas}/edit', [FasilitasTransportasiController::class, 'edit'])->name('fasilitas.edit');
    Route::put('/fasilitas-transportasi/{fasilitas}', [FasilitasTransportasiController::class, 'update'])->name('fasilitas.update');
    Route::delete('/fasilitas-transportasi/{fasilitas}', [FasilitasTransportasiController::class, 'destroy'])->name('fasilitas.destroy');


    /*
    |--------------------------------------------------------------------------
    | INPUT DATA PEMDA
    |--------------------------------------------------------------------------
    |
    | Membutuhkan hak akses pemda.unggah.
    | Super Admin tetap mengikuti mekanisme hak akses aplikasi.
    |
    */

    Route::middleware(PunyaHakAkses::class . ':pemda.unggah')
        ->group(function () {

            Route::get('/input-data', [InputDataController::class, 'create'])
                ->name('input-data.create');

            Route::post('/input-data', [InputDataController::class, 'store'])
                ->name('input-data.store');

        });


    /*
    |--------------------------------------------------------------------------
    | DATA WILAYAH
    |--------------------------------------------------------------------------
    */

    Route::get('/data-wilayah', [DataWilayahController::class, 'index'])
        ->name('data-wilayah.index');

    Route::get('/data-wilayah/export', [DataWilayahController::class, 'export'])
        ->name('data-wilayah.export');

    Route::get('/data-wilayah/{pemda}', [DataWilayahController::class, 'show'])
        ->whereNumber('pemda')
        ->name('data-wilayah.show');

    Route::delete('/data-wilayah/{pemda}', [DataWilayahController::class, 'destroy'])
        ->whereNumber('pemda')
        ->name('data-wilayah.destroy');

    Route::get('/dokumen/{dokumen}', [DataWilayahController::class, 'unduh'])
        ->whereNumber('dokumen')
        ->name('dokumen.unduh');


    /*
    |--------------------------------------------------------------------------
    | PERMOHONAN MODA
    |--------------------------------------------------------------------------
    |
    | Khusus role Moda dengan hak akses laporan.tinjau.
    | Pembatasan laporan berdasarkan moda dilakukan di controller.
    |
    */

    Route::middleware([
        HanyaRole::class . ':Moda',
        PunyaHakAkses::class . ':laporan.tinjau'
    ])
        ->prefix('permohonan')
        ->name('permohonan.')
        ->group(function () {

            Route::get('/', [PermohonanController::class, 'index'])
                ->name('index');

            Route::get('export', [PermohonanController::class, 'export'])
                ->name('export');

            Route::get('{rute}/export', [PermohonanController::class, 'exportDetail'])
                ->whereNumber('rute')
                ->name('export-detail');

            Route::get('{rute}', [PermohonanController::class, 'show'])
                ->whereNumber('rute')
                ->name('show');

            Route::post('{rute}/keputusan', [PermohonanController::class, 'keputusan'])
                ->whereNumber('rute')
                ->name('keputusan');

            Route::get('{rute}/dokumen/{dokumen}', [PermohonanController::class, 'dokumen'])
                ->whereNumber(['rute', 'dokumen'])
                ->name('dokumen');

        });


    /*
    |--------------------------------------------------------------------------
    | SURAT CHR MODA
    |--------------------------------------------------------------------------
    |
    | Moda mengunggah surat untuk laporan Pemda yang disetujui,
    | kemudian mengirimkannya kepada INTRAM.
    |
    */

    Route::middleware(HanyaRole::class . ':Moda')
        ->prefix('surat')
        ->name('surat.')
        ->group(function () {

            Route::get('/', [SuratController::class, 'index'])
                ->middleware(PunyaHakAkses::class . ':chr.pantau')
                ->name('index');

            Route::get('laporan/{rute}', [SuratController::class, 'laporan'])
                ->whereNumber('rute')
                ->middleware(PunyaHakAkses::class . ':chr.pantau')
                ->name('laporan');

            Route::post('laporan/{rute}', [SuratController::class, 'simpan'])
                ->whereNumber('rute')
                ->middleware(PunyaHakAkses::class . ':chr.unggah')
                ->name('simpan');

            Route::delete('dokumen/{dokumen}', [SuratController::class, 'hapus'])
                ->whereNumber('dokumen')
                ->middleware(PunyaHakAkses::class . ':chr.unggah')
                ->name('hapus');

        });


    /*
    |--------------------------------------------------------------------------
    | APPROVAL DAN HASIL UNGGAHAN INTRAM
    |--------------------------------------------------------------------------
    */

    Route::middleware(HanyaRole::class . ':Intram')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | APPROVAL SURAT CHR
            |--------------------------------------------------------------------------
            */

            Route::prefix('approval')
                ->name('approval.')
                ->middleware(PunyaHakAkses::class . ':chr.tinjau')
                ->group(function () {

                    Route::get('/', [ApprovalController::class, 'index'])
                        ->name('index');

                    Route::post('{pengajuan}/mulai', [ApprovalController::class, 'mulai'])
                        ->whereNumber('pengajuan')
                        ->name('mulai');

                    Route::post('{pengajuan}/keputusan', [ApprovalController::class, 'keputusan'])
                        ->whereNumber('pengajuan')
                        ->name('keputusan');

                    Route::post('{pengajuan}/revisi-pemda', [ApprovalController::class, 'revisiPemda'])
                        ->whereNumber('pengajuan')
                        ->name('revisi');

                });


            /*
            |--------------------------------------------------------------------------
            | HASIL UNGGAHAN
            |--------------------------------------------------------------------------
            */

            Route::prefix('hasil-unggahan')
                ->name('hasil.')
                ->group(function () {

                    Route::get('/', [RekomendasiController::class, 'index'])
                        ->middleware(PunyaHakAkses::class . ':cakupan.lihat_unggahan')
                        ->name('index');

                    Route::get('{rute}', [RekomendasiController::class, 'show'])
                        ->whereNumber('rute')
                        ->middleware(PunyaHakAkses::class . ':cakupan.lihat_unggahan')
                        ->name('show');

                    Route::post('{rute}', [RekomendasiController::class, 'simpan'])
                        ->whereNumber('rute')
                        ->middleware(PunyaHakAkses::class . ':rekomendasi_teknis.unggah')
                        ->name('simpan');

                });

        });


    /*
    |--------------------------------------------------------------------------
    | DOKUMEN REKOMENDASI TEKNIS
    |--------------------------------------------------------------------------
    |
    | Akses untuk INTRAM, Super Admin, Moda terkait, dan Pemda pemilik.
    | Pemeriksaan kepemilikan dilakukan di controller.
    |
    */

    Route::get('/rekomendasi-teknis/{rekomendasi}', [RekomendasiController::class, 'dokumen'])
        ->whereNumber('rekomendasi')
        ->name('rekomendasi.dokumen');


    /*
    |--------------------------------------------------------------------------
    | DOKUMEN SURAT CHR
    |--------------------------------------------------------------------------
    |
    | Pratinjau atau unduh dokumen sesuai otorisasi controller.
    |
    */

    Route::get('/surat/dokumen/{dokumen}', [SuratController::class, 'dokumen'])
        ->whereNumber('dokumen')
        ->name('surat.dokumen');


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(HanyaSuperAdmin::class)
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | DATA USER
            |--------------------------------------------------------------------------
            */

            Route::get('kabkota/saran', [UserController::class, 'saranInstansi'])
                ->name('kabkota.saran');

            Route::get('user/export', [UserController::class, 'export'])
                ->name('user.export');

            Route::get('user', [UserController::class, 'index'])
                ->name('user.index');

            Route::get('user/create', [UserController::class, 'create'])
                ->name('user.create');

            Route::post('user', [UserController::class, 'store'])
                ->name('user.store');

            Route::get('user/{user}', [UserController::class, 'show'])
                ->whereNumber('user')
                ->name('user.show');

            Route::get('user/{user}/edit', [UserController::class, 'edit'])
                ->whereNumber('user')
                ->name('user.edit');

            Route::put('user/{user}', [UserController::class, 'update'])
                ->whereNumber('user')
                ->name('user.update');

            Route::delete('user/{user}', [UserController::class, 'destroy'])
                ->whereNumber('user')
                ->name('user.destroy');


            /*
            |--------------------------------------------------------------------------
            | MASTER SEKTOR DAN KOMODITAS
            |--------------------------------------------------------------------------
            */

            Route::get('sektor', [SektorController::class, 'index'])
                ->name('sektor.index');

            Route::post('sektor', [SektorController::class, 'simpanSektor'])
                ->name('sektor.store');

            Route::put('sektor/{sektor}', [SektorController::class, 'ubahSektor'])
                ->whereNumber('sektor')
                ->name('sektor.update');

            Route::delete('sektor/{sektor}', [SektorController::class, 'hapusSektor'])
                ->whereNumber('sektor')
                ->name('sektor.destroy');

            Route::post('sektor/{sektor}/komoditas', [SektorController::class, 'simpanKomoditas'])
                ->whereNumber('sektor')
                ->name('sektor.komoditas.store');

            Route::put('komoditas/{komoditas}', [SektorController::class, 'ubahKomoditas'])
                ->whereNumber('komoditas')
                ->name('komoditas.update');

            Route::delete('komoditas/{komoditas}', [SektorController::class, 'hapusKomoditas'])
                ->whereNumber('komoditas')
                ->name('komoditas.destroy');


            /*
            |--------------------------------------------------------------------------
            | HAK AKSES ROLE
            |--------------------------------------------------------------------------
            */

            Route::get('role/{role}/hak-akses', [HakAksesController::class, 'edit'])
                ->whereNumber('role')
                ->name('role.hak-akses.edit');

            Route::put('role/{role}/hak-akses', [HakAksesController::class, 'update'])
                ->whereNumber('role')
                ->name('role.hak-akses.update');

        });


    /*
    |--------------------------------------------------------------------------
    | MASTER SATUAN DATA
    |--------------------------------------------------------------------------
    |
    | Mempertahankan middleware dan struktur route dari versi lama.
    |
    */

    Route::middleware(['auth'])
        ->prefix('admin/master-satuan')
        ->name('admin.master-satuan.')
        ->group(function () {

            Route::get('/', [
                MasterSatuanDataController::class,
                'index',
            ])->name('index');

            Route::post('/', [
                MasterSatuanDataController::class,
                'store',
            ])->name('store');

            Route::put('/{satuan}', [
                MasterSatuanDataController::class,
                'update',
            ])->name('update');

            Route::patch('/{satuan}/toggle', [
                MasterSatuanDataController::class,
                'toggle',
            ])->name('toggle');

            Route::delete('/{satuan}', [
                MasterSatuanDataController::class,
                'destroy',
            ])->name('destroy');

        });


    /*
    |--------------------------------------------------------------------------
    | AKUN SAYA
    |--------------------------------------------------------------------------
    |
    | Semua role dapat mengganti kata sandinya sendiri.
    |
    */

    Route::get('/akun/password', [AkunController::class, 'password'])
        ->name('akun.password');

    Route::put('/akun/password', [AkunController::class, 'ubahPassword'])
        ->middleware('throttle:6,1')
        ->name('akun.password.update');


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

});
