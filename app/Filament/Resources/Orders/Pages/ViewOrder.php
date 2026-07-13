<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printReceipt')
                ->label('Cetak Struk')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(
                    fn (): string => OrderResource::getUrl('view-receipt', ['record' => $this->getRecord()]),
                    shouldOpenInNewTab: true,
                ),
            EditAction::make(),
        ];
    }
}
