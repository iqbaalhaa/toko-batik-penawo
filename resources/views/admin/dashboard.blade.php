@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Ringkasan aktivitas toko hari ini')

@section('content')

	@php
		$statusBadge = [
			'selesai'        => 'badge-success',
			'dikirim'        => 'badge-info',
			'diproses'       => 'badge-warning',
			'menunggu_bayar' => 'badge-muted',
			'dibatalkan'     => 'badge-danger',
		];
	@endphp

	<!-- Stat cards -->
	<div class="stat-grid">
		<div class="stat-card">
			<div class="stat-card-icon bg-brand"><i class="zmdi zmdi-shopping-basket"></i></div>
			<div>
				<div class="stat-card-label">Total Produk</div>
				<div class="stat-card-value">{{ $totalProducts }}</div>
				<div class="stat-card-trend"><i class="zmdi zmdi-trending-up"></i> Aktif di katalog</div>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-card-icon bg-blue"><i class="zmdi zmdi-receipt"></i></div>
			<div>
				<div class="stat-card-label">Total Pesanan</div>
				<div class="stat-card-value">{{ $totalOrders }}</div>
				<div class="stat-card-trend"><i class="zmdi zmdi-trending-up"></i> Semua periode</div>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-card-icon bg-green"><i class="zmdi zmdi-money"></i></div>
			<div>
				<div class="stat-card-label">Pendapatan</div>
				<div class="stat-card-value">{{ $rupiah($totalRevenue) }}</div>
				<div class="stat-card-trend"><i class="zmdi zmdi-trending-up"></i> Pesanan dikirim &amp; selesai</div>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-card-icon bg-red"><i class="zmdi zmdi-accounts"></i></div>
			<div>
				<div class="stat-card-label">Pelanggan Aktif</div>
				<div class="stat-card-value">{{ $totalCustomers }}</div>
				<div class="stat-card-trend">Akun pelanggan</div>
			</div>
		</div>
	</div>

	<div class="row" style="margin-top: 22px;">
		<!-- Pesanan Terbaru -->
		<div class="col-lg-8">
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Pesanan Terbaru</h3>
						<div class="admin-card-sub">5 pesanan terakhir dari pelanggan</div>
					</div>
					<a href="{{ route('admin.pesanan') }}" class="btn-admin btn-admin-outline btn-admin-sm">Lihat Semua</a>
				</div>

				<table class="admin-table">
					<thead>
						<tr>
							<th>No. Pesanan</th>
							<th>Pelanggan</th>
							<th>Total</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						@forelse($recentOrders as $order)
						<tr>
							<td><strong style="color:#2d2a26;">{{ $order->invoice_number }}</strong></td>
							<td>{{ $order->customer_name }}</td>
							<td>{{ $rupiah($order->total) }}</td>
							<td><span class="badge-pill {{ $statusBadge[$order->status] ?? 'badge-muted' }}">{{ $order->status_label }}</span></td>
						</tr>
						@empty
						<tr><td colspan="4" style="text-align:center; padding:24px; color:#9a9288;">Belum ada pesanan</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<!-- Produk Terlaris -->
		<div class="col-lg-4">
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Stok Terbanyak</h3>
						<div class="admin-card-sub">Top 5 produk</div>
					</div>
				</div>

				<ul style="list-style:none; padding:0; margin:0;">
					@foreach($topProducts as $i => $p)
					<li style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f2efe7;">
						<img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="table-thumb">
						<div style="flex:1; min-width:0;">
							<div style="font-size:13.5px; color:#2d2a26; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $p->name }}</div>
							<div style="font-size:12px; color:#9a9288;">Stok: {{ $p->stock }}</div>
						</div>
						<span class="badge-pill badge-brand">#{{ $i+1 }}</span>
					</li>
					@endforeach
				</ul>
			</div>
		</div>
	</div>

	<!-- Quick summary row -->
	<div class="row" style="margin-top: 22px;">
		<div class="col-md-6">
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Ringkasan Stok</h3>
						<div class="admin-card-sub">Pergerakan 7 hari terakhir</div>
					</div>
					<a href="{{ route('admin.laporan') }}" class="btn-admin btn-admin-outline btn-admin-sm">Laporan</a>
				</div>
				<div style="display:flex; gap:16px;">
					<div style="flex:1; padding:16px; background:#edf7ef; border-radius:6px;">
						<div style="font-size:12px; color:#2f7a4c; letter-spacing:.5px; text-transform:uppercase;">Barang Masuk</div>
						<div style="font-size:24px; font-weight:600; color:#2f7a4c; margin-top:4px;">+{{ $stockIn7Day }}</div>
						<div style="font-size:12px; color:#5b8d70; margin-top:2px;">dari {{ $trxIn7Day }} pengajuan</div>
					</div>
					<div style="flex:1; padding:16px; background:#fbe4df; border-radius:6px;">
						<div style="font-size:12px; color:#a5432f; letter-spacing:.5px; text-transform:uppercase;">Barang Keluar</div>
						<div style="font-size:24px; font-weight:600; color:#a5432f; margin-top:4px;">−{{ $stockOut7Day }}</div>
						<div style="font-size:12px; color:#a5432f; margin-top:2px;">dari {{ $trxOut7Day }} pesanan</div>
					</div>
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
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
					<a href="{{ route('admin.produk') }}" class="btn-admin btn-admin-outline" style="justify-content:center;">
						<i class="zmdi zmdi-plus-circle"></i> Tambah Produk
					</a>
					<a href="{{ route('admin.pesanan') }}" class="btn-admin btn-admin-outline" style="justify-content:center;">
						<i class="zmdi zmdi-receipt"></i> Proses Pesanan
					</a>
					<a href="{{ route('admin.user') }}" class="btn-admin btn-admin-outline" style="justify-content:center;">
						<i class="zmdi zmdi-account-add"></i> Tambah User
					</a>
					<a href="{{ route('admin.cms') }}" class="btn-admin btn-admin-outline" style="justify-content:center;">
						<i class="zmdi zmdi-edit"></i> Edit Konten
					</a>
				</div>
			</div>
		</div>
	</div>

@endsection
