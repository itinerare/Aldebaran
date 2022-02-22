<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Model;

class CommissionPayment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commission_id', 'cost', 'tip', 'is_paid', 'is_intl', 'paid_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_payments';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    public $dates = ['paid_at'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the commission associated with this payment.
     */
    public function commission()
    {
        return $this->belongsTo(Commission::class, 'commission_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get cost with fees.
     *
     * @return int
     */
    public function getTotalWithFeesAttribute()
    {
        $total = 0;
        $total += $this->commission->paymentWithFees($this);

        return $total;
    }
}
