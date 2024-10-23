<?php

namespace Database\Factories;

use App\Http\Controllers\Controller;
use App\Models\InforStudent;
use App\Models\InforTeacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Client\Response;
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
    protected static Response  $apiData;

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
            'role_id' => fake()->numberBetween(1, 3),
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

    /**
     * @param int $count Number of model instances to create
     * @param array $ids User IDs to create from the API
     * @return \Illuminate\Support\Collection Collection of users
     */
    public function createWithExtraInfo($id)
    {
        return $this->state(function () use ($id) {
            $userApi = Controller::apiUserId($id);
            self::$apiData = $userApi;
            // self::$apiData = $userApi;
            $role_id = ($userApi['tipo'] == 'estudiante') ? 2 : 3;
            $code = $userApi['codigo'];
            return [
                'user_name' => $userApi['nombre'] . "_" . $userApi['apellidos'],
                'code' => $code,
                'email' => $userApi['email'],
                'role_id' => $role_id,
                'password' => bcrypt('password'), // Generate a random password
                'state' => $this->faker->randomElement(['0', '1']),
                'remember_token' => Str::random(10),
            ];
        });
    }

    /**
     * Crear las relaciones después de crear el usuario.
     */
    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            // Obtener los datos de la API directamente basados en el código del usuario
            self::$apiData;

            if ($user->role_id == 2 && self::$apiData) {
                $user->student()->create([
                    'career' => self::$apiData['carrera'] ?? 'Temporal',
                    'semester' => self::$apiData['semestre'] ?? 0,
                    'user_code' => $user->code,
                ]);
            }

            if ($user->role_id == 3 && self::$apiData) {
                $user->teacher()->create([
                    'department' => self::$apiData['departamento'] ?? 'Temporal',
                    'user_code' => $user->code,
                ]);
            }
        });
    }


}
