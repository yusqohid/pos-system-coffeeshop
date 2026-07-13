@php
    $order = $this->getRecord()->loadMissing('customer', 'orderDetails.product');
@endphp

<x-filament-panels::page>
    <div class="mx-auto w-full max-w-2xl space-y-6 rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-900 print:max-w-none print:rounded-none print:border-0 print:p-0">
        <div class="flex items-start justify-between border-b border-dashed border-gray-300 pb-4">
            <div>
                <h2 class="text-xl font-semibold">Struk Pesanan</h2>
                <p class="text-gray-600">{{ $order->order_number }}</p>
            </div>
            <div class="text-right text-gray-600">
                <p>{{ $order->date?->format('d M Y H:i') }}</p>
                <p>{{ $order->status?->value ?? '-' }}</p>
            </div>
        </div>

        <div class="space-y-1">
            <p><span class="font-medium">Customer:</span> {{ $order->customer?->name ?? '-' }}</p>
            <p><span class="font-medium">Telepon:</span> {{ $order->customer?->phone ?? '-' }}</p>
        </div>

        <div class="overflow-hidden rounded-md border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Produk</th>
                        <th class="px-3 py-2 text-right font-medium">Qty</th>
                        <th class="px-3 py-2 text-right font-medium">Harga</th>
                        <th class="px-3 py-2 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($order->orderDetails as $detail)
                        <tr>
                            <td class="px-3 py-2">{{ $detail->product?->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-right">{{ $detail->qty }}</td>
                            <td class="px-3 py-2 text-right">Rp {{ number_format((float) $detail->product?->price, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right">Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-3 py-3 text-right font-semibold">Total</td>
                        <td class="px-3 py-3 text-right font-semibold">Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <p class="text-center text-xs text-gray-500">Halaman ini sudah format print. Gunakan Ctrl/Cmd + P untuk mencetak.</p>
    </div>
</x-filament-panels::page>
