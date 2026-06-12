<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', "%{$q}%")
                  ->orWhere('customer_name', 'like', "%{$q}%")
                  ->orWhere('customer_email', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return view('admin.pesanan', [
            'orders' => $query->latest()->paginate(10)->withQueryString(),
            'counts' => [
                'total'          => Order::count(),
                'perlu_diproses' => Order::whereIn('status', ['diproses', 'menunggu_bayar'])->count(),
                'selesai'        => Order::where('status', 'selesai')->count(),
                'dibatalkan'     => Order::where('status', 'dibatalkan')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:menunggu_bayar,diproses,dikirim,selesai,dibatalkan',
        ]);
        $oldStatus = $order->status;
        $oldLabel  = $order->status_label;
        $order->status = $data['status'];
        $order->save();

        // Auto stok keluar: saat order baru PERTAMA KALI masuk status "dikirim"
        // (dari non-dikirim/selesai), potong stok + log mutasi.
        $newStatus = $data['status'];
        $wasShipped = in_array($oldStatus, ['dikirim', 'selesai']);
        $nowShipped = in_array($newStatus, ['dikirim', 'selesai']);
        if (! $wasShipped && $nowShipped) {
            $authUser = session('auth_user');
            foreach ($order->items as $item) {
                if (! $item->product_id) continue;
                $p = Product::find($item->product_id);
                if (! $p) continue;

                StockMovement::create([
                    'product_id'  => $p->id,
                    'user_id'     => $authUser['id'] ?? null,
                    'type'        => 'keluar',
                    'qty'         => $item->qty,
                    'reference'   => $order->invoice_number,
                    'note'        => 'Auto: pengiriman pesanan',
                    'occurred_at' => now(),
                ]);
                $p->stock = max(0, $p->stock - $item->qty);
                if ($p->stock === 0 && $p->status === 'aktif') {
                    $p->status = 'habis';
                }
                $p->save();
            }
        }

        return back()->with('status', "Status {$order->invoice_number} diubah: {$oldLabel} → {$order->status_label}.");
    }

    public function cetak(Order $order)
    {
        $order->load('items');
        return view('admin.pesanan-cetak', compact('order'));
    }

    public function cetakMassal(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id',
        ]);
        $orders = Order::with('items')
            ->whereIn('id', $data['ids'])
            ->latest()
            ->get();
        return view('admin.pesanan-cetak-massal', compact('orders'));
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = Order::whereIn('id', $data['ids'])->get();
        foreach ($orders as $order) {
            if ($order->payment_proof && str_starts_with($order->payment_proof, 'uploads/')) {
                $full = public_path($order->payment_proof);
                if (is_file($full)) {
                    @unlink($full);
                }
            }
            $order->delete();
        }

        return redirect()->route('admin.pesanan')->with('status', "{$orders->count()} pesanan berhasil dihapus.");
    }

    public function destroy(Order $order)
    {
        $invoice = $order->invoice_number;

        // Hapus file bukti transfer (legacy) jika ada
        if ($order->payment_proof && str_starts_with($order->payment_proof, 'uploads/')) {
            $full = public_path($order->payment_proof);
            if (is_file($full)) {
                @unlink($full);
            }
        }

        // order_items akan ikut terhapus via cascadeOnDelete
        $order->delete();

        return redirect()->route('admin.pesanan')->with('status', "Pesanan {$invoice} berhasil dihapus.");
    }
}
