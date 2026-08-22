<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_as_students(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student-test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_LECTURER,
        ]);

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'student-test@example.com',
            'role' => User::ROLE_STUDENT,
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'student-test@example.com',
            'role' => User::ROLE_LECTURER,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
