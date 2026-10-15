<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name')),
            'instansi'  => trim((string) $this->input('instansi')),
            'email'     => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'instansi'  => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'setuju'    => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'Nama lengkap',
            'instansi'  => 'Instansi / organisasi',
            'email'     => 'Alamat email',
            'password'  => 'Kata sandi',
        ];
    }

    public function messages(): array
    {
        return [
            'required'           => ':attribute wajib diisi.',
            'max.string'         => ':attribute maksimal :max karakter.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.unique'       => 'Alamat email ini sudah terdaftar.',
            'password.min'       => 'Kata sandi minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'setuju.accepted'    => 'Anda harus menyetujui Ketentuan Layanan dan Kebijakan Privasi.',
        ];
    }
}
