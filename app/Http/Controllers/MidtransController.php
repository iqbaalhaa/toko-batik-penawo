<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransController extends Controller
{
    // Midtrans Snap: regenerate token (kalau hilang / kadaluarsa)
    public function token(string $invoice)
    {
        $order = Order::where('invoice_number', $invoice)->firstOrFail();
        $authUser = session('auth_user');
        if (! $authUser || ($order->customer_email !== $authUser['email'] && ($authUser['role'] ?? null) !== 'admin')) {
            abort(403);
        }
        if ($order->payment_method !== 'Midtrans' || $order->status !== 'menunggu_bayar') {
            return response()->json(['error' => 'Pesanan tidak dapat dibayar ulang.'], 422);
        }

        try {
            \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
            \Midtrans\Config::$isSanitized  = config('services.midtrans.is_sanitized');
            \Midtrans\Config::$is3ds        = config('services.midtrans.is_3ds');
            if (app()->environment('local')) {
                \Midtrans\Config::$curlOptions = [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ];
            }

            $itemDetails = [];
            foreach ($order->items as $it) {
                $itemDetails[] = [
                    'id'       => (string) ($it->product_id ?? $it->product_name),
                    'price'    => (int) $it->price,
                    'quantity' => (int) $it->qty,
                    'name'     => Str::limit($it->product_name, 50, ''),
                ];
            }
            $mtOrderId = $order->invoice_number . '-' . time();
            $payload = [
                'transaction_details' => [
                    'order_id'     => $mtOrderId,
                    'gross_amount' => (int) $order->total,
                ],
                'item_details'        => $itemDetails,
                'customer_details'    => [
                    'first_name' => $order->customer_name,
                    'email'      => $order->customer_email,
                ],
                'callbacks' => ['finish' => route('pesanan.sukses', $order->invoice_number)],
            ];

            $snapToken                = \Midtrans\Snap::getSnapToken($payload);
            $order->snap_token        = $snapToken;
            $order->midtrans_order_id = $mtOrderId;
            $order->save();

            return response()->json(['snap_token' => $snapToken]);
        } catch (\Throwable $e) {
            Log::error('Midtrans re-token error', ['invoice' => $invoice, 'err' => $e->getMessage()]);
            return response()->json(['error' => 'Gagal memuat pembayaran. Coba lagi.'], 500);
        }
    }

    // Midtrans Webhook — dipanggil oleh server Midtrans ketika status berubah
    public function notification(Request $request)
    {
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

            $notif = new \Midtrans\Notification();

            $orderIdRaw    = $notif->order_id;         // bisa INV-...-{timestamp}
            $invoice       = explode('-', $orderIdRaw);
            // invoice_number pattern: INV-YYYYMMDD-0001 → 3 bagian. Kalau ada suffix retry, potong.
            $invoiceNumber = count($invoice) >= 3 ? implode('-', array_slice($invoice, 0, 3)) : $orderIdRaw;

            $order = Order::where('invoice_number', $invoiceNumber)->first();
            if (! $order) {
                Log::warning('Midtrans notif: order tidak ditemukan', ['order_id' => $orderIdRaw]);
                return response()->json(['status' => 'order_not_found'], 404);
            }

            $txStatus   = $notif->transaction_status;
            $fraud      = $notif->fraud_status ?? null;
            $paymentType = $notif->payment_type ?? null;
            $txId        = $notif->transaction_id ?? null;

            $order->midtrans_transaction_status = $txStatus;
            $order->midtrans_payment_type       = $paymentType;
            $order->midtrans_transaction_id     = $txId;

            if (in_array($txStatus, ['capture', 'settlement']) && (! $fraud || $fraud === 'accept')) {
                if ($order->status === 'menunggu_bayar') {
                    $order->status  = 'diproses';
                    $order->paid_at = now();
                }
            } elseif (in_array($txStatus, ['deny', 'cancel', 'expire'])) {
                if ($order->status === 'menunggu_bayar') {
                    $order->status = 'dibatalkan';
                }
            } elseif ($txStatus === 'pending') {
                // biarkan menunggu_bayar
            }

            $order->save();

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Midtrans notif error', ['err' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
