<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Model;

class CommissionerIp extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commissioner_id', 'ip',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commissioner_ips';

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
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category associated with this commission type.
     */
    public function commissioner()
    {
        return $this->belongsTo(Commissioner::class, 'commissioner_id');
    }
}
