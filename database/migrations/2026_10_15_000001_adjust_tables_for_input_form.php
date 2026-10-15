<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penyesuaian minimal agar form Input Data Wilayah dapat berjalan di atas skema ERD:
 *  1. data_pemda.kebutuhan_konektivitas  : form punya isian ini, ERD tidak punya kolomnya.
 *  2. data_pemda.id_user tidak lagi unik : satu akun pemda dapat menginput lebih dari satu wilayah.
 *  3. rute_subsidi.id_kab_kota_tujuan nullable : form belum meminta kota tujuan.
 *
 * Versi ini kompatibel dengan Laravel 10 (tanpa doctrine/dbal) maupun Laravel 11+.
 * Untuk MySQL/MariaDB perubahan kolom memakai ALTER TABLE langsung.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_pemda', function (Blueprint $t) {
            $t->text('kebutuhan_konektivitas')->nullable()->after('keterangan_pemda');
            $t->dropForeign(['id_user']);
            $t->dropUnique(['id_user']);
        });
        Schema::table('data_pemda', function (Blueprint $t) {
            $t->foreign('id_user')->references('id_user')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('rute_subsidi', function (Blueprint $t) {
            $t->dropForeign(['id_kab_kota_tujuan']);
        });
        $this->ubahTujuan(true);
        Schema::table('rute_subsidi', function (Blueprint $t) {
            $t->foreign('id_kab_kota_tujuan')->references('id_kab_kota')->on('kab_kota')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Catatan: mengembalikan tujuan menjadi NOT NULL gagal bila sudah ada baris dengan tujuan NULL.
        Schema::table('rute_subsidi', function (Blueprint $t) {
            $t->dropForeign(['id_kab_kota_tujuan']);
        });
        $this->ubahTujuan(false);
        Schema::table('rute_subsidi', function (Blueprint $t) {
            $t->foreign('id_kab_kota_tujuan')->references('id_kab_kota')->on('kab_kota')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::table('data_pemda', function (Blueprint $t) {
            $t->dropForeign(['id_user']);
        });
        Schema::table('data_pemda', function (Blueprint $t) {
            $t->unique('id_user');
            $t->foreign('id_user')->references('id_user')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $t->dropColumn('kebutuhan_konektivitas');
        });
    }

    private function ubahTujuan(bool $nullable): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE rute_subsidi MODIFY id_kab_kota_tujuan BIGINT UNSIGNED ' . ($nullable ? 'NULL' : 'NOT NULL'));

            return;
        }

        // Database lain: butuh doctrine/dbal pada Laravel 10 (composer require doctrine/dbal)
        Schema::table('rute_subsidi', function (Blueprint $t) use ($nullable) {
            $t->unsignedBigInteger('id_kab_kota_tujuan')->nullable($nullable)->change();
        });
    }
};
