<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Pemakaian di route: ->middleware(PunyaHakAkses::class . ':pemda.unggah') */
class PunyaHakAkses
{
    public function handle(Request $request, Closure $next, string $kode)
    {
        abort_unless($request->user()?->bisa($kode), 403, 'Anda tidak memiliki hak akses untuk halaman ini.');

        return $next($request);
    }
}
