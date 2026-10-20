<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HakAkses;
use App\Models\Role;
use Illuminate\Http\Request;

class HakAksesController extends Controller
{
    public function edit(Role $role)
    {
        if ($role->isSuperAdmin()) {
            return redirect()->route('admin.user.index')
                ->with('error', 'Super Admin selalu memiliki seluruh hak akses dan tidak dapat diubah.');
        }

        return view('admin.roles.hak-akses', [
            'role'    => $role,
            'katalog' => HakAkses::orderBy('id_hak_akses')->get(),
            'dipilih' => $role->hakAkses()->pluck('hak_akses.id_hak_akses')->all(),
            'jumlahAkun' => $role->users()->count(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->isSuperAdmin(), 403);

        $data = $request->validate([
            'hak_akses'   => ['nullable', 'array'],
            'hak_akses.*' => ['integer', 'exists:hak_akses,id_hak_akses'],
        ]);

        $role->hakAkses()->sync($data['hak_akses'] ?? []);

        return redirect()->route('admin.user.index')
            ->with('success', 'Hak akses role ' . $role->nama_role . ' berhasil disimpan.');
    }
}
