<?php

namespace App\Models;

use Database\Factories\OrderDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    /** @use HasFactory<OrderDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_id',
        'qty',
        'subtotal',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }
}
