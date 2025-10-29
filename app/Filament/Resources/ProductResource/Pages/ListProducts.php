<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use App\Models\Product;
use Filament\Notifications\Notification;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function mount(): void
    {
        parent::mount();

        // 🔍 Ambil semua produk dengan stok <= reorder point (ROP)
        $lowStockProducts = Product::whereColumn('stock', '<=', 'reorder_point')->get();

        // Jika ada produk dengan stok menipis, kirim notifikasi satu per satu
        if ($lowStockProducts->count() > 0) {
            foreach ($lowStockProducts as $product) {
                Notification::make()
                    ->title('⚠️ Stok Menipis!')
                    ->body("Produk {$product->name} hanya tersisa {$product->stock}")
                    ->danger()
                    ->persistent() // Notifikasi bertahan sampai ditutup
                    ->send();
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
