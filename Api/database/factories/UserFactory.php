<?php

namespace Database\Factories;

use App\Http\Controllers\Controller;
use App\Models\InforStudent;
use App\Models\InforTeacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpParser\Node\NullableType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $year = date('Y');
        $code = $year . $this->faker->unique()->randomNumber(5);
        // Retornar solo los atributos del usuario
        return [
            'state' => $this->faker->randomElement(['0', '1']),
            'password' => Hash::make('password'),
            'remember_token' => fake()->name(),
            'user_name' => fake()->name() . '_' . fake()->lastName(),
            'code' => $code,
            'email' => fake()->unique()->safeEmail(),
            'role_id' => fake()->numberBetween(0, 3),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function createFromApi($idFromApi = null)
    {
        if (!is_null($idFromApi)) {
            $userData = Controller::apiUserId($idFromApi);

            return $this->state(function (array $attributes) use ($userData) {
                return [
                    'user_name' => $userData['nombre'] . "_" . $userData['apellidos'],
                    'code' => $userData['codigo'],
                    'email' => $userData['email'],
                    'role_id' => ($userData['tipo'] == 'Estudiante') ? 2 : 3,
                ];
            });
        }
    }

    /**
     * @param int $count Number of model instances to create
     * @param array $ids User IDs to create from the API
     * @return \Illuminate\Support\Collection Collection of users
     */
    public function createWithExtraInfo(int $count = 1, array $ids = [])
    {
        $users = collect();

        // First, create users based on the provided $ids
        for ($i = 0; $i < count($ids); $i++) {
            $users->push($this->createUser($ids[$i]));
        }

        // Then, create users from 1 to $count, skipping already created $ids
        for ($i = 1; $i <= $count; $i++) {
            // Skip the user creation if the current $i is already in the $ids array
            if (!empty($ids) && in_array($i, $ids)) {
                continue;
            }

            // Create a new user if it's not in the provided $ids
            $users->push($this->createUser($i));
        }

        // Return the collection of users
        return $users;
    }

    private function createUser($idNodelModelo)
    {
        // Get user information from the API
        $userApi = Controller::apiUserId($idNodelModelo);

        if (!$userApi) {
            return null; // Return null if no user info is retrieved
        }

        $role_id = ($userApi['tipo'] == 'estudiante') ? 2 : 3;
        $code = $userApi['codigo'];

        // Create the user
        $user = User::create([
            'user_name' => $userApi['nombre'] . "_" . $userApi['apellidos'],
            'code' => $code,
            'email' => $userApi['email'],
            'role_id' => $role_id,
            'password' => Hash::make(Str::random(10)), // Generate a random password
            'state' => $this->faker->randomElement(['0', '1']),
            'remember_token' => Str::random(10),
        ]);

        // Save additional user information
        if ($user->role_id == 2) {
            $user->student = InforStudent::create([
                'career' => $userApi['carrera'] ?? null,
                'semester' => $userApi['semestre'] ?? null,
                'user_code' => $user->code,
            ]);
        } elseif ($user->role_id == 3) {
            $user->teacher = InforTeacher::create([
                'department' => $userApi['departamento'] ?? 'Assigned department',
                'user_code' => $user->code,
            ]);
        }

        return $user;
    }
}
