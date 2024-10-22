<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::orderBy('created_at' , 'DESC')->paginate(2);
        $users = User::where('role_id', 3)
            ->where('state', '1')
            ->paginate( 2, ['id']);

        $posts->each(function ($post) use ($users) {
            $users->each(
                function ($user) use ($post) {
                    $post->comments()->save(
                        Comment::factory()->make(['user_id' => $user->id])
                    );
                }
            );
        });
    }
}
