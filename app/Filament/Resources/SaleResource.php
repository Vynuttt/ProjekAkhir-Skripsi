<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $label = 'Penjualan';
    protected static ?string $pluralLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informasi Transaksi')
                ->schema([

                    Forms\Components\TextInput::make('invoice_number')
                        ->label('No. Transaksi')
                        ->disabled()
                        ->dehydrated(true)
                        ->default(function () {
                            $latest = Sale::whereDate('created_at', today())->max('invoice_number');
                            $lastNumber = $latest ? (int) substr($latest, -4) : 0;
                            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                            return 'PJ-' . now()->format('Ymd') . '-' . $nextNumber;
                        })
                        ->unique(Sale::class, 'invoice_number'),

                    Forms\Components\DatePicker::make('sale_date')
                        ->label('Tanggal Keluar')
                        ->default(now())
                        ->required(),

                    Forms\Components\Hidden::make('user_id')
                        ->default(auth()->id()),
                ]),

            Forms\Components\Section::make('Detail Barang Keluar')
                ->schema([
                    Forms\Components\HasManyRepeater::make('items')
                        ->relationship('items')
                        ->schema([

                            // PRODUK
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $product = Product::find($state);

                                    if ($product) {
                                        // Isi harga otomatis
                                        $set('price', $product->sale_price);

                                        // Stok habis → beri warning
                                        if ($product->stock <= 0) {
                                            Notification::make()
                                                ->title('Stok produk habis!')
                                                ->danger()
                                                ->send();
                                        }
                                    }
                                }),

                            // JUMLAH
                            Forms\Components\TextInput::make('quantity')
                                ->label('Jumlah')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->rule(function ($get) {
                                    return function ($attribute, $value, $fail) use ($get) {
                                        $product = Product::find($get('product_id'));

                                        if (!$product) return;

                                        if ($value <= 0) {
                                            $fail("Jumlah harus lebih dari 0.");
                                        }

                                        if ($value > $product->stock) {
                                            $fail("Jumlah melebihi stok! Stok tersedia: {$product->stock}");
                                        }
                                    };
                                })
                                ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                                    $set('subtotal', ($get('price') ?? 0) * ($state ?? 0))
                                ),

                            // HARGA
                            Forms\Components\TextInput::make('price')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            // SUBTOTAL
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->disabled()
                                ->reactive()
                                ->afterStateHydrated(fn ($state, $set, $get) =>
                                    $set('subtotal', ($get('quantity') ?? 0) * ($get('price') ?? 0))
                                ),

                        ])
                        ->columns(4)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {

                            $items = $get('items');
                            $total = 0;

                            foreach ($items as $item) {
                                $total += ($item['subtotal'] ?? 0);
                            }

                            $set('total_amount', $total);
                        }),
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

    // Jangan update stok di sini → stok diatur oleh SaleItem
    public static function afterSave($record, $form): void
    {
        // Fix total "yang lebih akurat" setelah save + setelah SaleItem diproses
        $record->total_amount = $record->items()->sum('subtotal');
        $record->saveQuietly();
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('invoice_number')->label('No. Transaksi')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('sale_date')->label('Tanggal')->date('d M Y'),
            Tables\Columns\TextColumn::make('user.name')->label('Petugas'),
            Tables\Columns\TextColumn::make('total_amount')->label('Total')->money('idr', true)->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

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
        return in_array(auth()->user()?->role, ['owner']);
    }
}