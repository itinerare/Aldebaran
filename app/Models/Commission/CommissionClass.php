<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionClass extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'is_active', 'sort', 'data', 'invoice_data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_classes';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'         => 'array',
        'invoice_data' => 'array',
    ];

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
        'name'         => 'required|unique:commission_classes',
        'product_name' => 'filled',
    ];

    /**
     * Validation rules for class editing.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'name'             => 'required',
        'page_key.*'       => 'nullable|required_with:page_title.*|between:3,25|alpha_dash',
        'page_title.*'     => 'nullable|required_with:page_key.*|between:3,100',
        'field_key.*'      => 'nullable|between:3,25|alpha_dash',
        'field_type.*'     => 'nullable|required_with:field_key.*',
        'field_label.*'    => 'nullable|string|required_with:field_key.*',
        'field_choices.*'  => 'nullable|string|required_if:field_type.*,choice,multiple',
        'field_rules.*'    => 'nullable|string|max:255',
        'field_value.*'    => 'nullable|string|max:255',
        'field_help.*'     => 'nullable|string|max:255',
        'product_name'     => 'filled',
        'product_category' => 'filled',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active commission types.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User                      $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $user = null) {
        if ($user) {
            return $query->whereNotNull('id');
        } else {
            return $query->where('is_active', 1);
        }
    }

    /**********************************************************************************************

       ACCESSORS

    **********************************************************************************************/

    /**
     * Get the class' edit url.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('/admin/data/commission-classes/edit/'.$this->id);
    }
}
