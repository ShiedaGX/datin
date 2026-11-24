<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('id_kab_kota')->nullable()->after('id_wilayah')
              ->constrained('kab_kota', 'id_kab_kota')->cascadeOnUpdate()->nullOnDelete();
        });

        // isi otomatis dari teks instansi lama, mis. "Pemda Kabupaten Musi Banyuasin"
        DB::statement("
            UPDATE users u
            JOIN kab_kota k ON u.instansi = CONCAT('Pemda ', k.jenis, ' ', k.nama_kab_kota)
            SET u.id_kab_kota = k.id_kab_kota
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('id_kab_kota');
        });
    }
};