<?php

namespace App\Models\Commission;

use Settings;

use Illuminate\Database\Eloquent\Model;

class CommissionClass extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'is_active', 'sort', 'data'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_classes';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for class creation.
     *
     * @var array
     */
    public static $createRules = [
        //
        'name' => 'required|unique:commission_classes'
    ];

    /**
     * Validation rules for class editing.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'name' => 'required',
        'page_key.*' => 'nullable|unique:text_pages,key'
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active commission types.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User                       $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $user = null)
    {
        if($user) return $query;
        return $query->where('is_active', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true);
    }

}
