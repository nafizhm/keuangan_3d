<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('username', 100)->nullable()->unique());
        foreach (DB::table('users')->orderBy('id')->get(['id', 'email']) as $user) {
            $base = substr(Str::slug(Str::before($user->email, '@'), '_'), 0, 80) ?: 'user';
            $username = $base;
            $suffix = 1;
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $base.'_'.($suffix++);
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }
        Schema::table('users', fn (Blueprint $table) => $table->string('username', 100)->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
