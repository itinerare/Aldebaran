<?php

namespace Tests\Feature;

use App\Models\Commission\CommissionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommissionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        COMMISSIONS
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        // Set up testing type and default pages (necessary to view new commission page)
        $this->type = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();
        $this->artisan('add-text-pages');
    }

    /**
     * Test commission form access.
     *
     * @dataProvider commissionAccessProvider
     * @dataProvider commissionFormProvider
     *
     * @param array      $visibility
     * @param bool       $user
     * @param array|null $data
     * @param int        $status
     */
    public function testGetNewCommission($visibility, $user, $data, $status)
    {
        // Adjust various settings
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);
        $this->type->category->class->update(['is_active' => $visibility[1]]);
        $this->type->update([
            'is_active'  => $visibility[3],
            'is_visible' => $visibility[4],
            'data'       => '{"fields":null,"include":{"class":0,"category":0},"pricing":{"type":"flat","cost":"10"},"extras":null,"tags":null}',
        ]);

        // If relevant, set field data
        if ($data) {
            $this->type->update([
                'data' => '{"fields":{"'.Str::lower($this->faker->unique()->domainWord()).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"'.$data[0].'","rules":'.($data[1] ? '"required"' : 'null').',"choices":'.($data[2] ? '["option 1","option 2"]' : 'null').',"value":'.($data[3] ? '"'.$data[3].'"' : 'null').',"help":'.($data[4] ? '"'.$data[4].'"' : 'null').'}},"include":{"class":'.$data[6].',"category":'.$data[5].'},"pricing":{"type":"flat","cost":"10"},"extras":null,"tags":null}',
            ]);

            if ($data[5]) {
                $this->type->category->update([
                    'data' => '{"fields":{"'.Str::lower($this->faker->unique()->domainWord()).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}},"include":{"class":'.$data[7].'}}',
                ]);
            }

            if ($data[6] || $data[7]) {
                $this->type->category->class->update([
                    'data' => '{"fields":{"'.Str::lower($this->faker->unique()->domainWord()).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}}}',
                ]);
            }
        }

        if ($visibility[2]) {
            DB::table('site_settings')->where('key', $this->type->category->class->slug.'_comms_open')->update([
                'value' => 1,
            ]);
        }

        // Set up URL
        $url = 'commissions/'.$this->type->category->class->slug.'/new?type='.$this->type->id.($visibility[5] ? '&key='.$this->type->key : '');

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get($url);
        } else {
            $response = $this->get($url);
        }

        $response->assertStatus($status);
    }

    public function commissionFormProvider()
    {
        return [
            // (string) type, (bool) rules, (bool) choices, value, (string) help, (bool) include category, (bool) include class, (bool) include class in category

            // Visible
            'text field'            => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 0, 0, 0], 200],
            'text field with rule'  => [[1, 1, 1, 1, 1, 0], 0, ['text', 1, 0, null, null, 0, 0, 0], 200],
            'text field with value' => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, 'test', null, 0, 0, 0], 200],
            'text field with help'  => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, 'test', 0, 0, 0], 200],
            'textbox field'         => [[1, 1, 1, 1, 1, 0], 0, ['textarea', 0, 0, null, null, 0, 0, 0], 200],
            'number field'          => [[1, 1, 1, 1, 1, 0], 0, ['number', 0, 0, null, null, 0, 0, 0], 200],
            'checkbox field'        => [[1, 1, 1, 1, 1, 0], 0, ['checkbox', 0, 0, null, null, 0, 0, 0], 200],
            'choose one field'      => [[1, 1, 1, 1, 1, 0], 0, ['choice', 0, 0, null, null, 0, 0, 0], 200],
            'choose multiple field' => [[1, 1, 1, 1, 1, 0], 0, ['multiple', 0, 0, null, null, 0, 0, 0], 200],

            'include from category'           => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 1, 0, 0], 200],
            'include from class'              => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 0, 1, 0], 200],
            'include from category and class' => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 1, 1, 0], 200],
            'include from class via category' => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 1, 0, 1], 200],
        ];
    }

    public function commissionAccessProvider()
    {
        // $visibility = [commsEnabled, classActive, commsOpen, typeActive, typeVisible, withKey]

        return [
            'visitor, type active, visible'           => [[1, 1, 1, 1, 1, 0], 0, null, 200],
            'visitor, type inactive, visible'         => [[1, 1, 1, 0, 1, 0], 0, null, 404],
            'visitor, type active, hidden'            => [[1, 1, 1, 1, 0, 0], 0, null, 404],
            'visitor, type inactive, hidden'          => [[1, 1, 1, 0, 0, 0], 0, null, 404],
            'visitor, type active, hidden with key'   => [[1, 1, 1, 1, 0, 1], 0, null, 200],
            'visitor, type inactive, hidden with key' => [[1, 1, 1, 0, 0, 1], 0, null, 404],
            'visitor, comms closed'                   => [[1, 1, 0, 1, 1, 0], 0, null, 404],
            'visitor, class inactive'                 => [[1, 0, 1, 1, 1, 0], 0, null, 404],
            'visitor, comms disabled'                 => [[0, 1, 1, 1, 1, 0], 0, null, 404],
            'user, type active, visible'              => [[1, 1, 1, 1, 1, 0], 1, null, 200],
            'user, type inactive, visible'            => [[1, 1, 1, 0, 1, 0], 1, null, 404],
            'user, type active, hidden'               => [[1, 1, 1, 1, 0, 0], 1, null, 404],
            'user, type inactive, hidden'             => [[1, 1, 1, 0, 0, 0], 1, null, 404],
            'user, type active, hidden with key'      => [[1, 1, 1, 1, 0, 1], 1, null, 200],
            'user, type inactive, hidden with key'    => [[1, 1, 1, 0, 0, 1], 1, null, 404],
            'user, comms closed'                      => [[1, 1, 0, 1, 1, 0], 1, null, 404],
            'user, class inactive'                    => [[1, 0, 1, 1, 1, 0], 1, null, 404],
            'user, comms disabled'                    => [[0, 1, 1, 1, 1, 0], 1, null, 404],
        ];
    }
}
