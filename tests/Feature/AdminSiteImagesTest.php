<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminSiteImagesTest extends TestCase {
    use RefreshDatabase;

    /******************************************************************************
        SITE IMAGES
    *******************************************************************************/

    protected function setUp(): void {
        parent::setUp();

        // Create a fake file to test image uploading
        $this->file = UploadedFile::fake()->image('test_image.png');
    }

    /**
     * Test site image index access.
     */
    public function testGetSiteImagesIndex() {
        $this->actingAs($this->user)
            ->get('/admin/site-images')
            ->assertStatus(200);
    }

    /**
     * Test image uploading.
     *
     * @dataProvider siteImageProvider
     *
     * @param string $key
     */
    public function testPostUploadImage($key) {
        // Copy default images to ensure that the directory exists
        $this->artisan('app:copy-default-images');

        // Remove the current file if it exists
        if (File::exists(public_path('images/assets/'.$key.'.'.config('aldebaran.settings.image_formats.site_images', 'png')))) {
            unlink('public/images/assets/'.$key.'.'.config('aldebaran.settings.image_formats.site_images', 'png'));
        }

        // Try to post data
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/site-images/upload', [
                $key.'_file' => $this->file,
                'key'        => $key,
            ]);

        $response->assertSessionHasNoErrors();
        // Check that the file is now present
        $this->
            assertTrue(File::exists(public_path('images/assets/'.$key.'.'.config('aldebaran.settings.image_formats.site_images', 'png'))));

        // Replace with default images for tidiness
        $this->artisan('app:copy-default-images');
    }

    public static function siteImageProvider() {
        return [
            'avatar'    => ['avatar'],
            'watermark' => ['watermark'],
            'sidebar'   => ['sidebar_bg'],
        ];
    }

    /**
     * Test custom css uploading.
     */
    public function testPostUploadSiteCss() {
        // Create a fake file
        $file = UploadedFile::fake()->create('test.css', 50);

        // Check that the file is absent, and if not, remove it
        if (File::exists(public_path('css/custom.css'))) {
            unlink('public/css/custom.css');
        }

        // Try to post data
        $response = $this
            ->actingAs($this->user)
            ->post('/admin/site-images/upload/css', [
                'css_file' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        // Check that the file is now present
        $this->
            assertTrue(File::exists(public_path('css/custom.css')));

        // Clean up
        unlink('public/css/custom.css');
    }
}
