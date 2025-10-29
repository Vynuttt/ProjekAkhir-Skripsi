<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Filament\Notifications\Notification;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SendLowStockNotificationAfterLogin
{
    public function handle(Login $event): void
    {
        // Ambil user login saat ini secara aman
        $user = Auth::user();

        // Validasi: pastikan model User dan role sesuai
        if (!$user instanceof User || !in_array($user->role, ['owner', 'admin'])) {
            return;
        }

        // Ambil produk dengan stok kurang dari atau sama dengan reorder_point
        $lowStockProducts = Product::whereColumn('stock', '<=', 'reorder_point')->get();

        if ($lowStockProducts->isEmpty()) {
            return;
        }

        // Delay sedikit supaya muncul setelah halaman dashboard tampil
        sleep(1);

        // Kirim notifikasi popup untuk setiap produk
        foreach ($lowStockProducts as $product) {
            Notification::make()
                ->title('⚠️ Stok Menipis')
                ->body("Produk {$product->name} hanya tersisa {$product->stock} unit.")
                ->danger()
                ->send();
        }
    }
}
