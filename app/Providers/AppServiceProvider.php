<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('*', function ($view) {
            $items = config('cart', []);
            $subtotal = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $items));

            $view->with([
                'cartItems' => $items,
                'cartSubtotal' => $subtotal,
                'rupiah' => fn ($n) => 'Rp'.number_format($n, 0, ',', '.'),
                'authUser' => session('auth_user'),
            ]);
        });
    }
}
