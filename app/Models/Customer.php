<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'total_visits', 'last_visit'
    ];

    protected $casts = [
        'last_visit' => 'datetime',
        'total_visits' => 'integer',
    ];

    // Relasi dengan booking (opsional)
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Format tanggal kunjungan terakhir
    public function getLastVisitFormattedAttribute()
    {
        return $this->last_visit ? $this->last_visit->format('d M Y') : '-';
    }
}