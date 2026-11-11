<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // Registrasi berhasil sebagai citizen
    public function test_user_can_register_as_citizen(): void
    {
        $response = $this->post(route('register.process'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/citizen/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => 'citizen',
        ]);

        $this->assertAuthenticated();
    }

    // Login berhasil
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'role' => 'citizen',
        ]);

        $response = $this->post(route('login.process'), [
            'email' => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/citizen/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    // Login gagal jika password salah
    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.process'), [
            'email' => 'budi@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // Officer diarahkan ke dashboard officer
    public function test_officer_is_redirected_to_officer_dashboard(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'role' => 'officer',
        ]);

        $response = $this->post(route('login.process'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/officer/dashboard');
    }

    // Governance diarahkan ke dashboard governance
    public function test_governance_is_redirected_to_governance_dashboard(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'role' => 'governance',
        ]);

        $response = $this->post(route('login.process'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/governance/dashboard');
    }

    // Superadmin diarahkan ke dashboard superadmin
    public function test_superadmin_is_redirected_to_superadmin_dashboard(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $response = $this->post(route('login.process'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/superadmin/dashboard');
    }

    // Logout berhasil
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
