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
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah Retur')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->required(),

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
