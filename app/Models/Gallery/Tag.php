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
        'name', 'description', 'is_active', 'sort'
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
        'name' => 'required|unique:tags'
    ];

    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'name' => 'required'
    ];
}
