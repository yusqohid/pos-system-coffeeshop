<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderDetail extends Model
{
    protected $fillable = [
        'product_id',
        'order_id',
        'qty',
        'subtotal',
    ];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
}
