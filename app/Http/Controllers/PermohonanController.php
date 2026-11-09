<?php
namespace App\Http\Controllers;

use App\Models\DokumenPengajuan;
use App\Models\KabKota;
use App\Models\Provinsi;
use App\Models\RuteSubsidi;
use App\Models\VerifikasiPengajuan;
use App\Support\Paginasi;
use App\Support\Permohonan;
use App\Support\StatusPengajuan;
use App\Support\Tanggal;
use App\Support\XlsxExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Halaman Permohonan untuk user Moda. Setiap akun Moda hanya melihat laporan yang dipilih
 * Pemda dengan moda yang sama seperti cakupan moda akun (users.id_moda = rute_subsidi.id_moda).
 */
class PermohonanController extends Controller
{
    private const PER_HALAMAN = [10, 25, 50];

    public const CHECKLIST = [
        'dokumen' => ['Kelengkapan dokumen', 'Seluruh surat wajib tersedia, terbaca, ditandatangani, dan memiliki lampiran pendukung.'],
        'data'    => ['Konsistensi data', 'Identitas instansi, nomor laporan, wilayah, serta data pada seluruh dokumen saling sesuai.'],
        'teknis'  => ['Kelayakan teknis', 'Rencana layanan, trayek, sarana, jadwal, dan proyeksi kebutuhan dinilai dapat dilaksanakan.'],
        'syarat'  => ['Kesesuaian persyaratan', 'Pengajuan memenuhi kriteria penerima subsidi transportasi dan ketentuan program yang berlaku.'],
    ];

    private const KEPUTUSAN = [
        'setuju' => ['status' => StatusPengajuan::V1,      'hasil' => 'Disetujui',    'pesan' => 'disetujui'],
        'revisi' => ['status' => StatusPengajuan::REVISI,  'hasil' => 'Perlu Revisi', 'pesan' => 'dikembalikan untuk direvisi'],
        'tolak'  => ['status' => StatusPengajuan::DITOLAK, 'hasil' => 'Ditolak',      'pesan' => 'ditolak'],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $f = $this->filter($request);

        $dasar = Permohonan::untukModa($user);
        $periode = (clone $dasar)->pluck('created_at')->filter()
            ->map(fn ($d) => $d->format('Y-m'))->unique()->sortDesc()->values();

        // Kartu ringkasan mengikuti filter periode
        $hitung = ['menunggu' => 0, 'disetujui' => 0, 'revisi' => 0, 'ditolak' => 0];
        $kartu = Permohonan::untukModa($user);
        $this->terapkanPeriode($kartu, $f['periode']);
        $kartu->select('status_pengajuan', DB::raw('COUNT(*) AS n'))->groupBy('status_pengajuan')->get()
            ->each(function ($r) use (&$hitung) {
                $grup = StatusPengajuan::grup($r->status_pengajuan);
                if (isset($hitung[$grup])) {
                    $hitung[$grup] += (int) $r->n;
                }
            });

        $data = $this->query($user, $f)->paginate($f['per'])->withQueryString();

        $provinsi = Provinsi::whereIn(
            'id_provinsi',
            KabKota::select('id_provinsi')->whereIn('id_kab_kota', RuteSubsidi::select('id_kab_kota_asal')->where('id_moda', $user->id_moda ?? 0))
        )->orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']);

        // Chip "Filter aktif": klik tanda silang untuk melepas satu filter
        $tanpa = fn (array $kunci) => route('permohonan.index', array_filter(
            Arr::except($request->query(), array_merge($kunci, ['page'])),
            fn ($v) => $v !== null && $v !== ''
        ));
        $chips = [];
        if ($f['periode']) {
            $chips[] = ['label' => Tanggal::bulanTahun($f['periode']), 'url' => $tanpa(['periode'])];
        }
        if ($f['provinsi']) {
            $chips[] = ['label' => $provinsi->firstWhere('id_provinsi', $f['provinsi'])->nama_provinsi ?? 'Provinsi', 'url' => $tanpa(['provinsi'])];
        }
        if ($f['status']) {
            $chips[] = ['label' => StatusPengajuan::LABEL_GRUP[$f['status']], 'url' => $tanpa(['status'])];
        }
        if ($f['q'] !== '') {
            $chips[] = ['label' => '"' . $f['q'] . '"', 'url' => $tanpa(['q'])];
        }

        return view('permohonan.index', [
            'data'         => $data,
            'filter'       => $f,
            'kpi'          => ['total' => array_sum($hitung)] + $hitung,
            'labelPeriode' => $f['periode'] ? 'Periode ' . Tanggal::bulanTahun($f['periode']) : 'Semua periode',
            'provinsi'     => $provinsi,
            'statusOpsi'   => StatusPengajuan::LABEL_GRUP,
            'periodeOpsi'  => $periode->mapWithKeys(fn ($ym) => [$ym => Tanggal::bulanTahun($ym)]),
            'perOpsi'      => self::PER_HALAMAN,
            'chips'        => $chips,
            'halaman'      => Paginasi::nomor($data->currentPage(), $data->lastPage()),
            'punyaCakupan' => $user->id_moda !== null,
        ]);
    }

