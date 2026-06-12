<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Step 1: dari keranjang — stash cart_keys terpilih ke session, redirect ke halaman bayar
    public function start(Request $request)
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk checkout.']);
        }

        $data = $request->validate([
            'selected'   => 'required|array|min:1',
            'selected.*' => 'required|string',
        ]);

        $cart = session('cart', []);
        $selectedKeys = array_values(array_intersect($data['selected'], array_keys($cart)));
        if (empty($selectedKeys)) {
            return redirect()->route('keranjang')->withErrors(['selected' => 'Produk yang dipilih tidak ada di keranjang.']);
        }

        session(['checkout_pending' => $selectedKeys]);
        return redirect()->route('checkout.show');
    }

    // Step 2: halaman bayar — tampilkan ringkasan + form alamat + pilih metode bayar
    public function show()
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk checkout.']);
        }

        $pending = session('checkout_pending', []);
        if (empty($pending)) {
            return redirect()->route('keranjang')->with('status', 'Pilih produk dari keranjang untuk checkout.');
        }

        $cart = session('cart', []);
        $keys = array_values(array_intersect($pending, array_keys($cart)));
        if (empty($keys)) {
            session()->forget('checkout_pending');
            return redirect()->route('keranjang')->withErrors(['selected' => 'Item checkout sudah tidak tersedia di keranjang.']);
        }

        $slugs = collect($keys)->map(fn ($k) => $cart[$k]['slug'] ?? null)->filter()->unique()->values()->all();
        $products = Product::whereIn('slug', $slugs)->get()->keyBy('slug');

        // Bangun "lines" yang akan dikonsumsi CheckoutShippingService.
        $lines = [];
        foreach ($keys as $k) {
            $row = $cart[$k] ?? null;
            $p = $row ? ($products[$row['slug']] ?? null) : null;
            if (! $p) continue;
            $lines[] = [
                'cart_key'   => $k,
                'product'    => $p,
                'qty'        => (int) $row['qty'],
                'unit_price' => (int) $p->price,
                'name'       => $p->name,
                'size'       => $row['size']  ?? null,
                'color'      => $row['color'] ?? null,
                'image_url'  => $p->image_url,
            ];
        }

        $user            = User::find($authUser['id'] ?? null);
        $addresses       = $user ? $user->addresses : collect();

        // Pelanggan wajib punya minimal 1 alamat tersimpan untuk checkout.
        if ($addresses->isEmpty()) {
            return redirect()->route('akun.profil')
                ->withErrors(['alamat' => 'Tambahkan dulu alamat pengiriman di profil sebelum checkout.']);
        }

        // Pilih alamat: dari ?address_id (kembalian POST gagal) → atau default → atau yg pertama.
        $selectedAddressId = (int) request()->query('address_id', 0);
        $selectedAddress   = $addresses->firstWhere('id', $selectedAddressId)
            ?? $user->defaultAddress()
            ?? $addresses->first();

        // Selection kurir per toko (mis. ?shipping[default]=jne:REG). Default kosong
        // → calculator pilih opsi termurah otomatis.
        $shippingSelections = collect((array) request()->input('shipping', []))
            ->filter(fn ($v) => is_string($v) && $v !== '' && strlen($v) <= 64)
            ->all();

        $shippingSvc     = new CheckoutShippingService();
        $summary         = $shippingSvc->summary(
            $lines,
            $selectedAddress->toShippingPayload(),
            $shippingSelections,
        );

        return view('home.checkout', [
            'summary'             => $summary,
            'user'                => $user,
            'addresses'           => $addresses,
            'selectedAddress'     => $selectedAddress,
            'shippingSelections'  => $shippingSelections,
        ]);
    }

    // Step 3: konfirmasi — buat Order + OrderItems, bersihkan cart/pending, redirect ke halaman sukses
    public function confirm(Request $request)
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk checkout.']);
        }

        $data = $request->validate([
            'recipient_name'  => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:25',
            'payment_method'  => 'required|in:Midtrans,COD',
            // Alamat hanya wajib untuk pengiriman (Midtrans). Untuk COD jemput, opsional.
            'address_id'      => 'required_if:payment_method,Midtrans|nullable|integer|exists:addresses,id',
            'note'            => 'nullable|string|max:300',
            // Opsi kurir per toko (mis. shipping[default]=jne:REG). Server otoritatif
            // — calculator akan tolak kode yang tidak cocok dengan opsi tersedia
            // dan fallback ke termurah.
            'shipping'        => 'nullable|array',
            'shipping.*'      => 'nullable|string|max:64',
        ]);

        $isPickup = $data['payment_method'] === 'COD';

        $address = null;
        if (! empty($data['address_id'])) {
            $address = Address::where('id', $data['address_id'])
                ->where('user_id', $authUser['id'])
                ->first();
            if (! $address && ! $isPickup) {
                return redirect()->route('checkout.show')
                    ->withErrors(['address_id' => 'Alamat yang dipilih tidak ditemukan.']);
            }
        }

        $pending = session('checkout_pending', []);
        $cart = session('cart', []);
        $keys = array_values(array_intersect($pending, array_keys($cart)));
        if (empty($keys)) {
            return redirect()->route('keranjang')->withErrors(['selected' => 'Item checkout sudah tidak tersedia.']);
        }

        $slugs = collect($keys)->map(fn ($k) => $cart[$k]['slug'] ?? null)->filter()->unique()->values()->all();
        $products = Product::whereIn('slug', $slugs)->get()->keyBy('slug');

        // Susun lines + items pesanan sekaligus.
        $lines = [];
        $items = [];
        foreach ($keys as $k) {
            $row = $cart[$k] ?? null;
            $p = $row ? ($products[$row['slug']] ?? null) : null;
            if (! $p) continue;
            $qty = (int) $row['qty'];
            $lines[] = [
                'cart_key'   => $k,
                'product'    => $p,
                'qty'        => $qty,
                'unit_price' => (int) $p->price,
                'name'       => $p->name,
                'size'       => $row['size']  ?? null,
                'color'      => $row['color'] ?? null,
                'image_url'  => $p->image_url,
            ];
            $items[] = [
                'product_id'   => $p->id,
                'product_name' => $p->name,
                'size'         => $row['size'] ?? null,
                'color'        => $row['color'] ?? null,
                'qty'          => $qty,
                'price'        => (int) $p->price,
            ];
        }

        $user = User::find($authUser['id'] ?? null);

        $summary = null;
        if ($isPickup) {
            // COD jemput: tidak ada ongkir, tidak validasi zona.
            $subtotalProducts = collect($lines)->sum(fn ($l) => $l['unit_price'] * $l['qty']);
            $subtotal         = $subtotalProducts;
            $total            = $subtotalProducts;
            $shippingTotal    = 0;
            $shippingBreakdown = [];
            $shippingAddrText = 'JEMPUT DI TOKO BATIK PENAWUO';
        } else {
            $shippingSelections = array_filter(
                (array) ($data['shipping'] ?? []),
                fn ($v) => is_string($v) && $v !== '',
            );
            $shippingSvc = new CheckoutShippingService();
            $summary     = $shippingSvc->summary($lines, $address->toShippingPayload(), $shippingSelections);

            if (! $summary['all_available']) {
                $message = 'Checkout tidak dapat dilanjutkan: ' . implode(' | ', $summary['errors']);
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => $message], 422);
                }
                return redirect()->route('checkout.show', ['address_id' => $address->id])->withErrors(['shipping' => $message]);
            }

            $subtotal      = $summary['subtotal_products'];
            $total         = $summary['grand_total'];
            $shippingTotal = $summary['shipping_total'];
            $shippingBreakdown = array_map(function ($s) {
                $sh = $s['shipping'];
                return [
                    'store_id'        => $s['store_id'],
                    'store_name'      => $s['store_name'],
                    'total_weight_kg' => $sh['total_weight_kg'],
                    'weight_grams'    => $sh['weight_grams'] ?? null,
                    'zone'            => $sh['zone'],
                    'zone_label'      => $sh['zone_label'],
                    'shipping_cost'   => $sh['shipping_cost'],
                    // Snapshot kurir RajaOngkir bila dipakai — null untuk tarif lokal.
                    'source'          => $sh['source']       ?? 'local',
                    'courier_code'    => $sh['courier_code'] ?? null,
                    'courier_name'    => $sh['courier_name'] ?? null,
                    'service_name'    => $sh['service_name'] ?? null,
                    'etd'             => $sh['etd']          ?? null,
                ];
            }, $summary['stores']);
            $shippingAddrText = $address->toFormattedText();
        }

        $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT);

        $noteParts = array_filter([
            'Penerima: ' . $data['recipient_name'] . ' (' . $data['recipient_phone'] . ')',
            $isPickup ? 'Metode: Jemput sendiri di toko' : null,
            $data['note'] ? 'Catatan: ' . $data['note'] : null,
        ]);

        $order = Order::create([
            'invoice_number'     => $invoice,
            'user_id'            => $user?->id,
            'customer_name'      => $authUser['name'],
            'customer_email'     => $authUser['email'],
            'total'              => $total,
            'subtotal_products'  => $subtotal,
            'shipping_total'     => $shippingTotal,
            'shipping_breakdown' => $shippingBreakdown,
            'payment_method'     => $isPickup ? 'COD' : 'Midtrans',
            'status'             => $isPickup ? 'diproses' : 'menunggu_bayar',
            'shipping_address'   => $shippingAddrText,
            'note'               => implode(' · ', $noteParts),
        ]);

        foreach ($items as $item) {
            $order->items()->create($item);
        }

        // COD jemput tidak butuh Snap token — langsung selesaikan flow.
        if ($isPickup) {
            foreach ($keys as $k) { unset($cart[$k]); }
            session(['cart' => $cart]);
            session()->forget('checkout_pending');

            $redirectUrl = route('pesanan.sukses', $order->invoice_number);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'invoice'      => $order->invoice_number,
                    'snap_token'   => null,
                    'is_pickup'    => true,
                    'redirect_url' => $redirectUrl,
                ]);
            }
            return redirect($redirectUrl);
        }

        // Generate Midtrans Snap token
        $snapToken = null;
        $snapError = null;
        try {
            \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
            \Midtrans\Config::$isSanitized  = config('services.midtrans.is_sanitized');
            \Midtrans\Config::$is3ds        = config('services.midtrans.is_3ds');
            // Workaround SSL untuk environment lokal (Windows/XAMPP tanpa CA bundle)
            if (app()->environment('local')) {
                \Midtrans\Config::$curlOptions = [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_HTTPHEADER     => [], // required by midtrans-php (diakses tanpa isset)
                ];
            }

            $itemDetails = [];
            foreach ($items as $it) {
                $variantSuffix = '';
                $variantParts  = array_filter([$it['size'] ?? null, $it['color'] ?? null]);
                if ($variantParts) $variantSuffix = ' (' . implode(', ', $variantParts) . ')';
                $itemDetails[] = [
                    'id'       => (string) ($it['product_id'] ?? $it['product_name']),
                    'price'    => (int) $it['price'],
                    'quantity' => (int) $it['qty'],
                    'name'     => Str::limit($it['product_name'] . $variantSuffix, 50, ''),
                ];
            }
            // Tambahkan baris ongkir per toko supaya gross_amount cocok dengan item_details.
            foreach ($summary['stores'] as $s) {
                if (($s['shipping']['shipping_cost'] ?? 0) <= 0) continue;
                $itemDetails[] = [
                    'id'       => 'SHIP-' . $s['store_id'],
                    'price'    => (int) $s['shipping']['shipping_cost'],
                    'quantity' => 1,
                    'name'     => Str::limit('Ongkir ' . $s['store_name'] . ' (' . $s['shipping']['zone_label'] . ')', 50, ''),
                ];
            }
            [$firstName, $lastName] = array_pad(explode(' ', trim($data['recipient_name']), 2), 2, '');

            // Order_id ke Midtrans WAJIB unik per Snap session — kalau pakai
            // invoice_number polos, sandbox bisa men-resolve ke transaksi lama
            // (mis. setelah migrate:fresh) dan `Transaction::status()` mengembalikan
            // status "settlement" dari sesi sebelumnya → halaman sukses keliru
            // menandai order baru sebagai "Dibayar" padahal user belum bayar.
            $mtOrderId = $order->invoice_number . '-' . time();

            $payload = [
                'transaction_details' => [
                    'order_id'     => $mtOrderId,
                    'gross_amount' => (int) $total,
                ],
                'item_details'        => $itemDetails,
                'customer_details'    => [
                    'first_name'   => $firstName ?: $authUser['name'],
                    'last_name'    => $lastName,
                    'email'        => $authUser['email'],
                    'phone'        => $data['recipient_phone'],
                    'shipping_address' => [
                        'first_name' => $firstName ?: $authUser['name'],
                        'last_name'  => $lastName,
                        'phone'      => $data['recipient_phone'],
                        'address'    => Str::limit($address->toFormattedText(), 200, ''),
                    ],
                ],
                'callbacks' => [
                    'finish' => route('pesanan.sukses', $order->invoice_number),
                ],
            ];

            $snapToken               = \Midtrans\Snap::getSnapToken($payload);
            $order->snap_token       = $snapToken;
            $order->midtrans_order_id = $mtOrderId;
            $order->save();
        } catch (\Throwable $e) {
            Log::error('Midtrans snap token error', ['invoice' => $order->invoice_number, 'err' => $e->getMessage()]);
            $snapError = $e->getMessage();
        }

        // Bersihkan cart dan checkout pending
        foreach ($keys as $k) {
            unset($cart[$k]);
        }
        session(['cart' => $cart]);
        session()->forget('checkout_pending');

        $redirectUrl = route('pesanan.sukses', $order->invoice_number);

        // AJAX / Midtrans flow: kembalikan JSON supaya front-end bisa buka Snap langsung
        if ($request->expectsJson() || $request->ajax()) {
            if (! $snapToken) {
                return response()->json([
                    'error'        => 'Gagal menyiapkan pembayaran. ' . ($snapError ? '(' . $snapError . ')' : ''),
                    'redirect_url' => $redirectUrl,
                ], 500);
            }
            return response()->json([
                'invoice'      => $order->invoice_number,
                'snap_token'   => $snapToken,
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl);
    }

    // Pelanggan konfirmasi pesanan diterima → ubah status ke "selesai"
    public function selesai(string $invoice)
    {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk mengonfirmasi pesanan.']);
        }

        $order = Order::where('invoice_number', $invoice)->firstOrFail();

        // Hanya pemilik pesanan (atau admin) yang boleh konfirmasi
        if ($order->customer_email !== $authUser['email'] && ($authUser['role'] ?? null) !== 'admin') {
            abort(403);
        }
        if ($order->status !== 'dikirim') {
            return back()->withErrors(['status' => 'Pesanan hanya dapat dikonfirmasi setelah berstatus Dikirim.']);
        }

        $order->status = 'selesai';
        $order->save();

        return redirect()->route('pesanan.sukses', $order->invoice_number)
            ->with('status', 'Terima kasih! Pesanan ditandai selesai.');
    }

    // Step 4: halaman sukses / invoice
    public function sukses(string $invoice, Request $request)
    {
        $order = Order::with('items')->where('invoice_number', $invoice)->firstOrFail();
        // Simple access guard: user matches, OR admin
        $authUser = session('auth_user');
        $isOwner = $authUser && ($order->customer_email === $authUser['email'] || ($authUser['role'] ?? null) === 'admin');
        if (! $isOwner) {
            return redirect()->route('home')->withErrors(['email' => 'Anda tidak dapat melihat pesanan ini.']);
        }

        // Sinkronkan status dengan Midtrans — webhook bisa tidak sampai di lokal,
        // jadi saat user mendarat di halaman invoice, kita cek langsung ke API.
        //
        // Skip kalau user baru saja menutup popup Snap (?cancelled=1 dari onClose JS):
        // user belum bayar, dan kita tidak mau ambil resiko `Transaction::status()`
        // mengembalikan data sesi sebelumnya yang nyangkut di sandbox.
        $cancelled = $request->boolean('cancelled');

        if ($order->payment_method === 'Midtrans' && $order->status === 'menunggu_bayar' && ! $cancelled) {
            try {
                \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
                \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
                if (app()->environment('local')) {
                    \Midtrans\Config::$curlOptions = [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_HTTPHEADER     => [],
                    ];
                }

                // Query Midtrans pakai order_id yang TEPAT dikirim saat membuat Snap
                // token — fallback ke invoice_number untuk order legacy yang dibuat
                // sebelum kolom midtrans_order_id diperkenalkan.
                $idForStatus = $order->midtrans_order_id ?: $order->invoice_number;

                $status = \Midtrans\Transaction::status($idForStatus);
                $status = is_object($status) ? (array) $status : (array) $status;

                $txStatus    = $status['transaction_status'] ?? null;
                $fraud       = $status['fraud_status'] ?? null;
                $paymentType = $status['payment_type'] ?? null;
                $txId        = $status['transaction_id'] ?? null;

                if ($txStatus) {
                    $order->midtrans_transaction_status = $txStatus;
                    $order->midtrans_payment_type       = $paymentType;
                    $order->midtrans_transaction_id     = $txId;

                    // Hanya tandai lunas pada kondisi pasti (Midtrans docs):
                    //  - settlement                 : final paid (semua channel)
                    //  - capture + fraud=accept     : credit card lolos fraud check
                    // Kondisi `capture` tanpa fraud_status='accept' (mis. fraud=null
                    // atau 'challenge') sengaja tidak di-paid otomatis.
                    $isPaid = $txStatus === 'settlement'
                        || ($txStatus === 'capture' && $fraud === 'accept');

                    if ($isPaid) {
                        $order->status  = 'diproses';
                        $order->paid_at = $order->paid_at ?? now();
                    } elseif (in_array($txStatus, ['deny', 'cancel', 'expire'])) {
                        $order->status = 'dibatalkan';
                    }
                    $order->save();
                }
            } catch (\Throwable $e) {
                Log::warning('Midtrans sync status error', ['invoice' => $invoice, 'err' => $e->getMessage()]);
            }
        }

        return view('home.pesanan-sukses', compact('order'));
    }
}
