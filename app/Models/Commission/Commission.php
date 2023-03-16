<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commission_key', 'commissioner_id', 'commission_type', 'progress',
        'status', 'description', 'data', 'comments', 'payment_processor',
        'invoice_data',
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
        'data'         => 'array',
        'cost_data'    => 'array',
        'description'  => 'array',
        'invoice_data' => 'array',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'type', 'pieces',
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
        'name'          => 'string|nullable|min:3|max:191',
        'email'         => 'email|required|min:3|max:191',
        'contact'       => 'required|string|min:3|max:191',
        'payment_email' => 'email|nullable|min:3|max:191',

        // Other
        'terms'   => 'accepted',
    ];

    /**
     * Validation rules for manual commission creation.
     *
     * @var array
     */
    public static $manualCreateRules = [
        // Contact information
        'name'          => 'string|nullable|min:3|max:191',
        'email'         => 'email|required_without:commissioner_id|min:3|max:191|nullable',
        'contact'       => 'required_without:commissioner_id|string|min:3|max:191|nullable',
        'payment_email' => 'email|nullable|min:3|max:191',
    ];

    /**
     * Validation rules for commission updating.
     *
     * @var array
     */
    public static $updateRules = [
        'cost.*' => 'nullable|filled|required_with:tip.*',
        'tip.*'  => 'nullable|filled|required_with:cost.*',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the type associated with this commission.
     */
    public function type() {
        return $this->belongsTo(CommissionType::class, 'commission_type');
    }

    /**
     * Get the type associated with this commission.
     */
    public function commissioner() {
        return $this->belongsTo(Commissioner::class, 'commissioner_id');
    }

    /**
     * Get the payments associated with this commission.
     */
    public function payments() {
        return $this->hasMany(CommissionPayment::class, 'commission_id');
    }

    /**
     * Get the pieces associated with this commission.
     */
    public function pieces() {
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
    public function scopeClass($query, $class) {
        return $query->whereRelation('type.category', 'class_id', $class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the commission info page's url.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('commissions/view/'.$this->commission_key);
    }

    /**
     * Get the commission's edit url.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('/admin/commissions/edit/'.$this->id);
    }

    /**
     * Get formatted paid status.
     *
     * @return bool
     */
    public function getPaidStatusAttribute() {
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
    public function getIsPaidAttribute() {
        return $this->paidStatus ?
            '<span class="text-success">Paid</span>' :
            ($this->status == 'Accepted' ? '<span class="text-danger"><strong>Unpaid</strong></span>' : '<s>Unpaid</s>');
    }

    /**
     * Get overall cost.
     *
     * @return float
     */
    public function getCostAttribute() {
        if ($this->payments->count()) {
            return $this->payments->pluck('cost')->sum();
        }

        return 0;
    }

    /**
     * Get overall tip.
     *
     * @return float
     */
    public function getTipAttribute() {
        if ($this->payments->count()) {
            return $this->payments->pluck('tip')->sum();
        }

        return 0;
    }

    /**
     * Get total cost, including tip.
     *
     * @return float
     */
    public function getCostWithTipAttribute() {
        return $this->cost + $this->tip;
    }

    /**
     * Get overall cost with fees.
     *
     * @return int
     */
    public function getTotalWithFeesAttribute() {
        if ($this->payments->count()) {
            return $this->payments->pluck('totalWithFees')->sum();
        }

        return 0;
    }

    /**
     * Get the position of the commission in the queue.
     *
     * @return int
     */
    public function getQueuePositionAttribute() {
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

    /**
     * Checks if a commission should use payment processor integration features.
     *
     * @return bool
     */
    public function getUseIntegrationsAttribute() {
        if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled') && $this->payment_processor == 'stripe') {
            return true;
        }

        return false;
    }

    /**
     * Return the next most relevant invoice data.
     *
     * @return array|null
     */
    public function getParentInvoiceDataAttribute() {
        if (isset($this->type->invoice_data)) {
            return $this->type->invoice_data;
        } elseif ($this->type->parentInvoiceData) {
            return $this->type->parentInvoiceData;
        }

        return null;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Format the currently configured progress states for selection.
     *
     * @return array
     */
    public static function progressStates() {
        $states = collect(config('aldebaran.commissions.progress_states'))->mapWithKeys(function ($state) {
            return [$state => $state];
        });

        return $states->toArray();
    }

    /**
     * Formats the currently enabled payment processors for selection.
     *
     * @return Illuminate\Support\Collection
     */
    public static function paymentProcessors() {
        $paymentProcessors = collect(config('aldebaran.commissions.payment_processors'))
            ->where('enabled', 1)->map(function ($processor) {
                return $processor['label'];
            });

        return $paymentProcessors;
    }
}
