<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'booking_id',
        'transaction_code',
        'amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function getCustomerAttribute()
    {
        return $this->booking?->customer;
    }

    public function getServiceAttribute()
    {
        return $this->booking?->service;
    }
}