<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Surat dari INTRAM adalah SURAT REKOMENDASI TEKNIS (bukan Surat Rancangan Teknis).
 * Untuk instalasi yang sudah menjalankan migration 2026_10_22: ganti nama tabel/kolom dan kode hak akses.
 * Untuk instalasi baru tidak ada yang berubah (migration sebelumnya sudah memakai nama baru).
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rancangan_teknis') && ! Schema::hasTable('rekomendasi_teknis')) {
            Schema::rename('rancangan_teknis', 'rekomendasi_teknis');

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                // CHANGE dipakai (bukan RENAME COLUMN) agar jalan juga di MariaDB/MySQL versi lama
                DB::statement('ALTER TABLE rekomendasi_teknis CHANGE id_rancangan_teknis id_rekomendasi_teknis BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            } else {
                Schema::table('rekomendasi_teknis', function (Blueprint $t) {
                    $t->renameColumn('id_rancangan_teknis', 'id_rekomendasi_teknis');
                });
            }
        }

        // Kode hak akses lama -> baru (penugasan ke role tetap, karena terhubung lewat id)
        DB::table('hak_akses')->where('kode', 'rancangan_teknis.unggah')->update([
            'kode' => 'rekomendasi_teknis.unggah',
            'nama' => 'Mengunggah Surat Rekomendasi Teknis',
        ]);
    }

    public function down(): void
    {
        DB::table('hak_akses')->where('kode', 'rekomendasi_teknis.unggah')->update([
            'kode' => 'rancangan_teknis.unggah',
            'nama' => 'Mengunggah Surat Rancangan Teknis',
        ]);

        if (Schema::hasTable('rekomendasi_teknis') && ! Schema::hasTable('rancangan_teknis')) {
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE rekomendasi_teknis CHANGE id_rekomendasi_teknis id_rancangan_teknis BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            } else {
                Schema::table('rekomendasi_teknis', function (Blueprint $t) {
                    $t->renameColumn('id_rekomendasi_teknis', 'id_rancangan_teknis');
                });
            }
            Schema::rename('rekomendasi_teknis', 'rancangan_teknis');
        }
    }
};
