<?php

namespace App\Models\MailingList;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailingListEntry extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mailing_list_id', 'subject', 'text', 'is_draft', 'sent_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mailing_list_entries';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['mailingList'];

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
        'subject' => 'required',
        'text'    => 'required',
    ];

    /**
     * Validation rules for model updating.
     *
     * @var array
     */
    public static $updateRules = [
        //
        'subject' => 'required',
        'text'    => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the mailing list associated with this entry.
     */
    public function mailingList() {
        return $this->belongsTo(MailingList::class, 'mailing_list_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort entries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort($query) {
        return $query->orderBy('is_draft', 'DESC')->orderBy('sent_at', 'DESC');
    }
}
