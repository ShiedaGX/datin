<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rute_subsidi_tujuan', function (Blueprint $t) {
            $t->string('komoditas', 150)->nullable()->after('id_kab_kota_tujuan');
            $t->decimal('berat', 12, 2)->nullable()->after('komoditas'); // ton
        });
    }

    public function down(): void
    {
        Schema::table('rute_subsidi_tujuan', function (Blueprint $t) {
            $t->dropColumn(['komoditas', 'berat']);
        });
    }
};