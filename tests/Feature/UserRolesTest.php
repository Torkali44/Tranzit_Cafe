<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_cashier_only(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'كاشير جديد',
            'username' => 'cashier99',
            'password' => 'Cashier@Tranzit1',
            'is_active' => 1,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'cashier99',
            'role' => 'cashier',
        ]);
    }

    public function test_cannot_delete_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHasErrors('user');

        $this->assertNotNull(User::find($admin->id));
    }

    public function test_admin_can_delete_cashier(): void
    {
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->cashier()->create(['username' => 'delme']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $cashier))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['username' => 'delme']);
    }

    public function test_users_form_has_only_two_role_concepts(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('كاشير')
            ->assertDontSee('مدير — إدارة');
    }
}
