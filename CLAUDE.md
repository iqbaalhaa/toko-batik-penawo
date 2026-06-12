# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Toko Batik Penawuo** — single-tenant e-commerce store for batik products, built on Laravel 12 (PHP 8.2+). All UI text, comments, route names, and status values are in **Bahasa Indonesia**. Prices are integer rupiah (IDR). The public frontend is a Colorlib "Cozastore" template ported into Blade.

## Commands

```bash
composer dev                 # serve + queue + pail logs + vite (concurrently)
php artisan serve            # web server only (usually sufficient)

composer test                # config:clear + run all tests
php artisan test --filter=ShippingCalculatorTest          # single test class
php artisan test --filter=test_method_name                # single test method

vendor/bin/pint              # code formatter (Laravel Pint)

php artisan migrate          # dev DB is MySQL `batik_penawo` (see .env)
php artisan db:seed          # users, categories, wilayah (products/orders seeders disabled by default)

php artisan rajaongkir:sync-wilayah --from-users   # map Kemendagri wilayah → RajaOngkir IDs (quota-aware)
```

Tests run on in-memory SQLite (phpunit.xml); the dev database is MySQL.

## Critical Architecture Facts

### Controller layout

`routes/web.php` is declaration-only; all logic lives in controllers under `app/Http/Controllers/`:

- Public: `HomeController` (catalog, search, static pages), `CartController` (session cart), `CheckoutController` (3-step checkout, invoice page, order completion), `MidtransController` (Snap re-token + webhook), `WilayahController` (`/api/wilayah/*` cascading dropdowns)
- Auth/account: `AuthController` (session login/register/logout), `AccountController` (profil, pesanan, pengaturan, hapus akun), `AddressController` (max 3 addresses per user)
- Admin (`App\Http\Controllers\Admin`, behind `admin` middleware): `DashboardController`, `ProductController`, `OrderController`, `ReportController` (stock movements), `UserController`, `CmsController` (site settings, banners, categories)

Route names are Indonesian (`produk`, `keranjang`, `pesanan`, `akun.*`, `admin.*`) and are referenced extensively in Blade views — never rename them. In the admin order routes, static paths (`/pesanan/cetak-massal`, `/pesanan/bulk`) must stay registered before `/pesanan/{order}`.

### Custom session auth — NOT Laravel Auth

Authentication does **not** use Laravel's Auth facade/guards. Login stores an array in the session:

```php
session(['auth_user' => ['id' => ..., 'name' => ..., 'email' => ..., 'role' => ...]]);
```

- Check login with `session('auth_user')`; there is no `auth()->user()`.
- Two roles only: `admin` and `pelanggan`. Admin login redirects to `/admin`, customer to `/`.
- Admin routes are guarded by the `admin` middleware alias → `App\Http\Middleware\EnsureAdmin` (registered in `bootstrap/app.php`).
- A global view composer (`AppServiceProvider`) injects `$authUser` into every view.
- Ownership checks on orders compare `customer_email` to the session email.

### Global view composers (`app/Providers/AppServiceProvider.php`)

Every view automatically receives: `$cartItems`, `$cartSubtotal`, `$rupiah` (closure: `$rupiah($n)` → `Rp1.234.567`), `$authUser`, `$siteSettings`, `$setting($key, $default)`. The admin layout additionally gets pending-order counts. Use these instead of re-querying.

### Cart & checkout flow

