<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessUsulan
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canAccessUsulan(), 403, 'Anda tidak memiliki hak akses ke modul usulan.');

        return $next($request);
    }
}
