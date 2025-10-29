<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'User Management';
    protected static ?string $pluralLabel = 'User';
    protected static ?string $label = 'User';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Lengkap')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('role')
                ->label('Role')
                ->options(function () {
                    $user = auth()->user();

                    // 🔒 Admin tidak bisa membuat Owner
                    if ($user && $user->role === 'admin') {
                        return [
                            'admin' => 'Admin',
                            'kasir' => 'Kasir',
                        ];
                    }

                    // 👑 Owner bisa membuat semua role
                    return [
                        'owner' => 'Owner',
                        'admin' => 'Admin',
                        'kasir' => 'Kasir',
                    ];
                })
                ->required(),

            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->required(fn ($record) => $record === null)
                ->maxLength(255),

            Forms\Components\Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama'),
                Tables\Columns\TextColumn::make('email')->label('Email'),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'success' => 'owner',
                        'warning' => 'admin',
                        'info'    => 'kasir',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([])
            ->actions([
                // ✏️ Edit bisa dilakukan oleh Owner & Admin
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->check() && in_array(auth()->user()->role, ['owner', 'admin'])),

                // 🗑️ Hapus bisa dilakukan oleh Owner & Admin
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->check() && in_array(auth()->user()->role, ['owner', 'admin'])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->check() && in_array(auth()->user()->role, ['owner', 'admin'])),
            ]);
    }

    // Role-based access
    public static function canViewAny(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']);
    }

    public static function canCreate(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']);
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']);
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
