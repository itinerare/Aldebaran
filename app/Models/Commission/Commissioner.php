<?php

namespace App\Models\Commission;

use Illuminate\Database\Eloquent\Model;

class Commissioner extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'contact', 'paypal', 'is_banned',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commissioners';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for commissioner creation.
     * Validation is performed on commission creation instead.
     *
     * @var array
     */
    public static $createRules = [
        //
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the IP entries associated with this commissioner.
     */
    public function ips()
    {
        return $this->hasMany('App\Models\Commission\CommissionerIp', 'commissioner_id');
    }

    /**
     * Get the commissions associated with this commissioner.
     */
    public function commissions()
    {
        return $this->hasMany('App\Models\Commission\Commission', 'commissioner_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the commissioner's name--
     * name if set, or email minus domain if not.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        } else {
            list($address, $domain) = explode('@', $this->email);

            return $address;
        }
    }

    /**
     * Get the commissioner's full name--
     * name if set plus email addres, or email address alone if not.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'].' - '.$this->email;
        } else {
            return $this->email;
        }
    }

    /**
     * Get the commissioner's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if ($this->is_banned) {
            return '<s>'.$this->name.'</s>';
        } else {
            return $this->name;
        }
    }
}
