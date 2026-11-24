<?php
namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Support\PeranUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Dipakai untuk tambah (POST) dan ubah (PUT) akun. Cakupan yang wajib diisi mengikuti role yang dipilih. */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware HanyaSuperAdmin pada route
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name')),
            'username'  => mb_strtolower(trim((string) $this->input('username'))),
            'email'     => mb_strtolower(trim((string) $this->input('email'))),
        ]);

        // Cadangan: ID kab/kota kosong tapi teks instansi persis cocok dengan satu kab/kota
        if (! $this->filled('id_kab_kota') && $this->filled('instansi_teks')) {
            $teks = mb_strtolower(trim(preg_replace('/^\s*pemda\s+/i', '', (string) $this->input('instansi_teks'))));

            $cocok = DB::table('kab_kota')->get(['id_kab_kota', 'jenis', 'nama_kab_kota'])
                ->filter(function ($k) use ($teks) {
                    $nama  = mb_strtolower($k->nama_kab_kota);
                    $penuh = mb_strtolower(trim(($k->jenis ?? '') . ' ' . $k->nama_kab_kota));
                    return $teks === $nama || $teks === $penuh;
                })
                ->values();

            // hanya dipakai kalau hasilnya tepat satu (tidak ambigu)
            if ($cocok->count() === 1) {
                $this->merge(['id_kab_kota' => $cocok[0]->id_kab_kota]);
            }
        }
    }

    public function rules(): array
    {
        $user = $this->route('user');                       // null saat menambah akun
        $id = $user?->id_user;
        $syarat = PeranUser::syarat(Role::find($this->input('id_role'))?->nama_role);
        $wajib = fn (string $kunci) => in_array($kunci, $syarat, true) ? 'required' : 'nullable';

        return [
            'full_name'  => ['required', 'string', 'max:150'],
            'username'   => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($id, 'id_user')],
            'email'      => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id, 'id_user')],
            'id_role'    => ['required', 'integer', 'exists:role,id_role'],
            'instansi'   => [$wajib('instansi'), 'string', 'max:150'],
            'id_wilayah' => [$wajib('wilayah'), 'integer', 'exists:wilayah,id_wilayah'],
            'id_moda'    => [$wajib('moda'), 'integer', 'exists:moda,id_moda'],
            'password'   => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'aktif'      => ['nullable', 'boolean'],
        $namaRole = Role::find($this->input('id_role'))?->nama_role;
        $syarat = PeranUser::syarat($namaRole);
        $wajib = fn (string $kunci) => in_array($kunci, $syarat, true) ? 'required' : 'nullable';
        $pemda = $namaRole === PeranUser::PEMDA;

        return [
            'full_name'   => ['required', 'string', 'max:150'],
            'username'    => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($id, 'id_user')],
            'email'       => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id, 'id_user')],
            'id_role'     => ['required', 'integer', 'exists:role,id_role'],
            // Pemda wajib memilih kab/kota; instansi dan wilayah diisi otomatis oleh server
            'id_kab_kota' => [$pemda ? 'required' : 'nullable', 'integer', 'exists:kab_kota,id_kab_kota'],
            'id_wilayah'  => ['nullable', 'integer', 'exists:wilayah,id_wilayah'],
            'id_moda'     => [$wajib('moda'), 'integer', 'exists:moda,id_moda'],
            'password'    => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'aktif'       => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name'  => 'Nama lengkap',
            'username'   => 'Username',
            'email'      => 'Alamat email',
            'id_role'    => 'Role',
            'instansi'   => 'Instansi / organisasi',
            'id_wilayah' => 'Cakupan wilayah',
            'id_moda'    => 'Cakupan moda',
            'password'   => 'Kata sandi',
            'full_name'   => 'Nama lengkap',
            'username'    => 'Username',
            'email'       => 'Alamat email',
            'id_role'     => 'Role',
            'id_kab_kota' => 'Instansi / organisasi',
            'id_wilayah'  => 'Cakupan wilayah',
            'id_moda'     => 'Cakupan moda',
            'password'    => 'Kata sandi',
        ];
    }

    public function messages(): array
    {
        return [
            'required'           => ':attribute wajib diisi.',
            'max.string'         => ':attribute maksimal :max karakter.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.unique'       => 'Alamat email ini sudah dipakai akun lain.',
            'username.unique'    => 'Username ini sudah dipakai akun lain.',
            'username.regex'     => 'Username hanya boleh huruf kecil, angka, titik, garis bawah, dan strip.',
            'password.min'       => 'Kata sandi minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'exists'             => ':attribute tidak valid.',
        ];
    }
}
            'required'             => ':attribute wajib diisi.',
            'id_kab_kota.required' => 'Pilih kabupaten/kota dari daftar saran yang muncul.',
            'max.string'           => ':attribute maksimal :max karakter.',
            'email.email'          => 'Format alamat email tidak valid.',
            'email.unique'         => 'Alamat email ini sudah dipakai akun lain.',
            'username.unique'      => 'Username ini sudah dipakai akun lain.',
            'username.regex'       => 'Username hanya boleh huruf kecil, angka, titik, garis bawah, dan strip.',
            'password.min'         => 'Kata sandi minimal :min karakter.',
            'password.confirmed'   => 'Konfirmasi kata sandi tidak sama.',
            'exists'               => ':attribute tidak valid.',
        ];
    }
}
