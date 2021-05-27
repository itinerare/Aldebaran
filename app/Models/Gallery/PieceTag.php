<?php

namespace App\Models\Gallery;

use Illuminate\Database\Eloquent\Model;

class PieceTag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'piece_id', 'tag_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'piece_tags';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $createRules = [
        //
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the tag associated with this piece tag.
     */
    public function tag()
    {
        return $this->belongsTo('App\Models\Gallery\Tag', 'tag_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible tags.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->whereIn('tag_id', Tag::visible()->pluck('id')->toArray());
    }
}
