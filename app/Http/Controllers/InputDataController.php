<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreWilayahRequest;
use App\Models\DataPemda;
use App\Models\DokumenPengajuan;
use App\Models\KabKota;
use App\Models\Moda;
use App\Models\PermintaanRevisi;
use App\Models\Provinsi;
use App\Models\RuteSubsidi;
use App\Models\Wilayah;
use App\Support\FormOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InputDataController extends Controller
{
    public function create(Request $request)
    {
        $user = auth()->user();

        // id_wilayah pada provinsi ikut diambil supaya Wilayah Regional bisa terisi otomatis
        $semua = KabKota::with('provinsi:id_provinsi,id_wilayah,nama_provinsi', 'dataPemda.ruteSubsidi', 'dataPemda.permintaanRevisi')
            ->orderBy('nama_kab_kota')->get();

        // Kab/kota milik akun (Pemda): wilayah, provinsi, dan kab/kota terisi otomatis & terkunci
        $kabSaya = $user->id_kab_kota ? $semua->firstWhere('id_kab_kota', (int) $user->id_kab_kota) : null;
        $terkunci = $kabSaya !== null;
        $akunBelumTerhubung = ! $terkunci && ! $user->isSuperAdmin();

        [$isian, $sumber] = $this->isianDariPengajuanSebelumnya($request, $semua, $user);
        $namaModa = Moda::pluck('nama_moda', 'id_moda');

        return view('input-data.create', [
            'isian'       => $isian,           // data pengajuan sebelumnya (revisi INTRAM), kosong untuk pengajuan baru
            'sumber'      => $sumber,
            'terkunci'    => $terkunci,
            'kabSaya'     => $kabSaya,
            'alasanSaya'  => $kabSaya?->alasanTidakTersedia($user),
            'akunBelumTerhubung' => $akunBelumTerhubung,
            // saran sektor pada kolom Potensi Unggulan Daerah (nama + kata kunci untuk pencarian)
            'sektor'      => collect(\App\Support\Sektor::peta())->map(fn ($kunci, $nama) => ['nama' => $nama, 'kunci' => $kunci])->values(),
            // daftar komoditas + sektornya, untuk kolom Sektor di samping Komoditas
            'komoditasSektor' => \App\Support\Sektor::komoditas(),
            'provinsi'    => Provinsi::orderBy('nama_provinsi')->get(),
            'wilayah'     => Wilayah::orderBy('nama_wilayah')->get(),
            'moda'        => Moda::orderBy('id_moda')->get(),
            'klasifikasi' => FormOptions::KLASIFIKASI,
            'keterangan'  => FormOptions::KETERANGAN,
            'dokumen'     => FormOptions::DOKUMEN,
            // pilihan dropdown Kabupaten/Kota
            'kabKota'     => $semua->map(fn ($k) => [
                'id'          => $k->id_kab_kota,
                'id_provinsi' => $k->id_provinsi,
                'nama'        => $k->nama_kab_kota,
                'alasan'      => $k->alasanTidakTersedia($user),
                'revisi'      => $k->modaRevisiTerbuka($user),     // id moda yang diminta INTRAM
                'sudah'       => $k->modaDiajukan($user),          // id moda yang sudah diajukan akun ini
                'sudahNama'   => collect($k->modaDiajukan($user))->map(fn ($id) => $namaModa[$id] ?? '')->filter()->values()->all(),
                'revisiNama'  => Moda::whereIn('id_moda', $k->modaRevisiTerbuka($user))->orderBy('id_moda')->pluck('nama_moda')->all(),
            ])->values(),
            // saran otomatis untuk pemilih Rute Tujuan (id, nama, provinsi)
            'saran'       => $semua->map(fn ($k) => [
                'id'       => $k->id_kab_kota,
                'nama'     => $k->nama_kab_kota,
                'provinsi' => $k->provinsi->nama_provinsi,
            ])->values(),
        ]);
    }

    /**
     * Pengajuan ulang atas permintaan revisi INTRAM (?kab=ID): form diisi dengan data pengajuan terakhir
     * daerah tersebut agar Pemda cukup memeriksa dan mengubah seperlunya. Dokumen tetap harus diunggah.
     *
     * @return array{0: array, 1: ?RuteSubsidi}
     */
    private function isianDariPengajuanSebelumnya(Request $request, $semuaKabKota, $user): array
    {
        $idKab = (int) $request->query('kab');

        // Akun yang terhubung ke satu kab/kota hanya boleh memakai kab/kota miliknya
        if ($idKab && $user->id_kab_kota && (int) $user->id_kab_kota !== $idKab) {
            return [[], null];
        }

        $kab = $idKab ? $semuaKabKota->firstWhere('id_kab_kota', $idKab) : null;
        if (! $kab) {
            return [[], null];
        }

        $modaDiminta = $kab->modaRevisiTerbuka($user);
        $sumber = $modaDiminta ? RuteSubsidi::where('id_kab_kota_asal', $idKab)->orderByDesc('id_rute_subsidi')->first() : null;
        if (! $sumber) {
            return [[], null];
        }

        $kunciKeterangan = collect(FormOptions::KETERANGAN)->mapWithKeys(fn ($v, $k) => [mb_strtolower($v['label']) => $k]);

        return [[
            'id_wilayah'        => Provinsi::where('id_provinsi', $kab->id_provinsi)->value('id_wilayah'),
            'id_provinsi'       => $kab->id_provinsi,
            'id_kab_kota'       => $kab->id_kab_kota,
            'klasifikasi_pemda' => $sumber->klasifikasi_pemda,
            'keterangan'        => collect($sumber->keterangan_daftar)->map(fn ($l) => $kunciKeterangan[mb_strtolower($l)] ?? null)->filter()->values()->all(),
            'potensi'           => array_slice($sumber->potensi_daftar, 0, 5),
            // tujuan lama beserta komoditas dan berat (dari tabel pivot)
            'tujuan'            => $sumber->tujuan->map(fn ($k) => [
                'id'        => $k->id_kab_kota,
                'nama'      => $k->nama_kab_kota,
                'komoditas' => $k->pivot->komoditas,
                'berat'     => $k->pivot->berat,
            ])->values()->all(),
            'id_moda'           => count($modaDiminta) === 1 ? $modaDiminta[0] : null,
        ], $sumber];
    }

    public function store(StoreWilayahRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        // Pengaman: kab/kota harus milik akun (readonly di browser bisa diakali lewat DevTools)
        if ($user->id_kab_kota) {
            if ((int) $data['id_kab_kota'] !== (int) $user->id_kab_kota) {
                throw ValidationException::withMessages(['id_kab_kota' => 'Kabupaten/Kota harus sesuai dengan akun Anda.']);
            }
        } elseif (! $user->isSuperAdmin()) {
            throw ValidationException::withMessages(['id_kab_kota' => 'Akun Anda belum terhubung ke kabupaten/kota. Hubungi Super Admin.']);
        }

        $kab = KabKota::findOrFail($data['id_kab_kota']);

        // Format sama dengan data Excel: "1. A; 2. B; 3. C"
        $potensi = collect($data['potensi'])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values()
            ->map(fn ($v, $i) => ($i + 1) . '. ' . $v)
            ->implode('; ');

        // Format sama dengan data Excel: "3TP, KSPEAN, ..."
        $keterangan = collect($data['keterangan'] ?? [])
            ->map(fn ($key) => FormOptions::KETERANGAN[$key]['nilai'])
            ->implode(', ');

        // Tujuan dipilih dari tabel kab_kota (berdasarkan id), masing-masing dengan komoditas dan berat
        $tujuan = collect($data['tujuan'])
            ->map(fn ($t) => [
                'id'        => (int) $t['id'],
                'komoditas' => trim((string) $t['komoditas']),
                'berat'     => $t['berat'],
            ])
            ->unique('id')->values();
        $namaTujuan = KabKota::whereIn('id_kab_kota', $tujuan->pluck('id'))->orderBy('nama_kab_kota')->pluck('nama_kab_kota');
        $konektivitas = $namaTujuan->implode(', ');   // salinan teks nama tujuan untuk halaman lama

        $tersimpan = []; // path file, untuk dibersihkan jika transaksi gagal

        try {
            DB::transaction(function () use ($request, $data, $user, $kab, $potensi, $keterangan, $konektivitas, $tujuan, &$tersimpan) {
                // Daerah dipilih dari data yang sudah ada: isi/ambil alih data_pemda miliknya.
                $pemda = DataPemda::where('id_kab_kota', $kab->id_kab_kota)->lockForUpdate()->first()
                    ?? new DataPemda(['id_kab_kota' => $kab->id_kab_kota]);

                // Pemda boleh mengajukan berkali-kali (pengajuan lama tidak diubah); data milik akun lain tetap ditolak.
                $permintaan = null;
                if ($pemda->exists) {
                    if (! $pemda->bisaDiajukanOleh($user)) {
                        throw ValidationException::withMessages(['id_kab_kota' => 'Kabupaten/Kota ini sudah diajukan atau dikelola akun lain.']);
                    }
                    // Bila INTRAM sedang meminta revisi untuk moda ini, pengajuan ini otomatis memenuhinya
                    $permintaan = PermintaanRevisi::where('id_pemda', $pemda->id_pemda)
                        ->where('id_moda', $data['id_moda'])->where('status', PermintaanRevisi::MENUNGGU)
                        ->lockForUpdate()->first();
                }

                $isian = [
                    'potensi_pemda'          => $potensi,
                    'klasifikasi_pemda'      => $data['klasifikasi_pemda'],
                    'keterangan_pemda'       => $keterangan !== '' ? $keterangan : null,
                    'kebutuhan_konektivitas' => $konektivitas !== '' ? $konektivitas : null,
                ];

                // data_pemda menyimpan isian terbaru daerah; setiap pengajuan juga menyimpan salinannya sendiri
                $pemda->fill(['id_user' => $user->id_user] + $isian)->save();

                // Satu pengajuan = satu rute_subsidi (satu moda). Tujuan + komoditas + berat disimpan lewat relasi tujuan().
                $rute = RuteSubsidi::create([
                    'id_pemda'          => $pemda->id_pemda,
                    'id_moda'           => $data['id_moda'],
                    'id_kab_kota_asal'  => $kab->id_kab_kota,
                    'status_pengajuan'  => FormOptions::STATUS_AWAL,
                    'tanggal_pengajuan' => now()->toDateString(),
                ] + $isian);

                $rute->tujuan()->sync(
                    $tujuan->mapWithKeys(fn ($t) => [
                        $t['id'] => ['komoditas' => $t['komoditas'], 'berat' => $t['berat']],
                    ])->all()
                );

                if ($permintaan) {
                    $permintaan->update(['status' => PermintaanRevisi::DIPENUHI, 'id_rute_baru' => $rute->id_rute_subsidi]);
                }

                foreach (FormOptions::DOKUMEN as $field => $jenis) {
                    $file = $request->file($field);
                    $path = $file->store("dokumen_pengajuan/{$rute->id_rute_subsidi}", 'local');
                    $tersimpan[] = $path;

                    DokumenPengajuan::create([
                        'id_rute_subsidi' => $rute->id_rute_subsidi,
                        'id_user'         => $user->id_user,
                        'jenis_dokumen'   => $jenis,
                        'nama_file'       => $file->getClientOriginalName(),
                        'nomor_surat'     => trim($data['nomor'][$field]),
                        'path_file'       => $path,
                        'versi_dokumen'   => 1,
                        'tanggal_upload'  => now(),
                    ]);
                }
            });
        } catch (ValidationException $e) {
            Storage::disk('local')->delete($tersimpan);

            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($tersimpan);
            report($e);

            return back()->withInput()->with('error', 'Data gagal disimpan. Silakan coba lagi atau hubungi administrator.');
        }

        return redirect()->route('data-wilayah.index')
            ->with('success', 'Pengajuan untuk "' . $kab->nama_kab_kota . '" berhasil ditambahkan.');
    }
}