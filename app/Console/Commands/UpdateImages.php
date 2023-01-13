<?php

namespace App\Console\Commands;

use App\Models\Gallery\PieceImage;
use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class UpdateImages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates images in accordance with config settings.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        if ($this->confirm('Do you want to update your site images to '.config('aldebaran.settings.image_formats.site_images').' now?')) {
            $directory = public_path().'/images/assets';
            foreach (config('aldebaran.image_files') as $image) {
                // Determine origin filetype
                if (file_exists($directory.'/'.$image['filename'].'.png') && config('aldebaran.settings.image_formats.site_images') != 'png') {
                    $old = 'png';
                } elseif (file_exists($directory.'/'.$image['filename'].'.webp') && config('aldebaran.settings.image_formats.site_images') != 'webp') {
                    $old = 'webp';
                }

                if (isset($old) && file_exists($directory.'/'.$image['filename'].'.'.$old) && config('aldebaran.settings.image_formats.site_images')) {
                    // Convert and save the image
                    Image::make($directory.'/'.$image['filename'].'.'.$old)->save($directory.'/'.$image['filename'].'.'.config('aldebaran.settings.image_formats.site_images'), null, config('aldebaran.settings.image_formats.site_images'));

                    // Clean up the old image
                    if (file_exists($directory.'/'.$image['filename'].'.'.$old)) {
                        unlink($directory.'/'.$image['filename'].'.'.$old);
                    }
                }

                // Just for safety's sake, unset this
                unset($old);
            }
            unset($directory);
        } else {
            $this->line('Skipped updating site images.');
        }

        $this->info("\n".'The next section concerns piece images. Please see config/aldebaran/settings.php for the relevant settings.');

        if (config('aldebaran.settings.image_formats.full') && (config('aldebaran.settings.image_formats.display') == config('aldebaran.settings.image_formats.full') || config('aldebaran.settings.image_formats.display') == null) && $this->confirm('Do you want to update all piece images to '.config('aldebaran.settings.image_formats.full').' now?'.(config('aldebaran.settings.image_formats.admin_view') ? ' Note that they will appear as '.config('aldebaran.settings.image_formats.admin_view').' files in the admin panel for convenience.' : ''))) {
            $this->line('Updating all piece images...');
            foreach (PieceImage::all() as $image) {
                if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                    $fullFile = Image::make($image->imagePath.'/'.$image->fullsizeFileName);
                    unlink($image->imagePath.'/'.$image->fullsizeFileName);
                }
                if (file_exists($image->imagePath.'/'.$image->imageFileName)) {
                    $displayFile = Image::make($image->imagePath.'/'.$image->imageFileName);
                    unlink($image->imagePath.'/'.$image->imageFileName);
                }
                if (file_exists($image->imagePath.'/'.$image->thumbnailFileName)) {
                    $thumbFile = Image::make($image->imagePath.'/'.$image->thumbnailFileName);
                    unlink($image->imagePath.'/'.$image->thumbnailFileName);
                }

                $image->update([
                    'extension'         => config('aldebaran.settings.image_formats.full'),
                    'display_extension' => null,
                ]);

                if (isset($fullFile)) {
                    $fullFile->save($image->imagePath.'/'.$image->fullsizeFileName, null, config('aldebaran.settings.image_formats.full'));
                    unset($fullFile);
                }
                if (isset($displayFile)) {
                    $displayFile->save($image->imagePath.'/'.$image->imageFileName, null, config('aldebaran.settings.image_formats.full'));
                    unset($displayFile);
                }
                if (isset($thumbFile)) {
                    $thumbFile->save($image->imagePath.'/'.$image->thumbnailFileName, null, config('aldebaran.settings.image_formats.full'));
                    unset($thumbFile);
                }
            }
        } else {
            if (config('aldebaran.settings.image_formats.full') && $this->confirm('Do you want to update piece full-size images to '.config('aldebaran.settings.image_formats.full').' now?'.(config('aldebaran.settings.image_formats.admin_view') ? ' Note that they will appear as '.config('aldebaran.settings.image_formats.admin_view').' files in the admin panel for convenience.' : ''))) {
                $this->line('Updating piece full-size images...');
                foreach (PieceImage::all() as $image) {
                    if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                        $file = Image::make($image->imagePath.'/'.$image->fullsizeFileName);
                        unlink($image->imagePath.'/'.$image->fullsizeFileName);

                        // Set the display extension to ensure the display image/thumbnail
                        // remain accessible even if display images are not likewise updated
                        $image->display_extension = $image->extension;
                        $image->extension = config('aldebaran.settings.image_formats.full');
                        $image->save();

                        $file->save($image->imagePath.'/'.$image->fullsizeFileName, null, config('aldebaran.settings.image_formats.full'));
                        unset($file);
                    }
                }
            } elseif ((config('aldebaran.settings.image_formats.display') || config('aldebaran.settings.image_formats.full')) && $this->confirm('Do you want to update piece display and thumbnail images to '.(config('aldebaran.settings.image_formats.display') ?? config('aldebaran.settings.image_formats.full')).' now?'.(config('aldebaran.settings.image_formats.admin_view') ? ' Note that they will appear as '.config('aldebaran.settings.image_formats.admin_view').' files in the admin panel for convenience.' : ''))) {
                $format = config('aldebaran.settings.image_formats.display') ?? config('aldebaran.settings.image_formats.full');
                foreach (PieceImage::all() as $image) {
                    if (file_exists($image->imagePath.'/'.$image->imageFileName)) {
                        $displayFile = Image::make($image->imagePath.'/'.$image->imageFileName);
                        unlink($image->imagePath.'/'.$image->imageFileName);
                    }
                    if (file_exists($image->imagePath.'/'.$image->thumbnailFileName)) {
                        $thumbFile = Image::make($image->imagePath.'/'.$image->thumbnailFileName);
                        unlink($image->imagePath.'/'.$image->thumbnailFileName);
                    }

                    if (config('aldebaran.settings.image_formats.display') && (config('aldebaran.settings.image_formats.display') != config('aldebaran.settings.image_formats.full'))) {
                        // Supply the display extension, but only if necessary
                        $image->display_extension = config('aldebaran.settings.image_formats.display');
                    } elseif (isset($image->display_extension)) {
                        // Otherwise, if it was already set (e.g. above), make it null
                        $image->display_extension = null;
                    }
                    $image->save();

                    if (isset($displayFile)) {
                        $displayFile->save($image->imagePath.'/'.$image->imageFileName, null, $format);
                        unset($displayFile);
                    }
                    if (isset($thumbFile)) {
                        $thumbFile->save($image->imagePath.'/'.$image->thumbnailFileName, null, $format);
                        unset($thumbFile);
                    }
                }
            } else {
                $this->line('Skipped updating piece images.');
            }
        }

        return Command::SUCCESS;
    }
}
