<?php

namespace App\Models\MailingList;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailingListSubscriber extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mailing_list_id', 'email', 'token', 'is_verified', 'last_entry_sent',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mailing_list_subscribers';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['lastEntry'];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for model creation.
     *
     * @var array
     */
    public static $createRules = [
        //
        'email' => 'required',
    ];

    /**
     * Validation rules for model updating.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'email' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the mailing list associated with this subscriber.
     */
    public function mailingList() {
        return $this->belongsTo(MailingList::class, 'mailing_list_id');
    }

    /**
     * Get the last entry associated with this subscriber.
     */
    public function lastEntry() {
        return $this->hasOne(MailingListEntry::class, 'id', 'last_entry_sent');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to return only verified or non-verified subscribers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $isVerified
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query, $isVerified = 1) {
        return $query->where('is_verified', $isVerified);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the verify subscription url.
     *
     * @return string
     */
    public function getVerifyUrlAttribute() {
        return url('/mailing-lists/verify/'.$this->id.'?token='.$this->token);
    }

    /**
     * Get the unsubscription url.
     *
     * @return string
     */
    public function getUnsubscribeUrlAttribute() {
        return url('/mailing-lists/unsubscribe/'.$this->id.'?token='.$this->token);
    }
}
