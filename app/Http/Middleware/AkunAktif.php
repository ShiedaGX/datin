<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Mengeluarkan pengguna yang akunnya dinonaktifkan/belum diverifikasi, termasuk yang sedang login. */
class AkunAktif
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->diverifikasi_at === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda belum aktif atau telah dinonaktifkan administrator.']);
        }

        return $next($request);
    }
}
