<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PurchaseItem;
use App\Observers\PurchaseItemObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    PurchaseItem::observe(PurchaseItemObserver::class);
    }
}
