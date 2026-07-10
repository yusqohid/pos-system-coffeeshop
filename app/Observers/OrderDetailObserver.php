<?php

namespace App\Observers;

use App\Models\OrderDetail;
use App\Services\OrderService;

class OrderDetailObserver
{
    public function __construct(private OrderService $orderService) {}

    public function created(OrderDetail $orderDetail): void
    {
        $this->orderService->decrementStock($orderDetail->product_id, $orderDetail->qty);
    }

    public function updated(OrderDetail $orderDetail): void
    {
        if (! $orderDetail->wasChanged(['product_id', 'qty'])) {
            return;
        }

        $originalProductId = (int) $orderDetail->getOriginal('product_id');
        $originalQty = (int) $orderDetail->getOriginal('qty');

        if ($originalProductId !== $orderDetail->product_id) {
            $this->orderService->incrementStock($originalProductId, $originalQty);
            $this->orderService->decrementStock($orderDetail->product_id, $orderDetail->qty);

            return;
        }

        $delta = $orderDetail->qty - $originalQty;

        if ($delta > 0) {
            $this->orderService->decrementStock($orderDetail->product_id, $delta);
        } elseif ($delta < 0) {
            $this->orderService->incrementStock($orderDetail->product_id, abs($delta));
        }
    }

    public function deleted(OrderDetail $orderDetail): void
    {
        $this->orderService->incrementStock($orderDetail->product_id, $orderDetail->qty);
    }
}
