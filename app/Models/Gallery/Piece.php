<?php

namespace App\Models\Gallery;

use App\Facades\Settings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Piece extends Model implements Feedable {
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'project_id', 'description', 'timestamp', 'is_visible', 'good_example',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pieces';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'project:id,name,is_visible',
        'primaryImages', 'primaryLiteratures',
        'images', 'literatures',
    ];

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
    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get images associated with this piece.
     */
    public function images() {
        return $this->hasMany(PieceImage::class, 'piece_id')->orderBy('is_primary_image', 'DESC')->orderBy('sort', 'DESC');
    }

    /**
     * Get only primary images associated with this piece.
     */
    public function primaryImages() {
        return $this->hasMany(PieceImage::class, 'piece_id')->where('is_primary_image', 1)->orderBy('sort', 'DESC');
    }

    /**
     * Get only non-primary images associated with this piece.
     */
    public function otherImages() {
        return $this->hasMany(PieceImage::class, 'piece_id')->where('is_primary_image', 0)->orderBy('sort', 'DESC');
    }

    /**
     * Get literatures associated with this piece.
     */
    public function literatures() {
        return $this->hasMany(PieceLiterature::class, 'piece_id')->orderBy('is_primary', 'DESC')->orderBy('sort', 'DESC');
    }

    /**
     * Get only primary literatures associated with this piece.
     */
    public function primaryLiteratures() {
        return $this->hasMany(PieceLiterature::class, 'piece_id')->where('is_primary', 1)->orderBy('sort', 'DESC');
    }

    /**
     * Get only non-primary literatures associated with this piece.
     */
    public function otherLiteratures() {
        return $this->hasMany(PieceLiterature::class, 'piece_id')->where('is_primary', 0)->orderBy('sort', 'DESC');
    }

    /**
     * Get tags associated with this piece.
     */
    public function tags() {
        return $this->hasMany(PieceTag::class, 'piece_id');
    }

    /**
     * Get programs associated with this piece.
     */
    public function programs() {
        return $this->hasMany(PieceProgram::class, 'piece_id');
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user) {
            return $query->has('images')->orHas('literatures');
        }

        return $query
            ->where('is_visible', 1)
            ->whereRelation('project', 'is_visible', true)
            ->where(function ($query) {
                $query->whereRelation('images', 'is_visible', true)
                ->orWhereRelation('literatures', 'is_visible', true);
            });
    }

    /**
     * Scope a query to only include pieces which should be included in the gallery.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGallery($query) {
        return $query
            ->whereRelation('tags.tag', 'is_active', true)
            ->orWhereDoesntHave('tags');
    }

    /**
     * Scope a query to sort pieces by timestamp if set, and otherwise by created_at.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort($query) {
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
    public function getUrlAttribute() {
        return url('/gallery/pieces/'.$this->id.'.'.$this->slug);
    }

    /**
     * Get the piece's edit url.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('/admin/data/pieces/edit/'.$this->id);
    }

    /**
     * Get the piece's slug.
     *
     * @return string
     */
    public function getSlugAttribute() {
        $string = str_replace(' ', '-', $this->name);

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    /**
     * Get the piece's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'">'.$this->name.'</a>';
    }

    /**
     * Return the piece's thumbnail URL.
     *
     * @return string
     */
    public function getThumbnailUrlAttribute() {
        if (!$this->whereRelation('images', 'is_visible', true)->count() && !$this->literatures()->visible()->whereNotNull('hash')->count()) {
            return null;
        }

        // Cycle through conditions attempting to locate a valid thumbnail
        if ($this->primaryImages()->visible()->count()) {
            // First check for primary images; if so select a random one
            return $this->primaryImages()->visible()->get()->random()->thumbnailUrl;
        } elseif ($this->images()->visible()->count()) {
            // Otherwise, if there are non-primary images, select a random one
            return $this->images()->visible()->get()->random()->thumbnailUrl;
        } elseif ($this->literatures()->visible()->whereNotNull('hash')->where('is_primary', 1)->count()) {
            // Otherwise, check for primary literatures with thumbnails,
            // and select one
            return $this->literatures()->visible()->whereNotNull('hash')->where('is_primary', 1)->get()->random()->thumbnailUrl;
        } elseif ($this->literatures()->visible()->whereNotNull('hash')->count()) {
            // Otherwise, check for non-primary literatures with thumbnails,
            // and select one
            return $this->literatures()->visible()->whereNotNull('hash')->get()->random()->thumbnailUrl;
        }

        return null;
    }

    /**
     * Get the relevant date for the piece's creation.
     *
     * @return \Carbon\Carbon
     */
    public function getDateAttribute() {
        return $this->timestamp ?? $this->created_at;
    }

    /**
     * Check if the piece should be displayed in the gallery.
     *
     * @return bool
     */
    public function getShowInGalleryAttribute() {
        // Check if the gallery is enabled to be displayed in
        if (!config('aldebaran.settings.navigation.gallery')) {
            return 0;
        }

        // Check if the piece should be included in the gallery or not
        if ($this->whereRelation('tags.tag', 'is_active', false)->count()) {
            return 0;
        }

        return 1;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Returns all feed items.
     *
     * @param mixed      $gallery
     * @param mixed|null $project
     */
    public static function getFeedItems($gallery = true, $project = null) {
        $pieces = self::visible()->with(['images', 'literatures', 'tags']);
        if ($gallery) {
            return $pieces->gallery()->get();
        } elseif (isset($project) && $project) {
            return $pieces->where('project_id', $project)->get();
        }

        return $pieces->get();
    }

    /**
     * Generates feed item information.
     *
     * @return /Spatie/Feed/FeedItem;
     */
    public function toFeedItem(): FeedItem {
        $summary = '';
        if ($this->images()->visible()->count()) {
            $summary = $summary.'<a href="'.$this->url.'"><img src="'.$this->thumbnailUrl.'" alt="Thumbnail for '.$this->name.'" /></a><br/>This piece contains '.$this->images->count().' image'.($this->images()->visible()->count() > 1 ? 's' : '').'. Click the thumbnail to view in full.<hr/>';
        }
        if ($this->literatures()->visible()->count()) {
            foreach ($this->literatures()->visible()->get() as $literature) {
                $summary = $summary.$literature->text.'<hr/>';
            }
        }
        $summary = $summary.$this->description;

        return FeedItem::create([
            'id'         => '/gallery/pieces/'.$this->id,
            'title'      => $this->name,
            'summary'    => $summary,
            'updated'    => $this->timestamp ?? $this->created_at,
            'link'       => $this->url,
            'author'     => Settings::get('site_name'),
            'authorName' => Settings::get('site_name'),
        ]);
    }
}
