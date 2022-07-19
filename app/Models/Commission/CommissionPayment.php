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
        'commission_id', 'cost', 'tip', 'is_paid', 'is_intl', 'paid_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_payments';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'paid_at' => 'datetime',
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
     * Get cost with fees.
     *
     * @return int
     */
    public function getTotalWithFeesAttribute() {
        $total = 0;
        $total += $this->commission->paymentWithFees($this);

        return $total;
    }
}
