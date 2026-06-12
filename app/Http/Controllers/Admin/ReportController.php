<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Filter mutasi stok yang sama dipakai halaman daftar & halaman cetak
     * supaya hasilnya konsisten.
     */
    private function filteredQuery(Request $request)
    {
        $baseQuery = StockMovement::query();
        if ($request->filled('q')) {
            $q = $request->q;
            $baseQuery->whereHas('product', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
            });
        }
        if ($request->filled('type') && in_array($request->type, ['masuk', 'keluar'])) {
            $baseQuery->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $baseQuery->whereDate('occurred_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $baseQuery->whereDate('occurred_at', '<=', $request->to);
        }
        return $baseQuery;
    }

    public function index(Request $request)
    {
        $baseQuery = $this->filteredQuery($request);

        // Totals periode (dari query yang SAMA supaya konsisten dengan tabel)
        $totalIn  = (clone $baseQuery)->where('type', 'masuk')->sum('qty');
        $totalOut = (clone $baseQuery)->where('type', 'keluar')->sum('qty');
        $trxIn    = (clone $baseQuery)->where('type', 'masuk')->count();
        $trxOut   = (clone $baseQuery)->where('type', 'keluar')->count();

        $movements = $baseQuery->with(['product', 'user'])
            ->orderBy('occurred_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $lowStock = Product::whereColumn('stock', '<', 'stock_min')->get();
        $products = Product::orderBy('name')->get(['id', 'sku', 'name', 'stock']);

        return view('admin.laporan', compact(
            'movements', 'lowStock', 'products',
            'totalIn', 'totalOut', 'trxIn', 'trxOut'
        ));
    }

    // Halaman cetak laporan stok — view khusus print (A4 landscape, tanpa sidebar/filter UI).
    public function cetak(Request $request)
    {
        $baseQuery = $this->filteredQuery($request);

        $movements = (clone $baseQuery)->with(['product', 'user'])
            ->orderBy('occurred_at', 'desc')
            ->get();

        $totalIn  = (clone $baseQuery)->where('type', 'masuk')->sum('qty');
        $totalOut = (clone $baseQuery)->where('type', 'keluar')->sum('qty');
        $trxIn    = (clone $baseQuery)->where('type', 'masuk')->count();
        $trxOut   = (clone $baseQuery)->where('type', 'keluar')->count();

        // Ringkasan filter aktif untuk dicetak di header laporan.
        $filters = [
            'q'    => $request->input('q'),
            'type' => $request->input('type'),
            'from' => $request->input('from'),
            'to'   => $request->input('to'),
        ];

        return view('admin.laporan-cetak', compact(
            'movements', 'totalIn', 'totalOut', 'trxIn', 'trxOut', 'filters'
        ));
    }

    // Tambah mutasi stok manual (masuk / keluar)
    public function storeMutasi(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:masuk,keluar',
            'qty'        => 'required|integer|min:1|max:9999',
            'reference'  => 'nullable|string|max:100',
            'note'       => 'nullable|string|max:200',
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['type'] === 'keluar' && $product->stock < $data['qty']) {
            return back()->withErrors(['qty' => "Stok {$product->name} hanya {$product->stock}, tidak cukup untuk dikurangi {$data['qty']}."])->withInput();
        }

        $authUser = session('auth_user');

        StockMovement::create([
            'product_id'  => $product->id,
            'user_id'     => $authUser['id'] ?? null,
            'type'        => $data['type'],
            'qty'         => $data['qty'],
            'reference'   => $data['reference'] ?? null,
            'note'        => $data['note'] ?? null,
            'occurred_at' => now(),
        ]);

        // Update stock di tabel products
        $product->stock = $data['type'] === 'masuk'
            ? $product->stock + $data['qty']
            : max(0, $product->stock - $data['qty']);

        // Auto-sync status: stok 0 → habis; stok > 0 dan status habis → aktif
        if ($product->stock === 0 && $product->status === 'aktif') {
            $product->status = 'habis';
        } elseif ($product->stock > 0 && $product->status === 'habis') {
            $product->status = 'aktif';
        }

        $product->save();

        $label = $data['type'] === 'masuk' ? 'masuk' : 'keluar';
        return back()->with('status', "Mutasi stok {$label} sebanyak {$data['qty']} unit untuk {$product->name} berhasil dicatat.");
    }
}
