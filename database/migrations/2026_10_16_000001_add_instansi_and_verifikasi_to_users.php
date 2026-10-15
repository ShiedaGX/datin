<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pendukung halaman registrasi:
 *  - users.instansi        : isian "Instansi / organisasi" (ERD tidak punya kolom ini).
 *  - users.diverifikasi_at : waktu akun diverifikasi administrator. NULL = masih menunggu.
 * Akun yang sudah ada otomatis dianggap terverifikasi agar tetap bisa masuk.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('instansi', 150)->nullable()->after('full_name');
            $t->timestamp('diverifikasi_at')->nullable()->after('instansi');
        });

        DB::table('users')->update(['diverifikasi_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['instansi', 'diverifikasi_at']);
        });
    }
};
