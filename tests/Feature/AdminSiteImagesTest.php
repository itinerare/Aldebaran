<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminSiteImagesTest extends TestCase
{
    use RefreshDatabase;

    /******************************************************************************
        SITE IMAGES
    *******************************************************************************/

    /**
     * Test site image index access.
     */
    public function test_canGetSiteImagesIndex()
    {
        $response = $this->actingAs(User::factory()->make())
            ->get('/admin/site-images')
            ->assertStatus(200);
    }

    /**
     * Test avatar image uploading.
     */
    public function test_canPostEditAvatar()
    {
        // Create a fake file
        $file = UploadedFile::fake()->image('test_image.png');

        // Remove the current logo file if it exists
        if (File::exists(public_path('images/assets/avatar.png'))) {
            unlink('public/images/assets/avatar.png');
        }

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/site-images/upload', [
                'file' => $file,
                'key'  => 'avatar',
            ]);

        // Check that the file is now present
        $this->
            assertTrue(File::exists(public_path('images/assets/avatar.png')));

        // Replace with default images for tidiness
        $this->artisan('copy-default-images');
    }

    /**
     * Test watermark image uploading.
     */
    public function test_canPostEditWatermark()
    {
        // Create a fake file
        $file = UploadedFile::fake()->image('test_image.png');

        // Remove the current logo file if it exists
        if (File::exists(public_path('images/assets/watermark.png'))) {
            unlink('public/images/assets/watermark.png');
        }

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/site-images/upload', [
                'file' => $file,
                'key'  => 'watermark',
            ]);

        // Check that the file is now present
        $this->
            assertTrue(File::exists(public_path('images/assets/watermark.png')));

        // Replace with default images for tidiness
        $this->artisan('copy-default-images');
    }

    /**
     * Test sidebar image uploading.
     */
    public function test_canPostEditSidebar()
    {
        // Create a fake file
        $file = UploadedFile::fake()->image('test_image.png');

        // Remove the current logo file if it exists
        if (File::exists(public_path('images/assets/sidebar_bg.png'))) {
            unlink('public/images/assets/sidebar_bg.png');
        }

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/site-images/upload', [
                'file' => $file,
                'key'  => 'sidebar_bg',
            ]);

        // Check that the file is now present
        $this->
            assertTrue(File::exists(public_path('images/assets/sidebar_bg.png')));

        // Replace with default images for tidiness
        $this->artisan('copy-default-images');
    }

    /**
     * Test custom css uploading.
     */
    public function test_canPostEditSiteCss()
    {
        // Create a fake file
        $file = UploadedFile::fake()->create('test.css', 50);

        // Check that the file is absent, and if not, remove it
        if (File::exists(public_path('css/custom.css'))) {
            unlink('public/css/custom.css');
        }

        // Try to post data
        $response = $this
            ->actingAs(User::factory()->make())
            ->post('/admin/site-images/upload/css', [
                'file' => $file,
            ]);

        // Check that the file is now present
        $this->
            assertTrue(File::exists(public_path('css/custom.css')));

        // Clean up
        unlink('public/css/custom.css');
    }
}
