<?php

namespace Tests\Feature;

use App\Models\MailingList\MailingList;
use App\Models\MailingList\MailingListEntry;
use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminMailingListTest extends TestCase {
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        MAILING LISTS
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        config(['aldebaran.settings.email_features' => 1]);

        // Generate name and description values
        $this->name = $this->faker()->unique()->domainWord();
        $this->description = '<p>'.$this->faker->unique()->domainWord().'</p>';
    }

    /**
     * Test mailing list index access.
     *
     * @dataProvider listIndexProvider
     *
     * @param array $data
     */
    public function testGetMailingListIndex($data) {
        if ($data) {
            $list = MailingList::factory()->create([
                'is_open' => $data[2],
            ]);

            if ($data[0]) {
                MailingListSubscriber::factory()->mailingList($list->id)->create();
            }

            if ($data[1]) {
                $entry = MailingListEntry::factory()->mailingList($list->id)->create();
            }
        }

        $response = $this->actingAs($this->user)
            ->get('/admin/mailing-lists')
            ->assertStatus(200);

        if ($data) {
            $response->assertViewHas('mailingLists', function ($mailingLists) use ($list) {
                return $mailingLists->contains($list);
            });

            if ($data[1]) {
                $response->assertSee($entry->subject);
            }
        } else {
            $response->assertViewHas('mailingLists', function ($mailingLists) {
                return $mailingLists->count() == 0;
            });
        }
    }

    public static function listIndexProvider() {
        return [
            'empty'                                    => [null],
            'with open list'                           => [[0, 0, 1]],
            'with closed list'                         => [[0, 0, 0]],
            'with open list with subscriber'           => [[1, 0, 1]],
            'with open list with entry'                => [[0, 1, 1]],
            'with open list with subscriber and entry' => [[1, 1, 1]],
        ];
    }

    /**
     * Test mailing list create access.
     */
    public function testGetCreateMailingList() {
        $response = $this->actingAs($this->user)
            ->get('/admin/mailing-lists/create')
            ->assertStatus(200);
    }

    /**
     * Test mailng list edit access.
     *
     * @dataProvider listGetEditProvider
     *
     * @param bool $withSubscriber
     * @param bool $withEntry
     * @param bool $isOpen
     */
    public function testGetEditMailingList($withSubscriber, $withEntry, $isOpen) {
        $list = MailingList::factory()->create([
            'is_open' => $isOpen,
        ]);

        if ($withSubscriber) {
            $subscriber = MailingListSubscriber::factory()->mailingList($list->id)->create();
        }

        if ($withEntry) {
            $entry = MailingListEntry::factory()->mailingList($list->id)->create();
        }

        $response = $this->actingAs($this->user)
            ->get('/admin/mailing-lists/edit/'.$list->id)
            ->assertStatus(200);

        if ($withSubscriber) {
            $response->assertSee($subscriber->email);
        }

        if ($withEntry) {
            $response->assertSee($entry->subject);
        }
    }

    public static function listGetEditProvider() {
        return [
            'with open list'                           => [0, 0, 1],
            'with closed list'                         => [0, 0, 0],
            'with open list with subscriber'           => [1, 0, 1],
            'with open list with entry'                => [0, 1, 1],
            'with open list with subscriber and entry' => [1, 1, 1],
        ];
    }

    /**
     * Test mailng list creation.
     *
     * @dataProvider listCreateEditProvider
     *
     * @param bool $withData
     * @param bool $description
     * @param bool $isOpen
     */
    public function testPostCreateMailingList($withData, $description, $isOpen) {
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/create', [
                'name'        => $this->name,
                'description' => $description ? $this->description : null,
                'is_open'     => $isOpen,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('mailing_lists', [
            'name'        => $this->name,
            'description' => $description ? $this->description : null,
            'is_open'     => $isOpen,
        ]);
    }

    /**
     * Test mailing list editing.
     *
     * @dataProvider listCreateEditProvider
     *
     * @param bool $withData
     * @param bool $description
     * @param bool $isOpen
     */
    public function testPostEditMailingList($withData, $description, $isOpen) {
        if ($withData) {
            $list = MailingList::factory()
                ->description()->closed()->create();
        } else {
            $list = MailingList::factory()->create();
        }

        $response = $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/edit/'.$list->id, [
                'name'        => $this->name,
                'description' => $description ? $this->description : null,
                'is_open'     => $isOpen,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('mailing_lists', [
            'id'          => $list->id,
            'name'        => $this->name,
            'description' => $description ? $this->description : null,
            'is_open'     => $isOpen,
        ]);
    }

    public static function listCreateEditProvider() {
        return [
            'closed'                  => [0, 0, 0],
            'open'                    => [0, 0, 1],
            'with desc, closed'       => [0, 1, 0],
            'with desc, open'         => [0, 1, 1],
            'with data, closed'       => [1, 0, 0],
            'with data, open'         => [1, 0, 1],
            'with data, desc, closed' => [1, 1, 0],
            'with data, desc, open'   => [1, 1, 1],
        ];
    }

    /**
     * Test mailing list delete access.
     */
    public function testGetDeleteMailingList() {
        $list = MailingList::factory()->create();

        $this->actingAs($this->user)
            ->get('/admin/mailing-lists/delete/'.$list->id)
            ->assertStatus(200);
    }

    /**
     * Test mailing list deletion.
     *
     * @dataProvider listDeleteProvider
     *
     * @param bool $withEntry
     * @param bool $withSubscriber
     */
    public function testPostDeleteMailingList($withEntry, $withSubscriber) {
        $list = MailingList::factory()->create();

        if ($withSubscriber) {
            $subscriber = MailingListSubscriber::factory()->mailingList($list->id)->create();
        }

        if ($withEntry) {
            $entry = MailingListEntry::factory()->mailingList($list->id)->create();
        }

        $this
            ->actingAs($this->user)
            ->post('/admin/mailing-lists/delete/'.$list->id);

        if ($withSubscriber) {
            $this->assertModelMissing($subscriber);
        }

        if ($withEntry) {
            $this->assertModelMissing($entry);
        }

        $this->assertModelMissing($list);
    }

    public static function listDeleteProvider() {
        return [
            'with nothing'    => [0, 0],
            'with subscriber' => [0, 1],
            'with entry'      => [1, 0],
            'with both'       => [1, 1],
        ];
    }
}
