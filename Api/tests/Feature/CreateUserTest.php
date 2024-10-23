<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        //Create roles
        Role::factory(3)->create();
    }

    use RefreshDatabase;
    public function test_can_create_user()
    {
        $this->withoutExceptionHandling();
        //Create roles before creating the user
        $response = $this->postJson(
            route('api.user.create'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' =>
                    [ 'code' =>  2023001, 'password' => 'password']
                ]
            ]
        );
        //The user was created successfully ( 201 )
        $response->assertCreated();
        $user = User::first();
        // Assert the user resource structure
        $response->assertJsonApiAuthResource($user, 201);
    }

    public function test_code_is_required()
    {
        // Send a request without code
        $response = $this->postJson(
            route('api.user.create'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'password' => 'password',
                    ]
                ]
            ]
        );
        // Using the macro create in MakesJsonRequest to validate
        $response->assertJsonApiValidationErrors('code');
    }

    public function test_password_is_required()
    {
        // Send a request without password
        $response = $this->postJson(
            route('api.user.create'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'code' =>  202499535,
                    ]
                ]
            ]
        );
        // Using the macro create in MakesJsonRequest to validate
        $response->assertJsonApiValidationErrors('password');
    }

    public function test_password_min_8()
    {
        // Send a request with password not valid
        $response = $this->postJson(
            route('api.user.create'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'code' =>  202499535,
                        'password' => 'passw',
                    ]
                ]
            ]
        );
        // Using the macro create in MakesJsonRequest to validate
        $response->assertJsonApiValidationErrors('password');
    }

    public function test_invalid_code_registration()
    {
        // Send a request with a invalid code
        $response = $this->postJson(route('api.user.create'), [
            'data' => [
                'type' => 'user',
                'attributes' => [
                    'code' => 121212,
                    'password' => 'password',
                ]
            ]
        ]);
        $response->assertJsonApiValidationErrors('code' , 403);
    }

    public function test_user_is_already_registered(){
        $user = User::factory()->createWithExtraInfo(1)->create([
            'state' => '1'
        ])->first();

        $response = $this->postJson(
            route('api.user.create'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'code' =>  $user->code,
                        'password' => 'password',
                    ]
                ]
            ]
        );
        $response->assertJsonApiValidationErrors('code' , 409);
    }
}
