<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterSatuanData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterSatuanDataController extends Controller
{
    private function pastikanSuperAdmin(Request $request): void
    {
        abort_unless(
            $request->user() &&
            $request->user()->isSuperAdmin(),
            403
        );
    }

    public function index(Request $request)
    {
        $this->pastikanSuperAdmin($request);

        $satuan = MasterSatuanData::orderBy('nama_satuan')
            ->get();

        return view('admin.master-satuan.index', [
            'satuan' => $satuan,
        ]);
    }

    public function store(Request $request)
    {
        $this->pastikanSuperAdmin($request);

        $request->merge([
            'nama_satuan' => trim(
                (string) $request->input('nama_satuan', '')
            ),
        ]);

        $data = $request->validate([
            'nama_satuan' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'master_satuan_data',
                    'nama_satuan'
                ),
            ],
        ]);

        MasterSatuanData::create([
            'nama_satuan' => $data['nama_satuan'],
            'status_aktif' => true,
        ]);

        return redirect()
            ->route('admin.master-satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        MasterSatuanData $satuan
    ) {
        $this->pastikanSuperAdmin($request);

        $request->merge([
            'nama_satuan' => trim(
                (string) $request->input('nama_satuan', '')
            ),
        ]);

        $data = $request->validate([
            'nama_satuan' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'master_satuan_data',
                    'nama_satuan'
                )->ignore(
                    $satuan->id_satuan_data,
                    'id_satuan_data'
                ),
            ],
        ]);

        $satuan->update([
            'nama_satuan' => $data['nama_satuan'],
        ]);

        return redirect()
            ->route('admin.master-satuan.index')
            ->with('success', 'Nama satuan berhasil diperbarui.');
    }

    public function toggle(
        Request $request,
        MasterSatuanData $satuan
    ) {
        $this->pastikanSuperAdmin($request);

        $satuan->update([
            'status_aktif' => ! $satuan->status_aktif,
        ]);

        return redirect()
            ->route('admin.master-satuan.index')
            ->with(
                'success',
                $satuan->status_aktif
                    ? 'Satuan berhasil diaktifkan.'
                    : 'Satuan berhasil dinonaktifkan.'
            );
    }

    public function destroy(
        Request $request,
        MasterSatuanData $satuan
    ) {
        $this->pastikanSuperAdmin($request);

        $dihapus = DB::transaction(function () use ($satuan) {
            $terkunci = MasterSatuanData::whereKey(
                $satuan->getKey()
            )->lockForUpdate()->firstOrFail();

            if ($terkunci->sudah_digunakan) {
                return false;
            }

            $terkunci->delete();

            return true;
        });

        if (! $dihapus) {
            return redirect()
                ->route('admin.master-satuan.index')
                ->with(
                    'error',
                    'Satuan tidak dapat dihapus permanen karena sudah digunakan dalam pengajuan. Silakan nonaktifkan.'
                );
        }

        return redirect()
            ->route('admin.master-satuan.index')
            ->with('success', 'Satuan berhasil dihapus permanen.');
    }
}
