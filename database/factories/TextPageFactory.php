<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TextPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->domainWord();

        return [
            //
            'name' => $name,
            'key'  => Str::lower($name),
            'text' => '<p>'.$this->faker->unique()->domainWord().'</p>',
        ];
    }
}
