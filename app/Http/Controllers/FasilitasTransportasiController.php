<?php

namespace App\Http\Controllers;

use App\Models\FasilitasTransportasi;
use App\Models\KabKota;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FasilitasTransportasiController extends Controller
{
    private function pastikanAdmin(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
    }

    /*
    |--------------------------------------------------------------------------
    | DAFTAR FASILITAS TRANSPORTASI
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $this->pastikanAdmin();

        /*
         * Mengambil filter dari URL.
         */
        $filter = [
            'cari' => trim((string) $request->query('cari', '')),

            'moda' => (string) $request->query('moda', ''),

            'sumber_data' => (string) $request->query('sumber_data', ''),

            'wilayah' => (string) $request->query('wilayah', ''),

            'provinsi' => (string) $request->query('provinsi', ''),

            'kab_kota' => (string) $request->query('kab_kota', ''),
        ];

        /*
        |--------------------------------------------------------------------------
        | QUERY UTAMA
        |--------------------------------------------------------------------------
        */

        $query = FasilitasTransportasi::query()
            ->with([
                'kabKota.provinsi',
                'kabKota.wilayah',
                'moda',
            ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER NAMA FASILITAS
        |--------------------------------------------------------------------------
        */

        if ($filter['cari'] !== '') {
            $query->where(
                'nama',
                'like',
                '%' . addcslashes($filter['cari'], '\\%_') . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER MODA TRANSPORTASI
        |--------------------------------------------------------------------------
        */

        if (in_array($filter['moda'], ['1', '2', '3', '4'], true)) {
            $query->where(
                'id_moda',
                (int) $filter['moda']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER SUMBER DATA
        |--------------------------------------------------------------------------
        */

        if ($filter['sumber_data'] !== '') {
            $query->where(
                'sumber_data',
                $filter['sumber_data']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER WILAYAH REGIONAL
        |--------------------------------------------------------------------------
        */

        if (ctype_digit($filter['wilayah'])) {
            $query->whereHas('kabKota', function ($q) use ($filter) {
                $q->where(
                    'id_wilayah',
                    (int) $filter['wilayah']
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER PROVINSI
        |--------------------------------------------------------------------------
        */

        if (ctype_digit($filter['provinsi'])) {
            $query->whereHas('kabKota', function ($q) use ($filter) {
                $q->where(
                    'id_provinsi',
                    (int) $filter['provinsi']
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER KABUPATEN/KOTA
        |--------------------------------------------------------------------------
        */

        if ($filter['kab_kota'] === 'belum') {
            /*
             * Menampilkan fasilitas yang belum memiliki
             * kabupaten/kota.
             */
            $query->whereNull('id_kab_kota');

        } elseif (ctype_digit($filter['kab_kota'])) {
            $query->where(
                'id_kab_kota',
                (int) $filter['kab_kota']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATA UNTUK DROPDOWN
        |--------------------------------------------------------------------------
        */

        $wilayah = Wilayah::query()
            ->orderBy('nama_wilayah')
            ->get();

        /*
         * Mengambil kabupaten/kota lengkap dengan
         * provinsi dan wilayah regional.
         *
         * Data ini juga digunakan untuk membuat
         * dropdown provinsi dan kabupaten/kota bertingkat.
         */
        $kabKota = KabKota::query()
            ->with([
                'provinsi',
                'wilayah',
            ])
            ->orderBy('nama_kab_kota')
            ->get();

        /*
         * Mengambil daftar provinsi unik dari
         * relasi kabupaten/kota.
         */
        $provinsi = $kabKota
            ->pluck('provinsi')
            ->filter()
            ->unique('id_provinsi')
            ->sortBy('nama_provinsi')
            ->values();

        /*
         * Daftar sumber data.
         */
        $sumberData = FasilitasTransportasi::query()
            ->whereNotNull('sumber_data')
            ->where('sumber_data', '!=', '')
            ->distinct()
            ->orderBy('sumber_data')
            ->pluck('sumber_data');

        /*
         * Menentukan apakah filter sedang digunakan.
         */
        $adaFilter = collect($filter)
            ->contains(fn ($nilai) => $nilai !== '');

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $fasilitas = $query
            ->orderBy('nama')
            ->orderBy('id_fasilitas')
            ->paginate(20)
            ->withQueryString();

        return view('fasilitas.index', [
            'fasilitas' => $fasilitas,

            'filter' => $filter,

            'wilayah' => $wilayah,

            'provinsi' => $provinsi,

            'kabKotaFilter' => $kabKota,

            'sumberData' => $sumberData,

            'adaFilter' => $adaFilter,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH DAN EDIT
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $this->pastikanAdmin();

        return $this->form(null);
    }

    public function edit(FasilitasTransportasi $fasilitas): View
    {
        $this->pastikanAdmin();

        return $this->form($fasilitas);
    }

    private function form(?FasilitasTransportasi $fasilitas): View
    {
        return view('fasilitas.form', [
            'fasilitas' => $fasilitas,

            'kabKota' => KabKota::query()
                ->with('provinsi')
                ->orderBy('nama_kab_kota')
                ->get(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN FASILITAS
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanAdmin();

        FasilitasTransportasi::create(
            $this->validasi($request) + [
                'sumber_data' => 'Input Admin',
            ]
        );

        return redirect()
            ->route('fasilitas.index')
            ->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | PERBARUI FASILITAS
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        FasilitasTransportasi $fasilitas
    ): RedirectResponse {
        $this->pastikanAdmin();

        $fasilitas->update(
            $this->validasi($request)
        );

        return redirect()
            ->route('fasilitas.index')
            ->with('success', 'Fasilitas berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | NONAKTIFKAN FASILITAS
    |--------------------------------------------------------------------------
    */

    public function destroy(
        FasilitasTransportasi $fasilitas
    ): RedirectResponse {
        $this->pastikanAdmin();

        /*
         * Data tidak dihapus permanen.
         */
        $fasilitas->update([
            'aktif' => false,
        ]);

        return redirect()
            ->route('fasilitas.index')
            ->with('success', 'Fasilitas telah dinonaktifkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */

    private function validasi(Request $request): array
    {
        return $request->validate([
            'id_moda' => [
                'required',
                'integer',
                Rule::in([1, 2, 3, 4]),
            ],

            'id_kab_kota' => [
                'nullable',
                'integer',
                'exists:kab_kota,id_kab_kota',
            ],

            'nama' => [
                'required',
                'string',
                'max:150',
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'aktif' => [
                'required',
                'boolean',
            ],
        ]);
    }
}
