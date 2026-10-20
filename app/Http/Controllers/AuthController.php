<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Support\StatistikPortal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login', ['stats' => StatistikPortal::ringkasan()]);
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

        // Akun yang belum diverifikasi / dinonaktifkan administrator
        $user = User::where('email', $credentials['email'])->first();
        if ($user && $user->diverifikasi_at === null && Hash::check($credentials['password'], $user->password_hash)) {
            return back()
                ->withErrors(['email' => 'Akun Anda belum aktif. Menunggu verifikasi administrator atau telah dinonaktifkan.'])
                ->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
