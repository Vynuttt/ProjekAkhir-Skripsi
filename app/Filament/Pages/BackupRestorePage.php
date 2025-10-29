<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use Filament\Notifications\Notification;

class BackupRestorePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Backup & Restore';
    protected static ?string $title = 'Backup & Restore Database';
    protected static ?string $navigationGroup = 'Manajemen Sistem';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.backup-restore';

    public function createBackup(): void
    {
        $fileName = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $backupPath = storage_path('app/backups/' . $fileName);

        // Buat folder jika belum ada
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        // Jalankan perintah mysqldump
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_DATABASE'),
            $backupPath
        );

        system($command);

        // Kirim notifikasi global untuk semua admin/owner
        User::whereIn('role', ['owner', 'admin'])->get()->each(function ($recipient) use ($fileName) {
            Notification::make()
                ->title('💾 Backup Database Berhasil')
                ->body("File <b>{$fileName}</b> telah dibuat dan disimpan di folder <code>storage/app/backups</code>.")
                ->success()
                ->sendToDatabase($recipient);
        });

    }

    public function restoreBackup($fileName): void
    {
        $backupPath = storage_path('app/backups/' . $fileName);

        if (!file_exists($backupPath)) {
            Notification::make()
                ->title('File tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_DATABASE'),
            $backupPath
        );

        system($command);

        User::whereIn('role', ['owner', 'admin'])->get()->each(function ($recipient) use ($fileName) {
            Notification::make()
                ->title('🧩 Restore Database Berhasil')
                ->body("Database telah dipulihkan dari file <b>{$fileName}</b>.")
                ->success()
                ->sendToDatabase($recipient);
    });

    }

    public function getBackupFiles(): array
{
    $backupDir = storage_path('app/backups');

    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    // Ambil file dari storage lokal
    $files = Storage::disk('local')->files('backups');

    // Jika masih kosong, coba fallback manual
    if (empty($files)) {
        $files = glob($backupDir . '/*.sql');
        $files = array_map('basename', $files);
    } else {
        $files = array_map(fn($file) => basename($file), $files);
    }

    // Urutkan dari terbaru ke lama
    rsort($files);

    return $files;
}

    public function downloadBackup($file)
    {
    $filePath = 'backups/' . $file;

    if (!Storage::exists($filePath)) {
        Notification::make()
            ->title('❌ File tidak ditemukan')
            ->danger()
            ->send();

        return back();
    }

    return Storage::download($filePath);
}


    public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['owner', 'admin']);
}

}
