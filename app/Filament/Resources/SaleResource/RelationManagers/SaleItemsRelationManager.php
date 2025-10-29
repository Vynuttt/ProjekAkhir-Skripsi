<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

class SaleItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Detail Barang Keluar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(fn ($state, callable $set, $get) =>
                        $set('subtotal', ($get('quantity') ?? 0) * ($get('price') ?? 0))
                    )
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set, $get) =>
                        $set('subtotal', ($get('quantity') ?? 0) * ($get('price') ?? 0))
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Produk')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label('Jumlah'),
                Tables\Columns\TextColumn::make('price')->label('Harga')->money('idr'),
                Tables\Columns\TextColumn::make('subtotal')->label('Subtotal')->money('idr'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
