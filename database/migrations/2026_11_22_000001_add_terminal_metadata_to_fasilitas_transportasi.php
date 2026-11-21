
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fasilitas_transportasi', function (Blueprint $table) {
            $table->string('tipe_terminal', 25)->nullable();
            $table->string('status_verifikasi', 40)->nullable();
            $table->string('status_pencocokan', 90)->nullable();
            $table->string('wilayah_osm', 180)->nullable();
            $table->text('catatan_validasi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fasilitas_transportasi', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_terminal',
                'status_verifikasi',
                'status_pencocokan',
                'wilayah_osm',
                'catatan_validasi',
            ]);
        });
    }
};
