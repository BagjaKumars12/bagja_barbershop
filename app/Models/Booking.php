<?php
// app/Models/Booking.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_code', 'customer_id', 'barber_id', 'booking_time',
        'status', 'payment_method', 'notes', 'total_price'
    ];

    protected $casts = [
        'booking_time' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    // Relasi
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Relasi many-to-many dengan service
    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_service')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
    
    // Aksesori untuk format tampilan
    public function getBookingTimeFormattedAttribute()
    {
        return $this->booking_time->format('d M Y H:i') . ' WIB';
    }

    // Status label dengan warna
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending'   => ['label' => 'Tertunda', 'class' => 'bg-yellow-100 text-yellow-800'],
            'confirmed' => ['label' => 'Dikonfirmasi', 'class' => 'bg-blue-100 text-blue-800'],
            'completed' => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-800'],
            'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-800'],
        ];
        return $labels[$this->status] ?? ['label' => $this->status, 'class' => 'bg-gray-100 text-gray-800'];
    }

    // Aksesori total price dari service yang dipilih
    public function getTotalPriceAttribute()
    {
        return $this->services->sum('price');
    }
}