<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi halaman berdasarkan peran pengguna.
 * Pemakaian di route: ->middleware('role:kader,disnaker')
 * Pemilik modul (Rancangan Bab 8.2): Ahmad Rosihan Anwar.
 */
class CekRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(...$roles)) {
            abort(403, 'Halaman ini tidak tersedia untuk peran Anda.');
        }

        return $next($request);
    }
}
