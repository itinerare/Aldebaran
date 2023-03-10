<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPayment extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commission_id', 'cost', 'tip', 'total_with_fees',
        'is_paid', 'is_intl', 'paid_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_payments';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['commission'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'cost'            => 'decimal:2',
        'tip'             => 'decimal:2',
        'total_with_fees' => 'decimal:2',
        'paid_at'         => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the commission associated with this payment.
     */
    public function commission() {
        return $this->belongsTo(Commission::class, 'commission_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get total with fees.
     *
     * @return float
     */
    public function getTotalWithFeesAttribute() {
        if (isset($this->attributes['total_with_fees'])) {
            return $this->attributes['total_with_fees'];
        } else {
            // For unpaid payments, this is calculated on the fly
            // and may change with fees as appropriate until the payment is complete
            $total = 0;
            $total += $this->calculateAdjustedTotal($this->cost, $this->tip, $this->is_intl, $this->commission->payment_processor);

            return $total;
        }

        return 0;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Calculate the total for a payment after fees.
     *
     * @param float  $cost
     * @param float  $tip
     * @param bool   $isIntl
     * @param string $paymentProcessor
     *
     * @return float
     */
    public static function calculateAdjustedTotal($cost, $tip, $isIntl, $paymentProcessor) {
        $total = $cost + (isset($tip) && $tip ? $tip : 0);

        // Calculate fee and round
        $fee =
            ($total * ($isIntl ? config('aldebaran.commissions.payment_processors.'.$paymentProcessor.'.fee.percent_intl') : config('aldebaran.commissions.payment_processors.'.$paymentProcessor.'.fee.percent')) / 100) + config('aldebaran.commissions.payment_processors.'.$paymentProcessor.'.fee.base');
        $fee = round($fee, 2);

        return $total - $fee;
    }
}
