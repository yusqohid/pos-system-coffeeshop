<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Services\OrderService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Nomor Order')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default(OrderStatus::Pending)
                    ->required(),
                DateTimePicker::make('date')
                    ->default(now())
                    ->readOnly()
                    ->required(),
                Section::make()->description('Customer Information')
                    ->schema([
                        Select::make('customer_id')
                            ->required()
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->live()
                            ->preload(),
                        TextEntry::make('phone')
                            ->label('Phone')
                            ->state(fn (Get $get) => Customer::find($get('customer_id'))?->phone),
                        TextEntry::make('address')
                            ->label('Address')
                            ->state(fn (Get $get) => Customer::find($get('customer_id'))?->address),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make()->description('Order Detail')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('orderDetails')
                            ->relationship()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateTotalPrice($set, $get))
                            ->table([
                                TableColumn::make('Nama Produk')
                                    ->width('300px'),
                                TableColumn::make('Stok')
                                    ->width('80px'),
                                TableColumn::make('Jumlah')
                                    ->width('120px'),
                                TableColumn::make('Subtotal'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->relationship(
                                        'product',
                                        'name',
                                        fn ($query) => $query->where('is_active', true),
                                    )
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->preload()
                                    ->afterStateUpdated(
                                        fn (Set $set, Get $get, mixed $state) => self::recalculateLineAndTotal(
                                            $set,
                                            $get,
                                            productId: filled($state) ? (int) $state : null,
                                        ),
                                    ),
                                TextEntry::make('available_stock')
                                    ->label('Stok')
                                    ->state(fn (Get $get): ?string => self::formatAvailableStock($get)),
                                TextInput::make('qty')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(fn (Get $get): ?int => self::resolveMaxQty($get))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(
                                        fn (Set $set, Get $get, mixed $state) => self::recalculateLineAndTotal(
                                            $set,
                                            $get,
                                            qty: max(1, (int) ($state ?: 1)),
                                        ),
                                    ),
                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->columnSpanFull(),
                    ]),
                TextInput::make('total_price')
                    ->label('Harga Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly()
                    ->dehydrated()
                    ->required(),
            ]);
    }

    private static function recalculateLineAndTotal(
        Set $set,
        Get $get,
        ?int $qty = null,
        ?int $productId = null,
    ): void {
        $qty = max(1, $qty ?? (int) ($get('qty') ?: 1));
        $productId ??= filled($get('product_id')) ? (int) $get('product_id') : null;
        $price = Product::query()->find($productId)?->price ?? 0;

        $set('subtotal', $qty * $price);
        $set('../../total_price', self::calculateOrderTotal($get('../') ?? []));
    }

    private static function updateTotalPrice(Set $set, Get $get): void
    {
        $set('total_price', self::calculateOrderTotal($get('orderDetails') ?? []));
    }

    /**
     * @param  array<string, array<string, mixed>>  $orderDetails
     */
    private static function calculateOrderTotal(array $orderDetails): float
    {
        $productPrices = Product::query()
            ->whereIn('id', collect($orderDetails)->pluck('product_id')->filter())
            ->pluck('price', 'id');

        return (float) collect($orderDetails)->sum(function (array $item) use ($productPrices): float {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = $productPrices->get($item['product_id'] ?? null) ?? 0;

            return $qty * $price;
        });
    }

    private static function resolveMaxQty(Get $get): ?int
    {
        $productId = $get('product_id');

        if (! filled($productId)) {
            return null;
        }

        $existingDetail = filled($get('id'))
            ? OrderDetail::query()->find($get('id'))
            : null;

        return app(OrderService::class)->getAvailableStock((int) $productId, $existingDetail);
    }

    private static function formatAvailableStock(Get $get): ?string
    {
        $available = self::resolveMaxQty($get);

        return $available === null ? null : (string) $available;
    }
}
