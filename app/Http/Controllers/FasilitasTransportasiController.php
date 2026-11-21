<?php

namespace App\Http\Controllers;

use App\Models\FasilitasTransportasi;
use App\Models\KabKota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class FasilitasTransportasiController extends Controller
{
    private function pastikanAdmin(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
    }

    public function index(Request $request): View
    {
        $this->pastikanAdmin();

        $query = FasilitasTransportasi::with([
            'kabKota.provinsi',
            'moda',
        ]);

        // FILTER PENCARIAN NAMA FASILITAS
        if ($request->filled('cari')) {
            $query->where(
                'nama',
                'like',
                '%' . trim((string) $request->input('cari')) . '%'
            );
        }

        // FILTER MODA TRANSPORTASI
        if (in_array(
            (string) $request->input('moda'),
            ['1', '2', '3', '4'],
            true
        )) {
            $query->where(
                'id_moda',
                (int) $request->input('moda')
            );
        }

        // FILTER SUMBER DATA
        if ($request->filled('sumber_data')) {
            $query->where(
                'sumber_data',
                (string) $request->input('sumber_data')
            );
        }

        // DAFTAR SUMBER DATA UNTUK DROPDOWN
        $sumberData = FasilitasTransportasi::query()
            ->whereNotNull('sumber_data')
            ->where('sumber_data', '!=', '')
            ->distinct()
            ->orderBy('sumber_data')
            ->pluck('sumber_data');

        return view('fasilitas.index', [
            'fasilitas' => $query
                ->orderBy('nama')
                ->orderBy('id_fasilitas')
                ->paginate(20)
                ->withQueryString(),

            'sumberData' => $sumberData,
        ]);
    }

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

            'kabKota' => KabKota::with('provinsi')
                ->orderBy('nama_kab_kota')
                ->get(),
        ]);
    }

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

    public function destroy(
        FasilitasTransportasi $fasilitas
    ): RedirectResponse {
        $this->pastikanAdmin();

        // Data hanya dinonaktifkan, tidak dihapus permanen.
        $fasilitas->update([
            'aktif' => false,
        ]);

        return redirect()
            ->route('fasilitas.index')
            ->with('success', 'Fasilitas telah dinonaktifkan.');
    }

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
