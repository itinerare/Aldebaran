<?php

namespace App\Models\Gallery;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'is_active', 'sort', 'is_visible',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tags';

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
        'name' => 'required|unique:tags',
    ];

    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'name' => 'required',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible tags.
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
     * Get the piece's url.
     *
     * @param mixed|null $source
     *
     * @return string
     */
    public function getUrl($source = null)
    {
        return url('/'.($source ? $source : 'gallery').'?tags[]='.$this->id);
    }

    /**
     * Get the piece's display name.
     *
     * @param mixed|null $source
     *
     * @return string
     */
    public function getDisplayName($source = null)
    {
        return '<a href="'.$this->getUrl($source).'">'.$this->name.'</a>';
    }
}
