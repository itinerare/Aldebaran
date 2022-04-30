<?php

namespace Tests\Feature;

use App\Models\Commission\Commission;
use App\Models\Commission\CommissionPayment;
use App\Models\Commission\CommissionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCommissionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        COMMISSIONS
    *******************************************************************************/

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();
    }

    /**
     * Test commission viewing.
     *
     * @dataProvider commissionViewProvider
     *
     * @param bool       $withPayment
     * @param string     $status
     * @param array|null $data
     * @param int        $expected
     */
    public function testGetViewCommission($withPayment, $status, $data, $expected)
    {
        if ($data) {
            // Generate some keys so they can be referred back to later
            $fieldKeys = [
                $this->faker->unique()->domainWord(),
                $this->faker->unique()->domainWord(),
                $this->faker->unique()->domainWord(),
            ];

            $this->type->update([
                'data' => '{"fields":{"'.Str::lower($fieldKeys[0]).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"'.$data[0].'","rules":'.($data[1] ? '"required"' : 'null').',"choices":'.($data[2] ? '["option 1","option 2"]' : 'null').',"value":'.($data[3] ? '"'.$data[3].'"' : 'null').',"help":'.($data[4] ? '"'.$data[4].'"' : 'null').'}},"include":{"class":'.$data[6].',"category":'.$data[5].'},"pricing":{"type":"flat","cost":"10"},"extras":null,"tags":null}',
            ]);

            if ($data[5]) {
                $this->type->category->update([
                    'data' => '{"fields":{"'.Str::lower($fieldKeys[1]).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}},"include":{"class":0}}',
                ]);
            }

            if ($data[6]) {
                $this->type->category->class->update([
                    'data' => '{"fields":{"'.Str::lower($fieldKeys[2]).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}}}',
                ]);
            }

            if ($data[7]) {
                switch ($data[0]) {
                    case 'text':
                        $answer = $this->faker->domainWord();
                        break;
                    case 'textarea':
                        $answer = $this->faker->domainWord();
                        break;
                    case 'number':
                        $answer = (string) mt_rand(1, 10);
                        break;
                    case 'checkbox':
                        $answer = (string) 1;
                        break;
                    case 'choice':
                        $answer = (string) 0;
                        break;
                    case 'multiple':
                        $answer = [0 => (string) 0];
                        break;
                }
            } else {
                $answer = null;
            }
        }

        // Create a commission to view, alternately with or without payment information
        if ($withPayment) {
            $commission = Commission::factory()
                ->type($this->type->id)->has(CommissionPayment::factory()->count(2), 'payments')->status($status)
                ->create();
        } else {
            $commission = Commission::factory()
                ->type($this->type->id)->status($status)
                ->create();
        }

        // Adjust commission data as necessary
        $commission->update([
            'data' => $data && (isset($answer) || $data[5] || $data[6]) ? '{'.($data[6] ? '"'.$fieldKeys[2].'":"test",' : '').($data[5] ? '"'.$fieldKeys[1].'":"test",' : '').'"'.$fieldKeys[0].'":'.(isset($answer) ? ($data[0] != 'multiple' ? '"'.$answer.'"' : '["'.$answer[0].'"]') : 'null').'}' : null,
        ]);

        $this->actingAs($this->user)
            ->get('admin/commissions/edit/'.$commission->id)
            ->assertStatus($expected);
    }

    public function commissionViewProvider()
    {
        return [
            'basic'        => [0, 'Pending', null, 200],
            'with payment' => [1, 'Pending', null, 200],
            'accepted'     => [0, 'Accepted', null, 200],
            'declined'     => [0, 'Declined', null, 200],
            'complete'     => [0, 'Complete', null, 200],

            // Field testing
            // (string) type, (bool) rules, (bool) choices, value, (string) help, (bool) include category, (bool) include class, (bool) is empty
            'text field'                   => [0, 'Pending', ['text', 0, 0, null, null, 0, 0, 1], 200],
            'text field, empty'            => [0, 'Pending', ['text', 0, 0, null, null, 0, 0, 0], 200],
            'text field with rule'         => [0, 'Pending', ['text', 1, 0, null, null, 0, 0, 1], 200],
            'text field with value'        => [0, 'Pending', ['text', 0, 0, 'test', null, 0, 0, 1], 200],
            'text field with help'         => [0, 'Pending', ['text', 0, 0, null, 'test', 0, 0, 1], 200],
            'textbox field'                => [0, 'Pending', ['textarea', 0, 0, null, null, 0, 0, 1], 200],
            'textbox field, empty'         => [0, 'Pending', ['textarea', 0, 0, null, null, 0, 0, 0], 200],
            'number field'                 => [0, 'Pending', ['number', 0, 0, null, null, 0, 0, 1], 200],
            'number field, empty'          => [0, 'Pending', ['number', 0, 0, null, null, 0, 0, 0], 200],
            'checkbox field'               => [0, 'Pending', ['checkbox', 0, 0, null, null, 0, 0, 1], 200],
            'checkbox field, empty'        => [0, 'Pending', ['checkbox', 0, 0, null, null, 0, 0, 0], 200],
            'choose one field'             => [0, 'Pending', ['choice', 0, 0, null, null, 0, 0, 1], 200],
            'choose one field, empty'      => [0, 'Pending', ['choice', 0, 0, null, null, 0, 0, 0], 200],
            'choose multiple field'        => [0, 'Pending', ['multiple', 0, 0, null, null, 0, 0, 1], 200],
            'choose multiple field, empty' => [0, 'Pending', ['multiple', 0, 0, null, null, 0, 0, 0], 200],

            'include from category'           => [0, 'Pending', ['text', 0, 0, null, null, 1, 0, 1], 200],
            'include from class'              => [0, 'Pending', ['text', 0, 0, null, null, 0, 1, 1], 200],
            'include from category and class' => [0, 'Pending', ['text', 0, 0, null, null, 1, 1, 1], 200],
        ];
    }

    /**
     * Test commission state editing, or operations other than updating.
     * Includes banning commissioner for tidiness.
     *
     * @dataProvider commissionStateProvider
     *
     * @param string $status
     * @param string $operation
     * @param bool   $expected
     * @param mixed  $withComments
     */
    public function testPostEditCommissionState($status, $operation, $withComments, $expected)
    {
        $commission = Commission::factory()->status($status)->create();
        $comments = $withComments ? $this->faker->domainWord() : null;

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/commissions/edit/'.$commission->id.'/'.$operation, [
                'comments' => $comments,
            ]);

        if ($expected) {
            $response->assertSessionHasNoErrors();

            // Check that the commission has been updated appropriately
            switch ($operation) {
                case 'accept':
                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Accepted',
                        'comments' => $comments ?? null,
                    ]);
                    break;
                case 'complete':
                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Complete',
                        'progress' => 'Finished',
                        'comments' => $comments ?? null,
                    ]);
                    break;
                case 'decline':
                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Declined',
                        'comments' => $comments ?? null,
                    ]);
                    break;
                case 'ban':
                    // Check both that the commission and the commissioner have been
                    // updated appropriately
                    $this->assertDatabaseHas('commissioners', [
                        'id'        => $commission->commissioner->id,
                        'is_banned' => 1,
                    ]);

                    $this->assertDatabaseHas('commissions', [
                        'id'       => $commission->id,
                        'status'   => 'Declined',
                        'comments' => $comments ?? '<p>Automatically declined due to ban.</p>',
                    ]);
                    break;
            }
        } else {
            $response->assertSessionHasErrors();
        }
    }

    public function commissionStateProvider()
    {
        return [
            'accept pending'                => ['Pending', 'accept', 0, 1],
            'accept pending with comments'  => ['Pending', 'accept', 1, 1],
            'decline pending'               => ['Pending', 'decline', 0, 1],
            'decline pending with comments' => ['Pending', 'decline', 1, 1],
            'complete pending'              => ['Pending', 'complete', 0, 0],

            'accept accepted'                 => ['Accepted', 'accept', 0, 0],
            'decline accepted'                => ['Accepted', 'decline', 0, 1],
            'decline accepted with comments'  => ['Accepted', 'decline', 1, 1],
            'complete accepted'               => ['Accepted', 'complete', 0, 1],
            'complete accepted with comments' => ['Accepted', 'complete', 1, 1],

            'ban, pending'                => ['Pending', 'ban', 0, 1],
            'ban, pending with comments'  => ['Pending', 'ban', 1, 1],
            'ban, accepted'               => ['Accepted', 'ban', 0, 1],
            'ban, accepted with comments' => ['Accepted', 'ban', 1, 1],
            'ban, complete'               => ['Complete', 'ban', 0, 0],
            'ban, declined'               => ['Declined', 'ban', 0, 0],
        ];
    }
}
