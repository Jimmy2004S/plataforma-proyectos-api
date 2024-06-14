<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::paginate(1);
        $users = User::where('role_id', '!=', '1')
            ->paginate(2, ['id']);
        $posts->each(function ($post) use ($users) {
            //Iterate over users
            $users->each(function ($user) use ($post) {
                $post->reports()->save(
                    Report::factory()->make(['user_id' => $user->id])
                );
            });
        });
    }
}
