<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{

    private $user;
    protected function setUp(): void
    {
        parent::setUp();
        //Create roles
        Role::factory(3)->create();
        $this->user = User::factory(1)->create([
            'state' => '1'
        ])->first();
    }

    use RefreshDatabase;
    public function test_can_to_do_login(): void
    {

        $this->withoutExceptionHandling();
        //Send a request
        $response = $this->post(
            route('api.user.login'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'email' => $this->user->email,
                        'password' => 'password'
                    ]
                ]
            ]
        );
        // Assert the user resource structure
        $response->assertJsonApiAuthResource($this->user);
    }

    public function test_incorrect_password()
    {
        $response = $this->postJson(
            route('api.user.login'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'email' => $this->user->email,
                        'password' => 'passwo'
                    ]
                ]
            ]
        );
        // Using the macro create in MakesJsonRequest to validate
        $response->assertJsonApiValidationErrors('password' , 401);
    }

    public function test_account_does_not_exist()
    {

        $response = $this->postJson(
            route('api.user.login'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'email' => 'dudoqueexista@gmail.com',
                        'password' => 'password'
                    ]
                ]
            ]
        );
        // Using the macro create in MakesJsonRequest to validate
        $response->assertJsonApiValidationErrors('email' , 401);
    }

    public function test_account_is_suspended()
    {
        $user = User::factory(1)->create([
            'state' => '0'
        ])->first();
        $response = $this->postJson(
            route('api.user.login'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'email' => $user->email,
                        'password' => 'password'
                    ]
                ]
            ]
        );
        $response->assertJsonApiValidationErrors('email', 403);
    }

    public function test_password_is_required()
    {
        // Send a request without password
        $response = $this->postJson(
            route('api.user.login'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'email' => $this->user->email,
                    ]
                ]
            ]
        );

        $response->assertJsonApiValidationErrors('password');
    }

    public function test_email_is_required()
    {
        // Send a request without email
        $response = $this->postJson(
            route('api.user.login'),
            [
                'data' => [
                    'type' => 'user',
                    'attributes' => [
                        'password' => 'password',
                    ]
                ]
            ]
        );
        $response->assertJsonApiValidationErrors('email');
    }

    public function test_can_to_do_logout()
    {
        // Request to log out
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->user->createToken('TestToken' , ['user'])->plainTextToken
        ])->postJson(route('api.user.logout'))->dump();
        $response->assertStatus(204);
    }
}
