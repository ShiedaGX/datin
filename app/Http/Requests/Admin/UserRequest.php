<?php
namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Support\PeranUser;
use Illuminate\Foundation\Http\FormRequest;
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
    }

    public function rules(): array
    {
        $user = $this->route('user');                       // null saat menambah akun
        $id = $user?->id_user;
        $namaRole = Role::find($this->input('id_role'))?->nama_role;
        $syarat = PeranUser::syarat($namaRole);
        $wajib = fn (string $kunci) => in_array($kunci, $syarat, true) ? 'required' : 'nullable';

        // Instansi / organisasi: wajib untuk role Pemda, role lain boleh kosong (kolom dinonaktifkan di form)
        $instansiWajib = $namaRole === PeranUser::PEMDA ? 'required' : 'nullable';

        return [
            'full_name'  => ['required', 'string', 'max:150'],
            'username'   => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($id, 'id_user')],
            'email'      => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id, 'id_user')],
            'id_role'    => ['required', 'integer', 'exists:role,id_role'],
            'instansi'   => [$instansiWajib, 'string', 'max:150'],
            'id_wilayah' => [$wajib('wilayah'), 'integer', 'exists:wilayah,id_wilayah'],
            'id_moda'    => [$wajib('moda'), 'integer', 'exists:moda,id_moda'],
            'password'   => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'aktif'      => ['nullable', 'boolean'],
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