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
        'name' => 'required:unique',
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
        return $this->hasMany(MailingListEntry::class, 'mailing_list_id');
    }

    /**
     * Get the subscribers associated with this mailing list.
     */
    public function subscribers() {
        return $this->hasMany(MailingListSubscriber::class, 'mailing_list_id')->orderBy('email');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to return only open mailing lists.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query) {
        return $query->where('is_open', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the mailing list's url.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('/mailing-lists/'.$this->id);
    }
}
