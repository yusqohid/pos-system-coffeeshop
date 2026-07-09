<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'total_price',
        'date',
    ];

    public function customer():BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function product():HasMany
    {
        return $this->hasMany(Product::class);
    }
}
