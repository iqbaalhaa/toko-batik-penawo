@extends('layouts.admin')

@section('title', 'Kelola User')
@section('page_title', 'Kelola User')
@section('page_subtitle', 'Kelola akun admin dan pelanggan toko')

@section('content')
	@php
		$roleBadge = ['admin' => 'badge-danger', 'staff' => 'badge-info', 'pelanggan' => 'badge-brand'];
		$roleLabel = ['admin' => 'Admin', 'staff' => 'Staff', 'pelanggan' => 'Pelanggan'];
		$registeredThisMonth = $users->filter(fn($u) => $u->created_at && $u->created_at->isSameMonth(now()))->count();
	@endphp

	<div class="stat-grid" style="margin-bottom:22px;">
		<div class="stat-card">
			<div class="stat-card-icon bg-brand"><i class="fa fa-users"></i></div>
			<div>
				<div class="stat-card-label">Total User</div>
				<div class="stat-card-value">{{ $users->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-red"><i class="fa fa-shield"></i></div>
			<div>
				<div class="stat-card-label">Admin &amp; Staff</div>
				<div class="stat-card-value">{{ $users->whereIn('role', ['admin','staff'])->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-green"><i class="fa fa-user-circle"></i></div>
			<div>
				<div class="stat-card-label">Pelanggan</div>
				<div class="stat-card-value">{{ $users->where('role','pelanggan')->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-blue"><i class="fa fa-user-plus"></i></div>
			<div>
				<div class="stat-card-label">Registrasi Bulan Ini</div>
				<div class="stat-card-value">{{ $registeredThisMonth }}</div>
				<div class="stat-card-trend"><i class="fa fa-calendar"></i> {{ now()->translatedFormat('F Y') }}</div>
			</div>
		</div>
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Daftar User</h3>
				<div class="admin-card-sub">Kelola akses akun pengguna</div>
			</div>
			<button type="button" class="btn-admin"><i class="fa fa-user-plus"></i> Tambah User</button>
		</div>

		<div class="toolbar">
			<div class="toolbar-search">
				<i class="fa fa-search"></i>
				<input type="text" class="form-control-admin" placeholder="Cari nama atau email...">
			</div>
			<select class="form-control-admin" style="max-width:160px;">
				<option>Semua Peran</option>
				<option>Admin</option>
				<option>Staff</option>
				<option>Pelanggan</option>
			</select>
			<select class="form-control-admin" style="max-width:150px;">
				<option>Semua Status</option>
				<option>Aktif</option>
				<option>Nonaktif</option>
			</select>
		</div>

		<div style="overflow-x:auto;">
			<table class="admin-table">
				<thead>
					<tr>
						<th>User</th>
						<th>Peran</th>
						<th>Bergabung</th>
						<th>Total Pesanan</th>
						<th>Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@forelse($users as $u)
					<tr>
						<td>
							<div style="display:flex; align-items:center; gap:12px;">
								<div style="width:38px; height:38px; border-radius:50%; background:#c29e5c; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:13px;">
									{{ strtoupper(substr($u->name,0,2)) }}
								</div>
								<div>
									<div style="font-weight:500; color:#2d2a26;">{{ $u->name }}</div>
									<div style="font-size:12px; color:#9a9288;">{{ $u->email }}</div>
								</div>
							</div>
						</td>
						<td><span class="badge-pill {{ $roleBadge[$u->role] ?? 'badge-muted' }}">{{ $roleLabel[$u->role] ?? $u->role }}</span></td>
						<td>{{ $u->created_at?->format('d M Y') ?? '—' }}</td>
						<td>{{ $u->orders_count ?? 0 }}</td>
						<td>
							@if($u->status === 'aktif')
								<span class="badge-pill badge-success">Aktif</span>
							@else
								<span class="badge-pill badge-muted">Nonaktif</span>
							@endif
						</td>
						<td>
							<a href="#" class="btn-admin-icon" title="Detail"><i class="fa fa-eye"></i></a>
							<a href="#" class="btn-admin-icon" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
							<a href="#" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></a>
						</td>
					</tr>
					@empty
					<tr><td colspan="6" style="text-align:center; padding:24px; color:#9a9288;">Belum ada user</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px; border-top:1px solid #f2efe7; margin-top:8px;">
			<div style="font-size:13px; color:#9a9288;">Menampilkan 1 - {{ $users->count() }} dari {{ $users->count() }} user</div>
			<div>
				<button type="button" class="btn-admin-icon" disabled><i class="fa fa-chevron-left"></i></button>
				<button type="button" class="btn-admin btn-admin-sm">1</button>
				<button type="button" class="btn-admin-icon"><i class="fa fa-chevron-right"></i></button>
			</div>
		</div>
	</div>
@endsection
