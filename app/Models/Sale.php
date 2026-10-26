<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'sale_number',
        'type',
        'rental_start_date',
        'rental_end_date',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'sold_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
