<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'contact_name',
        'contact_phone',
        'zip',
        'province',
        'city',
        'district',
        'address',
        'last_used_at',
    ];

    protected $dates = [
        'last_used_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        return $this->province . $this->city . $this->district . $this->address;
    }
}
