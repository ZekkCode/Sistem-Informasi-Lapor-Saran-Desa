<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request, AuditService $audit): RedirectResponse
    {
        $user = User::query()->create($request->validated());
        $audit->record('user.created', $user, Arr::except($user->only(['name', 'username', 'email', 'role', 'is_active']), ['password']));

        return back()->with('status', 'Akun petugas berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user, AuditService $audit): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()->is($user) && (! $request->boolean('is_active') || $data['role'] !== $user->role->value)) {
            throw ValidationException::withMessages([
                'user' => 'Akun yang sedang dipakai tidak dapat dinonaktifkan atau diubah perannya.',
            ]);
        }

        $before = $user->only(['name', 'username', 'email', 'role', 'is_active']);

        DB::transaction(function () use ($user, $data, $request): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $removesSuperAdmin = $lockedUser->role === UserRole::SuperAdmin
                && ($data['role'] !== UserRole::SuperAdmin->value || ! $request->boolean('is_active'));

            if ($removesSuperAdmin) {
                $activeSuperAdmins = User::query()
                    ->where('role', UserRole::SuperAdmin->value)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id'])
                    ->count();

                if ($activeSuperAdmins <= 1) {
                    throw ValidationException::withMessages([
                        'user' => 'Minimal satu Super Admin aktif harus tetap tersedia.',
                    ]);
                }
            }

            if (blank($data['password'] ?? null)) {
                unset($data['password']);
            }

            $lockedUser->update($data);
        });

        $user->refresh();
        $audit->record('user.updated', $user, [
            'before' => $before,
            'after' => $user->only(['name', 'username', 'email', 'role', 'is_active']),
        ]);

        return back()->with('status', 'Data petugas berhasil diperbarui.');
    }
}
