<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        // Range periode untuk perbandingan bulan ini vs bulan lalu.
        $startThisMonth = now()->startOfMonth();
        $startLastMonth = now()->copy()->subMonth()->startOfMonth();
        $endLastMonth   = now()->copy()->startOfMonth()->subSecond();

        // Pendapatan & jumlah pesanan per periode (status realisasi: dikirim + selesai).
        $revenueThisMonth = Order::whereIn('status', ['dikirim', 'selesai'])
            ->where('created_at', '>=', $startThisMonth)->sum('total');
        $revenueLastMonth = Order::whereIn('status', ['dikirim', 'selesai'])
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])->sum('total');
        $ordersThisMonth = Order::where('created_at', '>=', $startThisMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();

        // Antrian aksi: pesanan yang masih perlu dikelola admin.
        $pendingActionCount = Order::whereIn('status', ['menunggu_bayar', 'diproses'])->count();

        // Alert "perlu perhatian": item yang stuck atau kritis.
        $stuckPaymentCount = Order::where('status', 'menunggu_bayar')
            ->where('created_at', '<', now()->subHours(24))->count();
        $shipmentDueCount = Order::where('status', 'diproses')
            ->where(function ($q) {
                $q->where('paid_at', '<', now()->subDays(2))
                  ->orWhere('updated_at', '<', now()->subDays(2));
            })->count();

        // Stok rendah: produk dengan stok < stok_min.
        $lowStockCount = Product::whereColumn('stock', '<', 'stock_min')->count();
        $lowStock      = Product::whereColumn('stock', '<', 'stock_min')
            ->orderBy('stock')->take(5)->get();

        return view('admin.dashboard', [
            // KPI cards (top)
            'revenueThisMonth'   => $revenueThisMonth,
            'revenueLastMonth'   => $revenueLastMonth,
            'ordersThisMonth'    => $ordersThisMonth,
            'ordersLastMonth'    => $ordersLastMonth,
            'pendingActionCount' => $pendingActionCount,
            'lowStockCount'      => $lowStockCount,

            // Alerts (perlu perhatian)
            'stuckPaymentCount'  => $stuckPaymentCount,
            'shipmentDueCount'   => $shipmentDueCount,

            // Total dasar (untuk konteks)
            'totalProducts'      => Product::count(),
            'totalOrders'        => Order::count(),
            'totalCustomers'     => User::where('role', 'pelanggan')->where('status', 'aktif')->count(),

            // Tabel & list
            'recentOrders'       => Order::latest()->take(6)->get(),
            'lowStock'           => $lowStock,

            // Ringkasan stok 7 hari (existing)
            'stockIn7Day'        => StockMovement::where('type', 'masuk')->where('occurred_at', '>=', now()->subDays(7))->sum('qty'),
            'stockOut7Day'       => StockMovement::where('type', 'keluar')->where('occurred_at', '>=', now()->subDays(7))->sum('qty'),
            'trxIn7Day'          => StockMovement::where('type', 'masuk')->where('occurred_at', '>=', now()->subDays(7))->count(),
            'trxOut7Day'         => StockMovement::where('type', 'keluar')->where('occurred_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    // Profil admin: kelola data akun sendiri (nama, email, telepon, password).
    public function profil()
    {
        $authUser = session('auth_user');
        $user = User::findOrFail($authUser['id']);
        return view('admin.profil', compact('user'));
    }

    public function updateProfil(Request $request)
    {
        $authUser = session('auth_user');
        $user = User::findOrFail($authUser['id']);

        $data = $request->validate([
            'name'             => 'required|string|min:2|max:60',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'phone'            => 'nullable|string|max:25',
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|string|min:6|confirmed',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (! empty($data['new_password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
            }
            $user->password = Hash::make($data['new_password']);
        }
        $user->save();

        // Sinkronisasi session supaya nama/email yang baru langsung muncul di header.
        session(['auth_user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]]);

        return redirect()->route('admin.profil')->with('status', 'Profil berhasil diperbarui.');
    }
}
