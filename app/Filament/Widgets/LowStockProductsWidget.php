<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?string $heading = '📉 Produk dengan Stok Menipis';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        // Ambil semua produk, lalu filter secara manual berdasarkan stok <= ROP (akses dinamis)
        $lowStockProducts = Product::all()->filter(function ($product) {
            return $product->stock <= $product->reorder_point; // ini akan menghormati accessor di model
        });

        return $table
            ->query(Product::query()->whereIn('id', $lowStockProducts->pluck('id')))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color(fn ($record) => $record->stock == 0 ? 'danger' : 'warning')
                    ->icon(fn ($record) => $record->stock == 0 
                        ? 'heroicon-o-exclamation-triangle' 
                        : 'heroicon-o-arrow-trending-down'),

                Tables\Columns\TextColumn::make('reorder_point')
                    ->label('ROP (Unit)')
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i'),
            ])
            ->paginated(false)
            ->defaultSort('stock', 'asc')
            ->emptyStateHeading('✅ Semua stok masih aman')
            ->emptyStateDescription('Belum ada produk yang mencapai titik pemesanan ulang.')
            ->striped()
            ->poll('10s');
    }
}
