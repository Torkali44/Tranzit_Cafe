<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_access_admin(): void
    {
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_access_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_cashier_can_access_pos_and_shift(): void
    {
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)->get('/pos/shift')->assertOk();
        $this->actingAs($cashier)->get('/pos')->assertRedirect(route('pos.shift.index'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pos')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
    }
}
