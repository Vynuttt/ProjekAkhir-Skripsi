<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && in_array($user->role, ['owner', 'admin']),
            403,
            'Hanya Owner dan Admin yang dapat menambahkan user.'
        );
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('User berhasil dibuat')
            ->body('User baru berhasil ditambahkan ke sistem.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
