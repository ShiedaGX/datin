<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HanyaSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Halaman ini hanya untuk Super Admin.');

        return $next($request);
    }
}
