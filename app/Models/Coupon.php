<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    //

    protected $fillable = [
        'type',
        'code',
        'value',
        'min_order',
        'usage_limit',
        'usage_count',
        'expiry_date',
        'is_active',
    ];

    protected $casts= [
        'type' => 'string',
        'code' => 'string',
        'value' => 'float',
        'min_order' => 'float',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'expiry_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders(){

    return $this->hasMany(Order::class);
    }
}
