<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Like;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikePostTest extends TestCase
{

    protected $user;
    protected $post;
    protected function setUp(): void
    {
        parent::setUp();
        Role::factory(3)->create();
        $this->user = User::factory()
            ->createWithExtraInfo(1)
            ->state(function (array $attributes) {
                return ['state' => '1'];
            })
            ->has(Post::factory()->count(1))
            ->create();

        $post = Post::first();
        $post->files()->saveMany([
            File::factory()->type('cover_image')->make(),
            File::factory()->type('pdf')->make(),
        ]);

        $this->post = Post::all();
        $this->withoutExceptionHandling();
    }
    use RefreshDatabase;
    public function test_user_can_like_post(): void
    {
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
        )->getJson(Route('api.like.post', $this->post->first()->getRouteKey()));
        $response->assertStatus(204);
        $like = Like::where('post_id', $this->post->first()->getRouteKey())
            ->where('user_id', $this->user->getRouteKey())->first();
        $this->assertNotNull($like);
    }

    public function test_user_can_unlike_post(): void
    {
        Like::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->first()->getRouteKey()
        ]);
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
        )->deleteJson(Route('api.unlike.post', $this->post->first()->getRouteKey()));
        $response->assertStatus(204);
        $like = Like::where('post_id', $this->post->first()->getRouteKey())
            ->where('user_id', $this->user->getRouteKey())->first();
        $this->assertNull($like);
    }
}
