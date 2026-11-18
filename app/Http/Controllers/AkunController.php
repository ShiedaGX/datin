<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Akun saya: setiap user (Pemda, Moda, INTRAM, Super Admin) dapat mengganti kata sandinya sendiri. */
class AkunController extends Controller
{
    public function password()
    {
        return view('akun.password');
    }

    public function ubahPassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password'      => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ], [
            'password_lama.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'      => 'Kata sandi baru wajib diisi.',
            'password.min'           => 'Kata sandi baru minimal :min karakter.',
            'password.confirmed'     => 'Konfirmasi kata sandi baru tidak sama.',
            'password.different'     => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        if (! Hash::check($data['password_lama'], $user->password_hash)) {
            return back()->withErrors(['password_lama' => 'Kata sandi saat ini tidak sesuai.']);
        }

        // password_hash di-hash otomatis oleh cast 'hashed'. Token "ingat saya" diganti agar perangkat lain keluar.
        $user->forceFill([
            'password_hash'  => $data['password'],
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('akun.password')->with('success', 'Kata sandi berhasil diubah.');
    }
}
