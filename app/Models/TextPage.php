<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextPage extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'name', 'text',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'text_pages';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for entry updating.
     *
     * @var array
     */
    public static $createRules = [
        'text' => 'required',
    ];

    /**
     * Validation rules for entry updating.
     *
     * @var array
     */
    public static $updateRules = [
        'text' => 'required',
    ];

    /**********************************************************************************************

       ACCESSORS

    **********************************************************************************************/

    /**
     * Get the page's edit url.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('/admin/pages/edit/'.$this->id);
    }
}
