<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pendukung halaman Approval Surat CHR (INTRAM):
 *  1. rute_subsidi menyimpan data yang diinput Pemda PER PENGAJUAN (klasifikasi, keterangan, potensi, kebutuhan konektivitas),
 *     agar pengajuan untuk moda lain pada daerah yang sama tidak menimpa data pengajuan sebelumnya.
 *  2. peninjauan_surat   : riwayat tindakan INTRAM pada Surat CHR (review dimulai, disetujui, dst.).
 *  3. permintaan_revisi  : permintaan INTRAM agar Pemda mengajukan lagi untuk moda tambahan.
 *  4. rekomendasi_teknis   : Surat Rekomendasi Teknis yang diunggah INTRAM setelah Surat CHR disetujui.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('rute_subsidi', function (Blueprint $t) {
            $t->string('klasifikasi_pemda', 100)->nullable()->after('status_pengajuan');
            $t->text('keterangan_pemda')->nullable()->after('klasifikasi_pemda');
            $t->text('potensi_pemda')->nullable()->after('keterangan_pemda');
            $t->text('kebutuhan_konektivitas')->nullable()->after('potensi_pemda');
        });

        // Isi pengajuan yang sudah ada dengan data daerahnya saat ini
        foreach (DB::table('rute_subsidi')->get(['id_rute_subsidi', 'id_pemda']) as $r) {
            $p = DB::table('data_pemda')->where('id_pemda', $r->id_pemda)->first();
            if ($p) {
                DB::table('rute_subsidi')->where('id_rute_subsidi', $r->id_rute_subsidi)->update([
                    'klasifikasi_pemda'      => $p->klasifikasi_pemda,
                    'keterangan_pemda'       => $p->keterangan_pemda,
                    'potensi_pemda'          => $p->potensi_pemda,
                    'kebutuhan_konektivitas' => $p->kebutuhan_konektivitas ?? null,
                ]);
            }
        }

        Schema::create('peninjauan_surat', function (Blueprint $t) {
            $t->id('id_peninjauan_surat');
            $t->foreignId('id_pengajuan_surat')->constrained('pengajuan_surat', 'id_pengajuan_surat')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('aksi', 40);
            $t->text('catatan')->nullable();
            $t->timestamp('created_at')->nullable();
        });

        Schema::create('permintaan_revisi', function (Blueprint $t) {
            $t->id('id_permintaan_revisi');
            $t->foreignId('id_pemda')->constrained('data_pemda', 'id_pemda')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreignId('id_rute_asal')->nullable()->constrained('rute_subsidi', 'id_rute_subsidi')->cascadeOnUpdate()->nullOnDelete();
            $t->foreignId('id_pengajuan_surat')->nullable()->constrained('pengajuan_surat', 'id_pengajuan_surat')->cascadeOnUpdate()->nullOnDelete();
            $t->foreignId('id_moda')->constrained('moda', 'id_moda')->cascadeOnUpdate()->restrictOnDelete();   // moda tambahan yang diminta
            $t->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnUpdate()->restrictOnDelete();   // INTRAM yang meminta
            $t->text('catatan');
            $t->string('status', 20)->default('Menunggu')->index();                                           // Menunggu | Dipenuhi
            $t->foreignId('id_rute_baru')->nullable()->constrained('rute_subsidi', 'id_rute_subsidi')->cascadeOnUpdate()->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('rekomendasi_teknis', function (Blueprint $t) {
            $t->id('id_rekomendasi_teknis');
            $t->foreignId('id_pengajuan_surat')->unique()->constrained('pengajuan_surat', 'id_pengajuan_surat')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnUpdate()->restrictOnDelete();
            $t->string('nama_file', 255);
            $t->string('path_file', 500);
            $t->unsignedInteger('ukuran')->default(0);
            $t->text('catatan')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_teknis');
        Schema::dropIfExists('permintaan_revisi');
        Schema::dropIfExists('peninjauan_surat');
        Schema::table('rute_subsidi', function (Blueprint $t) {
            $t->dropColumn(['klasifikasi_pemda', 'keterangan_pemda', 'potensi_pemda', 'kebutuhan_konektivitas']);
        });
    }
};
