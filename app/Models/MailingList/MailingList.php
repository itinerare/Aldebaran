<?php

namespace App\Models\MailingList;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailingList extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'is_open',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mailing_lists';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for model creation.
     *
     * @var array
     */
    public static $createRules = [
        //
        'name' => 'required',
    ];

    /**
     * Validation rules for model updating.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'name' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the entries associated with this mailing list.
     */
    public function entries() {
        return $this->hasMany(MailingListEntry::class, 'mailing_list_id')->orderBy('created_at', 'DESC');
    }

    /**
     * Get the subscribers associated with this mailing list.
     */
    public function subscribers() {
        return $this->hasMany(MailingListSubscriber::class, 'mailing_list_id');
    }
}
