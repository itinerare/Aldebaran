<?php

namespace App\Models\Commission;

use App\Facades\Settings;
use App\Models\Gallery\Piece;
use App\Models\Gallery\PieceTag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'name', 'availability', 'description', 'data', 'key',
        'is_active', 'is_visible', 'sort', 'show_examples',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_types';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for type creation and editing.
     *
     * @var array
     */
    public static $rules = [
        //
        'name'            => 'required',
        'category_id'     => 'required',
        'price_type'      => 'required',
        'flat_cost'       => 'required_if:price_type,flat',
        'cost_min'        => 'exclude_unless:price_type,range|required|lt:cost_max',
        'cost_max'        => 'exclude_unless:price_type,range|required|gt:cost_min',
        'minimum_cost'    => 'required_if:price_type,min',
        'rate'            => 'required_if:price_type,rate',
        'extras'          => 'max:255',
        'field_key.*'     => 'nullable|between:3,25|alpha_dash',
        'field_type.*'    => 'nullable|required_with:field_key.*',
        'field_label.*'   => 'nullable|string|required_with:field_key.*',
        'field_choices.*' => 'nullable|string|required_if:field_type.*,choice,multiple',
        'field_rules.*'   => 'nullable|string|max:255',
        'field_value.*'   => 'nullable|string|max:255',
        'field_help.*'    => 'nullable|string|max:255',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category associated with this commission type.
     */
    public function category()
    {
        return $this->belongsTo(CommissionCategory::class, 'category_id');
    }

    /**
     * Get the category associated with this commission type.
     */
    public function commissions()
    {
        return $this->hasMany(Commission::class, 'commission_type');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active commission types.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include visible commission types.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the commission type's url.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('/commissions/types/'.$this->key);
    }

    /**
     * Fetch display name-- linked if the type is visible, unlinked if not.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if (!$this->is_visible) {
            return $this->category->name.': '.$this->name;
        } else {
            return '<a href="'.url('commissions/'.$this->category->class->slug.'#'.$this->category->name).'">'.$this->category->name.': '.$this->name.'</a>';
        }
    }

    /**
     * Get any extras information.
     *
     * @return string
     */
    public function getExtrasAttribute()
    {
        if (!$this->id || !isset($this->data['extras'])) {
            return null;
        }

        return $this->data['extras'];
    }

    /**
     * Get formatted pricing information.
     *
     * @return string
     */
    public function getPricingAttribute()
    {
        if (!$this->id) {
            return null;
        }

        // Fallback for testing
        if (!is_array($this->data)) {
            $this->data = json_decode($this->data, true);
        }
        $pricingData = $this->data['pricing'];

        switch ($pricingData['type']) {
            case 'flat':
                return '$'.$pricingData['cost'];
                break;
            case 'range':
                return '$'.$pricingData['range']['min'].'-'.$pricingData['range']['max'];
                break;
            case 'min':
                return '$'.$pricingData['cost'].'+';
                break;
            case 'rate':
                return '$'.$pricingData['cost'].'/hour';
                break;
        }
    }

    /**
     * Check if this type can currently be commissioned.
     *
     * @return bool
     */
    public function getCanCommissionAttribute()
    {
        if (!Settings::get($this->category->class->slug.'_comms_open') || !$this->is_active || !$this->category->is_active) {
            return 0;
        } elseif (is_int($this->getSlots($this->category->class)) && $this->getSlots($this->category->class) == 0) {
            return 0;
        } elseif ($this->availability > 0 || is_int($this->slots)) {
            if (is_numeric($this->currentSlots) && $this->currentSlots > 0) {
                return 1;
            } else {
                return 0;
            }
        } elseif (is_numeric($this->currentSlots) && $this->currentSlots == 0) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Check overall slots for this type.
     *
     * @return int
     */
    public function getSlotsAttribute()
    {
        if ($this->availability == 0 && !is_int($this->getSlots($this->category->class))) {
            return null;
        }
        if (null !== $this->getSlots($this->category->class)) {
            if ($this->availability > 0) {
                return min(Settings::get('overall_'.$this->category->class->slug.'_slots'), $this->availability);
            } else {
                return $this->getSlots($this->category->class);
            }
        } else {
            return $this->availability;
        }
    }

    /**
     * Check current slots for this type.
     *
     * @return int
     */
    public function getCurrentSlotsAttribute()
    {
        if ($this->availability == 0 && !is_int($this->getSlots($this->category->class))) {
            return null;
        }
        if (is_numeric($this->slots) && $this->slots == 0) {
            return 0;
        }

        return $this->slots - $this->commissions->where('status', 'Accepted')->count();
    }

    /**
     * Format info about current slot status.
     *
     * @return string
     */
    public function getDisplaySlotsAttribute()
    {
        if (!is_numeric($this->slots)) {
            return null;
        }

        return $this->currentSlots.'/'.$this->slots;
    }

    /**
     * Assemble the commission type's custom form fields.
     *
     * @return array
     */
    public function getFormFieldsAttribute()
    {
        $fields = [];

        // Fallbacks for testing purposes
        if (!is_array($this->data)) {
            $this->data = json_decode($this->data, true);
        }
        if (!is_array($this->category->data)) {
            $categoryData = json_decode($this->category->data, true);
        } else {
            $categoryData = $this->category->data;
        }
        if (!is_array($this->category->class->data)) {
            $classData = json_decode($this->category->class->data, true);
        } else {
            $classData = $this->category->class->data;
        }

        if ((isset($this->data['include']) && ((isset($this->data['include']['class']) && $this->data['include']['class']) || (isset($this->data['include']['category']) && $this->data['include']['category']))) || isset($this->data['fields'])) {
            // Collect fields for the commission type
            if (isset($this->data['include']['class']) && $this->data['include']['class'] && isset($classData['fields'])) {
                $fields = $fields + $classData['fields'];
            }
            if (isset($this->data['include']['category']) && $this->data['include']['category'] && isset($categoryData['fields'])) {
                $fields = $fields + $categoryData['fields'];
            }
            if (isset($this->data['fields'])) {
                $fields = $fields + $this->data['fields'];
            }
        } elseif ((isset($categoryData['include']['class']) && $categoryData['include']['class']) || isset($categoryData['fields'])) {
            // Failing that, collect fields from the commission category
            if (isset($categoryData['include']['class']) && $categoryData['include']['class']) {
                $fields = $fields + $classData['fields'];
            }
            if (isset($categoryData['fields'])) {
                $fields = $fields + $categoryData['fields'];
            }
        } elseif (isset($classData['fields'])) {
            // Failing that, collect fields from the commission class
            $fields = $fields + $classData['fields'];
        }

        return $fields;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Collects example images for this commission type based on tags.
     *
     * @param \App\Models\User|null $user
     * @param bool                  $all
     * @param int                   $limit
     *
     * @return \Illuminate\Support\Collection
     */
    public function getExamples($user = null, $all = false, $limit = 4)
    {
        if (!is_array($this->data)) {
            // Fallback for testing purposes
            $this->data = json_decode($this->data, true);
        }
        if (!isset($this->data['tags'])) {
            return null;
        }
        if (!$all) {
            // Retrieve all pieces
            $examplePieces = Piece::visible($user ? $user : null)->whereIn('id', PieceTag::whereIn('tag_id', $this->data['tags'])->pluck('piece_id')->toArray())->where('good_example', 1);
            $pieces = Piece::visible($user ? $user : null)->whereIn('id', PieceTag::whereIn('tag_id', $this->data['tags'])->pluck('piece_id')->toArray())->where('good_example', 0)->orderBy('created_at', 'DESC');

            // Gather pieces according to the limit
            // If there are more example pieces than necessary, take a random assortment
            if ($examplePieces->count() > $limit) {
                $examplePieces = $examplePieces->get()->random(min($limit, $examplePieces->count()));
            }
            // Else if there are precisely enough example pieces, get them
            elseif ($examplePieces->count() == $limit) {
                $examplePieces = $examplePieces->get();
            }
            // Else if there are fewer example pieces than the limit, gather enough pieces to fill the gap
            elseif ($examplePieces->count() < $limit) {
                $pieces = $pieces->take(min($limit - $examplePieces->count(), $pieces->count()));
            }

            // If there are example pieces and more than or equal to the limit, use those
            if ($examplePieces->count() && $examplePieces->count() >= $limit) {
                $examples = $examplePieces;
            }
            // Else if there are fewer example pieces than the limit, and additional pieces, assemble examples from both example pieces and pieces
            elseif ($examplePieces->count() < $limit && $pieces->count()) {
                $examples = collect($examplePieces->get())->merge($pieces->get());
            }
            // Else if there are only example pieces and no pieces, get whatever example pieces there are
            elseif ($examplePieces->count() && !$pieces->count()) {
                $examples = $examplePieces->get();
            }
            // Else get whatever pieces there are
            else {
                $examples = $pieces->take($limit)->get();
            }
        } else {
            // Retrieve all pieces
            $pieces = Piece::visible($user ? $user : null)->whereIn('id', PieceTag::whereIn('tag_id', $this->data['tags'])->pluck('piece_id')->toArray())->orderBy('created_at', 'DESC')->orderBy('good_example', 'DESC');

            $examples = $pieces->get();
        }

        return $examples;
    }

    /**
     * Gets the current total commission slots.
     *
     * @param \App\Models\Commission\CommissionClass $class
     *
     * @return int
     */
    public function getSlots($class)
    {
        $cap = Settings::get($class->slug.'_overall_slots');
        if ($cap == 0) {
            return null;
        }

        // Count all current commissions of the specified type
        $commissionsCount = Commission::where('status', 'Accepted')->orWhere('status', 'In Progress')->class($class->id)->count();

        return max(0, $cap - $commissionsCount);
    }
}
