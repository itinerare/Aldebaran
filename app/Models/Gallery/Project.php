<?php

namespace App\Models\Gallery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'is_visible', 'sort',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';

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
    public static $createRules = [
        //
        'name' => 'required|unique:projects',
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
     * Scope a query to only include visible projects.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query) {
        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

       ACCESSORS

    **********************************************************************************************/

    /**
     * Get the project's edit url.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('/admin/data/projects/edit/'.$this->id);
    }

    /**
     * Get the project's slug.
     *
     * @return string
     */
    public function getSlugAttribute() {
        return str_replace(' ', '_', strtolower($this->name));
    }

    /**
     * Get the project's URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('/projects/'.$this->slug);
    }

    /**
     * Get the project's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'">'.$this->name.'</a>';
    }
}
