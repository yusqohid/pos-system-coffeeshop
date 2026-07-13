<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class TodaySalesStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $todayOrders = Order::query()
            ->whereDate('date', today());

        $paidTodayOrders = Order::query()
            ->whereDate('date', today())
            ->where('status', OrderStatus::Paid);

        return [
            Stat::make('Pendapatan Hari Ini', Number::currency((float) $paidTodayOrders->sum('total_price'), 'IDR', locale: 'id'))
                ->description('Hanya order lunas')
                ->color('success'),
            Stat::make('Order Hari Ini', (string) $todayOrders->count())
                ->description('Semua status')
                ->color('primary'),
            Stat::make('Order Lunas Hari Ini', (string) $paidTodayOrders->count())
                ->description('Status lunas')
                ->color('warning'),
        ];
    }
}
