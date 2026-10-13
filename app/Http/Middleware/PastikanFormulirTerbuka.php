<?php

namespace App\Http\Middleware;

use App\Enums\JenisFormulir;
use App\Services\Formulir\LayananKunciFormulir;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak pengiriman warga sebelum validasi berjalan saat formulir dikunci,
 * sehingga warga tidak menerima campuran error field dan error kunci.
 */
class PastikanFormulirTerbuka
{
    public function __construct(
        private readonly LayananKunciFormulir $kunciFormulir,
    ) {}

    public function handle(Request $request, Closure $next, string $formulir): Response
    {
        $status = $this->kunciFormulir->status(JenisFormulir::from($formulir));

        if ($status->terbuka()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $status->pesanPublik()], 423);
        }

        return redirect()
            ->route($status->formulir->routeBuat())
            ->withErrors(['submission' => $status->pesanPublik()]);
    }
}
