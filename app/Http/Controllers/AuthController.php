<?php
namespace App\Http\Controllers;

use App\Models\DataPemda;
use App\Models\KabKota;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Ringkasan angka pada panel kiri halaman login (dihitung dari database)
        $total = DataPemda::count();
        $lengkap = DataPemda::query()
            ->whereNotNull('potensi_pemda')->where('potensi_pemda', '!=', '')
            ->whereNotNull('klasifikasi_pemda')->where('klasifikasi_pemda', '!=', '')
            ->whereNotNull('keterangan_pemda')->where('keterangan_pemda', '!=', '')
            ->count();

        return view('auth.login', [
            'stats' => [
                'provinsi'    => Provinsi::count(),
                'wilayah'     => KabKota::count(),
                // % data_pemda yang potensi, klasifikasi, dan keterangannya terisi
                'kelengkapan' => $total > 0 ? (int) round($lengkap / $total * 100) : 0,
            ],
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('input-data.create'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
