<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterKomoditas;
use App\Models\MasterSektor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Master sektor & komoditas (tabel master_sektor dan master_komoditas), khusus Super Admin.
 * Perubahan langsung dipakai form Input Data dan deteksi sektor (cache dibersihkan oleh model).
 */
class SektorController extends Controller
{
    public function index()
    {
        $sektor = MasterSektor::with(['komoditas' => fn ($q) => $q->orderBy('nama_komoditas')])
            ->orderBy('urutan')->orderBy('id_sektor')->get();

        // jumlah pengajuan yang memakai tiap sektor / komoditas (rute_subsidi_potensi)
        $pakaiSektor = DB::table('rute_subsidi_potensi')->select('id_sektor', DB::raw('COUNT(DISTINCT id_rute_subsidi) AS n'))
            ->groupBy('id_sektor')->pluck('n', 'id_sektor');
        $pakaiKomoditas = DB::table('rute_subsidi_potensi')->whereNotNull('id_komoditas')
            ->select('id_komoditas', DB::raw('COUNT(DISTINCT id_rute_subsidi) AS n'))
            ->groupBy('id_komoditas')->pluck('n', 'id_komoditas');

        return view('admin.sektor.index', [
            'sektor'         => $sektor,
            'pakaiSektor'    => $pakaiSektor,
            'pakaiKomoditas' => $pakaiKomoditas,
            'urutanBerikut'  => ((int) MasterSektor::max('urutan')) + 1,
        ]);
    }

    // ---- Sektor ----

    public function simpanSektor(Request $request)
    {
        $v = $this->validasiSektor($request);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        MasterSektor::create([
            'nama_sektor' => $v['nama_sektor'],
            'urutan'      => $v['urutan'] ?? ((int) MasterSektor::max('urutan')) + 1,
        ]);

        return back()->with('success', 'Sektor "' . $v['nama_sektor'] . '" ditambahkan.');
    }

    public function ubahSektor(Request $request, MasterSektor $sektor)
    {
        $v = $this->validasiSektor($request, $sektor);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        $sektor->update(['nama_sektor' => $v['nama_sektor'], 'urutan' => $v['urutan'] ?? $sektor->urutan]);

        return back()->with('success', 'Sektor "' . $sektor->nama_sektor . '" disimpan.');
    }

    public function hapusSektor(MasterSektor $sektor)
    {
        $dipakai = DB::table('rute_subsidi_potensi')->where('id_sektor', $sektor->id_sektor)->distinct()->count('id_rute_subsidi');
        if ($dipakai > 0) {
            return back()->with('error', 'Sektor "' . $sektor->nama_sektor . '" dipakai ' . $dipakai . ' pengajuan sehingga tidak dapat dihapus.');
        }
        $komoditasDipakai = DB::table('rute_subsidi_potensi')
            ->whereIn('id_komoditas', $sektor->komoditas()->pluck('id_komoditas'))->distinct()->count('id_rute_subsidi');
        if ($komoditasDipakai > 0) {
            return back()->with('error', 'Komoditas pada sektor "' . $sektor->nama_sektor . '" dipakai ' . $komoditasDipakai . ' pengajuan sehingga sektor tidak dapat dihapus.');
        }

        $nama = $sektor->nama_sektor;
        $jumlah = $sektor->komoditas()->count();
        $sektor->delete();   // komoditas ikut terhapus (cascade)

        return back()->with('success', 'Sektor "' . $nama . '"' . ($jumlah ? " dan {$jumlah} komoditasnya" : '') . ' dihapus.');
    }

    // ---- Komoditas ----

    public function simpanKomoditas(Request $request, MasterSektor $sektor)
    {
        $v = $this->validasiKomoditas($request, $sektor);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        $sektor->komoditas()->create(['nama_komoditas' => $v['nama_komoditas']]);

        return back()->with('success', 'Komoditas "' . $v['nama_komoditas'] . '" ditambahkan ke ' . $sektor->nama_sektor . '.');
    }

    public function ubahKomoditas(Request $request, MasterKomoditas $komoditas)
    {
        $v = $this->validasiKomoditas($request, $komoditas->sektor, $komoditas);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        $komoditas->update(['nama_komoditas' => $v['nama_komoditas']]);

        return back()->with('success', 'Komoditas "' . $komoditas->nama_komoditas . '" disimpan.');
    }

    public function hapusKomoditas(MasterKomoditas $komoditas)
    {
        $dipakai = DB::table('rute_subsidi_potensi')->where('id_komoditas', $komoditas->id_komoditas)->distinct()->count('id_rute_subsidi');
        if ($dipakai > 0) {
            return back()->with('error', 'Komoditas "' . $komoditas->nama_komoditas . '" dipakai ' . $dipakai . ' pengajuan sehingga tidak dapat dihapus.');
        }

        $nama = $komoditas->nama_komoditas;
        $komoditas->delete();

        return back()->with('success', 'Komoditas "' . $nama . '" dihapus.');
    }

    // ------------------------------------------------------------------

    /** @return array|\Illuminate\Http\RedirectResponse */
    private function validasiSektor(Request $request, ?MasterSektor $sektor = null)
    {
        $request->merge(['nama_sektor' => trim((string) $request->input('nama_sektor'))]);

        $v = Validator::make($request->all(), [
            'nama_sektor' => ['required', 'string', 'max:100',
                Rule::unique('master_sektor', 'nama_sektor')->ignore($sektor?->id_sektor, 'id_sektor')],
            'urutan'      => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], [
            'nama_sektor.required' => 'Nama sektor wajib diisi.',
            'nama_sektor.max'      => 'Nama sektor maksimal 100 karakter.',
            'nama_sektor.unique'   => 'Sektor dengan nama itu sudah ada.',
            'urutan.integer'       => 'Urutan harus berupa angka.',
            'urutan.min'           => 'Urutan tidak boleh negatif.',
            'urutan.max'           => 'Urutan maksimal 65535.',
        ]);

        return $v->fails()
            ? back()->withInput()->with('error', $v->errors()->first())
            : $v->validated();
    }

    /** Nama komoditas disimpan huruf kecil, seperti data awal (pencocokan teks tidak peka huruf besar/kecil). */
    private function validasiKomoditas(Request $request, MasterSektor $sektor, ?MasterKomoditas $komoditas = null)
    {
        $request->merge(['nama_komoditas' => mb_strtolower(trim((string) $request->input('nama_komoditas')))]);

        $v = Validator::make($request->all(), [
            'nama_komoditas' => ['required', 'string', 'max:100',
                Rule::unique('master_komoditas', 'nama_komoditas')
                    ->where('id_sektor', $sektor->id_sektor)
                    ->ignore($komoditas?->id_komoditas, 'id_komoditas')],
        ], [
            'nama_komoditas.required' => 'Nama komoditas wajib diisi.',
            'nama_komoditas.max'      => 'Nama komoditas maksimal 100 karakter.',
            'nama_komoditas.unique'   => 'Komoditas itu sudah ada pada sektor ' . $sektor->nama_sektor . '.',
        ]);

        return $v->fails()
            ? back()->withInput()->with('error', $v->errors()->first())
            : $v->validated();
    }
}
