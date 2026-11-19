
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rute_subsidi_tujuan_komoditas', function (Blueprint $table) {
            $table->id('id_komoditas_tujuan');

            $table->unsignedBigInteger('id_rute_subsidi');
            $table->unsignedBigInteger('id_kab_kota_tujuan');

            $table->string('nama_komoditas', 150);
            $table->decimal('berat', 14, 2);

            $table->timestamps();

            $table->foreign('id_rute_subsidi')
                ->references('id_rute_subsidi')
                ->on('rute_subsidi')
                ->cascadeOnDelete();

            $table->foreign('id_kab_kota_tujuan')
                ->references('id_kab_kota')
                ->on('kab_kota')
                ->restrictOnDelete();

            $table->index(
                ['id_rute_subsidi', 'id_kab_kota_tujuan'],
                'idx_tujuan_komoditas'
            );
        });

        // Memindahkan komoditas lama dari tabel pivot ke tabel detail.
        if (Schema::hasTable('rute_subsidi_tujuan')
            && Schema::hasColumn('rute_subsidi_tujuan', 'komoditas')
            && Schema::hasColumn('rute_subsidi_tujuan', 'berat')) {

            DB::table('rute_subsidi_tujuan')
                ->whereNotNull('komoditas')
                ->orderBy('id_rute_subsidi')
                ->chunk(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('rute_subsidi_tujuan_komoditas')
                            ->insert([
                                'id_rute_subsidi' => $row->id_rute_subsidi,
                                'id_kab_kota_tujuan' => $row->id_kab_kota_tujuan,
                                'nama_komoditas' => $row->komoditas,
                                'berat' => $row->berat ?? 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rute_subsidi_tujuan_komoditas');
    }
};
