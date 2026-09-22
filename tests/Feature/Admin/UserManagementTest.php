<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_an_admin_desa(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Petugas Baru',
            'username' => 'petugas.baru',
            'email' => 'petugas@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => UserRole::Admin->value,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['username' => 'petugas.baru', 'role' => UserRole::Admin->value]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $superAdmin->id, 'action' => 'user.created']);
    }

    public function test_last_active_super_admin_cannot_be_deactivated(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)->from(route('admin.users.index'))->patch(route('admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'username' => $superAdmin->username,
            'email' => $superAdmin->email,
            'role' => UserRole::SuperAdmin->value,
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'))->assertSessionHasErrors('user');

        $this->assertTrue($superAdmin->fresh()->is_active);
    }
}
