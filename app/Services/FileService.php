<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class FileService extends Service {
    /*
    |--------------------------------------------------------------------------
    | File Manager
    |--------------------------------------------------------------------------
    |
    | Handles uploading and manipulation of files.
    |
    */

    /**
     * Uploads a file.
     *
     * @param array  $file
     * @param string $dir
     * @param string $name
     * @param bool   $isFileManager
     *
     * @return bool
     */
    public function uploadFile($file, $dir, $name, $isFileManager = true) {
        $directory = public_path().($isFileManager ? '/files'.($dir ? '/'.$dir : '') : '/images/assets');
        if (!file_exists($directory)) {
            $this->setError('error', 'Folder does not exist.');
        }
        File::move($file, $directory.'/'.$name);

        if (config('aldebaran.settings.image_formats.site_images')) {
            Image::make($directory.'/'.$name)->save($directory.'/'.$name, null, config('aldebaran.settings.image_formats.site_images', 'png'));
        }

        chmod($directory.'/'.$name, 0755);

        return true;
    }

    /**
     * Uploads a custom CSS file.
     *
     * @param array $file
     *
     * @return bool
     */
    public function uploadCss($file) {
        File::move($file, public_path().'/css/custom.css');
        chmod(public_path().'/css/custom.css', 0755);

        return true;
    }

    /**
     * Deletes a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function deleteFile($path) {
        if (!file_exists($path)) {
            $this->setError('error', 'File does not exist.');

            return false;
        }
        unlink($path);

        return true;
    }

    /**
     * Moves a file.
     *
     * @param string $oldDir
     * @param string $newDir
     * @param string $name
     *
     * @return bool
     */
    public function moveFile($oldDir, $newDir, $name) {
        if (!file_exists($oldDir.'/'.$name)) {
            $this->setError('error', 'File does not exist.');

            return false;
        } elseif (!file_exists($newDir)) {
            $this->setError('error', 'Destination does not exist.');

            return false;
        }
        rename($oldDir.'/'.$name, $newDir.'/'.$name);

        return true;
    }

    /**
     * Renames a file.
     *
     * @param string $dir
     * @param string $oldName
     * @param string $newName
     *
     * @return bool
     */
    public function renameFile($dir, $oldName, $newName) {
        if (!file_exists($dir.'/'.$oldName)) {
            $this->setError('error', 'File does not exist.');

            return false;
        }
        rename($dir.'/'.$oldName, $dir.'/'.$newName);

        return true;
    }
}
