<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorageLocationResource\Pages;
use App\Models\StorageLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StorageLocationResource extends Resource
{
    protected static ?string $model = StorageLocation::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $label = 'Lokasi Penyimpanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lokasi')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('code')
                    ->label('Kode Lokasi')
                    ->required()
                    ->maxLength(50),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Lokasi')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Kode Lokasi')->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageLocations::route('/'),
            'create' => Pages\CreateStorageLocation::route('/create'),
            'edit' => Pages\EditStorageLocation::route('/{record}/edit'),
        ];
    }

    //role based access

    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }

    public static function canEdit($record): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }

    public static function canDelete($record): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin', 'kasir']);
    }
}
