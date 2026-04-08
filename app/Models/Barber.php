<?php
// app/Models/Barber.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Barber extends Model
{
    protected $fillable = [
        'name', 'image', 'specialties', 'rating', 'jobs_count', 'experience_years', 'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'jobs_count' => 'integer',
        'experience_years' => 'integer',
        'is_active' => 'boolean',
    ];

    // Accessor untuk URL gambar
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/barbers/' . $this->image) : null;
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Non-aktif';
    }

    public function getRatingFormattedAttribute()
    {
        return number_format($this->rating, 1);
    }

    public function getSpecialtiesArrayAttribute()
    {
        return explode(',', $this->specialties ?? '');
    }
}