<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewOrderReceipt extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.view-order-receipt';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToOrder')
                ->label('Lihat Order')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn (): string => OrderResource::getUrl('view', ['record' => $this->getRecord()])),
            EditAction::make(),
        ];
    }
}
