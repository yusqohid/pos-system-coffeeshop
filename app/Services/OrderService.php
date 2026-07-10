<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function validateOrderSubmission(array $data, ?Order $order = null): void
    {
        $details = $this->extractOrderDetails($data);

        if ($details === []) {
            throw ValidationException::withMessages([
                'orderDetails' => 'Order harus memiliki minimal satu item.',
            ]);
        }

        $normalized = $this->normalizeOrderDetails($details);
        $this->validateStock($normalized, $order);

        $calculatedTotal = $this->calculateTotal($normalized);
        $submittedTotal = (float) ($data['total_price'] ?? 0);

        if (abs($calculatedTotal - $submittedTotal) > 0.01) {
            throw ValidationException::withMessages([
                'total_price' => 'Harga total tidak sesuai dengan item order.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareOrderHeader(array $data, array $formState): array
    {
        $details = $this->normalizeOrderDetails($this->extractOrderDetails($formState));
        $data['total_price'] = $this->calculateTotal($details);

        return $data;
    }

    public function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Order::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('ORD-%s-%03d', $date, $count);
    }

    public function getAvailableStock(int $productId, ?OrderDetail $existingDetail = null): int
    {
        $product = Product::query()->find($productId);

        if (! $product) {
            return 0;
        }

        $available = $product->stock;

        if ($existingDetail?->product_id === $productId) {
            $available += $existingDetail->qty;
        }

        return $available;
    }

    public function decrementStock(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        Product::query()
            ->whereKey($productId)
            ->decrement('stock', $qty);
    }

    public function incrementStock(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        Product::query()
            ->whereKey($productId)
            ->increment('stock', $qty);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public function extractOrderDetails(array $data): array
    {
        $details = $data['orderDetails'] ?? [];

        return is_array($details) ? array_values($details) : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     * @return array<int, array<string, mixed>>
     */
    public function normalizeOrderDetails(array $details): array
    {
        $productPrices = Product::query()
            ->whereIn('id', collect($details)->pluck('product_id')->filter())
            ->pluck('price', 'id');

        return collect($details)
            ->filter(fn (array $item): bool => filled($item['product_id'] ?? null))
            ->map(function (array $item) use ($productPrices): array {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = (float) ($productPrices->get($item['product_id']) ?? 0);

                $item['qty'] = $qty;
                $item['subtotal'] = $qty * $price;

                return $item;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     */
    public function calculateTotal(array $details): float
    {
        return (float) collect($details)->sum(
            fn (array $item): float => (float) ($item['subtotal'] ?? 0),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     */
    public function validateStock(array $details, ?Order $order = null): void
    {
        $existingQuantities = $this->existingQuantitiesByProduct($order);
        $requiredQuantities = $this->requiredQuantitiesByProduct($details);
        $productNames = Product::query()
            ->whereIn('id', $requiredQuantities->keys())
            ->pluck('name', 'id');

        foreach ($requiredQuantities as $productId => $requiredQty) {
            $productId = (int) $productId;
            $product = Product::query()->find($productId);

            if (! $product) {
                throw ValidationException::withMessages([
                    'orderDetails' => "Produk #{$productId} tidak ditemukan.",
                ]);
            }

            $available = $product->stock;

            if ($order) {
                $available += (int) $existingQuantities->get($productId, 0);
            }

            if ($requiredQty > $available) {
                $productName = $productNames->get($productId, "Produk #{$productId}");

                throw ValidationException::withMessages([
                    'orderDetails' => "Stok {$productName} tidak mencukupi. Tersedia: {$available}, dibutuhkan: {$requiredQty}.",
                ]);
            }
        }
    }

    /**
     * @return Collection<int, int>
     */
    private function existingQuantitiesByProduct(?Order $order): Collection
    {
        if (! $order) {
            return collect();
        }

        return $order->orderDetails()
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $items): int => (int) $items->sum('qty'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     * @return Collection<int, int>
     */
    private function requiredQuantitiesByProduct(array $details): Collection
    {
        return collect($details)
            ->groupBy('product_id')
            ->map(fn (Collection $items): int => (int) $items->sum('qty'));
    }
}
