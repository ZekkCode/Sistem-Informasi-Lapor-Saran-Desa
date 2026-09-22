<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_category_but_duplicate_slug_is_rejected(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($superAdmin)->post(route('admin.master.categories.store'), [
            'name' => 'Air Bersih',
            'sort_order' => 1,
            'is_active' => '1',
        ])->assertRedirect();

        $this->actingAs($superAdmin)->from(route('admin.master.index'))->post(route('admin.master.categories.store'), [
            'name' => 'Air   Bersih',
            'sort_order' => 2,
            'is_active' => '1',
        ])->assertRedirect(route('admin.master.index'))->assertSessionHasErrors('slug');

        $this->assertSame(1, Category::query()->where('slug', 'air-bersih')->count());
    }
}
