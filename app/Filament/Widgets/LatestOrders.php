<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Order Terbaru')
            ->query(
                fn (): Builder => Order::query()
                    ->with('customer')
                    ->latest('date')
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Customer'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR'),
                TextColumn::make('date')
                    ->label('Waktu')
                    ->dateTime(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
