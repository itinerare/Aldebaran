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
        'name', 'is_active', 'sort', 'class_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_categories';

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
     * Validation rules for category creation.
     *
     * @var array
     */
    public static $createRules = [
        //
        'name' => 'required|unique:commission_categories',
    ];

    /**
     * Validation rules for category editing.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'name'            => 'required',
        'field_key.*'     => 'nullable|between:3,25|alpha_dash',
        'field_type.*'    => 'nullable|required_with:field_key.*',
        'field_label.*'   => 'nullable|string|required_with:field_key.*',
        'field_choices.*' => 'nullable|string|required_if:field_type.*,choice,multiple',
        'field_rules.*'   => 'nullable|string|max:255',
        'field_value.*'   => 'nullable|string|max:255',
        'field_help.*'    => 'nullable|string|max:255',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the class this commission category belongs to.
     */
    public function class()
    {
        return $this->belongsTo('App\Models\Commission\CommissionClass', 'class_id');
    }

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
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include commission categories of a given class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $class
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByClass($query, $class)
    {
        return $query->where('class_id', $class);
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
        return ucfirst($this->class->name).' ・ '.$this->name;
    }
}
