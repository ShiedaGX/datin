
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_satuan_data', function (Blueprint $table) {
            $table->id('id_satuan_data');

            $table->string('nama_satuan', 100)->unique();

            $table->boolean('status_aktif')->default(true);

            $table->timestamps();
        });

        $sekarang = now();

        DB::table('master_satuan_data')->insert([
            [
                'nama_satuan' => 'Ton',
                'status_aktif' => true,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'nama_satuan' => 'Ekor',
                'status_aktif' => true,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'nama_satuan' => 'Perjalanan Wisatawan',
                'status_aktif' => true,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'nama_satuan' => 'Batang',
                'status_aktif' => true,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('master_satuan_data');
    }
};
