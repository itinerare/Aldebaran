<?php

namespace Database\Factories\Commission;

use App\Models\Commission\CommissionClass;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommissionClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommissionClass::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->domainWord();
        $slug = Str::lower($name);

        // Create site settings
        if (!DB::table('site_settings')->where('key', $slug.'_comms_open')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $slug.'_comms_open',
                    'value'       => 0,
                    'description' => 'Whether or not commissions are open.',
                ],
            ]);
        }

        if (!DB::table('site_settings')->where('key', $slug.'_overall_slots')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $slug.'_overall_slots',
                    'value'       => 0,
                    'description' => 'Overall number of availabile commission slots. Set to 0 to disable limits.',
                ],
            ]);
        }

        if (!DB::table('site_settings')->where('key', $slug.'_status')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $slug.'_status',
                    'value'       => 0,
                    'description' => 'Optional; a short message about commission status. Set to 0 to unset/leave blank.',
                ],
            ]);
        }

        if (!DB::table('site_settings')->where('key', $slug.'_full')->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $slug.'_full',
                    'value'       => 'Thank you for your interest in commissioning me, and I hope you consider submitting a request when next I open commissions!',
                    'description' => 'A short message used when auto-declining commissions over a slot limit.',
                ],
            ]);
        }

        // Create text pages
        $pages = [
            $slug.'tos' => [
                'name' => $name.' Commission Terms of Service',
                'text' => '<p>'.$name.' commssion terms of service go here.</p>',
                'flag' => 'tos',
            ],
            $slug.'info' => [
                'name' => $name.' Commission Info',
                'text' => '<p>'.$name.' commssion info goes here.</p>',
                'flag' => 'info',
            ],
        ];

        foreach ($pages as $key=>$page) {
            if (!DB::table('text_pages')->where('key', $key)->exists()) {
                DB::table('text_pages')->insert([
                    [
                        'key'        => $key,
                        'name'       => $page['name'],
                        'text'       => $page['text'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],

                ]);
            }
        }

        return [
            //
            'name'      => $name,
            'slug'      => $slug,
            'is_active' => 1,
            'sort'      => 0,
            'data'      => null,
        ];
    }

    /**
     * Generate an inactive class.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => 0,
            ];
        });
    }

    /**
     * Generate a class with test data.
     *
     * @param \App\Models\TextPage $page
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function testData($page)
    {
        return $this->state(function (array $attributes) use ($page) {
            return [
                'data' => '{"fields":{"'.$this->faker->unique()->domainWord().'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}},"pages":{"'.$page->id.'":{"key":"'.$page->key.'","title":"'.$page->name.'"}}}',
            ];
        });
    }
}
