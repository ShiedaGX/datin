<?php
namespace App\Http\Controllers;

use App\Models\DataPemda;
use Illuminate\Http\Request;

class DataWilayahController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $data = DataPemda::query()
            ->with('kabKota.provinsi', 'kabKota.wilayah')
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('kabKota', function ($k) use ($q) {
                    $k->where('nama_kab_kota', 'like', "%{$q}%")
                      ->orWhereHas('provinsi', fn ($p) => $p->where('nama_provinsi', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('id_pemda')
            ->simplePaginate(15)
            ->withQueryString();

        return view('data-wilayah.index', compact('data'));
    }
}
