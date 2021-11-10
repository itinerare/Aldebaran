<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commission_key', 'commissioner_id', 'commission_type', 'paid_status', 'progress',
        'status', 'description', 'data', 'comments', 'cost'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commissions';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for commission creation.
     *
     * @var array
     */
    public static $createRules = [
        // Contact information
        'name' => 'string|nullable|min:3|max:191',
        'email' => 'email|required|min:3|max:191',
        'contact' => 'required|string|min:3|max:191',
        'paypal' => 'email|nullable|min:3|max:191',

        // Other
        'terms' => 'accepted',
        'g-recaptcha-response' => 'required|recaptchav3:submit,0.5'
    ];

    /**
     * Validation rules for manual commission creation.
     *
     * @var array
     */
    public static $manualCreateRules = [
        // Contact information
        'name' => 'string|nullable|min:3|max:191',
        'email' => 'email|required_without:commissioner_id|min:3|max:191|nullable',
        'contact' => 'required_without:commissioner_id|string|min:3|max:191|nullable',
        'paypal' => 'email|nullable|min:3|max:191'
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the type associated with this commission.
     */
    public function type()
    {
        return $this->belongsTo('App\Models\Commission\CommissionType', 'commission_type');
    }

    /**
     * Get the type associated with this commission.
     */
    public function commissioner()
    {
        return $this->belongsTo('App\Models\Commission\Commissioner', 'commissioner_id');
    }

    /**
     * Get the pieces associated with this commission.
     */
    public function pieces()
    {
        return $this->hasMany('App\Models\Commission\CommissionPiece', 'commission_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include commissions of a given class.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int                                 $class
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClass($query, $class)
    {
        return $query->whereIn('commission_type',
            CommissionType::whereIn('category_id', CommissionCategory::byClass($class)->pluck('id')->toArray())->pluck('id')->toArray()
        );
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the commission info page's url.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('commissions/view/'.$this->commission_key);
    }

    /**
     * Get formatted paid status.
     *
     * @return string
     */
    public function getIsPaidAttribute()
    {
        return $this->attributes['paid_status'] ? '<span class="text-success">Paid</span>' : ($this->status == 'Accepted' ? '<span class="text-danger"><strong>Unpaid</strong></span>' : '<s>Unpaid</s>');
    }

    /**
     * Get tip.
     *
     * @return string
     */
    public function getTipAttribute()
    {
        if(isset($this->data['tip'])) return $this->data['tip'];
        return null;
    }

    /**
     * Get total cost, including tip.
     *
     * @return string
     */
    public function getCostWithTipAttribute()
    {
        if(isset($this->data['tip'])) return $this->cost + $this->data['tip'];
        return $this->cost;
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Get the description attribute as an associative array.
     *
     * @return array
     */
    public function getDescriptionAttribute()
    {
        return json_decode($this->attributes['description'], true);
    }

    /**
     * Get the position of the commission in the queue.
     *
     * @return int
     */
    public function getQueuePositionAttribute()
    {
        // Take the ID of this commission for ease of access
        $id = $this->id;

        // Get all accepted commissions of the current commission's class,
        // and filter by this commission's ID; this should return only it,
        // preserving its key/position in the queue
        // Then strip the collection down to just the key
        $commissions = $this->class($this->type->category->class->id)->where('status', 'Accepted')->orderBy('created_at')->get()->filter(function($commission) use ($id) {
            return $commission->id == $id;
        })->keys();

        // Return key plus one, since array keys start at 0
        return $commissions->first() + 1;
    }
}
