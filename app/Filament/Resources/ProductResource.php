<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\User;
use App\Services\PricePredictionService;
use App\Services\StockPredictionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification as FilamentNotification;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $label = 'Spare Part';
    protected static ?string $pluralLabel = 'Spare Part';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('ProductTabs')
                ->tabs([
                    // =========================
                    // TAB 1: INFO PRODUK
                    // =========================
                    Forms\Components\Tabs\Tab::make('Info Produk')
                        ->schema([
                            Forms\Components\Section::make('Informasi Produk')
                                ->schema([
                                    Forms\Components\TextInput::make('code')
                                        ->label('Kode / SKU')
                                        ->unique(ignoreRecord: true)
                                        ->required()
                                        ->maxLength(100),

                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama Produk')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\Textarea::make('description')
                                        ->label('Deskripsi')
                                        ->maxLength(500),

                                    Forms\Components\Select::make('brand_id')
                                        ->label('Merek')
                                        ->relationship('brand', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Forms\Components\Select::make('category_id')
                                        ->label('Kategori')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->required(),

                                    Forms\Components\Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->relationship('supplier', 'name')
                                        ->searchable(),

                                    Forms\Components\Select::make('storage_location_id')
                                        ->label('Lokasi Penyimpanan')
                                        ->relationship('storageLocation', 'name')
                                        ->searchable()
                                        ->required(),

                                    Forms\Components\TextInput::make('stock')
                                        ->label('Stok')
                                        ->numeric()
                                        ->default(0)
                                        ->disabled()
                                        ->dehydrated(false),

                                    Forms\Components\TextInput::make('purchase_price')
                                        ->label('Harga Beli')
                                        ->numeric()
                                        ->required(),

                                    Forms\Components\TextInput::make('sale_price')
                                        ->label('Harga Jual')
                                        ->numeric()
                                        ->required(),
                                ])
                                ->columns(2),
                        ]),

                    // =========================
                    // TAB 2: EOQ & ROP
                    // =========================
                    Forms\Components\Tabs\Tab::make('EOQ & ROP')
                        ->schema([
                            Forms\Components\Section::make('Parameter EOQ & Persediaan')
                                ->schema([
                                    Forms\Components\TextInput::make('annual_demand')
                                        ->label('Permintaan Tahunan (D)')
                                        ->numeric()
                                        ->nullable()
                                        ->default(null)
                                        ->helperText('Estimasi total kebutuhan barang dalam 1 tahun.'),

                                    Forms\Components\TextInput::make('ordering_cost')
                                        ->label('Biaya Pemesanan (S)')
                                        ->numeric()
                                        ->nullable()
                                        ->default(null)
                                        ->helperText('Biaya setiap kali melakukan pemesanan.'),

                                    Forms\Components\TextInput::make('holding_cost')
                                        ->label('Biaya Penyimpanan per Unit (H)')
                                        ->numeric()
                                        ->nullable()
                                        ->default(null)
                                        ->helperText('Biaya simpan per unit per tahun (jika kosong akan dihitung otomatis).'),

                                    Forms\Components\TextInput::make('safety_stock')
                                        ->label('Safety Stock')
                                        ->numeric()
                                        ->nullable()
                                        ->default(0),

                                    Forms\Components\TextInput::make('lead_time')
                                        ->label('Lead Time (Hari)')
                                        ->numeric()
                                        ->nullable()
                                        ->default(7)
                                        ->helperText('Waktu rata-rata pengiriman dari supplier (hari).'),

                                    Forms\Components\TextInput::make('reorder_point')
                                        ->label('Reorder Point (ROP)')
                                        ->numeric()
                                        ->nullable()
                                        ->default(null)
                                        ->helperText('Titik pemesanan ulang (jika kosong akan dihitung otomatis).'),
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('Hasil Perhitungan EOQ, Prediksi & Stok')
                                ->schema([
                                    Forms\Components\Placeholder::make('eoq_preview')
                                        ->label('Economic Order Quantity (EOQ)')
                                        ->content(fn (?Product $record) => $record ? $record->eoq . ' unit' : '-'),

                                    Forms\Components\Placeholder::make('rop_preview')
                                        ->label('Reorder Point (ROP)')
                                        ->content(fn (?Product $record) => $record ? $record->reorder_point . ' unit' : '-'),
                                    
                                    Forms\Components\Placeholder::make('cycle_time_preview')
                                        ->label('Cycle Time (Siklus Pemesanan)')
                                        ->content(fn (?Product $record) =>
                                            $record && $record->cycle_time
                                                ? $record->cycle_time . ' hari'
                                                : '-'
                                        ),

                                    Forms\Components\Placeholder::make('predicted_price')
                                        ->label('Prediksi Harga (SMA)')
                                        ->content(function (?Product $record) {
                                            if (!$record) return '-';
                                            $predicted = PricePredictionService::predictPrice($record->id);
                                            return $predicted
                                                ? 'Rp ' . number_format($predicted, 0, ',', '.')
                                                : 'Belum ada data penjualan';
                                        }),

                                    Forms\Components\Placeholder::make('predicted_stock')
                                        ->label('Prediksi Stok (7 Hari ke Depan)')
                                        ->content(function (?Product $record) {
                                            if (!$record) return '-';
                                            $predicted = StockPredictionService::predictStock($record->id, 7);
                                            return $predicted !== null
                                                ? $predicted . ' unit'
                                                : 'Belum ada cukup data untuk prediksi';
                                        }),

                                    Forms\Components\Placeholder::make('skip_info')
                                        ->label('💡 Catatan')
                                        ->content('Anda dapat menyimpan produk tanpa mengisi data EOQ. Pengaturan EOQ dapat diisi nanti di halaman edit produk.'),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode / SKU')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Merek')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('storageLocation.name')
                    ->label('Lokasi Penyimpanan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Harga Beli')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Harga Jual')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('eoq')
                    ->label('EOQ (Unit)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reorder_point')
                    ->label('Reorder Point (Unit)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('prediksi_harga')
                    ->label('Prediksi Harga')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $predicted = PricePredictionService::predictPrice($record->id);

                        if (!$predicted) {
                            FilamentNotification::make()
                                ->title('Data Tidak Cukup')
                                ->body('Belum ada cukup data transaksi untuk menghitung prediksi harga.')
                                ->warning()
                                ->send();
                            return;
                        }

                        FilamentNotification::make()
                            ->title('💰 Prediksi Harga Barang')
                            ->body("Harga jual berikutnya diperkirakan sebesar Rp " . number_format($predicted, 0, ',', '.'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('prediksi_barang')
                    ->label('Prediksi Barang')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $predicted = \App\Services\DemandPredictionService::predictDemand($record->id);

                        if (!$predicted) {
                            FilamentNotification::make()
                                ->title('Data Tidak Cukup')
                                ->body('Belum ada cukup data penjualan untuk memprediksi permintaan barang.')
                                ->warning()
                                ->send();
                            return;
                        }

                        FilamentNotification::make()
                            ->title('📦 Prediksi Permintaan Barang')
                            ->body("Perkiraan permintaan periode berikutnya: {$predicted} unit.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('prediksi_stok')
                    ->label('Prediksi Stok')
                    ->icon('heroicon-o-cube')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $predicted = StockPredictionService::predictStock($record->id, 7);

                        if ($predicted === null) {
                            FilamentNotification::make()
                                ->title('Data Tidak Cukup')
                                ->body('Belum ada cukup data penjualan untuk menghitung prediksi stok.')
                                ->warning()
                                ->send();
                            return;
                        }

                        FilamentNotification::make()
                            ->title('📦 Prediksi Stok Barang')
                            ->body("Dalam 7 hari ke depan, stok diperkirakan tersisa {$predicted} unit.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->color('danger')
                    ->modalHeading('Konfirmasi Penghapusan')
                    ->modalSubheading('Apakah Anda yakin ingin menghapus produk ini?')
                    ->modalButton('Ya, Hapus'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    // Role-based akses
    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin', 'kasir']);
    }

    public static function canEdit($record): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }

    public static function canDelete($record): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'admin']);
    }

    protected static function afterSave($record): void
    {
        if ($record->stock <= $record->reorder_point) {
            $users = User::whereIn('role', ['owner', 'admin'])->get();

            FilamentNotification::make()
                ->title('⚠️ Stok Menipis')
                ->body("Produk {$record->name} hanya tersisa {$record->stock} unit.")
                ->danger()
                ->send();
        }
    }
}