<?php

namespace Database\Seeders;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //Create admin user
        User::create([
            'user_name' => 'Administrador',
            'email' => 'jimmisitho450@gmail.com',
            'code' => 0,
            'password' => bcrypt('admin'),
            'role_id' => 1,
            'remember_token' => Str::random(10),
        ]);

        for ($i = 100; $i <= 120; $i++) {
            // Create users
            $state =  $i % 2 == 0 ? '1' : '0';
            User::factory()->createWithExtraInfo($i)
            ->state(function (array $attributes) use ($state) {
                return [
                    'state' => $state,
                ];
            })->create();
        }

    }
}
