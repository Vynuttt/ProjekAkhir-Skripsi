<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Filament\Notifications\Notification;
use App\Models\Product;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            \App\Listeners\SendLowStockNotificationAfterLogin::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
