<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessReports
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canAccessReports(), 403, 'Anda tidak memiliki hak akses ke modul laporan.');

        return $next($request);
    }
}
