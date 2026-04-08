<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name', 'image', 'category', 'duration', 'price', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'duration' => 'integer',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_service');
    }
    
    // Accessor untuk URL gambar
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/services/' . $this->image) : null;
    }

    // Format durasi
    public function getDurationFormattedAttribute()
    {
        return $this->duration ? $this->duration . ' menit' : '-';
    }

    // Format harga
    public function getPriceFormattedAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Label status
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Not-aktif';
    }
}