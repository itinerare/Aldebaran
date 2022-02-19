<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'              => $this->faker->unique()->userName(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Generate a user with a safe username.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function safeUsername()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $this->faker->unique()->domainWord(),
            ];
        });
    }

    /**
     * Generate a user with a simple, known password.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function simplePass()
    {
        return $this->state(function (array $attributes) {
            return [
                'password' => Hash::make('simple_password'),
            ];
        });
    }
}
