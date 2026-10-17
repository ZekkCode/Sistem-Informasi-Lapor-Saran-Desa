<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use App\Services\Admin\AuditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.admin-login', [
            'googleAktif' => MasukGoogleController::aktif(),
        ]);
    }

    public function store(LoginRequest $request, AuditService $audit): RedirectResponse
    {
        $loginInput = trim((string) $request->input('username'));
        $password = (string) $request->input('password');
        $normalized = strtolower($loginInput);

        try {
            $user = User::query()
                ->whereRaw('lower(username) = ?', [$normalized])
                ->orWhereRaw('lower(email) = ?', [$normalized])
                ->first();

            $berhasil = $user !== null && \Illuminate\Support\Facades\Hash::check($password, $user->password);

            if ($berhasil) {
                Auth::login($user, $request->boolean('remember'));
            }
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'username' => 'Login petugas belum aktif karena database belum tersambung. Hubungi pengelola situs.',
            ]);
        }

        if (! $berhasil) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password belum sesuai.',
            ]);
        }

        if (! $request->user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Akun petugas tidak aktif. Hubungi Super Admin.',
            ]);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();
        $audit->record('auth.login', $request->user(), request: $request);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(AuditService $audit): RedirectResponse
    {
        $user = request()->user();
        $audit->record('auth.logout', $user);
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
