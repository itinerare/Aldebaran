<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;
use Settings;
use Image;

use App\Models\Gallery\Project;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceImage;
use App\Models\Gallery\Tag;
use App\Models\Gallery\PieceTag;
use\App\Models\Commission\CommissionPiece;

class GalleryService extends Service
{
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
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Gallery\Project|bool
     */
    public function createProject($data, $user)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['is_visible'])) $data['is_visible'] = 0;

            $project = Project::create($data);

            return $this->commitReturn($project);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a project.
     *
     * @param  \App\Models\Gallery\Project    $project
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Gallery\Project|bool
     */
    public function updateProject($project, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(Project::where('name', $data['name'])->where('id', '!=', $project->id)->exists()) throw new \Exception("The name has already been taken.");

            if(!isset($data['is_visible'])) $data['is_visible'] = 0;

            $project->update($data);

            return $this->commitReturn($project);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Delete a project.
     *
     * @param  \App\Models\Gallery\Project  $project
     * @return bool
     */
    public function deleteProject($project)
    {
        DB::beginTransaction();

        try {
            // Check first if the project is currently in use
            if(Piece::where('project_id', $project->id)->exists()) throw new \Exception("A piece with this category exists. Please move or delete it first.");

            $project->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts project order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortProject($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Project::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
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
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Gallery\Piece
     */
    public function createPiece($data, $user)
    {
        DB::beginTransaction();

        try {
            if(!Project::where('id', $data['project_id'])->exists()) throw new \Exception("The selected project is invalid.");

            $data = $this->populateData($data);
            $piece = Piece::create($data);

            // If tags are selected, validate and create data for them
            if(isset($data['tags']) && $piece->id) {
                $data = $this->processTags($data, $piece);
                $piece->update($data);
            }

            return $this->commitReturn($piece);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a piece.
     *
     * @param  \App\Models\Gallery\Piece  $piece
     * @param  array                      $data
     * @param  \App\Models\User\User      $user
     * @return bool|\App\Models\Gallery\Piece
     */
    public function updatePiece($piece, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(Piece::where('name', $data['name'])->where('id', '!=', $piece->id)->where('project_id', $data['project_id'])->exists()) throw new \Exception("The name has already been taken in this project.");
            if((isset($data['project_id']) && $data['project_id']) && !Project::where('id', $data['project_id'])->exists()) throw new \Exception("The selected project is invalid.");

            $data = $this->populateData($data);
            // If tags are selected, validate and create data for them
            if(isset($data['tags']) && $piece->id) {
                $data = $this->processTags($data, $piece);
            }
            $piece->update($data);

            return $this->commitReturn($piece);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a piece.
     *
     * @param  array                    $data
     * @param  \App\Models\piece\Piece  $piece
     * @return array
     */
    private function populateData($data, $piece = null)
    {
        // Check toggles
        if(!isset($data['is_visible'])) $data['is_visible'] = 0;
        if(!isset($data['good_example'])) $data['good_example'] = 0;

        return $data;
    }

    /**
     * Processes tag data.
     *
     * @param  array                    $data
     * @param  \App\Models\piece\Piece  $piece
     * @return array
     */
    private function processTags($data, $piece)
    {
        if($piece->id && $piece->tags->count()) {
            // Collect old tags and delete them
            $oldTags = $piece->tags();
            PieceTag::where('piece_id', $piece->id)->delete();
        }

        foreach($data['tags'] as $tag) {
            if(!Tag::where('id', $tag)->exists()) throw new \Exception("One or more of the selected tags is invalid.");

            PieceTag::create([
                'piece_id' => $piece->id,
                'tag_id' => $tag
            ]);
        }

        return $data;
    }

    /**
     * Deletes a piece.
     *
     * @param  \App\Models\Gallery\Piece  $piece
     * @return bool
     */
    public function deletePiece($piece)
    {
        DB::beginTransaction();

        try {
            if(CommissionPiece::where('piece_id', $piece->id)->exists()) throw new \Exception('A commission exists using this piece. Deleting it would potentially remove access to the final piece from the commissioner.');

            // First delete all images and tags associated with a piece, then the piece itself
            foreach($piece->images as $image) $this->deletePieceImage($image);
            PieceTag::where('piece_id', $piece->id)->delete();

            $piece->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);

    }

    /**
     * Sorts project order.
     *
     * @param  int    $id
     * @param  array  $data
     * @return bool
     */
    public function sortPieceImages($id, $data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                PieceImage::where('piece_id', $id)->where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
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
     * @param  array                    $data
     * @param  \App\Models\User\User    $user
     * @return bool|\App\Models\Gallery\PieceImage
     */
    public function createImage($data, $user)
    {
        DB::beginTransaction();

        try {
            // Check that the piece exists
            $piece = Piece::find($data['piece_id']);
            if(!$piece) throw new \Exception ('No valid piece found!');

            // Collect and encode watermark settings
            $data['data'] = [
                'scale' => $data['watermark_scale'],
                'opacity' => $data['watermark_opacity'],
                'position' => $data['watermark_position'],
                'color' => isset($data['watermark_color']) ? $data['watermark_color'] : null,
                'image_scale' => isset($data['image_scale']) ? $data['image_scale'] : null,
                'watermarked' => isset($data['watermark_image']) ? $data['watermark_image'] : 0,
                'text_watermark' => isset($data['text_watermark']) ? $data['text_watermark'] : null,
                'text_opacity' => isset($data['text_opacity']) ? $data['text_opacity'] : null
            ];
            $data['data'] = json_encode($data['data']);

            // Record data for the image
            $image = PieceImage::create([
                'piece_id' => $piece->id,
                'hash' => randomString(15),
                'fullsize_hash' => randomString(15),
                'extension' => $data['image']->getClientOriginalExtension(),
                'description' => isset($data['description']) ? $data['description'] : null,
                'is_primary_image' => isset($data['is_primary_image']) ? $data['is_primary_image'] : 0,
                'is_visible' => isset($data['is_visible']) ? $data['is_visible'] : 0,
                'data' => $data['data']
            ]);

            $this->processImage($data, $image);
            $image->update();

            return $this->commitReturn($image);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an image.
     *
     * @param  \App\Models\Gallery\PieceImage     $target
     * @param  array                              $data
     * @param  \App\Models\User\User              $user
     * @return bool|\App\Models\Gallery\PieceIamge
     */
    public function updateImage($image, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Check the regenerate watermark toggle, including setting it to false if an image has been uploaded
            if(!isset($data['regenerate_watermark']) || isset($data['image'])) $data['regenerate_watermark'] = 0;

            if(isset($data['image']) || $data['regenerate_watermark']) {
                $this->processImage($data, $image, true, $data['regenerate_watermark']);
            }

            $image->update([
                'description' => isset($data['description']) ? $data['description'] : null,
                'is_primary_image' => isset($data['is_primary_image']) ? $data['is_primary_image'] : 0,
                'is_visible' => isset($data['is_visible']) ? $data['is_visible'] : 0,
                'data' => isset($data['data']) ? $data['data'] : $image->data,
            ]);

            return $this->commitReturn($image);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes images.
     *
     * @param  array                           $data
     * @param  \App\Models\Gallery\PieceImage  $image
     * @param  bool                            $reupload
     * @return array
     */
    private function processImage($data, $image, $reupload = false, $regen = false)
    {
        // If the image is a reupload, unlink the old image and regenerate the hashes
        // as well as re-setting the extension.
        if($reupload) {
            // Unlink images as necessary
            unlink($image->imagePath . '/' . $image->thumbnailFileName);
            unlink($image->imagePath . '/' . $image->imageFileName);
            if(!$regen) unlink($image->imagePath . '/' . $image->fullsizeFileName);

            $image->update([
                'hash' => randomString(15),
                'fullsize_hash' => $regen ? $image->fullsize_hash : randomString(15),
                'extension' => $regen ? $image->extension : $data['image']->getClientOriginalExtension()
            ]);
        }

        // Save fullsize image before doing any processing
        if(!$regen) $this->handleImage($data['image'], $image->imageDirectory, $image->fullsizeFileName);

        // Process and save thumbnail from the fullsize image
        $thumbnail = Image::make($image->imagePath . '/' .  $image->fullsizeFileName);
        // Resize and save thumbnail
        if(Config::get('itinerare.settings.gallery_arrangement') == 'columns') $thumbnail->resize(Config::get('itinerare.settings.thumbnail_width'), null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        else $thumbnail->resize(null, Config::get('itinerare.settings.thumbnail_height'), function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $thumbnail->save($image->thumbnailPath . '/' . $image->thumbnailFileName);
        $thumbnail->destroy();

        // Process and save watermarked image
        $processImage = Image::make($image->imagePath . '/' .  $image->fullsizeFileName);

        // Resize image if either dimension is larger than 2k px
        $adjustedCap = isset($data['image_scale']) ? min((max($processImage->height(), $processImage->width()) * $data['image_scale']), Config::get('itinerare.settings.display_image_size')) : Config::get('itinerare.settings.display_image_size');

        if(max($processImage->height(), $processImage->width()) > $adjustedCap) {
            if($processImage->width() > $processImage->height()) {
                // Landscape
                $processImage->resize($adjustedCap, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            else {
                // Portrait
                $processImage->resize(null, $adjustedCap, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
        }

        if(isset($data['watermark_image']) && $data['watermark_image']) {

            // Add text watermark if necessary
            if(isset($data['text_watermark']) && $data['text_watermark']) {
                // Set text based on form input
                switch($data['text_watermark']) {
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
                while ($y < $processImage->height()+150) {
                    $x = -100;

                    while($x < $processImage->width()+150) {
                        foreach($watermarkText as $key=>$text) {
                            $processImage->text($text, $key == 0 && count($watermarkText) > 1 ? $x+(22+($offset*5)) : $x, $key > 0 ? $y+$i : $y, function($font) use ($data) {
                                $font->file(public_path('webfonts/Forum-Regular.ttf'));
                                $font->size(24);
                                $font->color(array(255,255,255,$data['text_opacity']));
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
            $watermark = Image::make('images/assets/watermark.png');
            // Colorize the watermark if called for
            if(isset($data['watermark_color'])) {
                // Convert hex code to RGB
                list($r, $g, $b) = sscanf($data['watermark_color'], "#%02x%02x%02x");
                $r = round($r / (255 / 100));
                $g = round($g / (255 / 100));
                $b = round($b / (255 / 100));

                $watermark->colorize($r,$g,$b);
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

        $processImage->save($image->imagePath . '/' . $image->imageFileName);

        if($reupload) {
            $data['data'] = [
                'scale' => $data['watermark_scale'],
                'opacity' => $data['watermark_opacity'],
                'position' => $data['watermark_position'],
                'color' => isset($data['watermark_color']) ? $data['watermark_color'] : null,
                'image_scale' => isset($data['image_scale']) ? $data['image_scale'] : null,
                'watermarked' => isset($data['watermark_image']) ? $data['watermark_image'] : 0,
                'text_watermark' => isset($data['text_watermark']) ? $data['text_watermark'] : null,
                'text_opacity' => isset($data['text_opacity']) ? $data['text_opacity'] : null
            ];
            $image->update(['data' => json_encode($data['data'])]);
        }

        return $image;
    }

    /**
     * Deletes an image.
     *
     * @param  \App\Models\Gallery\PieceImage  $image
     * @return bool
     */
    public function deletePieceImage($image)
    {
        DB::beginTransaction();

        try {
            // Delete the associated files...
            unlink($image->imagePath . '/' . $image->thumbnailFileName);
            unlink($image->imagePath . '/' . $image->imageFileName);
            unlink($image->imagePath . '/' . $image->fullsizeFileName);

            // and then the model itself
            $image->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
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
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Gallery\Tag|bool
     */
    public function createTag($data, $user)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['is_visible'])) $data['is_visible'] = 0;
            if(!isset($data['is_active'])) $data['is_active'] = 0;

            $tag = Tag::create($data);

            return $this->commitReturn($tag);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a tag.
     *
     * @param  \App\Models\Gallery\Tag    $tag
     * @param  array                      $data
     * @param  \App\Models\User\User      $user
     * @return \App\Models\Gallery\Tag|bool
     */
    public function updateTag($tag, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(Tag::where('name', $data['name'])->where('id', '!=', $tag->id)->exists()) throw new \Exception("The name has already been taken.");

            if(!isset($data['is_visible'])) $data['is_visible'] = 0;
            if(!isset($data['is_active'])) $data['is_active'] = 0;

            $tag->update($data);

            return $this->commitReturn($tag);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Delete a tag.
     *
     * @param  \App\Models\Gallery\Tag  $tag
     * @return bool
     */
    public function deleteTag($tag)
    {
        DB::beginTransaction();

        try {
            // Check first if the tag is currently in use
            if(PieceTag::where('tag_id', $tag->id)->exists()) throw new \Exception("A piece with this tag exists. Please remove the tag first.");

            $pieceTag->where('tag_id', $tag->id)->delete();
            $tag->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
