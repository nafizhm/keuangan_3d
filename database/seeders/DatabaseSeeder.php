<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['pengelola', 'pemodal'] as $role) {
            if (! User::where('email', $role.'@ruang3d.local')->exists()) {
                $user = new User;
                $user->name = ucfirst($role);
                $user->username = $role;
                $user->email = $role.'@ruang3d.local';
                $user->role = $role;
                // Print the plaintext only during initial account creation.
                $plain = Str::password(16);
                $user->password = $plain;
                $user->save();
                $this->command?->info($user->username.' | '.$plain);
            }
        }
    }
}
