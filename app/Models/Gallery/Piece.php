<?php

namespace App\Models\Gallery;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Piece extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'project_id', 'description', 'timestamp', 'is_visible', 'good_example'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pieces';

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    public $dates = ['timestamp'];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $rules = [
        //
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the project associated with this piece.
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Gallery\Project', 'project_id');
    }

    /**
     * Get images associated with this piece.
     */
    public function images()
    {
        return $this->hasMany('App\Models\Gallery\PieceImage', 'piece_id')->orderBy('is_primary_image', 'DESC')->orderBy('sort', 'DESC');
    }

    /**
     * Get only primary images associated with this piece.
     */
    public function primaryImages()
    {
        return $this->hasMany('App\Models\Gallery\PieceImage', 'piece_id')->where('is_primary_image', 1)->orderBy('sort', 'DESC');
    }

    /**
     * Get only non-primary images associated with this piece.
     */
    public function otherImages()
    {
        return $this->hasMany('App\Models\Gallery\PieceImage', 'piece_id')->where('is_primary_image', 0)->orderBy('sort', 'DESC');
    }

    /**
     * Get tags associated with this piece.
     */
    public function tags()
    {
        return $this->hasMany('App\Models\Gallery\PieceTag', 'piece_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible pieces--
     * including only ones with at least one visible image.
     * Even with auth, pieces without an image are still hidden
     * as they will not display properly.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null)
    {
        if($user) return $query->whereIn('id', PieceImage::visible()->pluck('piece_id')->toArray());
        else return $query->where('is_visible', 1)->whereIn('id', PieceImage::visible($user ? $user : null)->pluck('piece_id')->toArray());
    }

    /**
     * Scope a query to only include pieces which should be included in the gallery.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGallery($query)
    {
        $hiddenTags = Tag::where('is_active', 0)->pluck('id')->toArray();
        return $query->whereNotIn('id', PieceTag::whereIn('tag_id', $hiddenTags)->pluck('piece_id')->toArray());
    }

    /**
     * Scope a query to sort pieces by timestamp if set, and otherwise by created_at.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort($query)
    {
        return $query->orderByRaw('ifnull(timestamp, created_at) DESC');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the piece's url.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('/gallery/pieces/'.$this->id.'.'.str_replace(' ', '-', str_replace('?', '', $this->name)));
    }

    /**
     * Get the piece's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->url.'">'.$this->name.'</a>';
    }

    /**
     * Return the piece's thumbnail URL.
     *
     * @return string
     */
    public function getThumbnailUrlAttribute()
    {
        if($this->images->where('is_visible', 1)->count() == 0) return null;
        return ($this->primaryImages->where('is_visible', 1)->count() ? $this->primaryImages->where('is_visible', 1)->random()->thumbnailUrl : $this->images->where('is_visible', 1)->first()->thumbnailUrl);
    }

    /**
     * Check if the piece should be displayed in the gallery.
     *
     * @return bool
     */
    public function getShowInGalleryAttribute()
    {
        // Check if the piece should be included in the gallery or not
        if($this->tags->whereIn('tag_id', Tag::where('is_active', 0)->pluck('id')->toArray())->first()) return 0;
        else return 1;
    }

}
