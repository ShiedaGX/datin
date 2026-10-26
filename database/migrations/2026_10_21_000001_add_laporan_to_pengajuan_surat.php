<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menghubungkan surat CHR dengan laporan (permohonan) Pemda yang sudah disetujui,
 * sehingga INTRAM tahu surat CHR berasal dari permohonan Pemda yang mana.
 * Nullable agar surat yang dibuat sebelum migration ini tetap tersimpan.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $t) {
            $t->foreignId('id_rute_subsidi')->nullable()->after('id_moda')
                ->constrained('rute_subsidi', 'id_rute_subsidi')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $t) {
            $t->dropForeign(['id_rute_subsidi']);
            $t->dropColumn('id_rute_subsidi');
        });
    }
};
