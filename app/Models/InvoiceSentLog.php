<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'type',
        'destination',
        'status',
        'response',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}