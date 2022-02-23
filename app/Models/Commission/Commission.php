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
        'status', 'description', 'data', 'comments', 'cost_data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commissions';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'        => 'array',
        'cost_data'   => 'array',
        'description' => 'array',
    ];

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
        'name'    => 'string|nullable|min:3|max:191',
        'email'   => 'email|required|min:3|max:191',
        'contact' => 'required|string|min:3|max:191',
        'paypal'  => 'email|nullable|min:3|max:191',

        // Other
        'terms' => 'accepted',
    ];

    /**
     * Validation rules for manual commission creation.
     *
     * @var array
     */
    public static $manualCreateRules = [
        // Contact information
        'name'    => 'string|nullable|min:3|max:191',
        'email'   => 'email|required_without:commissioner_id|min:3|max:191|nullable',
        'contact' => 'required_without:commissioner_id|string|min:3|max:191|nullable',
        'paypal'  => 'email|nullable|min:3|max:191',
    ];

    /**
     * Validation rules for commission updating.
     *
     * @var array
     */
    public static $updateRules = [
        'cost.*' => 'nullable|filled|required_with:tip.*',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the type associated with this commission.
     */
    public function type()
    {
        return $this->belongsTo(CommissionType::class, 'commission_type');
    }

    /**
     * Get the type associated with this commission.
     */
    public function commissioner()
    {
        return $this->belongsTo(Commissioner::class, 'commissioner_id');
    }

    /**
     * Get the payments associated with this commission.
     */
    public function payments()
    {
        return $this->hasMany(CommissionPayment::class, 'commission_id');
    }

    /**
     * Get the pieces associated with this commission.
     */
    public function pieces()
    {
        return $this->hasMany(CommissionPiece::class, 'commission_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include commissions of a given class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $class
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClass($query, $class)
    {
        return $query->whereIn('commission_type', CommissionType::whereIn('category_id', CommissionCategory::byClass($class)->pluck('id')->toArray())->pluck('id')->toArray());
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
     * @return bool
     */
    public function getPaidStatusAttribute()
    {
        if (!$this->payments->count()) {
            return 0;
        }
        foreach ($this->payments as $payment) {
            if ($payment->is_paid == 0) {
                return 0;
            }
        }

        return 1;
    }

    /**
     * Get formatted paid status.
     *
     * @return string
     */
    public function getIsPaidAttribute()
    {
        return $this->paidStatus ?
            '<span class="text-success">Paid</span>' :
            ($this->status == 'Accepted' ? '<span class="text-danger"><strong>Unpaid</strong></span>' : '<s>Unpaid</s>');
    }

    /**
     * Get overall cost.
     *
     * @return int
     */
    public function getCostAttribute()
    {
        $total = 0;
        if ($this->payments->count()) {
            foreach ($this->payments as $payment) {
                $total += $payment->cost;
            }
        }

        return $total;
    }

    /**
     * Get overall tip.
     *
     * @return int
     */
    public function getTipAttribute()
    {
        $total = 0;
        if ($this->payments->count()) {
            foreach ($this->payments as $payment) {
                $total += $payment->tip;
            }
        }

        return $total;
    }

    /**
     * Get total cost, including tip.
     *
     * @return string
     */
    public function getCostWithTipAttribute()
    {
        return $this->cost + $this->tip;
    }

    /**
     * Get overall cost with fees.
     *
     * @return int
     */
    public function getTotalWithFeesAttribute()
    {
        $total = 0;
        // Cycle through payments, getting their total with fees
        if ($this->payments->count()) {
            foreach ($this->payments as $payment) {
                $total += $this->paymentWithFees($payment);
            }
        }

        return $total;
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
        $commissions = $this->class($this->type->category->class->id)->where('status', 'Accepted')->orderBy('created_at')->get()->filter(function ($commission) use ($id) {
            return $commission->id == $id;
        })->keys();

        // Return key plus one, since array keys start at 0
        return $commissions->first() + 1;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Calculate the total for a payment after fees.
     *
     * @param \App\Models\Commission\CommissionPayment $payment
     *
     * @return int
     */
    public function paymentWithFees($payment)
    {
        $total = $payment->cost + (isset($payment->tip) && $payment->tip ? $payment->tip : 0);

        // Calculate fee and round
        $fee =
            ($total * ((isset($payment->is_intl) && $payment->is_intl ? config('aldebaran.settings.fee.percent_intl') : config('aldebaran.settings.fee.percent')) / 100)) + config('aldebaran.settings.fee.base');
        $fee = round($fee, 2);

        return $total - $fee;
    }
}
