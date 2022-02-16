<?php

namespace Database\Factories;

use App\Models\TextPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class TextPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TextPage::class;

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
