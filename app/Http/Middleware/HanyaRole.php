<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Pemakaian di route: ->middleware(HanyaRole::class . ':Moda') */
class HanyaRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        abort_unless(($request->user()->role->nama_role ?? null) === $role, 403, 'Halaman ini hanya untuk role ' . $role . '.');

        return $next($request);
    }
}
