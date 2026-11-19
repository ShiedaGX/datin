<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWilayahRequest;
use App\Models\DataPemda;
use App\Models\DokumenPengajuan;
use App\Models\KabKota;
use App\Models\MasterSatuanData;
use App\Models\Moda;
use App\Models\PermintaanRevisi;
use App\Models\Provinsi;
use App\Models\RuteSubsidi;
use App\Models\RuteSubsidiTujuanKomoditas;
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

        $semua = KabKota::with(
            'provinsi:id_provinsi,id_wilayah,nama_provinsi',
            'dataPemda.ruteSubsidi',
            'dataPemda.permintaanRevisi'
        )
            ->orderBy('nama_kab_kota')
            ->get();

        $kabSaya = $user->id_kab_kota
            ? $semua->firstWhere(
                'id_kab_kota',
                (int) $user->id_kab_kota
            )
            : null;

        $terkunci = $kabSaya !== null;

        $akunBelumTerhubung = ! $terkunci
            && ! $user->isSuperAdmin();

        [$isian, $sumber] = $this->isianDariPengajuanSebelumnya(
            $request,
            $semua,
            $user
        );

        $namaModa = Moda::pluck('nama_moda', 'id_moda');

        return view('input-data.create', [
            'isian' => $isian,
            'sumber' => $sumber,
            'terkunci' => $terkunci,
            'kabSaya' => $kabSaya,

            'alasanSaya' => $kabSaya
                ?->alasanTidakTersedia($user),

            'akunBelumTerhubung' => $akunBelumTerhubung,

            'sektor' => collect(\App\Support\Sektor::peta())
                ->map(fn ($kunci, $nama) => [
                    'nama' => $nama,
                    'kunci' => $kunci,
                ])
                ->values(),

            'komoditasSektor' => \App\Support\Sektor::komoditas(),

            'provinsi' => Provinsi::orderBy(
                'nama_provinsi'
            )->get(),

            'wilayah' => Wilayah::orderBy(
                'nama_wilayah'
            )->get(),

            'moda' => Moda::orderBy(
                'id_moda'
            )->get(),

            'klasifikasi' => FormOptions::KLASIFIKASI,
            'keterangan' => FormOptions::KETERANGAN,
            'dokumen' => FormOptions::DOKUMEN,

            // Pilihan satuan data dari master satuan aktif.
            'pilihanSatuan' => MasterSatuanData::aktif()
                ->orderBy('nama_satuan')
                ->pluck('nama_satuan'),

            // Pilihan kabupaten/kota.
            'kabKota' => $semua->map(fn ($k) => [
                'id' => $k->id_kab_kota,
                'id_provinsi' => $k->id_provinsi,
                'nama' => $k->nama_kab_kota,

                'alasan' => $k->alasanTidakTersedia($user),

                'revisi' => $k->modaRevisiTerbuka($user),

                'sudah' => $k->modaDiajukan($user),

                'sudahNama' => collect(
                    $k->modaDiajukan($user)
                )
                    ->map(fn ($id) => $namaModa[$id] ?? '')
                    ->filter()
                    ->values()
                    ->all(),

                'revisiNama' => Moda::whereIn(
                    'id_moda',
                    $k->modaRevisiTerbuka($user)
                )
                    ->orderBy('id_moda')
                    ->pluck('nama_moda')
                    ->all(),
            ])->values(),

            // Saran otomatis pemilihan rute tujuan.
            'saran' => $semua->map(fn ($k) => [
                'id' => $k->id_kab_kota,
                'nama' => $k->nama_kab_kota,
                'provinsi' => $k->provinsi->nama_provinsi,
            ])->values(),
        ]);
    }

    /**
     * Mengambil data pengajuan sebelumnya untuk revisi INTRAM.
     *
     * Setiap tujuan dapat memiliki beberapa komoditas.
     * Setiap komoditas memiliki jumlah dan satuan data.
     *
     * Data lama tetap didukung melalui fallback pivot.
     *
     * @return array{0: array, 1: ?RuteSubsidi}
     */
    private function isianDariPengajuanSebelumnya(
        Request $request,
        $semuaKabKota,
        $user
    ): array {
        $idKab = (int) $request->query('kab');

        // Pengamanan kepemilikan kabupaten/kota.
        if (
            $idKab
            && $user->id_kab_kota
            && (int) $user->id_kab_kota !== $idKab
        ) {
            return [[], null];
        }

        $kab = $idKab
            ? $semuaKabKota->firstWhere(
                'id_kab_kota',
                $idKab
            )
            : null;

        if (! $kab) {
            return [[], null];
        }

        // Periksa permintaan revisi INTRAM.
        $modaDiminta = $kab->modaRevisiTerbuka($user);

        $sumber = $modaDiminta
            ? RuteSubsidi::where(
                'id_kab_kota_asal',
                $idKab
            )
                ->whereIn('id_moda', $modaDiminta)
                ->orderByDesc('id_rute_subsidi')
                ->first()
            : null;

        if (! $sumber) {
            return [[], null];
        }

        $kunciKeterangan = collect(
            FormOptions::KETERANGAN
        )->mapWithKeys(
            fn ($v, $k) => [
                mb_strtolower($v['label']) => $k,
            ]
        );

        // Ambil seluruh komoditas untuk pengajuan ini.
        $komoditasPerTujuan = RuteSubsidiTujuanKomoditas::where(
            'id_rute_subsidi',
            $sumber->id_rute_subsidi
        )
            ->get()
            ->groupBy('id_kab_kota_tujuan');

        // Bentuk ulang tujuan beserta komoditasnya.
        $tujuan = $sumber->tujuan->map(
            function ($k) use ($komoditasPerTujuan) {

                $komoditas = $komoditasPerTujuan->get(
                    $k->id_kab_kota,
                    collect()
                );

                // Fallback untuk pengajuan lama.
                if (
                    $komoditas->isEmpty()
                    && $k->pivot->komoditas
                ) {
                    $komoditas = collect([
                        (object) [
                            'nama_komoditas' =>
                                $k->pivot->komoditas,

                            'jumlah' =>
                                $k->pivot->berat,

                            'satuan_data' => null,
                        ],
                    ]);
                }

                return [
                    'id' => $k->id_kab_kota,
                    'nama' => $k->nama_kab_kota,

                    'komoditas' => $komoditas
                        ->map(fn ($item) => [
                            'nama' => $item->nama_komoditas,

                            'jumlah' => $item->jumlah
                                ?? $item->berat,

                            'satuan_data' => $item->satuan_data
                                ?? null,
                        ])
                        ->values()
                        ->all(),
                ];
            }
        )->values()->all();

        return [[
            'id_wilayah' => Provinsi::where(
                'id_provinsi',
                $kab->id_provinsi
            )->value('id_wilayah'),

            'id_provinsi' => $kab->id_provinsi,

            'id_kab_kota' => $kab->id_kab_kota,

            'klasifikasi_pemda' =>
                $sumber->klasifikasi_pemda,

            'keterangan' => collect(
                $sumber->keterangan_daftar
            )
                ->map(
                    fn ($l) =>
                    $kunciKeterangan[
                        mb_strtolower($l)
                    ] ?? null
                )
                ->filter()
                ->values()
                ->all(),

            'potensi' => array_slice(
                $sumber->potensi_daftar,
                0,
                5
            ),

            'tujuan' => $tujuan,

            'id_moda' => count($modaDiminta) === 1
                ? $modaDiminta[0]
                : null,

        ], $sumber];
    }

    /**
     * Menyimpan pengajuan Pemda.
     *
     * Satu pengajuan memiliki satu moda.
     * Satu pengajuan dapat memiliki beberapa tujuan.
     * Setiap tujuan dapat memiliki beberapa komoditas.
     */
    public function store(StoreWilayahRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        // Pengamanan akun Pemda.
        if ($user->id_kab_kota) {
            if (
                (int) $data['id_kab_kota']
                !== (int) $user->id_kab_kota
            ) {
                throw ValidationException::withMessages([
                    'id_kab_kota' =>
                        'Kabupaten/Kota harus sesuai dengan akun Anda.',
                ]);
            }
        } elseif (! $user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'id_kab_kota' =>
                    'Akun Anda belum terhubung ke kabupaten/kota. Hubungi Super Admin.',
            ]);
        }

        $kab = KabKota::findOrFail(
            $data['id_kab_kota']
        );

        // Potensi unggulan daerah.
        $potensi = collect($data['potensi'])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values()
            ->map(
                fn ($v, $i) =>
                ($i + 1) . '. ' . $v
            )
            ->implode('; ');

        // Keterangan Pemda.
        $keterangan = collect(
            $data['keterangan'] ?? []
        )
            ->map(
                fn ($key) =>
                FormOptions::KETERANGAN[$key]['nilai']
            )
            ->implode(', ');

        /*
         * Pengolahan tujuan.
         *
         * Format:
         * tujuan => [
         *   [
         *     'id' => 101,
         *     'komoditas' => [
         *       [
         *         'nama' => 'Beras',
         *         'jumlah' => 100,
         *         'satuan_data' => 'Ton',
         *       ],
         *     ],
         *   ],
         * ]
         */
        $tujuan = collect($data['tujuan'])
            ->map(fn ($t) => [
                'id' => (int) $t['id'],

                'komoditas' => collect(
                    $t['komoditas']
                )
                    ->map(fn ($k) => [
                        'nama' => trim(
                            (string) $k['nama']
                        ),

                        'jumlah' => $k['jumlah'],

                        'satuan_data' => $k['satuan_data'],
                    ])
                    ->values()
                    ->all(),
            ])
            ->unique('id')
            ->values();

        // Pastikan setiap tujuan memiliki komoditas.
        foreach ($tujuan as $index => $t) {
            if (count($t['komoditas']) < 1) {
                throw ValidationException::withMessages([
                    "tujuan.$index.komoditas" =>
                        'Setiap tujuan wajib memiliki minimal satu komoditas.',
                ]);
            }
        }

        // Nama tujuan untuk kebutuhan konektivitas.
        $namaTujuan = KabKota::whereIn(
            'id_kab_kota',
            $tujuan->pluck('id')
        )
            ->orderBy('nama_kab_kota')
            ->pluck('nama_kab_kota');

        $konektivitas = $namaTujuan->implode(', ');

        // Daftar file untuk dibersihkan jika transaksi gagal.
        $tersimpan = [];

        try {
            DB::transaction(function () use (
                $request,
                $data,
                $user,
                $kab,
                $potensi,
                $keterangan,
                $konektivitas,
                $tujuan,
                &$tersimpan
            ) {
                // Ambil atau buat data Pemda.
                $pemda = DataPemda::where(
                    'id_kab_kota',
                    $kab->id_kab_kota
                )
                    ->lockForUpdate()
                    ->first()
                    ?? new DataPemda([
                        'id_kab_kota' =>
                            $kab->id_kab_kota,
                    ]);

                $permintaan = null;

                if ($pemda->exists) {
                    // Pengamanan kepemilikan data.
                    if (! $pemda->bisaDiajukanOleh($user)) {
                        throw ValidationException::withMessages([
                            'id_kab_kota' =>
                                'Kabupaten/Kota ini sudah diajukan atau dikelola akun lain.',
                        ]);
                    }

                    // Periksa revisi INTRAM.
                    $permintaan = PermintaanRevisi::where(
                        'id_pemda',
                        $pemda->id_pemda
                    )
                        ->where(
                            'id_moda',
                            $data['id_moda']
                        )
                        ->where(
                            'status',
                            PermintaanRevisi::MENUNGGU
                        )
                        ->lockForUpdate()
                        ->first();
                }

                // Data utama pengajuan.
                $isian = [
                    'potensi_pemda' => $potensi,

                    'klasifikasi_pemda' =>
                        $data['klasifikasi_pemda'],

                    'keterangan_pemda' =>
                        $keterangan !== ''
                            ? $keterangan
                            : null,

                    'kebutuhan_konektivitas' =>
                        $konektivitas !== ''
                            ? $konektivitas
                            : null,
                ];

                // Simpan data terbaru Pemda.
                $pemda->fill([
                    'id_user' => $user->id_user,
                ] + $isian)->save();

                // Buat pengajuan baru.
                $rute = RuteSubsidi::create([
                    'id_pemda' => $pemda->id_pemda,

                    'id_moda' => $data['id_moda'],

                    'id_kab_kota_asal' =>
                        $kab->id_kab_kota,

                    'status_pengajuan' =>
                        FormOptions::STATUS_AWAL,

                    'tanggal_pengajuan' =>
                        now()->toDateString(),

                ] + $isian);

                // Simpan daftar kabupaten/kota tujuan.
                // Tabel pivot hanya menyimpan daftar tujuan.
                $rute->tujuan()->sync(
                    $tujuan->mapWithKeys(
                        fn ($t) => [
                            $t['id'] => [],
                        ]
                    )->all()
                );

                // Simpan detail komoditas, jumlah, dan satuan.
                foreach ($tujuan as $t) {
                    foreach ($t['komoditas'] as $kom) {
                        RuteSubsidiTujuanKomoditas::create([
                            'id_rute_subsidi' =>
                                $rute->id_rute_subsidi,

                            'id_kab_kota_tujuan' =>
                                $t['id'],

                            'nama_komoditas' =>
                                $kom['nama'],

                            'jumlah' =>
                                $kom['jumlah'],

                            'satuan_data' =>
                                $kom['satuan_data'],
                        ]);
                    }
                }

                // Tandai permintaan revisi INTRAM terpenuhi.
                if ($permintaan) {
                    $permintaan->update([
                        'status' =>
                            PermintaanRevisi::DIPENUHI,

                        'id_rute_baru' =>
                            $rute->id_rute_subsidi,
                    ]);
                }

                // Simpan dokumen pengajuan.
                foreach (
                    FormOptions::DOKUMEN
                    as $field => $jenis
                ) {
                    $file = $request->file($field);

                    $path = $file->store(
                        "dokumen_pengajuan/{$rute->id_rute_subsidi}",
                        'local'
                    );

                    $tersimpan[] = $path;

                    DokumenPengajuan::create([
                        'id_rute_subsidi' =>
                            $rute->id_rute_subsidi,

                        'id_user' =>
                            $user->id_user,

                        'jenis_dokumen' =>
                            $jenis,

                        'nama_file' =>
                            $file->getClientOriginalName(),

                        'nomor_surat' =>
                            trim($data['nomor'][$field]),

                        'path_file' =>
                            $path,

                        'versi_dokumen' => 1,

                        'tanggal_upload' => now(),
                    ]);
                }
            });

        } catch (ValidationException $e) {
            Storage::disk('local')->delete($tersimpan);

            throw $e;

        } catch (\Throwable $e) {
            Storage::disk('local')->delete($tersimpan);

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Data gagal disimpan. Silakan coba lagi atau hubungi administrator.'
                );
        }

        return redirect()
            ->route('data-wilayah.index')
            ->with(
                'success',
                'Pengajuan untuk "' .
                    $kab->nama_kab_kota .
                    '" berhasil ditambahkan.'
            );
    }
}
