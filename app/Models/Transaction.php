<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['booking_id', 'amount', 'status', 'paid_at'];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Akses customer melalui booking
    public function getCustomerAttribute()
    {
        return $this->booking?->customer;
    }

    // Akses service melalui booking
    public function getServiceAttribute()
    {
        return $this->booking?->service;
    }
}