<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ViewRecord;
use App\Services\PricePredictionService;
use Filament\Notifications\Notification;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('Prediksi Harga')
                ->icon('heroicon-o-chart-bar')
                ->label('Prediksi Harga (SMA)')
                ->color('success')
                ->action(function ($record) {
                    $predicted = PricePredictionService::predictPrice($record->id);

                    if (!$predicted) {
                        Notification::make()
                            ->title('Data Penjualan Tidak Cukup')
                            ->body('Belum ada data harga yang cukup untuk memprediksi harga.')
                            ->warning()
                            ->send();
                        return;
                    }

                    Notification::make()
                        ->title('💰 Prediksi Harga Barang')
                        ->body("Harga beli berikutnya diperkirakan sebesar Rp " . number_format($predicted, 0, ',', '.'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
