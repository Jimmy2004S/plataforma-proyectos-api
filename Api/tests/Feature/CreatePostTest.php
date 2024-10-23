<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    private $user;
    private $file;
    private $cover_image;
    protected function setUp(): void
    {
        parent::setUp();
        //Create roles
        Role::factory(3)->create();
        //Create a new user
        $this->user = User::factory()
            ->createWithExtraInfo(1)
            ->create([
                'state' => '1',
                'role_id' => 2
            ])->first();

        Storage::fake('public');
        $this->file = UploadedFile::fake()->create('document.pdf');
        $this->cover_image = UploadedFile::fake()->image('cover_image.jpg');
    }

    use RefreshDatabase;
    public function test_student_can_create_post(): void
    {
        $response = $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
            ]
        )->postJson(
            route('api.post.create'),
            [
                'data' => [
                    'type' => 'post',
                    'attributes' => [
                        'title' => 'Test Title',
                        'description' => 'description',
                        'cover_image' => $this->cover_image,
                        'file' => $this->file,
                    ]
                ]
            ]
        );
        $post = Post::first();
        $response->assertJsonApiPostResource($post, 201);
    }


    public function test_title_validations()
    {
        $response = $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
            ]
        )->postJson(
            route('api.post.create'),
            [
                'data' => [
                    'type' => 'post',
                    'attributes' => [
                        'description' => 'Test Content',
                        'cover_image' => $this->cover_image,
                        'file' => $this->file,
                    ]
                ]
            ]
        );
        $response->assertJsonApiValidationErrors('title');
    }

    public function test_description_validation()
    {
        $response = $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
            ]
        )->postJson(
            route('api.post.create'),
            [
                'data' => [
                    'type' => 'post',
                    'attributes' => [
                        'title' => 'Test Title',
                        'cover_image' => $this->cover_image,
                        'file' => $this->file,
                    ]
                ]
            ]
        );
        $response->assertJsonApiValidationErrors('description');
    }

    public function test_file_validation()
    {
        $response = $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
            ]
        )->postJson(
            route('api.post.create'),
            [
                'data' => [
                    'type' => 'post',
                    'attributes' => [
                        'title' => 'Test Title',
                        'description' => 'description',
                        'cover_image' => $this->cover_image,
                    ]
                ]
            ]
        );
        $response->assertJsonApiValidationErrors('file');
    }

    public function test_cover_image_validation()
    {
        $response = $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->user->createToken('TestToken', ['student'])->plainTextToken
            ]
        )->postJson(
            route('api.post.create'),
            [
                'data' => [
                    'type' => 'post',
                    'attributes' => [
                        'title' => 'Test Title',
                        'description' => 'description',
                        'file' => $this->file,
                    ]
                ]
            ]
        );
        $response->assertJsonApiValidationErrors('cover_image');
    }
}
