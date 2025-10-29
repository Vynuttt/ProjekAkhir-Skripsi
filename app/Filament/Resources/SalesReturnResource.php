<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReturnResource\Pages;
use App\Models\SalesReturn;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class SalesReturnResource extends \Filament\Resources\Resource
{
    protected static ?string $model = SalesReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Retur Penjualan';
    protected static ?string $pluralModelLabel = 'Retur Penjualan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sale_id')
                ->label('No. Transaksi Penjualan')
                ->relationship('sale', 'invoice_number')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('product_id')
                ->label('Produk')
                ->relationship('product', 'name')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('quantity')
                ->label('Jumlah Retur')
                ->numeric()
                ->minValue(1)
                ->required(),

            Forms\Components\Textarea::make('reason')
                ->label('Alasan Retur')
                ->maxLength(500)
                ->nullable(),

            Forms\Components\DatePicker::make('return_date')
                ->label('Tanggal Retur')
                ->default(now())
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.invoice_number')
                    ->label('No Penjualan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah Retur'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30),

                Tables\Columns\TextColumn::make('return_date')
                    ->label('Tanggal Retur')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReturns::route('/'),
            'create' => Pages\CreateSalesReturn::route('/create'),
            'edit' => Pages\EditSalesReturn::route('/{record}/edit'),
        ];
    }

    //role-based access
    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin', 'kasir']);
    }

    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'kasir']);
    }

    public static function canEdit($record): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'kasir']);
    }

    public static function canDelete($record): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'kasir']);
    }
}
