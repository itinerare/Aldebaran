<?php

namespace Tests\Feature;

use App\Models\Commission\Commission;
use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionPiece;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceLiterature;
use App\Services\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommissionFormTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: COMMISSIONS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        // Set up testing type and default pages (necessary to view new commission page)
        $this->type = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();
        $this->artisan('add-text-pages');

        // Set up gallery service for image processing
        $this->service = new GalleryService;
    }

    /**
     * Test commission form access.
     *
     * @dataProvider commissionFormAccessProvider
     * @dataProvider commissionFormProvider
     *
     * @param array      $visibility
     * @param bool       $user
     * @param array|null $data
     * @param int        $status
     */
    public function testGetNewCommission($visibility, $user, $data, $status) {
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
                    'data' => '{"fields":{"'.Str::lower($this->faker->unique()->domainWord()).'":{"label":"'.$this->faker->unique()->domainWord().'","type":"text","rules":null,"choices":null,"value":null,"help":null}},"include":{"class":0}}',
                ]);
            }

            if ($data[6]) {
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

    public function commissionFormAccessProvider() {
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

    public function commissionFormProvider() {
        return [
            // (string) type, (bool) rules, (bool) choices, value, (string) help, (bool) include category, (bool) include class, (bool) include class in category

            // Visible
            'text field'            => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 0, 0], 200],
            'text field with rule'  => [[1, 1, 1, 1, 1, 0], 0, ['text', 1, 0, null, null, 0, 0], 200],
            'text field with value' => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, 'test', null, 0, 0], 200],
            'text field with help'  => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, 'test', 0, 0], 200],
            'textbox field'         => [[1, 1, 1, 1, 1, 0], 0, ['textarea', 0, 0, null, null, 0, 0], 200],
            'number field'          => [[1, 1, 1, 1, 1, 0], 0, ['number', 0, 0, null, null, 0, 0], 200],
            'checkbox field'        => [[1, 1, 1, 1, 1, 0], 0, ['checkbox', 0, 0, null, null, 0, 0], 200],
            'choose one field'      => [[1, 1, 1, 1, 1, 0], 0, ['choice', 0, 0, null, null, 0, 0], 200],
            'choose multiple field' => [[1, 1, 1, 1, 1, 0], 0, ['multiple', 0, 0, null, null, 0, 0], 200],

            'include from category'           => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 1, 0], 200],
            'include from class'              => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 0, 1], 200],
            'include from category and class' => [[1, 1, 1, 1, 1, 0], 0, ['text', 0, 0, null, null, 1, 1], 200],
        ];
    }

    /**
     * Test commission creation.
     *
     * @dataProvider newCommissionProvider
     *
     * @param bool       $withName
     * @param bool       $withEmail
     * @param bool       $paymentAddr
     * @param array      $visibility
     * @param array|null $data
     * @param bool       $extras
     * @param array|null $slotData
     * @param bool       $agree
     * @param bool       $isBanned
     * @param bool       $expected
     */
    public function testPostNewCommission($withName, $withEmail, $paymentAddr, $visibility, $data, $extras, $slotData, $agree, $isBanned, $expected) {
        // Adjust visibility settings
        config(['aldebaran.settings.commissions.enabled' => $visibility[0]]);
        $this->type->category->class->update(['is_active' => $visibility[1]]);
        $this->type->update([
            'is_active'  => $visibility[3],
            'is_visible' => $visibility[4],
            'data'       => '{"fields":null,"include":{"class":0,"category":0},"pricing":{"type":"flat","cost":"10"},"extras":null,"tags":null}',
        ]);
        DB::table('site_settings')->where('key', $this->type->category->class->slug.'_comms_open')->update([
            'value' => $visibility[2],
        ]);

        // If relevant, set field data
        if ($data) {
            // Generate some keys so they can be referred back to later
            $fieldKeys = [
                $this->faker->unique()->domainWord(),
                $this->faker->unique()->domainWord(),
                $this->faker->unique()->domainWord(),
            ];

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
        }

        if ($slotData) {
            // Handle filler commission info to test slot-related operations
            $slotCommission = Commission::factory()->status($slotData[0])->create();

            if ($slotData[1]) {
                // Adjust settings for type slot tests
                $slotCommission->update([
                    'commission_type' => $this->type->id,
                ]);

                $this->type->update([
                    'availability' => 1,
                ]);
            } else {
                // Adjust settings for class slot tests
                // as this is the only other relevant state
                $type = CommissionType::factory()->category($this->type->category->id)->create();

                $slotCommission->update([
                    'commission_type' => $type->id,
                ]);

                DB::table('site_settings')->where('key', $this->type->category->class->slug.'_overall_slots')->update([
                    'value' => 1,
                ]);
            }
        }

        if ($isBanned) {
            $commissioner = Commissioner::factory()->banned()->create();
        } else {
            // Generate an email address to use for form submission and lookup
            $email = $this->faker->unique()->safeEmail();
            $paymentEmail = $this->faker->unique()->safeEmail();
        }

        $response = $this
            ->post('/commissions/new', [
                'name'                   => $withName ? ($isBanned ? $commissioner->name : $this->faker->unique())->domainWord() : null,
                'email'                  => $withEmail ? ($isBanned ? $commissioner->email : $email) : null,
                'contact'                => $isBanned ? $commissioner->contact : $this->faker->unique()->domainWord(),
                'payment_address'        => $paymentAddr ?? 0,
                'paypal'                 => $paymentAddr ? $paymentEmail : null,
                'additional_information' => $extras ? $this->faker->domainWord() : null,
                'terms'                  => $agree,
                'type'                   => $this->type->id,
                'key'                    => $visibility[5] ? $this->type->key : null,
            ] + ($data ? [
                $fieldKeys[0] => $answer,
                $fieldKeys[1] => $data[5] ? 'test' : null,
                $fieldKeys[2] => $data[6] ? 'test' : null,
            ] : []));

        if ($expected == 1) {
            // Attempt to find the created commissioner and test that it exists
            $commissioner = Commissioner::where('email', $email)->where(function ($query) use ($email, $paymentAddr, $paymentEmail) {
                if ($paymentAddr) {
                    return $query->where('paypal', $paymentEmail);
                } else {
                    return $query->where('paypal', $email);
                }
            })->first();
            $this->assertModelExists($commissioner);

            // Then check for the existence of the commission using this info
            // as the commissioner is one of a few ready ways to identify the object
            $this->assertDatabaseHas('commissions', [
                'commissioner_id' => $commissioner->id,
                'status'          => 'Pending',
                'commission_type' => $this->type->id,
                'data'            => $data && (isset($answer) || $data[5] || $data[6]) ? '{'.($data[6] ? '"'.$fieldKeys[2].'":"test",' : '').($data[5] ? '"'.$fieldKeys[1].'":"test",' : '').'"'.$fieldKeys[0].'":'.(isset($answer) ? ($data[0] != 'multiple' ? '"'.$answer.'"' : '["'.$answer[0].'"]') : 'null').'}' : null,
            ]);
            $response->assertSessionHasNoErrors();
            $response->assertRedirectContains('commissions/view');
        } elseif ($expected == 0) {
            $response->assertSessionHasErrors();
        }
    }

    public function newCommissionProvider() {
        // $visibility = [commsEnabled, classActive, commsOpen, typeActive, typeVisible, withKey]

        return [
            // Access testing
            'visitor, type active, visible'           => [0, 1, 0, [1, 1, 1, 1, 1, 0], null, 0, null, 1, 0, 1],
            'visitor, type inactive, visible'         => [0, 1, 0, [1, 1, 1, 0, 1, 0], null, 0, null, 1, 0, 0],
            'visitor, type active, hidden'            => [0, 1, 0, [1, 1, 1, 1, 0, 0], null, 0, null, 1, 0, 0],
            'visitor, type inactive, hidden'          => [0, 1, 0, [1, 1, 1, 0, 0, 0], null, 0, null, 1, 0, 0],
            'visitor, type active, hidden with key'   => [0, 1, 0, [1, 1, 1, 1, 0, 1], null, 0, null, 1, 0, 1],
            'visitor, type inactive, hidden with key' => [0, 1, 0, [1, 1, 1, 0, 0, 1], null, 0, null, 1, 0, 0],
            'visitor, comms closed'                   => [0, 1, 0, [1, 1, 0, 1, 1, 0], null, 0, null, 1, 0, 0],
            'visitor, class inactive'                 => [0, 1, 0, [1, 0, 1, 1, 1, 0], null, 0, null, 1, 0, 0],
            'visitor, comms disabled'                 => [0, 1, 0, [0, 1, 1, 1, 1, 0], null, 0, null, 1, 0, 0],

            // Form testing
            'basic'               => [0, 1, 0, [1, 1, 1, 1, 1, 0], null, 0, null, 1, 0, 1],
            'without email'       => [0, 0, 0, [1, 1, 1, 1, 1, 0], null, 0, null, 1, 0, 0],
            'non-agreement'       => [0, 1, 0, [1, 1, 1, 1, 1, 0], null, 0, null, 0, 0, 0],
            'banned commissioner' => [0, 1, 0, [1, 1, 1, 1, 1, 0], null, 0, null, 1, 1, 0],

            // Slot testing
            'with full type'  => [0, 1, 0, [1, 1, 1, 1, 1, 0], null, 0, ['Accepted', 1, 1], 1, 0, 0],
            'with full class' => [0, 1, 0, [1, 1, 1, 1, 1, 0], null, 0, ['Accepted', 0, 1], 1, 0, 0],

            // Form field testing
            // (string) type, (bool) rules, (bool) choices, value, (string) help, (bool) include category, (bool) include class, (bool) is empty
            'text field'                   => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 0, 0, null, null, 0, 0, 1], 0, null, 1, 0, 1],
            'text field, empty'            => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 0, 0, null, null, 0, 0, 0], 0, null, 1, 0, 1],
            'text field with rule'         => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 1, 0, null, null, 0, 0, 1], 0, null, 1, 0, 1],
            'text field with rule, empty'  => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 1, 0, null, null, 0, 0, 0], 0, null, 1, 0, 0],
            'text field with value'        => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 0, 0, 'test', null, 0, 0, 1], 0, null, 1, 0, 1],
            'text field with help'         => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 0, 0, null, 'test', 0, 0, 1], 0, null, 1, 0, 1],
            'textbox field'                => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['textarea', 0, 0, null, null, 0, 0, 1], 0, null, 1, 0, 1],
            'textbox field, empty'         => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['textarea', 0, 0, null, null, 0, 0, 0], 0, null, 1, 0, 1],
            'number field'                 => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['number', 0, 0, null, null, 0, 0, 1], 0, null, 1, 0, 1],
            'number field,empty'           => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['number', 0, 0, null, null, 0, 0, 0], 0, null, 1, 0, 1],
            'checkbox field'               => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['checkbox', 0, 0, null, null, 0, 0, 1], 0, null, 1, 0, 1],
            'checkbox field, empty'        => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['checkbox', 0, 0, null, null, 0, 0, 0], 0, null, 1, 0, 1],
            'choose one field'             => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['choice', 0, 0, null, null, 0, 0, 1], 0, null, 1, 0, 1],
            'choose one field, empty'      => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['choice', 0, 0, null, null, 0, 0, 0], 0, null, 1, 0, 1],
            'choose multiple field'        => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['multiple', 0, 0, null, null, 0, 0, 1], 0, null, 1, 0, 1],
            'choose multiple field, empty' => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['multiple', 0, 0, null, null, 0, 0, 0], 0, null, 1, 0, 1],

            'include from category'           => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 0, 0, null, null, 1, 0, 1], 0, null, 1, 0, 1],
            'include from class'              => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 0, 0, null, null, 0, 1, 1], 0, null, 1, 0, 1],
            'include from category and class' => [0, 1, 0, [1, 1, 1, 1, 1, 0], ['text', 0, 0, null, null, 1, 1, 1], 0, null, 1, 0, 1],
        ];
    }

    /**
     * Test commission viewing.
     *
     * @dataProvider commissionViewProvider
     *
     * @param bool       $isValid
     * @param array|null $data
     * @param int        $expected
     * @param array|null $pieceData
     * @param mixed      $status
     */
    public function testGetViewCommission($isValid, $status, $data, $pieceData, $expected) {
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

        // Create a commission to view
        $commission = Commission::factory()
            ->type($this->type->id)->status($status)
            ->create([
                'data' => $data && (isset($answer) || $data[5] || $data[6]) ? '{'.($data[6] ? '"'.$fieldKeys[2].'":"test",' : '').($data[5] ? '"'.$fieldKeys[1].'":"test",' : '').'"'.$fieldKeys[0].'":'.(isset($answer) ? ($data[0] != 'multiple' ? '"'.$answer.'"' : '["'.$answer[0].'"]') : 'null').'}' : null,
            ]);

        if ($pieceData) {
            // Create a piece and link to the commission
            $piece = Piece::factory()->create([
                'is_visible' => $pieceData[1],
            ]);
            CommissionPiece::factory()->piece($piece->id)->commission($commission->id)->create();

            if ($pieceData[0]) {
                // Create images and test files
                $image = PieceImage::factory()->piece($piece->id)->create();
                $this->service->testImages($image);
            }

            if ($pieceData[2]) {
                $literature = PieceLiterature::factory()
                    ->piece($piece->id)->create();
            }
        }

        // Either take the commission's valid URL or generate a fake one
        $url = $isValid ? $commission->url : mt_rand(1, 10).'_'.randomString(15);

        $response = $this
            ->get($url)
            ->assertStatus($expected);

        if ($pieceData) {
            if ($expected) {
                // Check that the piece is displayed in some fashion
                $response->assertSee($piece->name);

                if ($pieceData[0]) {
                    // Check that the image's thumbnail is present/displayed
                    $response->assertSee($image->thumbnailUrl);
                }

                if ($pieceData[2]) {
                    // Check that the literature is present/displayed
                    $response->assertSee('Literature #'.$literature->id);
                }
            }

            if ($pieceData[0]) {
                // Clean up test images
                $this->service->testImages($image, false);
            }
        }
    }

    public function commissionViewProvider() {
        return [
            'basic'               => [1, 'Pending', null, null, 200],
            'accepted commission' => [1, 'Accepted', null, null, 200],
            'complete commission' => [1, 'Complete', null, null, 200],
            'declined commission' => [1, 'Declined', null, null, 200],
            'invalid commission'  => [0, 'Pending', null, null, 404],

            // $pieceData = [(bool) withImage, (bool) isVisible, (bool) withLiterature]
            'with piece'                 => [1, 'Accepted', null, [0, 1, 0], 200],
            'with hidden piece'          => [1, 'Accepted', null, [0, 0, 0], 200],
            'with piece with image'      => [1, 'Accepted', null, [1, 1, 0], 200],
            'with piece with literature' => [1, 'Accepted', null, [1, 1, 1], 200],

            // Field testing
            // (string) type, (bool) rules, (bool) choices, value, (string) help, (bool) include category, (bool) include class, (bool) is empty
            'text field'                   => [1, 'Pending', ['text', 0, 0, null, null, 0, 0, 1], null, 200],
            'text field, empty'            => [1, 'Pending', ['text', 0, 0, null, null, 0, 0, 0], null, 200],
            'text field with rule'         => [1, 'Pending', ['text', 1, 0, null, null, 0, 0, 1], null, 200],
            'text field with value'        => [1, 'Pending', ['text', 0, 0, 'test', null, 0, 0, 1], null, 200],
            'text field with help'         => [1, 'Pending', ['text', 0, 0, null, 'test', 0, 0, 1], null, 200],
            'textbox field'                => [1, 'Pending', ['textarea', 0, 0, null, null, 0, 0, 1], null, 200],
            'textbox field, empty'         => [1, 'Pending', ['textarea', 0, 0, null, null, 0, 0, 0], null, 200],
            'number field'                 => [1, 'Pending', ['number', 0, 0, null, null, 0, 0, 1], null, 200],
            'number field, empty'          => [1, 'Pending', ['number', 0, 0, null, null, 0, 0, 0], null, 200],
            'checkbox field'               => [1, 'Pending', ['checkbox', 0, 0, null, null, 0, 0, 1], null, 200],
            'checkbox field, empty'        => [1, 'Pending', ['checkbox', 0, 0, null, null, 0, 0, 0], null, 200],
            'choose one field'             => [1, 'Pending', ['choice', 0, 0, null, null, 0, 0, 1], null, 200],
            'choose one field, empty'      => [1, 'Pending', ['choice', 0, 0, null, null, 0, 0, 0], null, 200],
            'choose multiple field'        => [1, 'Pending', ['multiple', 0, 0, null, null, 0, 0, 1], null, 200],
            'choose multiple field, empty' => [1, 'Pending', ['multiple', 0, 0, null, null, 0, 0, 0], null, 200],

            'include from category'           => [1, 'Pending', ['text', 0, 0, null, null, 1, 0, 1], null, 200],
            'include from class'              => [1, 'Pending', ['text', 0, 0, null, null, 0, 1, 1], null, 200],
            'include from category and class' => [1, 'Pending', ['text', 0, 0, null, null, 1, 1, 1], null, 200],
        ];
    }
}
