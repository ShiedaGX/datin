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
<<<<<<< HEAD
=======
use App\Support\Sektor;
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InputDataController extends Controller
{
<<<<<<< HEAD
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
            // saran otomatis untuk kolom Kebutuhan Konektivitas
            'saran'       => $semua->map(fn ($k) => [
                'id'       => $k->id_kab_kota,
                'nama'     => $k->nama_kab_kota,
                'provinsi' => $k->provinsi->nama_provinsi,
=======
    /**
     * Menampilkan halaman input pengajuan Pemda.
     */
    public function create(Request $request)
    {
        $user = $request->user();

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

        $namaModa = Moda::pluck(
            'nama_moda',
            'id_moda'
        );

        return view('input-data.create', [
            'isian' => $isian,
            'sumber' => $sumber,
            'terkunci' => $terkunci,
            'kabSaya' => $kabSaya,

            'alasanSaya' => $kabSaya
                ?->alasanTidakTersedia($user),

            'akunBelumTerhubung' =>
                $akunBelumTerhubung,

            'sektor' => collect(Sektor::peta())
                ->map(fn ($kunci, $nama) => [
                    'nama' => $nama,
                    'kunci' => $kunci,
                ])
                ->values(),

            'komoditasSektor' => Sektor::komoditas(),

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
            'kabKota' => $semua->map(function ($k) use (
                $user,
                $namaModa
            ) {
                $sudah = $k->modaDiajukan($user);

                $revisi = $k->modaRevisiTerbuka($user);

                return [
                    'id' => $k->id_kab_kota,

                    'id_provinsi' => $k->id_provinsi,

                    'nama' => $k->nama_kab_kota,

                    'alasan' =>
                        $k->alasanTidakTersedia($user),

                    'revisi' => $revisi,

                    'sudah' => $sudah,

                    'sudahNama' => collect($sudah)
                        ->map(fn ($id) =>
                            $namaModa[$id] ?? ''
                        )
                        ->filter()
                        ->values()
                        ->all(),

                    'revisiNama' => collect($revisi)
                        ->sort()
                        ->map(fn ($id) =>
                            $namaModa[$id] ?? ''
                        )
                        ->filter()
                        ->values()
                        ->all(),
                ];
            })->values(),

            // Saran otomatis pemilihan rute tujuan.
            'saran' => $semua->map(fn ($k) => [
                'id' => $k->id_kab_kota,

                'nama' => $k->nama_kab_kota,

                'provinsi' =>
                    $k->provinsi->nama_provinsi,
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
            ])->values(),
        ]);
    }

    /**
<<<<<<< HEAD
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
=======
     * Mengambil data pengajuan sebelumnya untuk
     * memenuhi permintaan revisi INTRAM.
     *
     * Sumber pengajuan diambil berdasarkan id_rute_asal
     * pada tabel permintaan_revisi.
     *
     * Pengajuan sebelumnya tidak diubah.
     *
     * @return array{0: array, 1: ?RuteSubsidi}
     */
    private function isianDariPengajuanSebelumnya(
        Request $request,
        $semuaKabKota,
        $user
    ): array {
        $idKab = (int) $request->query('kab');

        // Form pengajuan biasa tetap kosong.
        // Isian otomatis hanya digunakan jika
        // halaman dibuka melalui tautan revisi.
        if ($idKab <= 0) {
            return [[], null];
        }

        // Pastikan akun terhubung ke kabupaten/kota.
        if (
            ! $user->id_kab_kota
            && ! $user->isSuperAdmin()
        ) {
            return [[], null];
        }

        // Pemda hanya dapat mengakses wilayahnya sendiri.
        if (
            $user->id_kab_kota
            && (int) $user->id_kab_kota !== $idKab
        ) {
            return [[], null];
        }

        $kab = $semuaKabKota->firstWhere(
            'id_kab_kota',
            $idKab
        );

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        if (! $kab) {
            return [[], null];
        }

<<<<<<< HEAD
        $modaDiminta = $kab->modaRevisiTerbuka($user);
        $sumber = $modaDiminta ? RuteSubsidi::where('id_kab_kota_asal', $idKab)->orderByDesc('id_rute_subsidi')->first() : null;
=======
        // Ambil moda tambahan yang diminta INTRAM.
        $modaDiminta = collect(
            $kab->modaRevisiTerbuka($user)
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($modaDiminta)) {
            return [[], null];
        }

        // Ambil data Pemda milik akun yang sedang login.
        $pemda = DataPemda::where(
            'id_kab_kota',
            $idKab
        )
            ->where('id_user', $user->id_user)
            ->first();

        if (
            ! $pemda
            || ! $pemda->bisaDiajukanOleh($user)
        ) {
            return [[], null];
        }

        // Ambil permintaan revisi yang masih menunggu.
        $query = PermintaanRevisi::query()
            ->with(
                'ruteAsal.tujuan',
                'ruteAsal.detailKomoditasTujuan'
            )
            ->where(
                'id_pemda',
                $pemda->id_pemda
            )
            ->whereIn(
                'id_moda',
                $modaDiminta
            )
            ->where(
                'status',
                PermintaanRevisi::MENUNGGU
            );

        // Mendukung pemilihan moda melalui URL,
        // jika tautan revisi menyertakan parameter moda.
        $idModa = (int) $request->query('moda');

        if (
            $idModa > 0
            && in_array($idModa, $modaDiminta, true)
        ) {
            $query->where('id_moda', $idModa);
        }

        $permintaan = $query
            ->orderByDesc('id_permintaan_revisi')
            ->get()
            ->first(function ($p) use ($pemda, $idKab) {
                $asal = $p->ruteAsal;

                return $asal
                    && (int) $asal->id_pemda
                        === (int) $pemda->id_pemda
                    && (int) $asal->id_kab_kota_asal
                        === $idKab;
            });

        if (! $permintaan) {
            return [[], null];
        }

        // Gunakan laporan asal permintaan revisi.
        $sumber = $permintaan->ruteAsal;

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        if (! $sumber) {
            return [[], null];
        }

<<<<<<< HEAD
        $kunciKeterangan = collect(FormOptions::KETERANGAN)->mapWithKeys(fn ($v, $k) => [mb_strtolower($v['label']) => $k]);

        return [[
            'id_wilayah'             => Provinsi::where('id_provinsi', $kab->id_provinsi)->value('id_wilayah'),
            'id_provinsi'            => $kab->id_provinsi,
            'id_kab_kota'            => $kab->id_kab_kota,
            'klasifikasi_pemda'      => $sumber->klasifikasi_pemda,
            'keterangan'             => collect($sumber->keterangan_daftar)->map(fn ($l) => $kunciKeterangan[mb_strtolower($l)] ?? null)->filter()->values()->all(),
            'potensi'                => array_slice($sumber->potensi_daftar, 0, 5),
            'kebutuhan_konektivitas' => $sumber->kebutuhan_konektivitas,
            'id_moda'                => count($modaDiminta) === 1 ? $modaDiminta[0] : null,
        ], $sumber];
    }

=======
        // Pemetaan keterangan Pemda.
        $kunciKeterangan = collect(
            FormOptions::KETERANGAN
        )->mapWithKeys(
            fn ($v, $k) => [
                mb_strtolower($v['label']) => $k,
            ]
        );

        // Ambil rincian komoditas berdasarkan tujuan.
        $komoditasPerTujuan =
            $sumber->detailKomoditasTujuan
                ->groupBy('id_kab_kota_tujuan');

        // Bentuk ulang daftar tujuan dan komoditas.
        $tujuan = $sumber->tujuan->map(
            function ($k) use ($komoditasPerTujuan) {
                $komoditas = $komoditasPerTujuan->get(
                    $k->id_kab_kota,
                    collect()
                );

                // Dukungan untuk data pengajuan lama.
                if (
                    $komoditas->isEmpty()
                    && trim(
                        (string) $k->pivot->komoditas
                    ) !== ''
                ) {
                    $komoditas = collect([
                        (object) [
                            'nama_komoditas' =>
                                $k->pivot->komoditas,

                            'jumlah' =>
                                $k->pivot->berat,

                            // Satuan data lama belum diketahui.
                            'satuan_data' => null,
                        ],
                    ]);
                }

                return [
                    'id' => $k->id_kab_kota,

                    'nama' => $k->nama_kab_kota,

                    'komoditas' => $komoditas
                        ->map(fn ($item) => [
                            'nama' =>
                                $item->nama_komoditas,

                            'jumlah' =>
                                $item->jumlah
                                ?? $item->berat
                                ?? null,

                            'satuan_data' =>
                                $item->satuan_data
                                ?? null,
                        ])
                        ->values()
                        ->all(),
                ];
            }
        )->values()->all();

        return [[
            'id_wilayah' =>
                $kab->provinsi->id_wilayah,

            'id_provinsi' =>
                $kab->id_provinsi,

            'id_kab_kota' =>
                $kab->id_kab_kota,

            'klasifikasi_pemda' =>
                $sumber->klasifikasi_pemda,

            'keterangan' => collect(
                $sumber->keterangan_daftar
            )
                ->map(fn ($l) =>
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

            // Moda sesuai permintaan revisi yang dipilih.
            'id_moda' => (int) $permintaan->id_moda,

        ], $sumber];
    }

    /**
     * Menyimpan pengajuan Pemda.
     *
     * Pemda dapat mengajukan laporan berkali-kali,
     * termasuk untuk moda yang sama.
     *
     * Setiap pengajuan menghasilkan satu laporan baru.
     * Pengajuan sebelumnya tidak diubah.
     *
     * Permintaan revisi INTRAM bukan syarat wajib
     * untuk membuat pengajuan baru.
     */
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
    public function store(StoreWilayahRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

<<<<<<< HEAD
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

        // "Merauke, Jayapura, " -> "Merauke, Jayapura"
        $konektivitas = trim((string) ($data['kebutuhan_konektivitas'] ?? ''), " \t\r\n,");

        $tersimpan = []; // path file, untuk dibersihkan jika transaksi gagal

        try {
            DB::transaction(function () use ($request, $data, $user, $kab, $potensi, $keterangan, $konektivitas, &$tersimpan) {
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

                // Satu pengajuan = satu rute_subsidi (satu moda). Kota tujuan tertulis pada kebutuhan_konektivitas.
                $rute = RuteSubsidi::create([
                    'id_pemda'           => $pemda->id_pemda,
                    'id_moda'            => $data['id_moda'],
                    'id_kab_kota_asal'   => $kab->id_kab_kota,
                    'id_kab_kota_tujuan' => null,
                    'status_pengajuan'   => FormOptions::STATUS_AWAL,
                    'tanggal_pengajuan'  => now()->toDateString(),
                ] + $isian);

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
=======
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

        // Pastikan provinsi sesuai kabupaten/kota.
        if (
            (int) $data['id_provinsi']
            !== (int) $kab->id_provinsi
        ) {
            throw ValidationException::withMessages([
                'id_provinsi' =>
                    'Provinsi tidak sesuai dengan Kabupaten/Kota yang dipilih.',
            ]);
        }

        // Pastikan wilayah sesuai provinsi.
        if (
            (int) $data['id_wilayah']
            !== (int) $kab->provinsi->id_wilayah
        ) {
            throw ValidationException::withMessages([
                'id_wilayah' =>
                    'Wilayah tidak sesuai dengan provinsi yang dipilih.',
            ]);
        }

        // Potensi unggulan daerah.
        $potensi = collect($data['potensi'])
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->values()
            ->map(fn ($v, $i) =>
                ($i + 1) . '. ' . $v
            )
            ->implode('; ');

        // Keterangan Pemda.
        $keterangan = collect(
            $data['keterangan'] ?? []
        )
            ->map(fn ($key) =>
                FormOptions::KETERANGAN[$key]['nilai']
            )
            ->implode(', ');

        // Pengolahan tujuan dan komoditas.
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

                        'satuan_data' => trim(
                            (string) $k['satuan_data']
                        ),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values();

        // Minimal satu tujuan.
        if ($tujuan->isEmpty()) {
            throw ValidationException::withMessages([
                'tujuan' =>
                    'Pilih minimal satu Kabupaten/Kota tujuan.',
            ]);
        }

        // Kabupaten/Kota tujuan tidak boleh ganda.
        if (
            $tujuan->pluck('id')->unique()->count()
            !== $tujuan->count()
        ) {
            throw ValidationException::withMessages([
                'tujuan' =>
                    'Kabupaten/Kota tujuan tidak boleh dipilih lebih dari satu kali.',
            ]);
        }

        // Validasi rincian komoditas.
        foreach ($tujuan as $index => $t) {
            if (
                (int) $t['id']
                === (int) $kab->id_kab_kota
            ) {
                throw ValidationException::withMessages([
                    "tujuan.$index.id" =>
                        'Tujuan tidak boleh sama dengan Kabupaten/Kota asal.',
                ]);
            }

            if (count($t['komoditas']) < 1) {
                throw ValidationException::withMessages([
                    "tujuan.$index.komoditas" =>
                        'Setiap tujuan wajib memiliki minimal satu komoditas.',
                ]);
            }

            foreach (
                $t['komoditas']
                as $indexKomoditas => $kom
            ) {
                $field =
                    "tujuan.$index.komoditas.$indexKomoditas";

                if ($kom['nama'] === '') {
                    throw ValidationException::withMessages([
                        "$field.nama" =>
                            'Nama komoditas wajib diisi.',
                    ]);
                }

                if (
                    ! is_numeric($kom['jumlah'])
                    || ! is_finite((float) $kom['jumlah'])
                    || (float) $kom['jumlah'] <= 0
                ) {
                    throw ValidationException::withMessages([
                        "$field.jumlah" =>
                            'Jumlah komoditas harus lebih dari 0.',
                    ]);
                }

                if ($kom['satuan_data'] === '') {
                    throw ValidationException::withMessages([
                        "$field.satuan_data" =>
                            'Satuan data komoditas wajib diisi.',
                    ]);
                }
            }
        }

        // Nama tujuan untuk kebutuhan konektivitas.
        $namaTujuan = KabKota::whereIn(
            'id_kab_kota',
            $tujuan->pluck('id')
        )
            ->orderBy('nama_kab_kota')
            ->pluck('nama_kab_kota');

        if ($namaTujuan->count() !== $tujuan->count()) {
            throw ValidationException::withMessages([
                'tujuan' =>
                    'Terdapat Kabupaten/Kota tujuan yang tidak valid.',
            ]);
        }

        $konektivitas = $namaTujuan->implode(', ');

        // Daftar berkas untuk dibersihkan jika gagal.
        $tersimpan = [];

        $revisiDipenuhi = false;

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
                &$tersimpan,
                &$revisiDipenuhi
            ) {
                // 1. Kunci Kabupaten/Kota.
                //
                // Mencegah pembuatan data induk Pemda
                // secara bersamaan.
                $kabTerkunci = KabKota::where(
                    'id_kab_kota',
                    $kab->id_kab_kota
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Kunci seluruh data induk Pemda
                // pada Kabupaten/Kota tersebut.
                $daftarPemda = DataPemda::where(
                    'id_kab_kota',
                    $kabTerkunci->id_kab_kota
                )
                    ->orderBy('id_pemda')
                    ->lockForUpdate()
                    ->get();

                // Periksa kepemilikan wilayah kembali
                // di dalam transaksi.
                $kabTerkunci->setRelation(
                    'dataPemda',
                    $daftarPemda
                );

                $alasan = $kabTerkunci
                    ->alasanTidakTersedia($user);

                if ($alasan) {
                    throw ValidationException::withMessages([
                        'id_kab_kota' =>
                            'Kabupaten/Kota ini '
                            . $alasan
                            . '.',
                    ]);
                }

                // Prioritaskan data Pemda milik akun ini.
                $pemda = $daftarPemda->first(
                    fn ($p) =>
                        $p->id_user !== null
                        && (int) $p->id_user
                            === (int) $user->id_user
                );

                // Gunakan data yang belum memiliki pemilik,
                // jika tersedia dan diperbolehkan.
                if (! $pemda) {
                    $pemda = $daftarPemda->first(
                        fn ($p) => $p->id_user === null
                    );
                }

                // Buat data induk apabila belum tersedia.
                if (! $pemda) {
                    $pemda = new DataPemda([
                        'id_kab_kota' =>
                            $kabTerkunci->id_kab_kota,
                    ]);
                }

                $permintaan = null;

                if ($pemda->exists) {
                    // Pengamanan kepemilikan data.
                    if (! $pemda->bisaDiajukanOleh($user)) {
                        throw ValidationException::withMessages([
                            'id_kab_kota' =>
                                'Kabupaten/Kota ini sudah diajukan atau dikelola akun lain.',
                        ]);
                    }

                    /*
                     * PENTING:
                     *
                     * Tidak ada pembatasan pengajuan
                     * berdasarkan moda sebelumnya.
                     *
                     * Pemda boleh mengajukan:
                     * - Moda yang sama.
                     * - Moda berbeda.
                     * - Tanpa permintaan revisi INTRAM.
                     *
                     * Setiap pengajuan tetap menghasilkan
                     * satu laporan baru.
                     */

                    // Periksa apakah terdapat permintaan
                    // revisi yang sesuai dengan moda ini.
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
                        ->orderBy('id_permintaan_revisi')
                        ->lockForUpdate()
                        ->first();

                    // Jika ada permintaan revisi,
                    // pastikan laporan asalnya valid.
                    if ($permintaan) {
                        $asal = $permintaan->ruteAsal;

                        if (
                            ! $asal
                            || (int) $asal->id_pemda
                                !== (int) $pemda->id_pemda
                            || (int) $asal->id_kab_kota_asal
                                !== (int) $kabTerkunci->id_kab_kota
                        ) {
                            throw ValidationException::withMessages([
                                'id_moda' =>
                                    'Rujukan permintaan revisi tidak valid. Hubungi administrator.',
                            ]);
                        }
                    }
                }

                // 3. Siapkan data utama pengajuan.
                $isian = [
                    'potensi_pemda' =>
                        $potensi,

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

                // 4. Simpan data terbaru Pemda.
                $pemda->fill($isian);

                // Jangan mengganti pemilik data lama.
                if (
                    ! $pemda->exists
                    || $pemda->id_user === null
                ) {
                    $pemda->id_user = $user->id_user;
                }

                $pemda->save();

                // 5. Selalu buat laporan baru.
                //
                // Tidak menggunakan updateOrCreate
                // karena pengajuan lama harus dipertahankan.
                $rute = RuteSubsidi::create([
                    'id_pemda' =>
                        $pemda->id_pemda,

                    'id_moda' =>
                        $data['id_moda'],

                    'id_kab_kota_asal' =>
                        $kabTerkunci->id_kab_kota,

                    'status_pengajuan' =>
                        FormOptions::STATUS_AWAL,

                    'tanggal_pengajuan' =>
                        now()->toDateString(),

                ] + $isian);

                // 6. Simpan daftar Kabupaten/Kota tujuan.
                $rute->tujuan()->sync(
                    $tujuan->mapWithKeys(
                        fn ($t) => [
                            $t['id'] => [],
                        ]
                    )->all()
                );

                // 7. Simpan seluruh rincian komoditas.
                //
                // Satuan data mengikuti isian pengguna.
                // Satuan default hanya sebagai saran form.
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

                // 8. Simpan seluruh dokumen pengajuan.
                $disk = Storage::disk('local');

                foreach (
                    FormOptions::DOKUMEN
                    as $field => $jenis
                ) {
                    $file = $request->file($field);

                    if (
                        ! $file
                        || ! $file->isValid()
                    ) {
                        throw ValidationException::withMessages([
                            $field =>
                                'Dokumen pengajuan tidak valid atau gagal diunggah.',
                        ]);
                    }

                    $nomorSurat = trim(
                        (string) (
                            $data['nomor'][$field] ?? ''
                        )
                    );

                    if ($nomorSurat === '') {
                        throw ValidationException::withMessages([
                            "nomor.$field" =>
                                'Nomor surat wajib diisi.',
                        ]);
                    }

                    // Format PDF, DOC, dan DOCX
                    // telah divalidasi StoreWilayahRequest.
                    $path = $file->store(
                        "dokumen_pengajuan/{$rute->id_rute_subsidi}",
                        'local'
                    );

                    if (
                        ! is_string($path)
                        || $path === ''
                    ) {
                        throw ValidationException::withMessages([
                            $field =>
                                'Dokumen gagal disimpan pada server.',
                        ]);
                    }

                    // Catat berkas untuk pembersihan
                    // jika transaksi gagal.
                    $tersimpan[] = $path;

                    // Pastikan berkas benar-benar tersimpan.
                    if (
                        ! $disk->exists($path)
                        || $disk->size($path) <= 0
                    ) {
                        throw ValidationException::withMessages([
                            $field =>
                                'Dokumen tidak ditemukan atau kosong setelah diunggah.',
                        ]);
                    }

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
                            $nomorSurat,

                        'path_file' =>
                            $path,

                        'versi_dokumen' => 1,

                        'tanggal_upload' => now(),
                    ]);
                }

                // 9. Tandai permintaan revisi terpenuhi
                // apabila terdapat permintaan yang sesuai.
                //
                // Pengajuan biasa tetap dapat disimpan
                // tanpa permintaan revisi.
                if ($permintaan) {
                    $permintaan->update([
                        'status' =>
                            PermintaanRevisi::DIPENUHI,

                        'id_rute_baru' =>
                            $rute->id_rute_subsidi,
                    ]);

                    $revisiDipenuhi = true;
                }
            });

        } catch (ValidationException $e) {
            $this->bersihkanBerkas($tersimpan);

            throw $e;

        } catch (\Throwable $e) {
            $this->bersihkanBerkas($tersimpan);

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Data gagal disimpan. Silakan coba lagi atau hubungi administrator.'
                );
        }

        // Pesan keberhasilan.
        $pesan = 'Pengajuan untuk "'
            . $kab->nama_kab_kota
            . '" berhasil ditambahkan.';

        if ($revisiDipenuhi) {
            $pesan .= ' Permintaan revisi INTRAM telah dipenuhi.';
        }

        return redirect()
            ->route('data-wilayah.index')
            ->with('success', $pesan);
    }

    /**
     * Membersihkan berkas apabila transaksi gagal.
     */
    private function bersihkanBerkas(array $paths): void
    {
        $disk = Storage::disk('local');

        foreach ($paths as $path) {
            if (
                ! is_string($path)
                || $path === ''
            ) {
                continue;
            }

            try {
                if (! $disk->delete($path)) {
                    report(new \RuntimeException(
                        'Gagal membersihkan berkas: ' . $path
                    ));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
    }
}