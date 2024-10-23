<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class ListUserTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $users;

    // Base setup for all tests
    private function setUpBase(): void
    {
        parent::setUp();
        // Create roles
        Role::factory(3)->create();
        // Create a new admin
        $this->admin = User::create([
            'user_name' => 'Administrador',
            'email' => 'jimmisitho450@gmail.com',
            'code' => 0,
            'password' => bcrypt('admin'),
            'state' => '1',
            'role_id' => 1,
        ]);
        $this->withoutExceptionHandling();
    }

    // Setup specific for case 2
    private function setUpCase2(): void
    {
        $this->setUpBase();
        $this->users[] = User::factory()
            ->createWithExtraInfo(120)->create();
        $this->users[] = User::factory()
            ->createWithExtraInfo(118)->create();
        // $this->users[] = User::factory()->create([
        //     'state' => '0'
        // ]);
    }

    // Setup specific for case 3
    private function setUpCase3(): void
    {
        $this->setUpBase();
        $this->users[] = User::factory()->createWithExtraInfo(120)->create();
        $this->users[] = User::factory()->createWithExtraInfo(1)->create();

    }

    /**
        @test
     */
    public function show_user()
    {
        $this->setUpBase(); // Base setup
        $user = User::factory()
            ->createWithExtraInfo(rand(100,110))->create([
                'state' => '1'
            ]);

        $user = $user->first();

        // Send a request with header 'Authorization'
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken')->plainTextToken
        ])->getJson(route('api.user.show', $user->getRouteKey()));

        $userResponse = $response->json()['data'];
        $response->assertJsonApiUserResource($user, $userResponse, $this, 200);
    }

    /**
        @test
     */
    public function can_list_users()
    {
        $this->setUpCase2();
        // Send a request with header 'Authorization'
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken')->plainTextToken
        ])->getJson(route('api.user.index'));
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this);
    }

    /**
        @test
     */
    public function paginate()
    {
        $this->setUpCase2();
        $response = $this->withHeader(
            'Authorization',
            'Bearer ' . $this->admin->createToken('TestToken')->plainTextToken
        )->getJson(route('api.user.index') . '?page=1&perPage=2');
        $usersResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $usersResponse, $this);
    }

    /**
        @test
     */
    public function can_list_category_users_for_state()
    {
        $this->setUpCase2();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.user.index') . '?filter[state]=0');
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this, ["state" => "0"]);
    }

    /**
        @test
     */
    public function admin_can_list_category_users_for_role()
    {
        $this->setUpCase2();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.user.index') . '?filter[role_id]=2');
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this, ["role" => 2]);
    }

    /**
        @test
     */
    public function can_filter_user_by_category_rol_and_state()
    {
        $this->setUpCase2();
        // Send a request with header 'Authorization'
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken')->plainTextToken
        ])->getJson(route('api.user.index') . '?filter[role_id]=2&filter[state]=1');
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this, ["role" => 2, "state" => "1"]);
    }

    /**
        @test
     */
    public function can_filter_user_by_category_semester()
    {
        $this->setUpCase3();
        // Send a request with header 'Authorization'
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken')->plainTextToken
        ])->getJson(route('api.user.index') . '?filter[semester]=semester 4');
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this);
    }

    /**
        @test
     */
    public function can_filter_user_by_category_career()
    {
        $this->setUpCase3();
        // Send a request with header 'Authorization'
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken')->plainTextToken
        ])->getJson(route('api.user.index') . '?filter[career]=Contabilidad');
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this);
    }

    /**
        @test
     */
    public function admin_can_filter_students_for_semester()
    {
        $this->setUpCase3();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.user.filter', 'Semestre 4'));
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this);
    }

    /**
        @test
     */
    public function admin_can_filter_students_for_career()
    {
        $this->setUpCase3();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.user.filter', 'Contabilidad'));
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this);
    }

    /**
        @test
     */
    public function admin_can_filter_teachers_for_department()
    {
        $this->setUpCase3();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.user.filter', 'Departamento de Artes'));
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this);
    }

    /**
        @test
     */
    public function admin_can_filter_users_for_user_name()
    {
        $this->setUpCase3();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.user.filter', 'Adrain'));
        $userResponse = $response->json()['data'];
        $response->assertJsonUsersFilterResource($this->users, $userResponse, $this);
    }

    /**
        @test
     */
    public function there_is_no_matched_in_the_filter()
    {
        $this->setUpBase(); // Base setup
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->admin->createToken('TestToken', ['admin'])->plainTextToken
        ])->getJson(route('api.user.filter', 'Esto no existe'));
        $response->assertStatus(204);
    }
}
