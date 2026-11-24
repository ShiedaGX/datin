<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::table('fasilitas_transportasi', function (Blueprint $table) {
            $table->string('jenis_fasilitas_osm', 40)
                ->nullable()
                ->after('tipe_terminal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas_transportasi', function (Blueprint $table) {
            $table->dropColumn('jenis_fasilitas_osm');
        });
    }
};
