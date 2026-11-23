<?php
namespace App\Http\Controllers;

use App\Models\DataPemda;
use App\Models\KabKota;
use App\Models\Provinsi;
use App\Models\Wilayah;
use App\Models\RuteSubsidi;
use App\Support\StatusPengajuan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const URUTAN_WILAYAH = ['Papua', 'Sulawesi', 'Maluku', 'Nusa Tenggara', 'Kalimantan', 'Sumatera', 'Jawa-Bali'];

    private const WARNA_WILAYAH = [
        'Papua' => '#1e3a8a', 'Sulawesi' => '#0d9488', 'Maluku' => '#d97706', 'Nusa Tenggara' => '#0f766e',
        'Kalimantan' => '#64748b', 'Sumatera' => '#94a3b8', 'Jawa-Bali' => '#475569',
    ];

    private const WARNA_KLASIFIKASI = [
        'Prioritas Strategis' => '#e11d48', 'Prioritas' => '#d97706', 'Strategis' => '#0d9488', 'Belum diklasifikasi' => '#94a3b8',
    ];

    public function index()
    {
        $totalPemda = DataPemda::count();

        // ---- Kartu ringkasan ----
        $kpi = [
            'provinsi'  => Provinsi::count(),
            'wilayah'   => Wilayah::count(),
            'kab_kota'  => KabKota::count(),
            'sektor'    => count(\App\Support\Sektor::daftar()),
        ];

        // ---- Distribusi per wilayah (jumlah kab/kota) ----
        $wilayah = Wilayah::withCount('kabKota')->get()
            ->sortBy(function ($w) {
                $i = array_search($w->nama_wilayah, self::URUTAN_WILAYAH, true);
                return $i === false ? 99 : $i;
            })->values();
        $maks = max(1, (int) $wilayah->max('kab_kota_count'));
        $distribusi = $wilayah->map(fn ($w) => [
            'nama'   => $w->nama_wilayah,
            'jumlah' => $w->kab_kota_count,
            'lebar'  => min(100, $w->kab_kota_count / ($maks * 1.25) * 100),   // sisakan ruang di ujung bar
            'warna'  => self::WARNA_WILAYAH[$w->nama_wilayah] ?? '#64748b',
        ]);

        // ---- Klasifikasi wilayah (donut) ----
        $hitung = array_fill_keys(array_keys(self::WARNA_KLASIFIKASI), 0);
        DataPemda::select('klasifikasi_pemda', DB::raw('COUNT(*) AS n'))
            ->groupBy('klasifikasi_pemda')->get()
            ->each(function ($r) use (&$hitung) {
                $hitung[DataPemda::normalisasiKlasifikasi($r->klasifikasi_pemda)] += (int) $r->n;
            });

        $donat = [];
        $awal = 0.0;
        foreach ($hitung as $label => $n) {
            if ($n === 0 || $totalPemda === 0) {
                continue;
            }
            $arc = $n / $totalPemda * 100;
            $donat[] = [
                'label'  => $label,
                'jumlah' => $n,
                'persen' => (int) round($arc),
                'warna'  => self::WARNA_KLASIFIKASI[$label],
                'arc'    => $arc,
                'awal'   => $awal,
            ];
            $awal += $arc;
        }

        // ---- 5 entri terbaru ----
        $terbaru = DataPemda::with('kabKota.provinsi', 'kabKota.wilayah')
            ->terbaru()->limit(5)->get();

        // Statistik konektivitas: mengikuti cakupan hak akses dan definisi
        // hubungan yang benar-benar tampil di Peta Konektivitas.
        $pengguna = auth()->user();
        $queryKonektivitas = RuteSubsidi::query()
            ->where('status_pengajuan', StatusPengajuan::FINAL);

        if ($pengguna->isModa()) {
            $pengguna->id_moda === null
                ? $queryKonektivitas->whereRaw('1 = 0')
                : $queryKonektivitas->where('id_moda', $pengguna->id_moda);
        } elseif (! $pengguna->isSuperAdmin() && ! $pengguna->isIntram()) {
            $queryKonektivitas->whereHas('pemda', fn ($q) => $q->where('id_user', $pengguna->id_user));
        }

        // Pemetaan sektor mengikuti Peta Konektivitas: berdasarkan nama komoditas.
        // Nama yang terdapat di beberapa sektor tidak dipaksakan ke satu sektor.
        $sektorPerKomoditas = DB::table('master_komoditas as k')
            ->join('master_sektor as s', 's.id_sektor', '=', 'k.id_sektor')
            ->get(['k.nama_komoditas', 's.nama_sektor'])
            ->groupBy(fn ($row) => mb_strtolower(trim((string) $row->nama_komoditas)))
            ->map(fn ($rows) => $rows->pluck('nama_sektor')->unique()->values()->all());

        $peringkatSektor = [];
        $peringkatKomoditas = [];
        $jumlahHubungan = 0;
        $daerahAsal = [];
        $daerahTujuan = [];
        $distribusiModa = [];
        $peringkatAsal = [];

        $punyaKoordinat = static fn ($kab): bool => $kab !== null
            && is_numeric($kab->latitude)
            && is_numeric($kab->longitude)
            && abs((float) $kab->latitude) <= 90
            && abs((float) $kab->longitude) <= 180;

        $queryKonektivitas->with(['moda', 'kabKotaAsal', 'tujuan', 'detailKomoditasTujuan'])
            ->orderBy('id_rute_subsidi')
            ->chunk(150, function ($pengajuan) use (
                &$jumlahHubungan, &$daerahAsal, &$daerahTujuan,
                &$distribusiModa, &$peringkatAsal, &$peringkatSektor, &$peringkatKomoditas,
                $sektorPerKomoditas, $punyaKoordinat
            ) {
                foreach ($pengajuan as $rute) {
                    $asal = $rute->kabKotaAsal;
                    if (! $punyaKoordinat($asal)) {
                        continue;
                    }

                    $namaModa = $rute->moda?->nama_moda ?: (match ((int) $rute->id_moda) {
                        1 => 'Darat', 2 => 'Laut', 3 => 'Udara', 4 => 'Kereta Api',
                        default => 'Moda #' . $rute->id_moda,
                    });

                    foreach ($rute->tujuan as $tujuan) {
                        if (! $punyaKoordinat($tujuan)) {
                            continue;
                        }

                        // Satu hubungan = satu pasangan laporan dan tujuan,
                        // bukan satu laporan maupun satu komoditas.
                        $jumlahHubungan++;
                        $daerahAsal[$asal->id_kab_kota] = true;
                        $daerahTujuan[$tujuan->id_kab_kota] = true;
                        $distribusiModa[$namaModa] = ($distribusiModa[$namaModa] ?? 0) + 1;

                        $idAsal = $asal->id_kab_kota;
                        if (! isset($peringkatAsal[$idAsal])) {
                            $peringkatAsal[$idAsal] = [
                                'nama' => $asal->nama_lengkap,
                                'jumlah' => 0,
                            ];
                        }
                        $peringkatAsal[$idAsal]['jumlah']++;

                        // Hitung setiap entri komoditas hanya pada pasangan
                        // laporan-tujuan yang valid, sama seperti Peta Konektivitas.
                        foreach ($rute->detailKomoditasTujuan as $detail) {
                            if ((int) $detail->id_kab_kota_tujuan !== (int) $tujuan->id_kab_kota) {
                                continue;
                            }
                            $nama = trim((string) $detail->nama_komoditas);
                            if ($nama === '') {
                                continue;
                            }
                            $key = mb_strtolower($nama);
                            if (! isset($peringkatKomoditas[$key])) {
                                $peringkatKomoditas[$key] = ['nama' => $nama, 'jumlah' => 0];
                            }
                            $peringkatKomoditas[$key]['jumlah']++;

                            $sektor = $sektorPerKomoditas->get($key, []);
                            $namaSektor = count($sektor) === 1 ? $sektor[0] : 'Belum terpetakan / ambigu';
                            $peringkatSektor[$namaSektor] = ($peringkatSektor[$namaSektor] ?? 0) + 1;
                        }
                    }
                }
            });

        arsort($distribusiModa);
        uasort($peringkatAsal, static fn ($a, $b) =>
            ($b['jumlah'] <=> $a['jumlah']) ?: strcmp($a['nama'], $b['nama'])
        );

        arsort($peringkatSektor);
        uasort($peringkatKomoditas, static fn ($a, $b) =>
            ($b['jumlah'] <=> $a['jumlah']) ?: strcmp($a['nama'], $b['nama'])
        );

        $konektivitas = [
            'hubungan' => $jumlahHubungan,
            'asal' => count($daerahAsal),
            'tujuan' => count($daerahTujuan),
            'wilayah_unik' => count($daerahAsal + $daerahTujuan),
            'moda' => collect($distribusiModa)->map(fn ($jumlah, $nama) => [
                'nama' => $nama,
                'jumlah' => $jumlah,
                'persen' => $jumlahHubungan ? round($jumlah / $jumlahHubungan * 100, 1) : 0,
            ])->values()->all(),
            'top_asal' => array_slice(array_values($peringkatAsal), 0, 5),
            'top_sektor' => collect(array_slice($peringkatSektor, 0, 5, true))
                ->map(fn ($jumlah, $nama) => ['nama' => $nama, 'jumlah' => $jumlah])
                ->values()->all(),
            'top_komoditas' => array_slice(array_values($peringkatKomoditas), 0, 5),
        ];

        return view('dashboard', compact('kpi', 'distribusi', 'donat', 'totalPemda', 'terbaru', 'konektivitas'));
    }
}
