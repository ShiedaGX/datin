
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rute_subsidi_tujuan_komoditas', function (Blueprint $table) {
            $table->decimal('jumlah', 14, 2)
                ->nullable()
                ->after('nama_komoditas');

            $table->string('satuan_data', 50)
                ->nullable()
                ->after('jumlah');
        });

        // Salin nilai berat lama ke jumlah.
        // Satuan dibiarkan NULL karena belum diketahui.
        DB::table('rute_subsidi_tujuan_komoditas')
            ->whereNotNull('berat')
            ->update([
                'jumlah' => DB::raw('berat'),
            ]);
    }

    public function down(): void
    {
        Schema::table('rute_subsidi_tujuan_komoditas', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah',
                'satuan_data',
            ]);
        });
    }
};
