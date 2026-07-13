<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_insufficient_stock_on_create(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->expectException(ValidationException::class);

        app(OrderService::class)->validateOrderSubmission([
            'total_price' => 50_000,
            'orderDetails' => [
                ['product_id' => $product->id, 'qty' => 5, 'subtotal' => 50_000],
            ],
        ]);
    }

    public function test_it_allows_qty_within_available_stock_on_edit(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 10_000]);
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'total_price' => 20_000,
        ]);

        $detail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 2,
            'subtotal' => 20_000,
        ]);

        $this->assertSame(8, $product->fresh()->stock);

        app(OrderService::class)->validateOrderSubmission([
            'total_price' => 40_000,
            'orderDetails' => [
                ['id' => $detail->id, 'product_id' => $product->id, 'qty' => 4, 'subtotal' => 40_000],
            ],
        ], $order->fresh());

        $this->assertTrue(true);
    }

    public function test_order_detail_observer_adjusts_stock_on_create_and_delete(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order = Order::factory()->create();

        $detail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 3,
            'subtotal' => 30_000,
        ]);

        $this->assertSame(7, $product->fresh()->stock);

        $detail->delete();

        $this->assertSame(10, $product->fresh()->stock);
    }
}
