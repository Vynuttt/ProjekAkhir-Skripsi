<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReturnResource\Pages;
use App\Models\PurchaseReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseReturnResource extends Resource
{
    protected static ?string $model = PurchaseReturn::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Retur Pembelian';
    protected static ?string $pluralLabel = 'Retur Pembelian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('return_date')
                    ->label('Tanggal Retur')
                    ->required(),

                Forms\Components\Select::make('purchase_id')
                    ->label('No. Transaksi Pembelian')
                    ->relationship('purchase', 'invoice_number')
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->options(function (callable $get) {
                        $purchaseId = $get('purchase_id'); // ambil transaksi pembelian yang dipilih
                        if (!$purchaseId) return [];
                
                        return \App\Models\PurchaseItem::where('purchase_id', $purchaseId)
                            ->with('product')
                            ->get()
                            ->pluck('product.name', 'product_id');
                    })
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Ambil harga pembelian terakhir
                        $latestPrice = \App\Models\PurchaseItem::where('product_id', $state)
                            ->orderByDesc('created_at')
                            ->value('price');
                
                        if ($latestPrice) {
                            $set('unit_price', $latestPrice);
                        } else {
                            $set('unit_price', null);
                        }
                    }),


                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah Retur')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->reactive()
                    ->rule(function ($get, $record) {
                        return function (string $attribute, $value, $fail) use ($get, $record) {
                
                            if (!$get('product_id') || !$get('purchase_id')) return;
                
                            $productId = $get('product_id');
                            $purchaseId = $get('purchase_id');
                
                            // Total qty pembelian produk ini
                            $purchasedQty = \App\Models\PurchaseItem::where('purchase_id', $purchaseId)
                                ->where('product_id', $productId)
                                ->sum('quantity');
                
                            // Total qty retur yang sudah dilakukan sebelumnya (kecuali retur ini kalau sedang edit)
                            $returnedQty = \App\Models\PurchaseReturn::where('purchase_id', $purchaseId)
                                ->where('product_id', $productId)
                                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                ->sum('quantity');
                
                            // Total gabungan retur lama + retur baru
                            $totalAfter = $returnedQty + $value;
                
                            if ($totalAfter > $purchasedQty) {
                                $fail("Total retur melebihi jumlah pembelian. Sudah diretur $returnedQty unit dari total $purchasedQty unit.");
                            }
                        };
                    }),

                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->disabled() 
                    ->dehydrated(true),

                Forms\Components\Textarea::make('reason')
                    ->label('Alasan Retur')
                    ->rows(2)
                    ->maxLength(255),

                Forms\Components\Placeholder::make('total')
                    ->label('Total Retur (Rp)')
                    ->content(fn ($record) => $record ? number_format($record->total, 0, ',', '.') : '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('return_date')->label('Tanggal')->date('d M Y'),
                Tables\Columns\TextColumn::make('purchase.invoice_number')->label('No. Pembelian'),
                Tables\Columns\TextColumn::make('product.name')->label('Produk'),
                Tables\Columns\TextColumn::make('quantity')->label('Qty'),
                Tables\Columns\TextColumn::make('unit_price')->label('Harga Satuan')->money('idr', true),
                Tables\Columns\TextColumn::make('total')->label('Total Retur')->money('idr', true),
                Tables\Columns\TextColumn::make('reason')->label('Alasan'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseReturns::route('/'),
            'create' => Pages\CreatePurchaseReturn::route('/create'),
            'edit' => Pages\EditPurchaseReturn::route('/{record}/edit'),
        ];
    }

        // Role-based akses
    public static function canCreate(): bool
    {
    return in_array(auth()->user()?->role, ['owner', 'admin']);
    }


    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }

}