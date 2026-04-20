@extends('layouts.admin')

@section('title', 'Laporan Stok')
@section('page_title', 'Laporan Barang Masuk &amp; Keluar')
@section('page_subtitle', 'Pantau mutasi stok produk batik')

@section('content')
	@php
		$totalIn  = $movements->where('type','masuk')->sum('qty');
		$totalOut = $movements->where('type','keluar')->sum('qty');
		$trxIn    = $movements->where('type','masuk')->count();
		$trxOut   = $movements->where('type','keluar')->count();
	@endphp

	<!-- Summary -->
	<div class="stat-grid" style="margin-bottom:22px;">
		<div class="stat-card">
			<div class="stat-card-icon bg-green"><i class="fa fa-download"></i></div>
			<div>
				<div class="stat-card-label">Barang Masuk</div>
				<div class="stat-card-value">+{{ $totalIn }}</div>
				<div class="stat-card-trend">{{ $trxIn }} transaksi</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-red"><i class="fa fa-upload"></i></div>
			<div>
				<div class="stat-card-label">Barang Keluar</div>
				<div class="stat-card-value">&minus;{{ $totalOut }}</div>
				<div class="stat-card-trend down">{{ $trxOut }} transaksi</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-brand"><i class="fa fa-exchange"></i></div>
			<div>
				<div class="stat-card-label">Saldo Periode</div>
				<div class="stat-card-value">{{ ($totalIn - $totalOut) >= 0 ? '+' : '' }}{{ $totalIn - $totalOut }}</div>
				<div class="stat-card-trend">Selisih masuk - keluar</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-blue"><i class="fa fa-exclamation-triangle"></i></div>
			<div>
				<div class="stat-card-label">Stok Kritis</div>
				<div class="stat-card-value">{{ $lowStock->count() }}</div>
				<div class="stat-card-trend down">Produk di bawah stok minimum</div>
			</div>
		</div>
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Mutasi Stok</h3>
				<div class="admin-card-sub">Riwayat barang masuk dan keluar</div>
			</div>
			<div>
				<button type="button" class="btn-admin btn-admin-outline"><i class="fa fa-print"></i> Cetak</button>
				<button type="button" class="btn-admin"><i class="fa fa-file-text-o"></i> Ekspor PDF</button>
			</div>
		</div>

		<div class="toolbar">
			<div class="toolbar-search">
				<i class="fa fa-search"></i>
				<input type="text" class="form-control-admin" placeholder="Cari berdasarkan SKU atau nama produk...">
			</div>
			<select class="form-control-admin" style="max-width:160px;">
				<option>Semua Jenis</option>
				<option>Barang Masuk</option>
				<option>Barang Keluar</option>
			</select>
			<input type="date" class="form-control-admin" style="max-width:150px;" title="Dari">
			<input type="date" class="form-control-admin" style="max-width:150px;" title="Sampai">
			<button type="button" class="btn-admin btn-admin-outline">Terapkan</button>
		</div>

		<div style="overflow-x:auto;">
			<table class="admin-table">
				<thead>
					<tr>
						<th>Tanggal</th>
						<th>SKU</th>
						<th>Produk</th>
						<th>Jenis</th>
						<th>Jumlah</th>
						<th>Referensi</th>
						<th>Keterangan</th>
					</tr>
				</thead>
				<tbody>
					@forelse($movements as $m)
					<tr>
						<td>{{ $m->occurred_at?->format('d M Y') ?? '—' }}</td>
						<td><code style="background:#f5f2ea; padding:2px 6px; border-radius:3px; font-size:12px;">{{ $m->product?->sku ?? '—' }}</code></td>
						<td style="font-weight:500;">{{ $m->product?->name ?? '—' }}</td>
						<td>
							@if($m->type === 'masuk')
								<span class="badge-pill badge-success"><i class="fa fa-arrow-down"></i> Masuk</span>
							@else
								<span class="badge-pill badge-danger"><i class="fa fa-arrow-up"></i> Keluar</span>
							@endif
						</td>
						<td>
							<strong style="color: {{ $m->type === 'masuk' ? '#2f7a4c' : '#a5432f' }};">
								{{ $m->type === 'masuk' ? '+' : '−' }}{{ $m->qty }}
							</strong>
						</td>
						<td style="font-size:12.5px; color:#6c665e;">{{ $m->reference ?? '—' }}</td>
						<td style="font-size:12.5px; color:#6c665e;">{{ $m->note }}</td>
					</tr>
					@empty
					<tr><td colspan="7" style="text-align:center; padding:24px; color:#9a9288;">Belum ada mutasi stok</td></tr>
					@endforelse
				</tbody>
				<tfoot>
					<tr style="background:#faf7ef;">
						<td colspan="4" style="text-align:right; font-weight:600; color:#2d2a26;">Total Periode:</td>
						<td colspan="3">
							<span style="color:#2f7a4c; font-weight:600; margin-right:16px;">Masuk: +{{ $totalIn }}</span>
							<span style="color:#a5432f; font-weight:600;">Keluar: −{{ $totalOut }}</span>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Peringatan Stok Rendah</h3>
				<div class="admin-card-sub">Produk yang perlu segera direstok</div>
			</div>
		</div>
		<table class="admin-table">
			<thead>
				<tr>
					<th>Produk</th>
					<th>SKU</th>
					<th>Stok Tersisa</th>
					<th>Stok Minimum</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				@forelse($lowStock as $l)
				<tr>
					<td style="font-weight:500;">{{ $l->name }}</td>
					<td><code style="background:#f5f2ea; padding:2px 6px; border-radius:3px; font-size:12px;">{{ $l->sku }}</code></td>
					<td><strong style="color:#a5432f;">{{ $l->stock }}</strong></td>
					<td>{{ $l->stock_min }}</td>
					<td><button type="button" class="btn-admin btn-admin-sm"><i class="fa fa-plus"></i> Restok</button></td>
				</tr>
				@empty
				<tr><td colspan="5" style="text-align:center; padding:16px; color:#9a9288;">Semua produk stok aman</td></tr>
				@endforelse
			</tbody>
		</table>
	</div>
@endsection
