<?php

namespace App\Models\Gallery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PieceImage extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'piece_id', 'hash', 'fullsize_hash', 'extension', 'display_extension',
        'description', 'is_primary_image', 'data', 'is_visible', 'sort', 'alt_text',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'piece_images';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for piece image creation.
     *
     * @var array
     */
    public static $createRules = [
        'image'              => 'required|mimes:png,jpg,jpeg,gif,webp,mp4,webm|max:15000',
        'watermark_scale'    => 'required',
        'watermark_opacity'  => 'required',
        'watermark_position' => 'required',
        'watermark_color'    => 'nullable|regex:/^#?[0-9a-fA-F]{6}$/i',
        'text_opacity'       => 'required_with:text_watermark',
        'description'        => 'max:255',
    ];

    /**
     * Validation rules for piece image creation.
     *
     * @var array
     */
    public static $updateRules = [
        'image'              => 'mimes:png,jpg,jpeg,gif,webp,mp4,webm|max:15000',
        'watermark_scale'    => 'required_with:image,regenerate_watermark',
        'watermark_opacity'  => 'required_with:image,regenerate_watermark',
        'watermark_position' => 'required_with:image,regenerate_watermark',
        'watermark_color'    => 'nullable|regex:/^#?[0-9a-fA-F]{6}$/i',
        'text_opacity'       => 'required_with:text_watermark',
        'description'        => 'max:255',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the piece associated with this image.
     */
    public function piece() {
        return $this->belongsTo(Piece::class, 'piece_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible images.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user) {
            return $query;
        } else {
            return $query->where('is_visible', 1);
        }
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Checks if an image should use multimedia handling.
     *
     * @return bool
     */
    public function getIsMultimediaAttribute() {
        if (in_array($this->extension, ['gif', 'mp4', 'webm'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if an image is a video.
     *
     * @return bool
     */
    public function getIsVideoAttribute() {
        if (in_array($this->extension, ['mp4', 'webm'])) {
            return true;
        }

        return false;
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/pieces/'.floor($this->id / 1000);
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        if ($this->isMultimedia) {
            return $this->fullsizeFileName;
        }

        return $this->id.'_'.$this->hash.'.'.($this->display_extension ?? $this->extension);
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the file name of the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailFileNameAttribute() {
        return $this->id.'_'.$this->hash.'_th.'.($this->display_extension ?? $this->extension);
    }

    /**
     * Gets the path to the file directory containing the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailPathAttribute() {
        return $this->imagePath;
    }

    /**
     * Gets the URL of the model's thumbnail.
     *
     * @return string
     */
    public function getThumbnailUrlAttribute() {
        return asset($this->imageDirectory.'/'.$this->thumbnailFileName);
    }

    /**
     * Gets the file name of the model's fullsize image.
     *
     * @return string
     */
    public function getFullsizeFileNameAttribute() {
        return $this->id.'_'.$this->fullsize_hash.'_full.'.$this->extension;
    }

    /**
     * Gets the URL of the model's fullsize image.
     *
     * @return string
     */
    public function getFullsizeUrlAttribute() {
        return asset($this->imageDirectory.'/'.$this->fullsizeFileName);
    }
}
