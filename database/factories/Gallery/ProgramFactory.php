<?php

namespace Database\Factories\Gallery;

use App\Models\Gallery\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Program::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];
    }
}
