<?php

namespace Tests\Feature;

use App\Mail\QuoteRequested;
use App\Models\Commission\Commission;
use App\Models\Commission\Commissioner;
use App\Models\Commission\CommissionQuote;
use App\Models\Commission\CommissionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CommissionQuoteFormTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        PUBLIC: COMMISSIONS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        Mail::fake();

        // Set up testing type and default pages (necessary to view new commission page)
        $this->type = CommissionType::factory()->testData(['type' => 'flat', 'cost' => 10])->create();
        $this->artisan('add-text-pages');
    }

    /**
     * Test quote form access.
     *
     * @dataProvider commissionFormAccessProvider
     *
     * @param array $visibility
     * @param bool  $user
     * @param int   $status
     */
    public function testGetNewQuote($visibility, $user, $status) {
        // Adjust various settings
        config(['aldebaran.commissions.enabled' => $visibility[0]]);
        $this->type->category->class->update(['is_active' => $visibility[1]]);
        $this->type->update([
            'is_visible'  => $visibility[3],
            'is_active'   => $visibility[4],
            'quotes_open' => $visibility[2],
        ]);

        // Set up URL
        $url = 'commissions/'.$this->type->category->class->slug.'/quotes/new?type='.$this->type->id;

        // Basic access testing
        if ($user) {
            $response = $this->actingAs($this->user)->get($url);
        } else {
            $response = $this->get($url);
        }

        $response->assertStatus($status);
    }

    public function commissionFormAccessProvider() {
        // $visibility = [commsEnabled, classActive, quotesOpen, typeVisible, typeActive]

        return [
            'visitor, quotes open, visible, active'     => [[1, 1, 1, 1, 1], 0, 200],
            'visitor, quotes closed, visible, active'   => [[1, 1, 0, 1, 1], 0, 404],
            'visitor, quotes open, visible, inactive'   => [[1, 1, 1, 1, 0], 0, 200],
            'visitor, quotes closed, visible, inactive' => [[1, 1, 0, 1, 0], 0, 404],
            'visitor, quotes open, hidden, active'      => [[1, 1, 1, 0, 1], 0, 200],
            'visitor, quotes closed, hidden, active'    => [[1, 1, 0, 0, 1], 0, 404],
            'visitor, quotes open, hidden, inactive'    => [[1, 1, 1, 0, 0], 0, 200],
            'visitor, quotes closed, hidden, inactive'  => [[1, 1, 0, 0, 0], 0, 404],
            'visitor, class inactive'                   => [[1, 0, 1, 1, 1], 0, 404],
            'visitor, comms disabled'                   => [[0, 1, 1, 1, 1], 0, 404],
            'user, quotes open, visible, active'        => [[1, 1, 1, 1, 1], 1, 200],
            'user, quotes closed, visible, active'      => [[1, 1, 0, 1, 1], 1, 404],
            'user, quotes open, visible, inactive'      => [[1, 1, 1, 1, 0], 1, 200],
            'user, quotes closed, visible, inactive'    => [[1, 1, 0, 1, 0], 1, 404],
            'user, quotes open, hidden, active'         => [[1, 1, 1, 0, 1], 1, 200],
            'user, quotes closed, hidden, active'       => [[1, 1, 0, 0, 1], 1, 404],
            'user, quotes open, hidden, inactive'       => [[1, 1, 1, 0, 0], 1, 200],
            'user, quotes closed, hidden, inactive'     => [[1, 1, 0, 0, 0], 1, 404],
            'user, class inactive'                      => [[1, 0, 1, 1, 1], 1, 404],
            'user, comms disabled'                      => [[0, 1, 1, 1, 1], 1, 404],
        ];
    }

    /**
     * Test quote creation.
     *
     * @dataProvider newQuoteProvider
     *
     * @param bool  $withName
     * @param bool  $withEmail
     * @param bool  $sendNotifs
     * @param array $visibility
     * @param bool  $withSubject
     * @param bool  $agree
     * @param bool  $isBanned
     * @param bool  $expected
     */
    public function testPostNewQuote($withName, $withEmail, $sendNotifs, $visibility, $withSubject, $agree, $isBanned, $expected) {
        if ($withEmail) {
            // Enable email notifications
            config(['aldebaran.settings.email_features' => 1]);

            $this->artisan('add-site-settings');
            DB::table('site_settings')->where('key', 'notif_emails')->update([
                'value' => 1,
            ]);
        }

        // Adjust visibility settings
        config(['aldebaran.commissions.enabled' => $visibility[0]]);
        $this->type->category->class->update(['is_active' => $visibility[1]]);
        $this->type->update([
            'is_visible'  => $visibility[3],
            'is_active'   => $visibility[4],
            'quotes_open' => $visibility[2],
        ]);

        if ($isBanned) {
            $commissioner = Commissioner::factory()->banned()->create();
        } else {
            // Generate an email address to use for form submission and lookup
            $email = $this->faker->unique()->safeEmail();
        }

        $response = $this
            ->post('/commissions/quotes/new', [
                'name'                  => $withName ? ($isBanned ? $commissioner->name : $this->faker->unique())->domainWord() : null,
                'email'                 => $withEmail ? ($isBanned ? $commissioner->email : $email) : null,
                'contact'               => $isBanned ? $commissioner->contact : $this->faker->unique()->domainWord(),
                'subject'               => $withSubject ? $this->faker->domainWord() : null,
                'description'           => $this->faker->realText(),
                'terms'                 => $agree,
                'commission_type_id'    => $this->type->id,
                'receive_notifications' => $sendNotifs,
            ]);

        if ($expected == 1) {
            // Attempt to find the created commissioner and test that it exists
            $commissioner = Commissioner::where('email', $email)->where('receive_notifications', $sendNotifs)->first();
            $this->assertModelExists($commissioner);

            // Then check for the existence of the commission using this info
            // as the commissioner is one of a few ready ways to identify the object
            $this->assertDatabaseHas('commission_quotes', [
                'commissioner_id'    => $commissioner->id,
                'status'             => 'Pending',
                'commission_type_id' => $this->type->id,
            ]);
            $response->assertSessionHasNoErrors();
            $response->assertRedirectContains('commissions/quotes/view');

            if ($withEmail) {
                // This works locally but not via GitHub action;
                // feel free to uncomment when running your own tests.
                //Mail::assertSent(QuoteRequested::class);
            } else {
                Mail::assertNotSent(QuoteRequested::class);
            }
        } elseif ($expected == 0) {
            $response->assertSessionHasErrors();
        }
    }

    public function newQuoteProvider() {
        // $visibility = [commsEnabled, classActive, quotesOpen, typeVisible, typeActive]

        return [
            // Access testing
            'visitor, quotes open, type active, visible'   => [0, 1, 0, [1, 1, 1, 1, 1], 0, 1, 0, 1],
            'visitor, quotes open, type inactive, visible' => [0, 1, 0, [1, 1, 1, 0, 1], 0, 1, 0, 1],
            'visitor, quotes open, type active, hidden'    => [0, 1, 0, [1, 1, 1, 1, 0], 0, 1, 0, 1],
            'visitor, quotes open, type inactive, hidden'  => [0, 1, 0, [1, 1, 1, 0, 0], 0, 1, 0, 1],
            'visitor, quotes closed'                       => [0, 1, 0, [1, 1, 0, 1, 1], 0, 1, 0, 0],
            'visitor, class inactive'                      => [0, 1, 0, [1, 0, 1, 1, 1], 0, 1, 0, 0],
            'visitor, comms disabled'                      => [0, 1, 0, [0, 1, 1, 1, 1], 0, 1, 0, 0],

            // Form testing
            'basic'                    => [0, 1, 0, [1, 1, 1, 1, 1], 0, 1, 0, 1],
            'with notification opt-in' => [0, 1, 1, [1, 1, 1, 1, 1], 0, 1, 0, 1],
            'with subject'             => [0, 1, 0, [1, 1, 1, 1, 1], 1, 1, 0, 1],
            'without email'            => [0, 0, 0, [1, 1, 1, 1, 1], 0, 1, 0, 0],
            'non-agreement'            => [0, 1, 0, [1, 1, 1, 1, 1], 0, 0, 0, 0],
            'banned commissioner'      => [0, 1, 0, [1, 1, 1, 1, 1], 0, 1, 1, 0],
        ];
    }

    /**
     * Test quote viewing.
     *
     * @dataProvider quoteViewProvider
     *
     * @param bool   $isValid
     * @param string $status
     * @param bool   $withCommission
     * @param int    $expected
     */
    public function testGetViewQuote($isValid, $status, $withCommission, $expected) {
        if ($withCommission) {
            $commission = Commission::factory()->create();
        }

        // Create a quote to view
        $quote = CommissionQuote::factory()->status($status)->create($withCommission ? [
            'commission_type_id' => $commission->type->id,
            'commission_id'      => $commission->id,
        ] : []);

        // Either take the commission's valid URL or generate a fake one
        $url = $isValid ? $quote->url : mt_rand(1, 10).'_'.randomString(15);

        $response = $this
            ->get($url)
            ->assertStatus($expected);

        // Check that the commission's URL is present, if set
        if ($withCommission) {
            $response->assertSee($commission->url);
        }
    }

    public function quoteViewProvider() {
        return [
            'basic'                    => [1, 'Pending', 0, 200],
            'accepted quote'           => [1, 'Accepted', 0, 200],
            'accepted quote with comm' => [1, 'Accepted', 1, 200],
            'complete quote'           => [1, 'Complete', 0, 200],
            'complete quote with comm' => [1, 'Complete', 1, 200],
            'declined quote'           => [1, 'Declined', 0, 200],
            'declined quote with comm' => [1, 'Declined', 1, 200],
            'invalid quote'            => [0, 'Pending', 0, 404],
        ];
    }
}
