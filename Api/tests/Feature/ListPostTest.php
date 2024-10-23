<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Like;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPostTest extends TestCase
{
    private $user;
    private $posts;
    private $post;

    private function setUpBase(): void
    {
        //Create roles
        Role::factory(3)->create();
    }
    private function setUpCase1(): void
    {
        $this->setUpBase();
        $this->withoutExceptionHandling();

        $user               =  $this->setUpSpecificUser();

        for ($i = 118; $i <= 120; $i++) {
            // Create users
            $state =  $i % 2 == 0 ? '1' : '0';
            User::factory()->createWithExtraInfo($i)
                ->state(function (array $attributes) use ($state) {
                    return [
                        'state' => $state,
                    ];
                })->create();
        }

        $users              = User::all();
        $users->each(function ($user) {
            if ($user->role_id == 2 && $user->state == '1') {
                $user->posts()->save(
                    Post::factory()->make(),
                );
            }
        });

        $posts              = Post::all();
        $posts->each(function ($post) use ($users) {
            //Create and associate the files with the post
            $post->files()->saveMany([
                File::factory()->type('cover_image')->make(),
                File::factory()->type('pdf')->make(),
            ]);

            //Iterate over users
            $users->each(function ($user) use ($post) {
                if ($user->role_id != 1) {
                    $like = new Like(['user_id' => $user->id]);
                    $post->likes()->save($like);
                }
            });
        });
        $this->posts        = Post::withCount('likes')->get();
        $this->user         = $user;
    }


    private function setUpSpecificUser()
    {
        return User::factory()->createWithExtraInfo(1)
            ->state(function (array $attributes) {
                return [
                    'state' => '1',
                ];
            })->has(Post::factory()->count(2)) // Create 2 post
            ->create();
    }

    private function setUpCase2(): void
    {
        $this->setUpBase();
        //Create a new user
        $this->user = $this->setUpSpecificUser();

        $like = new Like(['user_id' => $this->user->id]);
        $post = Post::first();
        $post->likes()->save($like);
        $post->files()->saveMany([
            File::factory()->type('cover_image')->make(),
            File::factory()->type('pdf')->make(),
        ]);
        $this->post = Post::withCount('likes')->first();
    }


    use RefreshDatabase;
    /**
      @test
     */
    public function test_paginate()
    {
        $this->setUpCase1();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.post.index') . '?page=1');

        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }

    /**
      @test
     */
    public function show_post()
    {
        $this->setUpCase2();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.post.show', $this->post->getRouteKey()));
        $response->assertJsonApiPostResource($this->post,  200);
    }
    /**
      @test
     */
    public function post_of_specific_user()
    {
        $this->setUpCase1();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.user.posts', $this->user->getRouteKey()));
        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this, $this->user);
    }

    public function test_list_all_post(): void
    {
        $this->setUpCase1();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.post.index'));
        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }

    public function test_filter_posts_for_category_career()
    {
        $this->setUpCase1();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.post.index') . '?filter[career]=Contabilidad');
        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }

    // public function test_filter_posts_for_category_semester()
    // {
    //     $this->withoutExceptionHandling();
    //     $response = $this->withHeader(
    //         'Authorization',
    //         'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
    //     )
    //         ->getJson(route('api.post.index') . '?filter[semester]=4');
    //     $postsResponse = $response->json()['data'];
    //     $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    // }
    
    /**
      @test
     */
    public function filter_posts_for_title()
    {
        $this->setUpCase1();
        $this->posts->first()->title = 'title for my post';
        $this->posts->first()->save();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.post.filter', 'title'));

        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }

    public function test_filter_posts_for_month_day_year()
    {
        $this->setUpCase1();
        $this->posts->first()->created_at = '2022-01-19 03:58:08';
        $this->posts->first()->save();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.post.filter', 'Enero 19 2022'));
        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }

    public function test_filter_posts_for_month_year()
    {
        $this->setUpCase1();
        $this->posts->first()->created_at = '2022-01-02 03:58:08';
        $this->posts->first()->save();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken')->plainTextToken
        )->getJson(route('api.post.filter', 'Enero 2022'));
        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }

    public function test_there_is_no_matched_in_the_filter()
    {
        $this->setUpCase1();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->user->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.post.filter', 'Esto no existe'));
        $response->assertStatus(204);
    }

    /**
     @test
     */
    public function student_see_relevant_content_in_my_home_page()
    {
        $this->setUpCase1();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
        )->getJson(route('api.post.relevant'));
        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }

    public function test_see_trending_post()
    {
        $this->setUpCase1();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
        )->getJson(route('api.post.trending'));
        $response->assertStatus(200);
        $postsResponse = $response->json()['data'];
        $response->assertJsonApiPostsResource($this->posts, $postsResponse, $this);
    }
}