- Cart is session-only: `session('cart')` = `[cartKey => ['slug','qty','size','color']]` where `cartKey = substr(md5(slug|size|color), 0, 12)`. Variants (size/color) come from the product's `sizes`/`colors` JSON arrays.
- Checkout is 3 steps: `POST /checkout` (stash selected cart keys in `session('checkout_pending')`) → `GET /checkout` (summary built by `CheckoutShippingService`) → `POST /checkout/confirm` (creates `Order` + `OrderItem`s, decrements stock, records `StockMovement`).
- Customers must have at least one saved address (`addresses` table, multi-address with `is_default`; `User::shippingAddress()` falls back to legacy embedded columns).
- Payment methods: `Midtrans` (Snap popup) or `COD`.
- Order status flow (Indonesian keys, labels in `Order::STATUS_LABELS`): `menunggu_bayar` → `diproses` → `dikirim` → `selesai`, or `dibatalkan`.
- Invoice format: `INV-YYYYMMDD-NNNN`. Midtrans `order_id` is `invoice_number . '-' . time()` (retry suffix); the webhook strips the suffix by taking the first 3 dash-separated parts.
- Midtrans webhook `POST /midtrans/notification` is CSRF-exempt (`bootstrap/app.php`). Midtrans config lives in `config/services.php` under `midtrans`.

### Shipping calculation (3-layer, `app/Services/`)

1. **`CheckoutShippingService`** — orchestrator: groups cart lines by store (currently single store `default`, address from `site_settings` `store_*` keys), totals weight per store (`qty × weight_kg`), aggregates totals. Products **must** have `weight_kg > 0` or checkout is blocked.
2. **`ShippingCalculator`** — picks rate source: RajaOngkir if `RAJAONGKIR_API_KEY` is set, otherwise a deterministic local zone calculator (`same_district`/`same_city`/`same_province`/`outside_province`) whose fees can be overridden via `site_settings` `shipping_*` keys. Courier selection codes look like `jne:REG`; the server validates them and falls back to cheapest.
3. **`RajaOngkirService`** — Komerce API client (`rajaongkir.komerce.id`). Heavy caching: wilayah hierarchy 30 days, rates 1 hour (free tier = 100 hits/day). District resolution is DB-first via the `rajaongkir_id` column (filled by `php artisan rajaongkir:sync-wilayah`), then name-search fallback. `lastErrorReason()` surfaces API failures to the UI.

### Wilayah (Indonesian administrative regions)

`provinces` / `regencies` / `districts` tables hold Kemendagri reference data with **cuid string primary keys** (seeded, idempotent). Cascading address dropdowns use the public JSON endpoints `/api/wilayah/provinces|regencies|districts` and the shared partial `resources/views/partials/_wilayah_cascade.blade.php`.

### Frontend assets — no Vite in practice

Views load static Colorlib template assets from `public/frontend/` via `asset()` (Bootstrap 4, jQuery, select2, slick). **No Blade view uses `@vite`** — the Vite/Tailwind setup in `package.json` is unused scaffolding; `npm run dev/build` is not needed to run the app. Layouts: `layouts/app` (public + customer account), `layouts/admin` (admin panel), `layouts/auth` (login/register). Admin pages are single-file Blades (`admin/produk.blade.php` etc.) containing their own modals/JS.

## Project Rules (must follow)

- **Uploads** go directly to `public/uploads/<feature>/` (e.g. `uploads/products/`, `uploads/banners/`, `uploads/bukti-transfer/`) via `$file->move(public_path(...))`. **Never** use `storage/app/public` or `php artisan storage:link`.
- **Price inputs** in forms use the rupiah-input pattern: displayed with dot thousand separators, dots stripped client-side on submit, stored server-side as integer. Display with the `$rupiah()` view helper or `Product::formatted_price`.
- **Site settings** (`site_settings` key-value table) are cached 10 minutes via `SiteSetting::all_assoc()`; always write through `SiteSetting::setMany()` so the cache is invalidated. Editable from admin CMS page (`/admin/cms`).
- Products use `status` values including `arsip` (archived) — public queries filter `where('status', '!=', 'arsip')`.
- Stock changes must be recorded in `stock_movements` (see existing checkout/admin patterns).
- Keep new code consistent with existing style: Indonesian comments and user-facing strings, Indonesian route names/URLs (`produk`, `keranjang`, `pesanan`, `laporan`), aligned array arrows.
- `Schema::defaultStringLength(191)` is set — keep indexed string columns ≤191 chars.
