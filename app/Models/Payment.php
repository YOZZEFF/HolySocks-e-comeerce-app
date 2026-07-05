<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
     protected $fillable = [
        'order_id',
        'method',
        'transaction_id',
        'status',
        'amount',
        'paid_at',
    ];

      protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function order(){

    return $this->belongsTo(Order::class);
    }
}
