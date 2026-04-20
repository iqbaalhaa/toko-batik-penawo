<?php

namespace App\Providers;

use App\Models\Product;
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
            $raw = session('cart', []);
            $items = [];
            $subtotal = 0;

            if (! empty($raw)) {
                $products = Product::whereIn('slug', array_keys($raw))->get()->keyBy('slug');
                foreach ($raw as $slug => $qty) {
                    if (! isset($products[$slug])) {
                        continue;
                    }
                    $p = $products[$slug];
                    $qty = (int) $qty;
                    $items[] = [
                        'slug'      => $p->slug,
                        'name'      => $p->name,
                        'price'     => (int) $p->price,
                        'qty'       => $qty,
                        'image_url' => $p->image_url,
                    ];
                    $subtotal += $p->price * $qty;
                }
            }

            $view->with([
                'cartItems'    => $items,
                'cartSubtotal' => $subtotal,
                'rupiah'       => fn ($n) => 'Rp'.number_format($n, 0, ',', '.'),
                'authUser'     => session('auth_user'),
            ]);
        });
    }
}
