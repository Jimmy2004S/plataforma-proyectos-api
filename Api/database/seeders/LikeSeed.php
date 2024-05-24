<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LikeSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::orderBy('created_at', 'ASC')
            ->paginate(30);
        $users = User::where('role_Id', '!=', 1)
            ->where('state', '1')
            ->orderBy('created_at', 'ASC')
            ->paginate(100 , ['id']);
        $posts->each(function ($post) use ($users) {
            //Iterate over users
            $users->each(function ($user) use ($post) {
                $post->likes()->save(new Like(['user_id' => $user->id]));
            });
        });
    }
}
