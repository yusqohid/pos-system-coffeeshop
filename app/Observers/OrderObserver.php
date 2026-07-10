<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;

class OrderObserver
{
    public function __construct(private OrderService $orderService) {}

    public function creating(Order $order): void
    {
        if (blank($order->order_number)) {
            $order->order_number = $this->orderService->generateOrderNumber();
        }

        if (blank($order->status)) {
            $order->status = OrderStatus::Pending;
        }
    }
}
