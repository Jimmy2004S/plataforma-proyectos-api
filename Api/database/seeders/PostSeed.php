<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role_id', 2)
            ->where('state', '1')
            ->orderBy('created_at' , 'ASC')
            ->paginate(2);
        $users->each(function ($user) {
            //create and associate the users with the post
            $user->posts()->saveMany([
                Post::factory()->make(),
                Post::factory()->make()
            ]);
        });
    }
}
