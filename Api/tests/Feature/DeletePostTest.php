<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeletePostTest extends TestCase
{
    private $user;
    private $posts;
    protected function setUp(): void
    {
        parent::setUp();
        //Create roles
        Role::factory(3)->create();
        //Create a new user
        $this->user = User::factory()
            ->createWithExtraInfo(1)
            ->state(function (array $attributes) {
                return ['state' => '1'];
            })
            ->has(Post::factory()->count(2))
            ->create();
        User::factory()
            ->count(2)
            ->state(function (array $attributes) {
                return ['state' => '1'];
            })
            ->has(Post::factory()->count(1))
            ->create();

        $posts = Post::all();
        $posts->each(function ($post) {
            $post->files()->saveMany([
                File::factory()->type('cover_image')->make(),
                File::factory()->type('pdf')->make(),
            ]);
        });
        $this->posts = Post::all();
    }

    use RefreshDatabase;
    public function test_delete_post(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->deleteJson(route('api.post.destroy', $this->posts->first()->getRouteKey()));
        $response->assertStatus(204);
    }
}
