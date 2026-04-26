<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
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
                // Ambil unique slug dari struktur cart baru: ['cartKey' => ['slug','qty','size','color']]
                $slugs = collect($raw)->pluck('slug')->filter()->unique()->values()->all();
                $products = Product::whereIn('slug', $slugs)->get()->keyBy('slug');
                foreach ($raw as $cartKey => $row) {
                    if (! is_array($row) || empty($row['slug']) || ! isset($products[$row['slug']])) {
                        continue;
                    }
                    $p = $products[$row['slug']];
                    $qty = (int) ($row['qty'] ?? 0);
                    if ($qty < 1) continue;
                    $items[] = [
                        'cart_key'  => $cartKey,
                        'slug'      => $p->slug,
                        'name'      => $p->name,
                        'price'     => (int) $p->price,
                        'qty'       => $qty,
                        'size'      => $row['size'] ?? null,
                        'color'     => $row['color'] ?? null,
                        'image_url' => $p->image_url,
                    ];
                    $subtotal += $p->price * $qty;
                }
            }

            // Site settings (CMS) — di-cache, dipakai di footer/topbar/halaman tentang & kontak
            $settings = [];
            if (Schema::hasTable('site_settings')) {
                $settings = SiteSetting::all_assoc();
            }

            $view->with([
                'cartItems'    => $items,
                'cartSubtotal' => $subtotal,
                'rupiah'       => fn ($n) => 'Rp'.number_format($n, 0, ',', '.'),
                'authUser'     => session('auth_user'),
                'siteSettings' => $settings,
                'setting'      => fn ($k, $d = null) => $settings[$k] ?? $d,
            ]);
        });

        // Notifikasi pesanan masuk untuk layout admin
        View::composer('layouts.admin', function ($view) {
            $pendingCount  = 0;
            $recentPending = collect();
            if (Schema::hasTable('orders')) {
                $statuses      = ['menunggu_bayar', 'diproses'];
                $pendingCount  = Order::whereIn('status', $statuses)->count();
                $recentPending = Order::whereIn('status', $statuses)
                    ->latest()->take(5)->get();
            }
            $view->with([
                'pendingOrdersCount'  => $pendingCount,
                'recentPendingOrders' => $recentPending,
            ]);
        });
    }
}
