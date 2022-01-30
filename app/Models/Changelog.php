<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Settings;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Changelog extends Model implements Feedable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'text', 'is_visible',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'changelog_entries';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for entry creation.
     *
     * @var array
     */
    public static $createRules = [
        //
        'text' => 'required',
    ];

    /**
     * Validation rules for entry updating.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'text' => 'required',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible entries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Returns all feed items.
     */
    public static function getFeedItems()
    {
        return self::visible()->get();
    }

    /**
     * Generates feed item information.
     *
     * @return /Spatie/Feed/FeedItem;
     */
    public function toFeedItem(): FeedItem
    {
        return FeedItem::create([
            'id'      => '/changelog/'.$this->id,
            'title'   => $this->name ? $this->name : $this->created_at->toFormattedDateString(),
            'summary' => $this->text,
            'updated' => $this->created_at,
            'link'    => '/changelog',
            'author'  => Settings::get('site_name'),
        ]);
    }
}
