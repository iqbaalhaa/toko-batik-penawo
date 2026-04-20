@extends('layouts.admin')

@section('title', 'Pesanan')
@section('page_title', 'Pesanan')
@section('page_subtitle', 'Kelola pesanan pelanggan dan pantau status pengiriman')

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

	<!-- Summary -->
	<div class="stat-grid" style="margin-bottom:22px;">
		<div class="stat-card">
			<div class="stat-card-icon bg-brand"><i class="fa fa-file-text-o"></i></div>
			<div>
				<div class="stat-card-label">Total Pesanan</div>
				<div class="stat-card-value">{{ $orders->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-blue"><i class="fa fa-truck"></i></div>
			<div>
				<div class="stat-card-label">Perlu Diproses</div>
				<div class="stat-card-value">{{ $orders->whereIn('status', ['diproses','menunggu_bayar'])->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-green"><i class="fa fa-check-square-o"></i></div>
			<div>
				<div class="stat-card-label">Selesai</div>
				<div class="stat-card-value">{{ $orders->where('status','selesai')->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-red"><i class="fa fa-times-circle"></i></div>
			<div>
				<div class="stat-card-label">Dibatalkan</div>
				<div class="stat-card-value">{{ $orders->where('status','dibatalkan')->count() }}</div>
			</div>
		</div>
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Daftar Pesanan</h3>
				<div class="admin-card-sub">Urutan terbaru ke terlama</div>
			</div>
		</div>

		<div class="toolbar">
			<div class="toolbar-search">
				<i class="fa fa-search"></i>
				<input type="text" class="form-control-admin" placeholder="Cari nomor pesanan atau nama pelanggan...">
			</div>
			<select class="form-control-admin" style="max-width:170px;">
				<option>Semua Status</option>
				<option>Menunggu Bayar</option>
				<option>Diproses</option>
				<option>Dikirim</option>
				<option>Selesai</option>
				<option>Dibatalkan</option>
			</select>
			<input type="date" class="form-control-admin" style="max-width:160px;">
			<button type="button" class="btn-admin btn-admin-outline"><i class="fa fa-download"></i> Ekspor</button>
		</div>

		<div style="overflow-x:auto;">
			<table class="admin-table">
				<thead>
					<tr>
						<th>No. Pesanan</th>
						<th>Tanggal</th>
						<th>Pelanggan</th>
						<th>Item</th>
						<th>Total</th>
						<th>Pembayaran</th>
						<th>Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@forelse($orders as $order)
					<tr>
						<td><strong style="color:#2d2a26;">{{ $order->invoice_number }}</strong></td>
						<td>{{ $order->created_at->format('d M Y') }}</td>
						<td>
							<div style="font-weight:500;">{{ $order->customer_name }}</div>
							<div style="font-size:12px; color:#9a9288;">{{ $order->customer_email }}</div>
						</td>
						<td>{{ $order->items->count() }} item</td>
						<td><strong>{{ $rupiah($order->total) }}</strong></td>
						<td>{{ $order->payment_method ?? '—' }}</td>
						<td><span class="badge-pill {{ $statusBadge[$order->status] ?? 'badge-muted' }}">{{ $order->status_label }}</span></td>
						<td>
							<a href="#" class="btn-admin-icon" title="Detail"><i class="fa fa-eye"></i></a>
							<a href="#" class="btn-admin-icon" title="Cetak Invoice"><i class="fa fa-print"></i></a>
							<a href="#" class="btn-admin-icon" title="Update Status"><i class="fa fa-pencil-square-o"></i></a>
						</td>
					</tr>
					@empty
					<tr><td colspan="8" style="text-align:center; padding:24px; color:#9a9288;">Belum ada pesanan</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px; border-top:1px solid #f2efe7; margin-top:8px;">
			<div style="font-size:13px; color:#9a9288;">Menampilkan 1 - {{ $orders->count() }} dari {{ $orders->count() }} pesanan</div>
			<div>
				<button type="button" class="btn-admin-icon" disabled><i class="fa fa-chevron-left"></i></button>
				<button type="button" class="btn-admin btn-admin-sm">1</button>
				<button type="button" class="btn-admin-icon"><i class="fa fa-chevron-right"></i></button>
			</div>
		</div>
	</div>
@endsection
