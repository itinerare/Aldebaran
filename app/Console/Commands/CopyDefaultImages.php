<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CopyDefaultImages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:copy-default-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies default images (as defined in the image_files config file) from the data/images directory to the public/images/assets directory.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        //
        $this->info('***********************');
        $this->info('* COPY DEFAULT IMAGES *');
        $this->info('***********************'."\n");

        $outputDir = public_path().'/images/assets/';

        if (!file_exists($outputDir)) {
            // Create the directory.
            if (!mkdir($outputDir, 0755, true)) {
                $this->setError('error', 'Failed to create image directory.');

                return false;
            }
            chmod($outputDir, 0755);
        }

        $images = config('aldebaran.image_files');

        foreach ($images as $image) {
            $this->line('Copying image: '.$image['filename']."\n");
            copy(
                base_path().'/data/assets/'.$image['filename'].'.'.config('aldebaran.settings.image_formats.site_images', 'png'),
                $outputDir.$image['filename'].'.'.config('aldebaran.settings.image_formats.site_images', 'png')
            );
        }
        $this->line('Done!');
    }
}
