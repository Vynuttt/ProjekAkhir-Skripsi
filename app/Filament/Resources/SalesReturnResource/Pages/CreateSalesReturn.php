<?php

namespace App\Filament\Resources\SalesReturnResource\Pages;

use App\Filament\Resources\SalesReturnResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSalesReturn extends CreateRecord
{
    protected static string $resource = SalesReturnResource::class;

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Retur Penjualan Berhasil')
            ->body('Stok produk telah diperbarui secara otomatis.')
            ->success()
            ->send();
    }
}
