<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('rute_subsidi_potensi') && ! Schema::hasColumn('rute_subsidi_potensi', 'berat')) {
            Schema::table('rute_subsidi_potensi', function (Blueprint $t) {
                $t->decimal('berat', 14, 2)->nullable()->after('id_komoditas'); // dalam ton
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rute_subsidi_potensi', 'berat')) {
            Schema::table('rute_subsidi_potensi', fn (Blueprint $t) => $t->dropColumn('berat'));
        }
    }
};