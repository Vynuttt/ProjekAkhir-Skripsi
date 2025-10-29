<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $label = 'Pembelian';
    protected static ?string $pluralLabel = 'Pembelian';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Transaksi')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')
                        ->label('No. Transaksi')
                        ->disabled()                    // tidak bisa diedit manual
                        ->dehydrated(true)  // tetap dikirim ke model agar tersimpan
                        ->default(function () {
                            // generate preview nomor sebelum disimpan
                            $latest = Purchase::whereDate('created_at', today())->max('invoice_number');
                            $lastNumber = $latest ? (int) substr($latest, -4) : 0;
                            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                            return 'PB-' . now()->format('Ymd') . '-' . $nextNumber;
                            })
                            ->unique(Purchase::class, 'invoice_number')
                            ->hint('No Transaksi generate otomatis'),

                    Forms\Components\DatePicker::make('purchase_date')
                        ->label('Tanggal Masuk')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier', 'name')
                        ->label('Supplier')
                        ->searchable()
                        ->required(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Detail Barang Masuk')
                ->schema([
                    Forms\Components\HasManyRepeater::make('items')
                        ->relationship('items')
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
                                ->reactive()
                                ->afterStateHydrated(fn($state, $set, $get) =>
                                    $set('subtotal', ($get('quantity') ?? 0) * ($get('price') ?? 0))
                                )
                                ->afterStateUpdated(fn($state, $set, $get) =>
                                    $set('subtotal', ($get('quantity') ?? 0) * ($get('price') ?? 0))
                                ),
                        ])
                        ->columns(4),
                ]),

            Forms\Components\Section::make('Total')
                ->schema([
                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total')
                        ->numeric()
                        ->disabled()
                        ->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('invoice_number')->label('No. Transaksi')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('purchase_date')->label('Tanggal')->date('d M Y'),
            Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'),
            Tables\Columns\TextColumn::make('total_amount')->label('Total')->money('idr', true)->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function afterSave($record, $form): void
    {
        $record->total_amount = $record->items()->sum('subtotal');
        $record->save();
    }

    // Role-based akses

    public static function canCreate(): bool
{
        return in_array(auth()->user()?->role, ['owner','admin','kasir']);
}
    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }
}
