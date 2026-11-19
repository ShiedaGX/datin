<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('master_sektor')) {
            Schema::create('master_sektor', function (Blueprint $t) {
                $t->id('id_sektor');
                $t->string('nama_sektor', 100)->unique();
                $t->unsignedSmallInteger('urutan')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('master_komoditas')) {
            Schema::create('master_komoditas', function (Blueprint $t) {
                $t->id('id_komoditas');
                $t->unsignedBigInteger('id_sektor');
                $t->string('nama_komoditas', 100);
                $t->timestamps();

                $t->foreign('id_sektor')->references('id_sektor')->on('master_sektor')->cascadeOnDelete();
                $t->unique(['id_sektor', 'nama_komoditas']);
            });
        }

        if (! Schema::hasTable('rute_subsidi_tujuan')) {
            Schema::create('rute_subsidi_tujuan', function (Blueprint $t) {
                $t->id('id_rute_subsidi_tujuan');
                $t->unsignedBigInteger('id_rute_subsidi');
                $t->unsignedBigInteger('id_kab_kota_tujuan');
                $t->timestamps();

                $t->foreign('id_rute_subsidi')->references('id_rute_subsidi')->on('rute_subsidi')->cascadeOnDelete();
                $t->foreign('id_kab_kota_tujuan')->references('id_kab_kota')->on('kab_kota')->restrictOnDelete();
                $t->unique(['id_rute_subsidi', 'id_kab_kota_tujuan']);
            });
        }

        // Backfill: pecah teks kebutuhan_konektivitas lama menjadi baris tujuan (aman diulang)
        $kab = DB::table('kab_kota')->pluck('id_kab_kota', 'nama_kab_kota')
            ->mapWithKeys(fn ($id, $nama) => [mb_strtolower(trim($nama)) => $id]);

        DB::table('rute_subsidi')->whereNotNull('kebutuhan_konektivitas')
            ->orderBy('id_rute_subsidi')->each(function ($r) use ($kab) {
                $ids = collect(preg_split('/[,;\r\n]+/', $r->kebutuhan_konektivitas))
                    ->map(fn ($n) => $kab[mb_strtolower(trim($n))] ?? null)
                    ->filter()->unique()
                    ->reject(fn ($id) => $id == $r->id_kab_kota_asal);

                foreach ($ids as $id) {
                    DB::table('rute_subsidi_tujuan')->insertOrIgnore([
                        'id_rute_subsidi'    => $r->id_rute_subsidi,
                        'id_kab_kota_tujuan' => $id,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_komoditas');
        Schema::dropIfExists('master_sektor');
        // rute_subsidi_tujuan sengaja tidak di-drop di sini: berisi data pengguna
    }
};