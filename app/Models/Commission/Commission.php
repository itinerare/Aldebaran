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
        'key', 'commissioner_id', 'commission_type', 'paid_status', 'progress',
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

        // Commission-type specific fields
        'references' => 'sometimes|required|string|min:3|max:500',
        'details' => 'sometimes|required|string|min:3|max:500',
        'shading' => 'sometimes|boolean',
        'style' => 'sometimes|required|in:regular,heraldic',
        'background' => 'sometimes|required|string|min:3|max:500',
        'code_check' => 'sometimes|required|accepted',

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
        'paypal' => 'email|nullable|min:3|max:191',

        // Commission-type specific fields
        'references' => 'sometimes|required|string|min:3|max:500',
        'details' => 'sometimes|required|string|min:3|max:500',
        'shading' => 'sometimes|boolean',
        'style' => 'sometimes|required|in:regular,heraldic',
        'background' => 'sometimes|required|string|min:3|max:500',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the type associated with this commission.
     */
    public function commType()
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
     * Scope a query to only include art commissions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string                                 $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, $type)
    {
        return $query->whereIn('commission_type',
            CommissionType::whereIn('category_id',
            CommissionCategory::type($type)->pluck('id')->toArray()
            )->pluck('id')->toArray()
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
        return url('commissions/view/'.$this->key);
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

}