    public function export(Request $request)
    {
        if (! class_exists(\ZipArchive::class)) {
            return back()->with('error', 'Ekspor Excel membutuhkan ekstensi PHP "zip". Aktifkan extension=zip di php.ini lalu restart server.');
        }

        $rows = [];
        foreach ($this->query($request->user(), $this->filter($request))->get() as $i => $r) {
            $rows[] = [
                $i + 1,
                $r->nomor_laporan,
                $r->kabKotaAsal->nama_lengkap,
                $r->kabKotaAsal->provinsi->nama_provinsi,
                $r->kabKotaAsal->wilayah->nama_wilayah,
                trim($r->tanggal_lengkap . ' ' . $r->jam_pengajuan),
                $r->label_status_moda,
                // data yang diinput Pemda
                $r->klasifikasi_label,
                $r->keterangan_pemda,
                $r->potensi_ringkas,
                implode(', ', $r->sektor_terdeteksi),
                implode(', ', $r->tujuan_daftar),
                $r->jumlah_tujuan,
            ];
        }

        $path = XlsxExporter::buat(
            ['No', 'Nomor Laporan', 'Pemda', 'Provinsi', 'Wilayah', 'Tanggal Pengajuan', 'Status',
             'Klasifikasi Wilayah', 'Keterangan Daerah', 'Potensi Unggulan Daerah', 'Sektor Potensi', 'Kebutuhan Konektivitas / Rute Tujuan', 'Jumlah Tujuan'],
            $rows,
            'Permohonan',
            [6, 18, 34, 24, 18, 30, 24, 22, 40, 60, 36, 50, 14]
        );

        return response()->download(
            $path,
            'permohonan-' . now()->format('Ymd-His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /** Ekspor satu laporan (data yang diinput Pemda, dokumen, dan riwayat peninjauan) ke Excel. */
    public function exportDetail(Request $request, RuteSubsidi $rute)
    {
        $this->pastikanMilik($request->user(), $rute);

        if (! class_exists(\ZipArchive::class)) {
            return back()->with('error', 'Ekspor Excel membutuhkan ekstensi PHP "zip". Aktifkan extension=zip di php.ini lalu restart server.');
        }

        $rute->load('pemda.user', 'kabKotaAsal.provinsi', 'kabKotaAsal.wilayah', 'moda', 'dokumen', 'verifikasi.verifikator');
        $p = $rute->pemda;
        $k = $rute->kabKotaAsal;
        $pengaju = $p->user;

        $baris = [];
        $tebal = [];
        $bagian = function (string $judul) use (&$baris, &$tebal) {
            if ($baris) {
                $baris[] = ['', ''];
            }
            $tebal[] = count($baris);
            $baris[] = [$judul, ''];
        };
        $isi = function (string $label, $nilai) use (&$baris) {
            $baris[] = [$label, $nilai];
        };

        $bagian('LAPORAN');
        $isi('Nomor laporan', $rute->nomor_laporan);
        $isi('Status', $rute->label_status_moda);
        $isi('Tanggal pengajuan', trim($rute->tanggal_lengkap . ' ' . $rute->jam_pengajuan));
        $isi('Moda transportasi', $rute->moda->nama_moda ?? '-');

        $bagian('PEMOHON');
        $isi('Instansi', $pengaju->instansi ?? ('Pemerintah Daerah ' . $k->nama_lengkap));
        $isi('Nama pengaju', $pengaju->full_name ?? '-');
        $isi('Email pengaju', $pengaju->email ?? '-');

        $bagian('WILAYAH');
        $isi('Provinsi', $k->provinsi->nama_provinsi);
        $isi('Kabupaten / Kota', $k->nama_lengkap);
        $isi('Wilayah regional', $k->wilayah->nama_wilayah);

        $bagian('DATA YANG DIINPUT PEMDA');
        $isi('Klasifikasi wilayah', $rute->klasifikasi_label);
        $isi('Keterangan daerah', $rute->keterangan_pemda ?: '-');
        $isi('Potensi unggulan daerah', $rute->potensi_pemda ?: '-');
        $isi('Sektor potensi', implode(', ', $rute->sektor_terdeteksi) ?: '-');
        $isi('Kebutuhan konektivitas / rute tujuan', $rute->kebutuhan_konektivitas ?: '-');
        $isi('Jumlah tujuan', $rute->jumlah_tujuan ?? '-');

        $bagian('DOKUMEN SURAT');
        foreach ($rute->dokumen as $d) {
            $isi($d->jenis_dokumen, ($d->nomor_surat ? 'No. ' . $d->nomor_surat . ' | ' : '') . $d->nama_file . ' (' . ($this->ukuran($d->path_file) ?? 'berkas tidak ditemukan') . ')');
        }

        $bagian('RIWAYAT PENINJAUAN');
        $isi('Laporan diajukan', trim(($pengaju->full_name ?? '-') . ', ' . $rute->tanggal_lengkap . ' ' . $rute->jam_pengajuan));
        foreach ($rute->verifikasi->sortBy('id_verifikasi_pengajuan') as $v) {
            $isi(
                $v->tahap_verifikasi . ': ' . $v->status_verifikasi,
                'Oleh ' . ($v->verifikator->full_name ?? '-') . ', ' . Tanggal::panjang($v->tanggal_verifikasi) . ' ' . Tanggal::jam($v->tanggal_verifikasi)
                    . ($v->catatan_verifikasi ? ' | Catatan: ' . $v->catatan_verifikasi : '')
            );
        }

        $path = XlsxExporter::buat(['Data', 'Isi'], $baris, 'Review Laporan', [38, 100], $tebal);

        return response()->download(
            $path,
            $rute->nomor_laporan . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public function show(Request $request, RuteSubsidi $rute)
    {
        $this->pastikanMilik($request->user(), $rute);
        $rute->load('pemda.user.role', 'kabKotaAsal.provinsi', 'kabKotaAsal.wilayah', 'moda', 'dokumen', 'verifikasi.verifikator', 'rekomendasi');

        $riwayat = $rute->verifikasi->sortBy('id_verifikasi_pengajuan')->values();

        return view('permohonan.review', [
            'rute'      => $rute,
            'pemda'     => $rute->pemda,
            'pengaju'   => $rute->pemda->user,
            'berkas'    => $rute->dokumen->map(fn ($d) => [
                'model'  => $d,
                'jenis'  => strtoupper(pathinfo($d->nama_file, PATHINFO_EXTENSION)),
                'ukuran' => $this->ukuran($d->path_file),
            ]),
            'riwayat'   => $riwayat,
            'terakhir'  => $riwayat->last(),
            'checklist' => self::CHECKLIST,
        ]);
    }

    public function keputusan(Request $request, RuteSubsidi $rute)
    {
        $user = $request->user();
        $this->pastikanMilik($user, $rute);

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(array_keys(self::KEPUTUSAN))],
            'catatan'   => ['nullable', 'string', 'max:1000'],
            'periksa'   => ['nullable', 'array'],
            'periksa.*' => [Rule::in(array_keys(self::CHECKLIST))],
        ], [
            'keputusan.required' => 'Pilih keputusan terlebih dahulu.',
            'keputusan.in'       => 'Keputusan tidak valid.',
            'catatan.max'        => 'Catatan maksimal :max karakter.',
        ]);

        $catatan = trim((string) ($data['catatan'] ?? ''));

        if (in_array($data['keputusan'], ['revisi', 'tolak'], true) && $catatan === '') {
            return back()->withInput()->withErrors(['catatan' => 'Catatan wajib diisi agar instansi pemohon mengetahui alasan keputusan ini.']);
        }
        if ($data['keputusan'] === 'setuju' && count(array_unique($data['periksa'] ?? [])) < count(self::CHECKLIST)) {
            return back()->withInput()->withErrors(['periksa' => 'Konfirmasi seluruh pemeriksaan pada checklist sebelum menyetujui laporan.']);
        }

        $putusan = self::KEPUTUSAN[$data['keputusan']];

        DB::transaction(function () use ($rute, $user, $putusan, $catatan) {
            $baru = RuteSubsidi::where('id_rute_subsidi', $rute->id_rute_subsidi)->lockForUpdate()->firstOrFail();

            if (StatusPengajuan::grup($baru->status_pengajuan) !== 'menunggu') {
                throw ValidationException::withMessages(['keputusan' => 'Laporan ini sudah ditinjau dan keputusannya tidak dapat diubah.']);
            }

            $baru->update(['status_pengajuan' => $putusan['status']]);

            VerifikasiPengajuan::create([
                'id_rute_subsidi'    => $baru->id_rute_subsidi,
                'id_user'            => $user->id_user,
                'tahap_verifikasi'   => StatusPengajuan::TAHAP_MODA,
                'status_verifikasi'  => $putusan['hasil'],
                'catatan_verifikasi' => $catatan !== '' ? $catatan : null,
                'tanggal_verifikasi' => now(),
            ]);
        });

        return redirect()->route('permohonan.index')
            ->with('success', 'Laporan ' . $rute->nomor_laporan . ' ' . $putusan['pesan'] . '.');
    }

    /** Pratinjau (default) atau unduh (?unduh=1) dokumen laporan. */
    public function dokumen(Request $request, RuteSubsidi $rute, DokumenPengajuan $dokumen)
    {
        $this->pastikanMilik($request->user(), $rute);
        abort_unless((int) $dokumen->id_rute_subsidi === (int) $rute->id_rute_subsidi, 404);
        abort_unless(Storage::disk('local')->exists($dokumen->path_file), 404);

        return $request->boolean('unduh')
            ? Storage::disk('local')->download($dokumen->path_file, $dokumen->nama_file)
            : Storage::disk('local')->response($dokumen->path_file, $dokumen->nama_file);
    }

    // ------------------------------------------------------------------

    /** Laporan di luar moda akun dijawab 404 agar keberadaannya tidak terlihat. */
    private function pastikanMilik($user, RuteSubsidi $rute): void
    {
        abort_unless($user->id_moda !== null && (int) $rute->id_moda === (int) $user->id_moda, 404);
    }

    private function filter(Request $r): array
    {
        $prov = (string) $r->query('provinsi', '');
        $status = (string) $r->query('status', '');
        $periode = (string) $r->query('periode', '');
        $per = (int) $r->query('per', self::PER_HALAMAN[0]);

        return [
            'q'        => trim((string) $r->query('q', '')),
            'provinsi' => ctype_digit($prov) ? (int) $prov : null,
            'status'   => array_key_exists($status, StatusPengajuan::LABEL_GRUP) ? $status : null,
            'periode'  => preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode) ? $periode : null,
            'urut'     => $r->query('urut') === 'terlama' ? 'terlama' : 'terbaru',
            'per'      => in_array($per, self::PER_HALAMAN, true) ? $per : self::PER_HALAMAN[0],
        ];
    }

    private function terapkanPeriode($query, ?string $periode): void
    {
        if ($periode) {
            $query->whereYear('created_at', (int) substr($periode, 0, 4))
                  ->whereMonth('created_at', (int) substr($periode, 5, 2));
        }
    }

    private function query($user, array $f)
    {
        $q = Permohonan::untukModa($user)->with('kabKotaAsal.provinsi', 'kabKotaAsal.wilayah', 'pemda.user');

        if ($f['q'] !== '') {
            $like = '%' . addcslashes($f['q'], '\\%_') . '%';
            $q->where(function ($w) use ($f, $like) {
                $w->whereHas('kabKotaAsal', function ($k) use ($like) {
                    $k->where('nama_kab_kota', 'like', $like)
                      ->orWhereHas('provinsi', fn ($p) => $p->where('nama_provinsi', 'like', $like));
                })->orWhereHas('pemda.user', function ($u) use ($like) {
                    $u->where('instansi', 'like', $like)->orWhere('full_name', 'like', $like);
                });
                // Nomor laporan: "LP-2026-00482", "2026-00482", atau "482"
                if (preg_match('/^(?:LP-?)?(?:\d{4}-?)?(\d{1,9})$/i', $f['q'], $m)) {
                    $w->orWhere('id_rute_subsidi', (int) $m[1]);
                }
            });
        }
        if ($f['provinsi']) {
            $q->whereHas('kabKotaAsal', fn ($k) => $k->where('id_provinsi', $f['provinsi']));
        }
        if ($f['status']) {
            $q->whereIn('status_pengajuan', StatusPengajuan::GRUP[$f['status']]);
        }
        $this->terapkanPeriode($q, $f['periode']);

        $arah = $f['urut'] === 'terlama' ? 'asc' : 'desc';

        return $q->orderBy('created_at', $arah)->orderBy('id_rute_subsidi', $arah);
    }

    private function ukuran(string $path): ?string
    {
        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            return null;
        }
        $byte = $disk->size($path);

        return $byte >= 1048576
            ? number_format($byte / 1048576, 1, ',', '.') . ' MB'
            : number_format(max($byte, 1) / 1024, 0, ',', '.') . ' KB';
    }
}
