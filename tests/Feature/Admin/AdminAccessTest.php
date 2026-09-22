<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_active_petugas_can_login_and_inactive_petugas_is_rejected(): void
    {
        $active = User::factory()->create(['username' => 'petugas.aktif', 'password' => 'rahasia123']);
        $inactive = User::factory()->create(['username' => 'petugas.nonaktif', 'password' => 'rahasia123', 'is_active' => false]);

        $this->post(route('admin.login.store'), ['username' => $active->username, 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($active);

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));

        $this->from(route('admin.login'))->post(route('admin.login.store'), ['username' => $inactive->username, 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_admin_desa_cannot_manage_users_or_master_data(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.master.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_super_admin_can_open_user_and_master_data_pages(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)->get(route('admin.master.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
    }
}
