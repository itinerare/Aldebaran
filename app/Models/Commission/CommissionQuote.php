<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionQuote extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote_key', 'commissioner_id', 'commission_type_id', 'commission_id',
        'status', 'subject', 'description', 'comments', 'amount',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_quotes';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'commissioner', 'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
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
        'name'        => 'string|nullable|min:3|max:191',
        'email'       => 'email|required|min:3|max:191',
        'contact'     => 'required|string|min:3|max:191',
        'description' => 'required',
        'terms'       => 'accepted',
    ];

    /**
     * Validation rules for manual commission creation.
     *
     * @var array
     */
    public static $manualCreateRules = [
        // Contact information
        'name'        => 'string|nullable|min:3|max:191',
        'email'       => 'email|required_without:commissioner_id|min:3|max:191|nullable',
        'contact'     => 'required_without:commissioner_id|string|min:3|max:191|nullable',
        'description' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the type associated with this quote.
     */
    public function type() {
        return $this->belongsTo(CommissionType::class, 'commission_type_id');
    }

    /**
     * Get the commissioner associated with this quote.
     */
    public function commissioner() {
        return $this->belongsTo(Commissioner::class, 'commissioner_id');
    }

    /**
     * Get the commission associated with this quote.
     */
    public function commission() {
        return $this->belongsTo(Commission::class, 'commission_id');
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
     * Get the quote info page's url.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('commissions/quotes/view/'.$this->quote_key);
    }

    /**
     * Get the quote's edit url.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('/admin/commissions/quotes/edit/'.$this->id);
    }
}
