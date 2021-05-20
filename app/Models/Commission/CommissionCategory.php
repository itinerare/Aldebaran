<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Model;

class CommissionCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type', 'is_active', 'sort'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_categories';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for category creation.
     *
     * @var array
     */
    public static $createRules = [
        //
        'name' => 'required|unique:commission_categories'
    ];

    /**
     * Validation rules for category editing.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'name' => 'required'
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the types associated with this commission category.
     */
    public function types()
    {
        return $this->hasMany('App\Models\Commission\CommissionType', 'category_id')->orderBy('sort', 'DESC');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active commission categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include art commission categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArt($query)
    {
        return $query->where('type', 'art');
    }

    /**
     * Scope a query to only include code commission categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCode($query)
    {
        return $query->where('type', 'code');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the commission category's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return ucfirst($this->type).' ・ '.$this->name;
    }

}
