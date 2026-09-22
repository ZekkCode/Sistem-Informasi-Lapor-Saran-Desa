@extends('layouts.panel-petugas')

@section('title', 'Akun Petugas')
@section('page-title', 'Akun petugas')
@section('eyebrow', 'Akses pengguna internal')

@section('content')
    <p class="admin-intro">Berikan akses sesuai kebutuhan kerja. Admin Desa menangani laporan; Super Admin juga dapat mengatur pengguna dan data master.</p>

    <div class="admin-manage-layout">
        <form action="{{ route('admin.users.store') }}" method="POST" class="admin-panel admin-panel__body admin-form-grid" data-submit-once>
            @csrf
            <h2 class="admin-section-title field--full">Tambah petugas</h2>
            <div class="field field--full"><label for="user-name" class="field__label">Nama lengkap</label><input id="user-name" name="name" class="control" autocomplete="name" maxlength="120" required></div>
            <div class="field"><label for="user-username" class="field__label">Username</label><input id="user-username" name="username" class="control" autocomplete="off" maxlength="80" required></div>
            <div class="field"><label for="user-email" class="field__label">Email <span class="font-normal text-muted">(opsional)</span></label><input id="user-email" name="email" type="email" class="control" autocomplete="email"></div>
            <div class="field field--full"><label for="user-role" class="field__label">Peran</label><select id="user-role" name="role" class="control" required>@foreach($roles as $role)<option value="{{ $role->value }}">{{ $role->label() }}</option>@endforeach</select></div>
            <div class="field"><label for="user-password" class="field__label">Password awal</label><input id="user-password" name="password" type="password" class="control" autocomplete="new-password" minlength="8" required></div>
            <div class="field"><label for="user-password-confirmation" class="field__label">Ulangi password</label><input id="user-password-confirmation" name="password_confirmation" type="password" class="control" autocomplete="new-password" minlength="8" required></div>
            <input type="hidden" name="is_active" value="1">
            <div class="admin-form-actions"><button type="submit" class="action action--primary">Tambah petugas</button></div>
        </form>

        <div class="admin-edit-list">
            @foreach($users as $user)
                <details class="admin-edit-row">
                    <summary>
                        <span>{{ $user->name }} @if(auth()->user()->is($user))<span class="admin-edit-meta">(Anda)</span>@endif <span class="admin-edit-meta">{{ $user->role->label() }} · {{ $user->is_active ? 'Aktif' : 'Nonaktif' }} · masuk {{ $user->last_login_at?->diffForHumans() ?? 'belum pernah' }}</span></span>
                    </summary>
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-form-grid" data-submit-once>
                        @csrf @method('PATCH')
                        <div class="field field--full"><label for="user-name-{{ $user->id }}" class="field__label">Nama lengkap</label><input id="user-name-{{ $user->id }}" name="name" class="control" value="{{ $user->name }}" required></div>
                        <div class="field"><label for="user-username-{{ $user->id }}" class="field__label">Username</label><input id="user-username-{{ $user->id }}" name="username" class="control" value="{{ $user->username }}" required></div>
                        <div class="field"><label for="user-email-{{ $user->id }}" class="field__label">Email</label><input id="user-email-{{ $user->id }}" name="email" type="email" class="control" value="{{ $user->email }}"></div>
                        <div class="field field--full"><label for="user-role-{{ $user->id }}" class="field__label">Peran</label><select id="user-role-{{ $user->id }}" name="role" class="control" required @disabled(auth()->user()->is($user))>@foreach($roles as $role)<option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>@endforeach</select>@if(auth()->user()->is($user))<input type="hidden" name="role" value="{{ $user->role->value }}">@endif</div>
                        <div class="field"><label for="user-password-{{ $user->id }}" class="field__label">Password baru</label><input id="user-password-{{ $user->id }}" name="password" type="password" class="control" autocomplete="new-password" minlength="8"><p class="field__hint">Kosongkan jika tidak diubah.</p></div>
                        <div class="field"><label for="user-password-confirmation-{{ $user->id }}" class="field__label">Ulangi password baru</label><input id="user-password-confirmation-{{ $user->id }}" name="password_confirmation" type="password" class="control" autocomplete="new-password" minlength="8"></div>
                        <label class="field--full flex min-h-12 items-center gap-3 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="size-4 accent-padelegan-600" @checked($user->is_active) @disabled(auth()->user()->is($user))> Akun aktif</label>
                        @if(auth()->user()->is($user))<input type="hidden" name="is_active" value="1">@endif
                        <div class="admin-form-actions"><button type="submit" class="action action--primary action--sm">Simpan perubahan</button></div>
                    </form>
                </details>
            @endforeach
        </div>
    </div>
@endsection
