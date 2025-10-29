<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    protected static ?string $title = 'Daftar User';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->check() && in_array(auth()->user()->role, ['owner', 'admin'])),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->check() && in_array(auth()->user()->role, ['owner', 'admin'])),

            Actions\DeleteAction::make()
                ->visible(fn () => auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']))
                ->after(function () {
                    Notification::make()
                        ->title('User berhasil dihapus')
                        ->body('User telah dihapus dari sistem.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
