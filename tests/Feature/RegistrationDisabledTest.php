<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_register_post_is_not_available(): void
    {
        $this->post('/register', [
            'name' => 'X',
            'email' => 'x@x.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();

        $this->assertGuest();
    }

    public function test_login_still_works(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
