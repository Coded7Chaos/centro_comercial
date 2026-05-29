<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test a successful login as client.
     */
    public function test_successful_login_client(): void
    {
        $password = 'secret-pass-123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);
        $user->assignRole('cliente');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/dashboard-cliente');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test a successful login as admin.
     */
    public function test_successful_login_admin(): void
    {
        $password = 'secret-pass-123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // intended('/admin') defaults to /admin if no intended URL is stored in session
        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test a failed login due to invalid password.
     */
    public function test_failed_login_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test successful logout.
     */
    public function test_logout(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
