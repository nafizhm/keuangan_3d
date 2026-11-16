<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_username_instead_of_email(): void
    {
        $user = User::factory()->create(['username' => 'pengelola', 'password' => 'password-lama']);
        $this->get('/login')->assertOk()->assertSee('name="username"', false)->assertDontSee('name="email"', false);
        $this->post('/login', ['email' => $user->email, 'password' => 'password-lama'])->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->post('/login', ['username' => 'pengelola', 'password' => 'password-lama'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_change_checks_old_password_and_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);
        $this->get('/ubah-password')->assertRedirect('/login');
        $this->put('/ubah-password', [])->assertRedirect('/login');
        $this->actingAs($user)->get('/ubah-password')->assertOk()->assertSee('Simpan password');
        $this->put('/ubah-password', ['current_password' => 'salah', 'password' => 'password-baru', 'password_confirmation' => 'password-baru'])->assertSessionHasErrors('current_password');
        $this->put('/ubah-password', ['current_password' => 'password-lama', 'password' => 'password-baru', 'password_confirmation' => 'berbeda'])->assertSessionHasErrors('password');
        $this->put('/ubah-password', ['current_password' => 'password-lama', 'password' => 'pendek', 'password_confirmation' => 'pendek'])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
        $this->put('/ubah-password', ['current_password' => 'password-lama', 'password' => 'password-baru', 'password_confirmation' => 'password-baru'])->assertRedirect('/ubah-password')->assertSessionHas('success');
        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
        $this->post('/logout');
        $this->post('/login', ['username' => $user->username, 'password' => 'password-lama'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => $user->username, 'password' => 'password-baru'])->assertRedirect('/dashboard');
    }
}
