<?php

namespace App\Services;

use App\Facades\Settings;
use App\Models\Commission\CommissionPiece;
use App\Models\Commission\CommissionType;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\PieceLiterature;
use App\Models\Gallery\PieceProgram;
use App\Models\Gallery\PieceTag;
use App\Models\Gallery\Program;
use App\Models\Gallery\Project;
use App\Models\Gallery\Tag;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class GalleryService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Gallery Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of gallery data.
    |
    */

    /******************************************************************************
        PROJECTS
    *******************************************************************************/

    /**
     * Create a project.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Project
     */
    public function createProject($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $project = Project::create($data);

            return $this->commitReturn($project);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a project.
     *
     * @param Project               $project
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Project
     */
    public function updateProject($project, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Project::where('name', $data['name'])->where('id', '!=', $project->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $project->update($data);

            return $this->commitReturn($project);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a project.
     *
     * @param Project $project
     *
     * @return bool
     */
    public function deleteProject($project) {
        DB::beginTransaction();

        try {
            // Check first if the project is currently in use
            if (Piece::where('project_id', $project->id)->exists()) {
                throw new \Exception('A piece with this category exists. Please move or delete it first.');
            }

            $project->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts project order.
     *
     * @param string $data
     *
     * @return bool
     */
    public function sortProject($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Project::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        PIECES
    *******************************************************************************/

    /**
     * Creates a new piece.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Piece
     */
    public function createPiece($data, $user) {
        DB::beginTransaction();

        try {
            if (!Project::where('id', $data['project_id'])->exists()) {
                throw new \Exception('The selected project is invalid.');
            }

            $data = $this->populateData($data);
            $piece = Piece::create(Arr::only($data, [
                'name', 'project_id', 'description', 'timestamp', 'is_visible', 'good_example',
            ]));

            // If tags are selected, validate and create data for them
            $data = $this->processTags($data, $piece);
            // If programs are selected, validate and create data for them
            $data = $this->processPrograms($data, $piece);

            return $this->commitReturn($piece);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a piece.
     *
     * @param Piece                 $piece
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Piece
     */
    public function updatePiece($piece, $data, $user) {
        DB::beginTransaction();

        try {
            if (Piece::where('name', $data['name'])->where('id', '!=', $piece->id)->where('project_id', $data['project_id'])->exists()) {
                throw new \Exception('The name has already been taken in this project.');
            }
            if ((isset($data['project_id']) && $data['project_id']) && !Project::where('id', $data['project_id'])->exists()) {
                throw new \Exception('The selected project is invalid.');
            }

            $data = $this->populateData($data);
            // If tags are selected, validate and create data for them
            $data = $this->processTags($data, $piece);
            // If programs are selected, validate and create data for them
            $data = $this->processPrograms($data, $piece);

            $piece->update(Arr::only($data, [
                'name', 'project_id', 'description', 'timestamp', 'is_visible', 'good_example',
            ]));

            return $this->commitReturn($piece);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a piece.
     *
     * @param Piece $piece
     *
     * @return bool
     */
    public function deletePiece($piece) {
        DB::beginTransaction();

        try {
            if (CommissionPiece::where('piece_id', $piece->id)->exists()) {
                throw new \Exception('A commission exists using this piece. Deleting it would potentially remove access to the final piece from the commissioner.');
            }

            // First delete all images, tags, and programs associated with a piece,
            // then the piece itself
            foreach ($piece->images as $image) {
                $this->deletePieceImage($image);
            }
            PieceTag::where('piece_id', $piece->id)->delete();
            PieceProgram::where('piece_id', $piece->id)->delete();

            $piece->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts piece image order.
     *
     * @param int    $id
     * @param string $data
     *
     * @return bool
     */
    public function sortPieceImages($id, $data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                PieceImage::where('piece_id', $id)->where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts piece literature order.
     *
     * @param int    $id
     * @param string $data
     *
     * @return bool
     */
    public function sortPieceLiteratures($id, $data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                PieceLiterature::where('piece_id', $id)->where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        IMAGES
    *******************************************************************************/

    /**
     * Creates a new image.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PieceImage
     */
    public function createImage($data, $user) {
        DB::beginTransaction();

        try {
            // Check that the piece exists
            $piece = Piece::find($data['piece_id']);
            if (!$piece) {
                throw new \Exception('No valid piece found!');
            }

            // Determine whether the file is multimedia (gif/video) or an image
            // as this determines whether the configured settings are used or not
            $extension = $data['image']->getClientOriginalExtension();
            if ($extension = 'gif' || $extension == 'mp4' || $extension == 'webm') {
                $data['extension'] = $data['image']->getClientOriginalExtension();
                $data['display_extension'] = config('aldebaran.settings.image_formats.display') ?? 'webp';

                $data['use_cropper'] = 0;
            } else {
                $data['extension'] = config('aldebaran.settings.image_formats.full') ?? $data['image']->getClientOriginalExtension();
                $data['display_extension'] = config('aldebaran.settings.image_formats.display') && config('aldebaran.settings.image_formats.display') != config('aldebaran.settings.image_formats.full') ? config('aldebaran.settings.image_formats.display') : null;
            }

            // Record data for the image
            $image = PieceImage::create([
                'piece_id'          => $piece->id,
                'hash'              => randomString(15),
                'fullsize_hash'     => randomString(15),
                'extension'         => $data['extension'],
                'description'       => $data['description'] ?? null,
                'alt_text'          => $data['alt_text'],
                'is_primary_image'  => $data['is_primary_image'] ?? 0,
                'is_visible'        => $data['is_visible'] ?? 0,
                'data'              => [],
                'display_extension' => $data['display_extension'],
            ]);

            if ($image->isMultimedia) {
                $this->processMultimedia($data, $image);
            } else {
                $this->processImage($data, $image);
            }
            $image->update();

            return $this->commitReturn($image);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an image.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $image
     *
     * @return \App\Models\Gallery\PieceIamge|bool
     */
    public function updateImage($image, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$image) {
                throw new \Exception('This image is invalid.');
            }

            // Check the regenerate watermark toggle, including setting it to false if an image has been uploaded
            if (!isset($data['regenerate_watermark']) || isset($data['image'])) {
                $data['regenerate_watermark'] = 0;
            }

            if (isset($data['image']) || $data['regenerate_watermark']) {
                if (isset($data['image'])) {
                    // If a new file is being uploaded, check if it's an image or multimedia
                    $extension = $data['image']->getClientOriginalExtension();
                    if ($extension = 'gif' || $extension == 'mp4' || $extension == 'webm') {
                        $data['extension'] = $data['image']->getClientOriginalExtension();
                        $data['use_cropper'] = 0;

                        $this->processMultimedia($data, $image, isset($data['image']));
                    } else {
                        $this->processImage($data, $image, isset($data['image']), $data['regenerate_watermark']);
                    }
                } else {
                    // Otherwise just process the image
                    $this->processImage($data, $image, isset($data['image']), $data['regenerate_watermark']);
                }
            }

            $image->update([
                'description'      => $data['description'] ?? null,
                'is_primary_image' => $data['is_primary_image'] ?? 0,
                'is_visible'       => $data['is_visible'] ?? 0,
                'alt_text'         => $data['alt_text'],
            ]);

            return $this->commitReturn($image);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an image.
     *
     * @param PieceImage $image
     *
     * @return bool
     */
    public function deletePieceImage($image) {
        DB::beginTransaction();

        try {
            if (!$image) {
                throw new \Exception('This image is invalid.');
            }

            // Delete the associated files...
            if (file_exists($image->imagePath.'/'.$image->thumbnailFileName)) {
                unlink($image->imagePath.'/'.$image->thumbnailFileName);
            }
            if (file_exists($image->imagePath.'/'.$image->imageFileName)) {
                unlink($image->imagePath.'/'.$image->imageFileName);
            }
            if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                unlink($image->imagePath.'/'.$image->fullsizeFileName);
            }

            // and then the model itself
            $image->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        LITERATURES
    *******************************************************************************/

    /**
     * Creates a new literature.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PieceLiterature
     */
    public function createLiterature($data, $user) {
        DB::beginTransaction();

        try {
            // Check that the piece exists
            $piece = Piece::find($data['piece_id']);
            if (!$piece) {
                throw new \Exception('No valid piece found!');
            }

            // Handle image and information if necessary
            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['hash'] = randomString(15);
                $data['extension'] = $data['image']->getClientOriginalExtension();
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['hash'] = null;
                $data['extension'] = null;
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            if (!isset($data['is_primary'])) {
                $data['is_primary'] = 0;
            }

            // Create the literature
            $literature = PieceLiterature::create(Arr::only($data, [
                'piece_id', 'text', 'hash', 'extension', 'is_visible', 'is_primary',
            ]));

            // Save image if necessary
            if ($image) {
                $this->handleImage($image, $literature->imagePath, $literature->thumbnailFileName);
            }

            return $this->commitReturn($literature);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a literature.
     *
     * @param PieceLiterature       $literature
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PieceLiterature
     */
    public function updateLiterature($literature, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$literature) {
                throw new \Exception('This literature is invalid.');
            }

            // Handle image and information if necessary
            if (isset($data['remove_image'])) {
                if ($literature->hash && $data['remove_image']) {
                    $data['hash'] = null;
                    $data['extension'] = null;
                    $this->deleteImage($literature->imagePath, $literature->thumbnailFileName);
                }
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                // If there is already an image, delete the file
                // before updating the recorded image information
                if ($literature->hash && !$data['remove_image']) {
                    $this->deleteImage($literature->imagePath, $literature->thumbnailFileName);
                }
                $data['hash'] = randomString(15);
                $data['extension'] = $data['image']->getClientOriginalExtension();
                $image = $data['image'];
                unset($data['image']);
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['is_primary'])) {
                $data['is_primary'] = 0;
            }

            $literature->update(Arr::only($data, [
                'text', 'hash', 'extension', 'is_visible', 'is_primary',
            ]));

            // Save image if necessary
            if ($image) {
                $this->handleImage($image, $literature->imagePath, $literature->thumbnailFileName);
            }

            return $this->commitReturn($literature);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a literature.
     *
     * @param PieceLiterature $literature
     *
     * @return bool
     */
    public function deleteLiterature($literature) {
        DB::beginTransaction();

        try {
            if (!$literature) {
                throw new \Exception('This literature is invalid.');
            }

            // Delete thumbnail file if set
            if ($literature->hash) {
                unlink($literature->imagePath.'/'.$literature->thumbnailFileName);
            }

            // and then the model itself
            $literature->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        TAGS
    *******************************************************************************/

    /**
     * Create a tag.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Tag
     */
    public function createTag($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }

            $tag = Tag::create($data);

            return $this->commitReturn($tag);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a tag.
     *
     * @param Tag                   $tag
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Tag
     */
    public function updateTag($tag, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Tag::where('name', $data['name'])->where('id', '!=', $tag->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }

            $tag->update($data);

            return $this->commitReturn($tag);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a tag.
     *
     * @param Tag $tag
     *
     * @return bool
     */
    public function deleteTag($tag) {
        DB::beginTransaction();

        try {
            // Check first if the tag is currently in use
            if (PieceTag::where('tag_id', $tag->id)->exists()) {
                throw new \Exception('A piece with this tag exists. Please remove the tag first.');
            }
            if (CommissionType::whereJsonContains('data->tags', (string) $tag->id)->exists()) {
                throw new \Exception('A commission type using this tag exists. Please remove the tag first.');
            }

            PieceTag::where('tag_id', $tag->id)->delete();
            $tag->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /******************************************************************************
        PROGRAMS
    *******************************************************************************/

    /**
     * Create a program.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Program
     */
    public function createProgram($data, $user) {
        DB::beginTransaction();

        try {
            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $program = Program::create(Arr::only($data, [
                'name', 'has_image', 'is_visible',
            ]));

            if ($image) {
                $this->handleImage($image, $program->imagePath, $program->imageFileName);
            }

            return $this->commitReturn($program);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a program.
     *
     * @param Program               $program
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Program
     */
    public function updateProgram($program, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Program::where('name', $data['name'])->where('id', '!=', $program->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            if (isset($data['remove_image']) && !isset($data['image'])) {
                if ($program->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $this->deleteImage($program->imagePath, $program->imageFileName);
                }
                unset($data['remove_image']);
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $program->update(Arr::only($data, [
                'name', 'has_image', 'is_visible',
            ]));

            if ($image) {
                $this->handleImage($image, $program->imagePath, $program->imageFileName);
            }

            return $this->commitReturn($program);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a program.
     *
     * @param Program $program
     *
     * @return bool
     */
    public function deleteProgram($program) {
        DB::beginTransaction();

        try {
            // Check first if the program is currently in use
            if (PieceProgram::where('program_id', $program->id)->exists()) {
                throw new \Exception('A piece using this program exists. Please remove the program first.');
            }

            PieceProgram::where('program_id', $program->id)->delete();
            $program->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Generates and saves test images for page image test purposes.
     * This is a workaround for normal image processing depending on Intervention.
     *
     * @param PieceImage $image
     * @param bool       $create
     *
     * @return bool
     */
    public function testImages($image, $create = true) {
        if ($create) {// Generate the fake files to save
            $file['fullsize'] = UploadedFile::fake()->image('test_image.png');
            $file['image'] = UploadedFile::fake()->image('test_watermarked.png');
            $file['thumbnail'] = UploadedFile::fake()->image('test_thumb.png');

            // Save the files in line with usual image handling.
            $this->handleImage($file['fullsize'], $image->imagePath, $image->fullsizeFileName);
            $this->handleImage($file['image'], $image->imagePath, $image->imageFileName);
            $this->handleImage($file['thumbnail'], $image->imagePath, $image->thumbnailFileName);
        } elseif (!$create && File::exists($image->imagePath.'/'.$image->thumbnailFileName)) {
            // Remove test files
            unlink($image->imagePath.'/'.$image->thumbnailFileName);
            unlink($image->imagePath.'/'.$image->imageFileName);
            unlink($image->imagePath.'/'.$image->fullsizeFileName);
        }

        return true;
    }

    /**
     * Processes user input for creating/updating a piece.
     *
     * @param array                   $data
     * @param \App\Models\piece\Piece $piece
     *
     * @return array
     */
    private function populateData($data, $piece = null) {
        // Check toggles
        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (!isset($data['good_example'])) {
            $data['good_example'] = 0;
        }

        return $data;
    }

    /**
     * Processes tag data.
     *
     * @param array                   $data
     * @param \App\Models\piece\Piece $piece
     *
     * @return array
     */
    private function processTags($data, $piece) {
        if ($piece->id && $piece->tags->count()) {
            // Collect old tags and delete them
            $oldTags = $piece->tags();
            PieceTag::where('piece_id', $piece->id)->delete();
        }

        if (isset($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                if (!Tag::where('id', $tag)->exists()) {
                    throw new \Exception('One or more of the selected tags is invalid.');
                }

                PieceTag::create([
                    'piece_id' => $piece->id,
                    'tag_id'   => $tag,
                ]);
            }
        }

        return $data;
    }

    /**
     * Processes program data.
     *
     * @param array                   $data
     * @param \App\Models\Piece\Piece $piece
     *
     * @return array
     */
    private function processPrograms($data, $piece) {
        if ($piece->id && $piece->programs->count()) {
            // Collect old tags and delete them
            $oldPrograms = $piece->programs();
            PieceProgram::where('piece_id', $piece->id)->delete();
        }

        if (isset($data['programs'])) {
            foreach ($data['programs'] as $program) {
                if (!Program::where('id', $program)->exists()) {
                    throw new \Exception('One or more of the selected programs is invalid.');
                }

                PieceProgram::create([
                    'piece_id'   => $piece->id,
                    'program_id' => $program,
                ]);
            }
        }

        return $data;
    }

    /**
     * Processes images.
     *
     * @param array      $data
     * @param PieceImage $image
     * @param bool       $reupload
     * @param mixed      $regen
     *
     * @return PieceImage
     */
    private function processImage($data, $image, $reupload = false, $regen = false) {
        // Unlink display image if a reupload or watermark regen
        if ($reupload || $regen) {
            if (file_exists($image->imagePath.'/'.$image->imageFileName)) {
                unlink($image->imagePath.'/'.$image->imageFileName);
            }
        }

        // If the image is a reupload, unlink the old image and regenerate the hashes
        // as well as re-setting the extension.
        if ($reupload) {
            // Unlink fullsize and thumbnail images
            if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                unlink($image->imagePath.'/'.$image->fullsizeFileName);
            }
            if (file_exists($image->imagePath.'/'.$image->thumbnailFileName)) {
                unlink($image->imagePath.'/'.$image->thumbnailFileName);
            }

            $image->update([
                'hash'              => randomString(15),
                'fullsize_hash'     => randomString(15),
                'extension'         => config('aldebaran.settings.image_formats.full') ?? $data['image']->getClientOriginalExtension(),
                'display_extension' => config('aldebaran.settings.image_formats.display') ?? null,
            ]);
        } elseif ($regen) {
            // Otherwise, just regenerate the display hash and move the thumbnail
            $oldHash = $image->hash;
            $image->update([
                'hash' => randomString(15),
            ]);

            $this->handleImage(null, $image->imagePath, $image->thumbnailFileName, $image->id.'_'.$oldHash.'_th.'.($image->display_extension ?? $image->extension));
        }

        if (!$regen) {
            // Save fullsize image before doing any processing
            $this->handleImage($data['image'], $image->imagePath, $image->fullsizeFileName);

            if (config('aldebaran.settings.image_formats.full')) {
                Image::make($image->imagePath.'/'.$image->fullsizeFileName)->save($image->imagePath.'/'.$image->fullsizeFileName, null, config('aldebaran.settings.image_formats.full'));
            }

            // Process thumbnail
            $this->processThumbnail($image, $data);
        }

        // Process and save watermarked image
        $processImage = Image::make($image->imagePath.'/'.$image->fullsizeFileName);

        // Resize image if either dimension is larger than 2k px
        $adjustedCap = isset($data['image_scale']) ? min((max($processImage->height(), $processImage->width()) * $data['image_scale']), config('aldebaran.settings.display_image_size')) : config('aldebaran.settings.display_image_size');

        if (max($processImage->height(), $processImage->width()) > $adjustedCap) {
            if ($processImage->width() > $processImage->height()) {
                // Landscape
                $processImage->resize($adjustedCap, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } else {
                // Portrait
                $processImage->resize(null, $adjustedCap, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
        }

        if (isset($data['watermark_image']) && $data['watermark_image']) {
            // Add text watermark if necessary
            if (isset($data['text_watermark']) && $data['text_watermark']) {
                $watermarkText = null;

                // Set text based on form input
                switch ($data['text_watermark']) {
                    case 'generic':
                        $watermarkText[] = Settings::get('site_name').' - do not repost';
                        break;
                    case 'personal':
                        $watermarkText = [Settings::get('site_name'), 'Personal work - Do not repost'];
                        break;
                    case 'gift':
                        $watermarkText = [Settings::get('site_name'), 'Gift work - Do not repost'];
                        break;
                    case 'commission':
                        $watermarkText = [Settings::get('site_name'), 'Commissioned work - Do not repost'];
                        break;
                }
                // Space out lines
                $offset = (count($watermarkText) > 1 ? strlen($data['text_watermark']) : 0);
                $i = 30 + (22 + $offset);

                // Apply text watermark
                $y = -100;
                while ($y < $processImage->height() + 150) {
                    $x = -100;

                    while ($x < $processImage->width() + 150) {
                        foreach ($watermarkText as $key=> $text) {
                            $processImage->text($text, $key == 0 && count($watermarkText) > 1 ? $x + (22 + ($offset * 5)) : $x, $key > 0 ? $y + $i : $y, function ($font) use ($data) {
                                $font->file(public_path('fonts/RobotoCondensed-Regular.ttf'));
                                $font->size(24);
                                $font->color([255, 255, 255, $data['text_opacity']]);
                                $font->valign(500);
                                $font->angle(30);
                                $font->align('center');
                            });
                        }
                        $x += 300 + (isset($watermarkText[1]) ? strlen($watermarkText[1]) : strlen($watermarkText[0]));
                    }
                    $y += 200;
                }
            }

            // Process the watermark in preparation for watermarking the image
            $watermark = Image::make('images/assets/watermark.'.config('aldebaran.settings.image_formats.site_images', 'png'));
            // Colorize the watermark if called for
            if (isset($data['watermark_color'])) {
                // Convert hex code to RGB
                [$r, $g, $b] = sscanf($data['watermark_color'], '#%02x%02x%02x');
                $r = round($r / (255 / 100));
                $g = round($g / (255 / 100));
                $b = round($b / (255 / 100));

                $watermark->colorize($r, $g, $b);
            }

            // Resize watermark to scale with image
            $watermark->resize(null, ($processImage->height() * $data['watermark_scale']), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Adjust opacity
            $watermark->opacity($data['watermark_opacity']);

            // Watermark and save the image
            $processImage->insert($watermark, $data['watermark_position']);
        }

        $processImage->save($image->imagePath.'/'.$image->imageFileName, null, config('aldebaran.settings.image_formats.display') ?? $image->extension);

        // Collect and encode watermark settings
        $data['data'] = [
            'scale'          => $data['watermark_scale'],
            'opacity'        => $data['watermark_opacity'],
            'position'       => $data['watermark_position'],
            'color'          => $data['watermark_color'] ?? null,
            'image_scale'    => $data['image_scale'] ?? null,
            'watermarked'    => $data['watermark_image'] ?? 0,
            'text_watermark' => $data['text_watermark'] ?? null,
            'text_opacity'   => $data['text_opacity'] ?? null,
        ];
        $image->update(['data' => $data['data']]);

        return $image;
    }

    /**
     * Processes images.
     *
     * @param array      $data
     * @param PieceImage $image
     * @param bool       $reupload
     *
     * @return PieceImage
     */
    private function processMultimedia($data, $image, $reupload = false) {
        // If the file is a reupload, unlink the old file and regenerate the hashes
        // as well as re-setting the extension.
        if ($reupload) {
            // Unlink file and thumbnail images
            if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                unlink($image->imagePath.'/'.$image->fullsizeFileName);
            }
            if (file_exists($image->imagePath.'/'.$image->thumbnailFileName)) {
                unlink($image->imagePath.'/'.$image->thumbnailFileName);
            }

            $image->update([
                'hash'              => randomString(15),
                'fullsize_hash'     => randomString(15),
                'extension'         => $data['extension'],
                'display_extension' => config('aldebaran.settings.image_formats.display') ?? 'webp',
            ]);
        }

        // Save the file itself
        $this->handleImage($data['image'], $image->imagePath, $image->fullsizeFileName);

        // Process thumbnail
        if ($data['extension'] == 'mp4' || $data['extension'] == 'webm') {
            // Use FFMpeg to grab a frame from the video
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($image->imagePath.'/'.$image->fullsizeFileName);
            $video->frame(TimeCode::fromSeconds(3))
                ->save($image->imagePath.'/'.$image->thumbnailFileName);

            $thumbnail = Image::make($image->imagePath.'/'.$image->thumbnailFileName);

            // Resize and save thumbnail
            if (config('aldebaran.settings.gallery_arrangement') == 'columns') {
                $thumbnail->resize(config('aldebaran.settings.thumbnail_width'), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } else {
                $thumbnail->resize(null, config('aldebaran.settings.thumbnail_height'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $thumbnail->save($image->thumbnailPath.'/'.$image->thumbnailFileName, null, config('aldebaran.settings.image_formats.display') ?? 'webp');

            $ffprobe = FFProbe::create();
            $contentWidth = $ffprobe->streams($image->imagePath.'/'.$image->fullsizeFileName)
                ->videos()->first()->get('width');

            $image->update(['data' => ['content_width' => $contentWidth]]);
        } else {
            $this->processThumbnail($image, $data);
        }

        return $image;
    }

    /**
     * Processes and saves piece image thumbnails.
     *
     * @param PieceImage $image
     * @param array      $data
     *
     * @return bool
     */
    private function processThumbnail($image, $data) {
        $thumbnail = Image::make($image->imagePath.'/'.$image->fullsizeFileName);

        if ($data['use_cropper'] ?? 0) {
            $cropWidth = $data['x1'] - $data['x0'];
            $cropHeight = $data['y1'] - $data['y0'];

            $thumbnail->crop($cropWidth, $cropHeight, $data['x0'], $data['y0']);

            // Resize to fit the thumbnail size
            $thumbnail->resize(config('aldebaran.settings.thumbnail_width'), config('aldebaran.settings.thumbnail_height'));
        } else {
            // Resize and save thumbnail
            if (config('aldebaran.settings.gallery_arrangement') == 'columns') {
                $thumbnail->resize(config('aldebaran.settings.thumbnail_width'), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } else {
                $thumbnail->resize(null, config('aldebaran.settings.thumbnail_height'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
        }

        $thumbnail->save($image->thumbnailPath.'/'.$image->thumbnailFileName, null, config('aldebaran.settings.image_formats.display') ?? $image->extension);

        $thumbnail->destroy();

        return true;
    }
}
