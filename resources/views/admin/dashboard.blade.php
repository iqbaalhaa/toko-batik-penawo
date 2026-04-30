@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Ringkasan aktivitas toko ' . now()->translatedFormat('l, d F Y'))

@push('styles')
<style>
	/* --- KPI hero cards --- */
	.kpi-card {
		background: #fff; border: 1px solid #ece8de; border-radius: 8px;
		padding: 18px 20px; display: flex; flex-direction: column; gap: 8px;
		position: relative; overflow: hidden; transition: transform .15s, box-shadow .15s;
	}
	.kpi-card:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.05); }
	.kpi-card.clickable { cursor: pointer; text-decoration: none; color: inherit; }
	.kpi-card .kpi-head { display: flex; justify-content: space-between; align-items: center; }
	.kpi-card .kpi-label { font-size: 11.5px; color: #9a9288; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; }
	.kpi-card .kpi-icon {
		width: 34px; height: 34px; border-radius: 8px;
		display: inline-flex; align-items: center; justify-content: center;
		color: #fff; font-size: 14px; flex-shrink: 0;
	}
	.kpi-card .kpi-icon.brand   { background: #c29e5c; }
	.kpi-card .kpi-icon.green   { background: #56a676; }
	.kpi-card .kpi-icon.orange  { background: #e89740; }
	.kpi-card .kpi-icon.red     { background: #d86a59; }
	.kpi-card .kpi-icon.blue    { background: #4f79c2; }
	.kpi-card .kpi-value { font-size: 24px; font-weight: 700; color: #1c1c1c; line-height: 1.1; letter-spacing: -.02em; }
	.kpi-card .kpi-meta  { font-size: 12px; color: #9a9288; display: flex; align-items: center; gap: 6px; }
	.kpi-delta {
		display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 999px;
		font-size: 11px; font-weight: 600; letter-spacing: .2px;
	}
	.kpi-delta.up   { background: #e7f3ec; color: #2f7a4c; }
	.kpi-delta.down { background: #fbe4df; color: #a5432f; }
	.kpi-delta.flat { background: #f2efe7; color: #6c665e; }
	.kpi-card .kpi-cta {
		font-size: 11.5px; color: #c29e5c; font-weight: 500;
		display: inline-flex; align-items: center; gap: 4px; margin-top: 2px;
	}

	/* --- Alert "Perlu Perhatian" --- */
	.attention-card {
		background: #fff7ed; border: 1px solid #f9d8a8; border-left: 4px solid #e89740;
		border-radius: 6px; padding: 16px 20px; margin-top: 22px;
	}
	.attention-card-title {
		font-size: 14px; font-weight: 600; color: #8a4f0c;
		display: flex; align-items: center; gap: 8px; margin: 0 0 10px;
	}
	.attention-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; }
	.attention-item {
		display: flex; align-items: center; gap: 10px;
		padding: 10px 12px; background: #fff; border: 1px solid #f2dcb8; border-radius: 4px;
		text-decoration: none; color: inherit; transition: border-color .15s, transform .15s;
	}
	.attention-item:hover { border-color: #e89740; text-decoration: none; color: inherit; transform: translateX(2px); }
	.attention-item .ic { width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%; background: #fff4d6; color: #8a6b2b; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; }
	.attention-item.urgent .ic { background: #fbe4df; color: #a5432f; }
	.attention-item .body { flex: 1; min-width: 0; }
	.attention-item .body strong { font-size: 13.5px; color: #2d2a26; display: block; }
	.attention-item .body small  { font-size: 11.5px; color: #6c665e; }

	/* --- Stok rendah list --- */
	.lowstock-row {
		display: flex; align-items: center; gap: 10px; padding: 10px 0;
		border-bottom: 1px solid #f2efe7;
	}
	.lowstock-row:last-child { border-bottom: 0; }
	.lowstock-row .info { flex: 1; min-width: 0; }
	.lowstock-row .name { font-size: 13.5px; color: #2d2a26; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.lowstock-row .sku  { font-size: 11.5px; color: #9a9288; font-family: 'Consolas', monospace; }
	.lowstock-row .stock-info { text-align: right; flex-shrink: 0; }
	.lowstock-row .stock-now  { font-size: 14px; font-weight: 700; color: #a5432f; }
	.lowstock-row .stock-min  { font-size: 11px; color: #9a9288; }

	/* --- Quick actions --- */
	.quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
	.quick-action {
		display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
		padding: 18px 10px; border: 1px solid #ece8de; background: #fff;
		border-radius: 6px; color: #4d4640; text-decoration: none;
		transition: all .15s; text-align: center;
	}
	.quick-action:hover { border-color: #c29e5c; color: #c29e5c; text-decoration: none; transform: translateY(-1px); }
	.quick-action i { font-size: 18px; color: #c29e5c; }
	.quick-action span { font-size: 12.5px; font-weight: 500; }
</style>
@endpush

@section('content')

	@php
		$statusBadge = [
			'selesai'        => 'badge-success',
			'dikirim'        => 'badge-info',
			'diproses'       => 'badge-warning',
			'menunggu_bayar' => 'badge-muted',
			'dibatalkan'     => 'badge-danger',
		];

		// Hitung delta MoM (month-over-month) untuk badge perubahan persen.
		$deltaPct = function ($curr, $prev) {
			if ($prev <= 0) return $curr > 0 ? 100 : null;
			return round((($curr - $prev) / $prev) * 100, 1);
		};
		$revDelta   = $deltaPct($revenueThisMonth, $revenueLastMonth);
		$orderDelta = $deltaPct($ordersThisMonth, $ordersLastMonth);

		$totalAttention = $stuckPaymentCount + $shipmentDueCount + $lowStockCount;
	@endphp

	{{-- ========================== KPI cards (4 hero metrics) ========================== --}}
	<div class="stat-grid">
		{{-- Pendapatan bulan ini + delta MoM --}}
		<div class="kpi-card">
			<div class="kpi-head">
				<span class="kpi-label">Pendapatan {{ now()->translatedFormat('F') }}</span>
				<span class="kpi-icon green"><i class="fa fa-money"></i></span>
			</div>
			<div class="kpi-value">{{ $rupiah($revenueThisMonth) }}</div>
			<div class="kpi-meta">
				@if($revDelta === null)
					<span class="kpi-delta flat">— Belum ada data periode lalu</span>
				@elseif($revDelta > 0)
					<span class="kpi-delta up"><i class="fa fa-arrow-up"></i> {{ number_format(abs($revDelta), 1) }}%</span>
					<span>vs bulan lalu ({{ $rupiah($revenueLastMonth) }})</span>
				@elseif($revDelta < 0)
					<span class="kpi-delta down"><i class="fa fa-arrow-down"></i> {{ number_format(abs($revDelta), 1) }}%</span>
					<span>vs bulan lalu ({{ $rupiah($revenueLastMonth) }})</span>
				@else
					<span class="kpi-delta flat">= Sama dengan bulan lalu</span>
				@endif
			</div>
		</div>

		{{-- Pesanan bulan ini + delta MoM --}}
		<div class="kpi-card">
			<div class="kpi-head">
				<span class="kpi-label">Pesanan {{ now()->translatedFormat('F') }}</span>
				<span class="kpi-icon blue"><i class="fa fa-file-text-o"></i></span>
			</div>
			<div class="kpi-value">{{ $ordersThisMonth }}</div>
			<div class="kpi-meta">
				@if($orderDelta === null)
					<span class="kpi-delta flat">— Belum ada data periode lalu</span>
				@elseif($orderDelta > 0)
					<span class="kpi-delta up"><i class="fa fa-arrow-up"></i> {{ number_format(abs($orderDelta), 1) }}%</span>
					<span>vs {{ $ordersLastMonth }} bulan lalu</span>
				@elseif($orderDelta < 0)
					<span class="kpi-delta down"><i class="fa fa-arrow-down"></i> {{ number_format(abs($orderDelta), 1) }}%</span>
					<span>vs {{ $ordersLastMonth }} bulan lalu</span>
				@else
					<span class="kpi-delta flat">= Sama ({{ $ordersLastMonth }})</span>
				@endif
			</div>
		</div>

		{{-- Pesanan perlu aksi (clickable) --}}
		<a href="{{ route('admin.pesanan', ['status' => 'diproses']) }}" class="kpi-card clickable">
			<div class="kpi-head">
				<span class="kpi-label">Perlu Diproses</span>
				<span class="kpi-icon orange"><i class="fa fa-bell-o"></i></span>
			</div>
			<div class="kpi-value">{{ $pendingActionCount }}</div>
			<div class="kpi-meta">
				Menunggu bayar + diproses
			</div>
			<div class="kpi-cta">
				Lihat antrian <i class="fa fa-arrow-right"></i>
			</div>
		</a>

		{{-- Stok kritis (clickable) --}}
		<a href="{{ route('admin.laporan') }}" class="kpi-card clickable">
			<div class="kpi-head">
				<span class="kpi-label">Stok Kritis</span>
				<span class="kpi-icon {{ $lowStockCount > 0 ? 'red' : 'green' }}">
					<i class="fa {{ $lowStockCount > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
				</span>
			</div>
			<div class="kpi-value">{{ $lowStockCount }}</div>
			<div class="kpi-meta">Produk di bawah stok minimum</div>
			@if($lowStockCount > 0)
				<div class="kpi-cta">Restok sekarang <i class="fa fa-arrow-right"></i></div>
			@else
				<div class="kpi-cta" style="color:#56a676;">Semua aman</div>
			@endif
		</a>
	</div>

	{{-- ========================== "Perlu Perhatian" alert (hanya muncul kalau ada masalah) ========================== --}}
	@if($totalAttention > 0)
	<div class="attention-card">
		<h3 class="attention-card-title">
			<i class="fa fa-bullhorn"></i> Perlu Perhatian Anda
		</h3>
		<div class="attention-list">
			@if($stuckPaymentCount > 0)
				<a href="{{ route('admin.pesanan', ['status' => 'menunggu_bayar']) }}" class="attention-item urgent">
					<span class="ic"><i class="fa fa-clock-o"></i></span>
					<div class="body">
						<strong>{{ $stuckPaymentCount }} pesanan menunggu bayar > 24 jam</strong>
						<small>Pertimbangkan untuk dibatalkan atau follow-up</small>
					</div>
				</a>
			@endif
			@if($shipmentDueCount > 0)
				<a href="{{ route('admin.pesanan', ['status' => 'diproses']) }}" class="attention-item urgent">
					<span class="ic"><i class="fa fa-truck"></i></span>
					<div class="body">
						<strong>{{ $shipmentDueCount }} pesanan harus segera dikirim</strong>
						<small>Sudah dibayar > 2 hari, belum dikirim</small>
					</div>
				</a>
			@endif
			@if($lowStockCount > 0)
				<a href="{{ route('admin.laporan') }}" class="attention-item">
					<span class="ic"><i class="fa fa-archive"></i></span>
					<div class="body">
						<strong>{{ $lowStockCount }} produk stok rendah</strong>
						<small>Restok sebelum kehabisan</small>
					</div>
				</a>
			@endif
		</div>
	</div>
	@endif

	{{-- ========================== Pesanan Terbaru + Stok Rendah ========================== --}}
	<div class="row" style="margin-top: 22px;">
		<div class="col-lg-8">
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Pesanan Terbaru</h3>
						<div class="admin-card-sub">{{ $recentOrders->count() }} pesanan terakhir</div>
					</div>
					<a href="{{ route('admin.pesanan') }}" class="btn-admin btn-admin-outline btn-admin-sm">Lihat Semua <i class="fa fa-arrow-right"></i></a>
				</div>

				<table class="admin-table">
					<thead>
						<tr>
							<th>No. Pesanan</th>
							<th>Pelanggan</th>
							<th>Tanggal</th>
							<th class="num">Total</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						@forelse($recentOrders as $order)
						<tr>
							<td><a href="{{ route('admin.pesanan', ['q' => $order->invoice_number]) }}" style="color:#c29e5c; font-weight:500;">{{ $order->invoice_number }}</a></td>
							<td>{{ $order->customer_name }}</td>
							<td style="color:#9a9288; font-size:12.5px;">{{ $order->created_at->diffForHumans() }}</td>
							<td class="num" style="font-weight:500;">{{ $rupiah($order->total) }}</td>
							<td><span class="badge-pill {{ $statusBadge[$order->status] ?? 'badge-muted' }}">{{ $order->status_label }}</span></td>
						</tr>
						@empty
						<tr><td colspan="5" style="text-align:center; padding:32px; color:#9a9288;">
							<i class="fa fa-inbox" style="font-size:24px; display:block; margin-bottom:6px; color:#d8d1bf;"></i>
							Belum ada pesanan
						</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Stok Rendah</h3>
						<div class="admin-card-sub">5 produk paling sedikit</div>
					</div>
					<a href="{{ route('admin.laporan') }}" class="btn-admin btn-admin-outline btn-admin-sm" style="font-size:11px; padding:4px 10px;">Restok</a>
				</div>

				@forelse($lowStock as $p)
				<div class="lowstock-row">
					<img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="table-thumb">
					<div class="info">
						<div class="name">{{ $p->name }}</div>
						<div class="sku">{{ $p->sku }}</div>
					</div>
					<div class="stock-info">
						<div class="stock-now">{{ $p->stock }}</div>
						<div class="stock-min">min: {{ $p->stock_min }}</div>
					</div>
				</div>
				@empty
				<div style="text-align:center; padding:24px 12px; color:#9a9288;">
					<i class="fa fa-check-circle" style="font-size:24px; color:#56a676; display:block; margin-bottom:6px;"></i>
					<div style="font-size:13px;">Semua stok di atas minimum</div>
				</div>
				@endforelse
			</div>
		</div>
	</div>

	{{-- ========================== Ringkasan Stok 7 hari + Aksi Cepat ========================== --}}
	<div class="row" style="margin-top: 22px;">
		<div class="col-md-6">
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Pergerakan Stok</h3>
						<div class="admin-card-sub">7 hari terakhir</div>
					</div>
					<a href="{{ route('admin.laporan') }}" class="btn-admin btn-admin-outline btn-admin-sm">Detail</a>
				</div>
				<div style="display:flex; gap:14px;">
					<div style="flex:1; padding:16px; background:#edf7ef; border-radius:6px; border:1px solid #cfe6d6;">
						<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
							<div style="font-size:11.5px; color:#2f7a4c; letter-spacing:.5px; text-transform:uppercase; font-weight:500;">Masuk</div>
							<i class="fa fa-arrow-down" style="color:#56a676; font-size:13px;"></i>
						</div>
						<div style="font-size:24px; font-weight:700; color:#2f7a4c;">+{{ number_format($stockIn7Day, 0, ',', '.') }}</div>
						<div style="font-size:12px; color:#5b8d70; margin-top:2px;">{{ $trxIn7Day }} transaksi</div>
					</div>
					<div style="flex:1; padding:16px; background:#fbe4df; border-radius:6px; border:1px solid #f2c6be;">
						<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
							<div style="font-size:11.5px; color:#a5432f; letter-spacing:.5px; text-transform:uppercase; font-weight:500;">Keluar</div>
							<i class="fa fa-arrow-up" style="color:#d86a59; font-size:13px;"></i>
						</div>
						<div style="font-size:24px; font-weight:700; color:#a5432f;">−{{ number_format($stockOut7Day, 0, ',', '.') }}</div>
						<div style="font-size:12px; color:#a5432f; margin-top:2px;">{{ $trxOut7Day }} transaksi</div>
					</div>
				</div>
				<div style="margin-top:12px; padding:10px 12px; background:#faf7ef; border-radius:4px; font-size:12px; color:#6c665e;">
					<strong>Saldo:</strong>
					<span style="color: {{ ($stockIn7Day - $stockOut7Day) >= 0 ? '#2f7a4c' : '#a5432f' }}; font-weight:600;">
						{{ ($stockIn7Day - $stockOut7Day) >= 0 ? '+' : '' }}{{ number_format($stockIn7Day - $stockOut7Day, 0, ',', '.') }}
					</span>
					&middot; Total transaksi {{ $trxIn7Day + $trxOut7Day }}
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Aksi Cepat</h3>
						<div class="admin-card-sub">Pintasan yang sering digunakan</div>
					</div>
				</div>
				<div class="quick-actions">
					<a href="{{ route('admin.produk') }}" class="quick-action">
						<i class="fa fa-plus-circle"></i><span>Tambah Produk</span>
					</a>
					<a href="{{ route('admin.pesanan') }}" class="quick-action">
						<i class="fa fa-list-alt"></i><span>Daftar Pesanan</span>
					</a>
					<a href="{{ route('admin.laporan') }}" class="quick-action">
						<i class="fa fa-bar-chart"></i><span>Laporan Stok</span>
					</a>
					<a href="{{ route('admin.cms', ['#tab-pengiriman']) }}" class="quick-action">
						<i class="fa fa-truck"></i><span>Atur Ongkir</span>
					</a>
					<a href="{{ route('admin.cms') }}" class="quick-action">
						<i class="fa fa-pencil-square-o"></i><span>Edit Konten</span>
					</a>
					<a href="{{ route('admin.user') }}" class="quick-action">
						<i class="fa fa-users"></i><span>Kelola User</span>
					</a>
				</div>

				<div style="margin-top:14px; padding-top:12px; border-top:1px solid #f2efe7; font-size:12px; color:#9a9288; display:flex; justify-content:space-between;">
					<span>{{ $totalProducts }} produk · {{ $totalCustomers }} pelanggan aktif · {{ $totalOrders }} total pesanan</span>
				</div>
			</div>
		</div>
	</div>

@endsection
