<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_login_with_username(): void
    {
        User::factory()->admin()->create([
            'username' => 'boss',
            'password' => Hash::make('Admin@Tranzit1'),
        ]);

        $this->post('/login', [
            'username' => 'boss',
            'password' => 'Admin@Tranzit1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_cashier_redirects_to_pos_after_login(): void
    {
        User::factory()->cashier()->create([
            'username' => 'cash1',
            'password' => Hash::make('Cashier@Tranzit1'),
        ]);

        $this->post('/login', [
            'username' => 'cash1',
            'password' => 'Cashier@Tranzit1',
        ])->assertRedirect(route('pos.index'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'username' => 'cash1',
            'password' => Hash::make('Cashier@Tranzit1'),
        ]);

        $this->from('/login')->post('/login', [
            'username' => 'cash1',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'off',
            'password' => Hash::make('Cashier@Tranzit1'),
            'is_active' => false,
        ]);

        $this->from('/login')->post('/login', [
            'username' => 'off',
            'password' => 'Cashier@Tranzit1',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_account_locks_after_failed_attempts(): void
    {
        $user = User::factory()->create([
            'username' => 'lockme',
            'password' => Hash::make('Cashier@Tranzit1'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'username' => 'lockme',
                'password' => 'bad',
            ]);
        }

        $user->refresh();
        $this->assertTrue($user->isLocked());
    }
}
