<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pendukung halaman Data User (Super Admin):
 *  - hak_akses       : daftar hak akses (katalog)
 *  - role_hak_akses  : hak akses yang dimiliki tiap role (diatur Super Admin)
 *  - users.id_wilayah: cakupan wilayah regional akun (Pemda & Intram)
 * Sekaligus mengisi data awal: role Super Admin, Pemda, Moda, Intram dan hak akses bawaannya.
 * Role 'Pemda' mendapat hak 'pemda.unggah' sehingga halaman Input Data tetap bisa dipakai seperti sebelumnya.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('hak_akses', function (Blueprint $t) {
            $t->id('id_hak_akses');
            $t->string('kode', 60)->unique();
            $t->string('nama', 150);
        });

        Schema::create('role_hak_akses', function (Blueprint $t) {
            $t->foreignId('id_role')->constrained('role', 'id_role')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreignId('id_hak_akses')->constrained('hak_akses', 'id_hak_akses')->cascadeOnUpdate()->cascadeOnDelete();
            $t->primary(['id_role', 'id_hak_akses']);
        });

        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('id_wilayah')->nullable()->after('id_moda')
                ->constrained('wilayah', 'id_wilayah')->cascadeOnUpdate()->nullOnDelete();
        });

        // ---- data awal ----
        foreach (['Super Admin', 'Pemda', 'Moda', 'Intram'] as $nama) {
            if (! DB::table('role')->where('nama_role', $nama)->exists()) {
                DB::table('role')->insert(['nama_role' => $nama]);
            }
        }

        $katalog = [
            'pemda.unggah'            => 'Mengunggah data dan laporan Pemda',
            'pemda.lihat_status'      => 'Melihat status laporan wilayahnya',
            'laporan.tinjau'          => 'Meninjau dan menyetujui laporan Pemda',
            'chr.tinjau'              => 'Meninjau dan menyetujui Surat CHR',
            'chr.unggah'              => 'Mengunggah Surat Catatan Hasil Review (CHR) sesuai moda',
            'chr.pantau'              => 'Memantau status Surat CHR',
            'rekomendasi_teknis.unggah' => 'Mengunggah Surat Rekomendasi Teknis',
            'cakupan.lihat_unggahan'  => 'Melihat unggahan dalam cakupan peninjauan',
        ];
        foreach ($katalog as $kode => $nama) {
            DB::table('hak_akses')->insert(['kode' => $kode, 'nama' => $nama]);
        }

        $bawaan = [
            'Pemda'  => ['pemda.unggah', 'pemda.lihat_status'],
            'Moda'   => ['laporan.tinjau', 'chr.unggah', 'chr.pantau'],
            'Intram' => ['laporan.tinjau', 'chr.tinjau', 'rekomendasi_teknis.unggah', 'cakupan.lihat_unggahan'],
        ];
        foreach ($bawaan as $role => $kodeList) {
            $idRole = DB::table('role')->where('nama_role', $role)->value('id_role');
            foreach ($kodeList as $kode) {
                DB::table('role_hak_akses')->insert([
                    'id_role'      => $idRole,
                    'id_hak_akses' => DB::table('hak_akses')->where('kode', $kode)->value('id_hak_akses'),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropForeign(['id_wilayah']);
            $t->dropColumn('id_wilayah');
        });
        Schema::dropIfExists('role_hak_akses');
        Schema::dropIfExists('hak_akses');
    }
};
