<?php

namespace Tests\Feature;

use App\Models\Gallery\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DataGalleryProgramTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /******************************************************************************
        GALLERY DATA: PROGRAMS
    *******************************************************************************/

    /**
     * Test program index access.
     */
    public function test_canGetProgramIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/programs')
            ->assertStatus(200);
    }

    /**
     * Test program create access.
     */
    public function test_canGetCreateProgram()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/programs/create')
            ->assertStatus(200);
    }

    /**
     * Test program edit access.
     */
    public function test_canGetEditProgram()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/programs/edit/'.Program::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test program creation.
     */
    public function test_canPostCreateProgram()
    {
        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/programs/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('programs', [
            'name' => $data['name'],
        ]);
    }

    /**
     * Test program editing.
     */
    public function test_canPostEditProgram()
    {
        $program = Program::factory()->create();

        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/programs/edit/'.$program->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('programs', [
            'id'   => $program->id,
            'name' => $data['name'],
        ]);
    }

    /**
     * Test program creation with an icon.
     */
    public function test_canPostCreateProgramWithIcon()
    {
        // Define some basic data
        $data = [
            'name'  => $this->faker->unique()->domainWord(),
            'image' => UploadedFile::fake()->image('test_image.png'),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/programs/create', $data);

        $program = Program::where('name', $data['name'])->first();

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('programs', [
            'name'      => $data['name'],
            'has_image' => 1,
        ]);

        // Check that the file is now present
        $this->assertTrue(File::exists(public_path('images/programs/'.$program->id.'-image.png')));

        // Perform cleanup
        if (File::exists(public_path('images/programs/'.$program->id.'-image.png'))) {
            unlink('public/images/programs/'.$program->id.'-image.png');
        }
    }

    /**
     * Test program editing with an icon.
     */
    public function test_canPostEditProgramWithIcon()
    {
        $program = Program::factory()->create();

        // Define some basic data
        $data = [
            'name'  => $this->faker->unique()->domainWord(),
            'image' => UploadedFile::fake()->image('test_image.png'),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/programs/edit/'.$program->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('programs', [
            'id'        => $program->id,
            'name'      => $data['name'],
            'has_image' => 1,
        ]);

        // Check that the file is now present
        $this->assertTrue(File::exists(public_path('images/programs/'.$program->id.'-image.png')));

        // Perform cleanup
        if (File::exists(public_path('images/programs/'.$program->id.'-image.png'))) {
            unlink('public/images/programs/'.$program->id.'-image.png');
        }
    }

    /**
     * Test program creation with visibility.
     */
    public function test_canPostCreateProgramVisibility()
    {
        // Define some basic data
        $data = [
            'name'       => $this->faker->unique()->domainWord(),
            'is_visible' => 1,
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/programs/create', $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('programs', [
            'name'       => $data['name'],
            'is_visible' => 1,
        ]);
    }

    /**
     * Test program editing with visibility.
     */
    public function test_canPostEditProgramVisibility()
    {
        $program = Program::factory()->create();

        // Define some basic data
        $data = [
            'name' => $this->faker->unique()->domainWord(),
        ];

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/programs/edit/'.$program->id, $data);

        // Directly verify that the appropriate change has occurred
        $this->assertDatabaseHas('programs', [
            'id'         => $program->id,
            'name'       => $data['name'],
            'is_visible' => 0,
        ]);
    }

    /**
     * Test program delete access.
     */
    public function test_canGetDeleteProgram()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/data/programs/delete/'.Program::factory()->create()->id)
            ->assertStatus(200);
    }

    /**
     * Test program deletion.
     */
    public function test_canPostDeleteProgram()
    {
        // Create a category to delete
        $program = Program::factory()->create();

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/data/programs/delete/'.$program->id);

        // Check that there are fewer categories than before
        $this->assertDeleted($program);
    }
}
