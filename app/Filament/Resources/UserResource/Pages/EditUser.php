<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && in_array($user->role, ['owner', 'admin']),
            403,
            'Hanya Owner dan Admin yang dapat mengedit user.'
        );
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('User berhasil diperbarui')
            ->body("Data user telah diperbarui.")
            ->success()
            ->send();
    }
}
