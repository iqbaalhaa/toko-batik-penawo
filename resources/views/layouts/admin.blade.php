<!DOCTYPE html>
<html lang="id">
<head>
	<title>@yield('title', 'Admin') &mdash; Batik Penawo</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/x-icon" href="{{ asset('image/favicon.ico') }}"/>

	<!-- Inter (modern dashboard font) -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/util.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/main.css') }}">
	<style>
		:root {
			--brand: #c29e5c;
			--brand-dark: #a88541;
			--sidebar-bg: #1f1d1b;
			--sidebar-hover: #2a2725;
			--sidebar-text: #d8d4cb;
			--page-bg: #f5f2ea;
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
			font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11', 'ss01';
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			background: var(--page-bg);
			color: #2d2a26;
			min-height: 100vh;
			letter-spacing: -0.005em;
		}
		/* Tabular numerals for tables, stats, prices — lines up digits cleanly */
		.admin-table td, .admin-table th,
		.stat-card-value, code, .admin-user-avatar {
			font-variant-numeric: tabular-nums;
		}
		/* Admin-scoped heading weight adjustment for Inter's natural scale */
		.admin-card-title, .admin-topbar-title, .stat-card-value { letter-spacing: -0.015em; }
		a { text-decoration: none; }
		.admin-wrap { display: flex; min-height: 100vh; }

		/* Sidebar */
		.admin-sidebar {
			width: 250px;
			background: var(--sidebar-bg);
			color: var(--sidebar-text);
			flex-shrink: 0;
			display: flex;
			flex-direction: column;
			position: sticky;
			top: 0;
			height: 100vh;
			overflow-y: auto;
		}
		.admin-sidebar-brand {
			padding: 22px 24px;
			border-bottom: 1px solid #2e2b28;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.admin-sidebar-brand img { width: 32px; height: 32px; object-fit: contain; }
		.admin-sidebar-brand-text { color: #fff; font-weight: 600; font-size: 15px; letter-spacing: .5px; }
		.admin-sidebar-brand-sub { color: var(--brand); font-size: 11px; letter-spacing: 2px; text-transform: uppercase; }

		.admin-sidebar-section { padding: 18px 22px 8px; font-size: 11px; letter-spacing: 2px; color: #6c665e; text-transform: uppercase; }
		.admin-sidebar-menu { list-style: none; margin: 0; padding: 0; }
		.admin-sidebar-menu li a {
			display: flex;
			align-items: center;
			padding: 11px 24px;
			color: var(--sidebar-text);
			font-size: 14px;
			transition: background .15s, color .15s, border-color .15s;
			border-left: 3px solid transparent;
		}
		.admin-sidebar-menu li a i { width: 22px; font-size: 17px; margin-right: 12px; }
		.admin-sidebar-menu li a:hover { background: var(--sidebar-hover); color: #fff; }
		.admin-sidebar-menu li.active a {
			background: var(--sidebar-hover);
			color: #fff;
			border-left-color: var(--brand);
		}
		.admin-sidebar-footer {
			margin-top: auto;
			padding: 16px 22px;
			border-top: 1px solid #2e2b28;
			font-size: 12px;
			color: #7c756c;
		}

		/* Main */
		.admin-main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
		.admin-topbar {
			background: #fff;
			border-bottom: 1px solid #ece8de;
			padding: 0 28px;
			height: 64px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			position: sticky;
			top: 0;
			z-index: 50;
		}
		.admin-topbar-title { font-size: 17px; font-weight: 600; color: #2d2a26; }
		.admin-topbar-title small { color: #9a9288; font-weight: 400; font-size: 12.5px; display: block; margin-top: 2px; letter-spacing: .2px; }
		.admin-topbar-right { display: flex; align-items: center; gap: 18px; }
		.admin-topbar-icon {
			width: 38px; height: 38px;
			display: inline-flex; align-items: center; justify-content: center;
			background: #f5f2ea; color: #6c665e; border-radius: 50%;
			font-size: 17px; transition: background .15s, color .15s;
			position: relative;
		}
		.admin-topbar-icon:hover { background: var(--brand); color: #fff; }
		.admin-topbar-icon .dot {
			position: absolute; top: 8px; right: 9px;
			width: 8px; height: 8px; border-radius: 50%; background: #e0533e; border: 2px solid #fff;
		}
		.admin-topbar-icon .count-badge {
			position: absolute; top: 4px; right: 2px;
			min-width: 18px; height: 18px; padding: 0 5px;
			border-radius: 999px;
			background: #e0533e; color: #fff;
			font-size: 10.5px; font-weight: 600;
			display: inline-flex; align-items: center; justify-content: center;
			border: 2px solid #fff;
			font-variant-numeric: tabular-nums;
		}

		/* Sidebar badge */
		.sidebar-badge {
			margin-left: auto;
			min-width: 22px; height: 20px; padding: 0 7px;
			border-radius: 999px;
			background: #e0533e; color: #fff;
			font-size: 11px; font-weight: 600;
			display: inline-flex; align-items: center; justify-content: center;
			font-variant-numeric: tabular-nums;
		}
		@media (max-width: 900px) {
			.sidebar-badge { display: none; }
		}

		/* Notifikasi dropdown */
		.admin-notif-wrap { position: relative; }
		.admin-notif-trigger {
			background: none; border: 0; padding: 0;
			width: 38px; height: 38px;
			display: inline-flex; align-items: center; justify-content: center;
			background: #f5f2ea; color: #6c665e; border-radius: 50%;
			font-size: 17px; cursor: pointer;
			transition: background .15s, color .15s;
			position: relative;
		}
		.admin-notif-trigger:hover { background: var(--brand); color: #fff; }
		.admin-notif-dropdown {
			position: absolute; top: calc(100% + 6px); right: 0;
			width: 340px; background: #fff;
			border: 1px solid #ece8de; border-radius: 8px;
			box-shadow: 0 10px 30px rgba(0,0,0,.10);
			z-index: 1100;
			opacity: 0; visibility: hidden; transform: translateY(-6px);
			transition: opacity .15s, transform .15s, visibility 0s linear .15s;
			overflow: hidden;
		}
		.admin-notif-wrap.open .admin-notif-dropdown {
			opacity: 1; visibility: visible; transform: translateY(0);
			transition: opacity .15s, transform .15s;
		}
		.admin-notif-dropdown:before {
			content: ''; position: absolute; top: -7px; right: 14px;
			width: 12px; height: 12px; background: #fff;
			border-top: 1px solid #ece8de; border-left: 1px solid #ece8de;
			transform: rotate(45deg);
		}
		.admin-notif-head {
			padding: 14px 16px; display: flex; justify-content: space-between; align-items: center;
			border-bottom: 1px solid #f2efe7; background: #faf7ef;
		}
		.admin-notif-head strong { font-size: 13.5px; color: #2d2a26; }
		.admin-notif-head .head-meta { font-size: 11.5px; color: #9a9288; }
		.admin-notif-list { list-style: none; margin: 0; padding: 0; max-height: 360px; overflow-y: auto; }
		.admin-notif-item {
			display: flex; gap: 12px; padding: 12px 16px;
			border-bottom: 1px solid #f2efe7; text-decoration: none;
			transition: background .12s;
		}
		.admin-notif-item:hover { background: #faf7ef; text-decoration: none; }
		.admin-notif-item:last-child { border-bottom: 0; }
		.admin-notif-icon {
			width: 36px; height: 36px; flex-shrink: 0;
			border-radius: 50%; background: #f5ecd7; color: #8a6b2b;
			display: inline-flex; align-items: center; justify-content: center;
			font-size: 14px;
		}
		.admin-notif-icon.diproses { background: #fcf1d9; color: #a87318; }
		.admin-notif-body { flex: 1; min-width: 0; }
		.admin-notif-title { font-size: 13px; color: #2d2a26; font-weight: 500; }
		.admin-notif-meta { font-size: 11.5px; color: #9a9288; margin-top: 2px; display: flex; gap: 6px; flex-wrap: wrap; }
		.admin-notif-amount { color: #c29e5c; font-weight: 600; font-size: 12px; white-space: nowrap; align-self: flex-start; padding-top: 2px; }
		.admin-notif-empty {
			padding: 28px 16px; text-align: center;
			color: #9a9288; font-size: 12.5px;
		}
		.admin-notif-empty i { font-size: 28px; color: #d8d1bf; display: block; margin-bottom: 8px; }
		.admin-notif-foot {
			padding: 10px 16px; text-align: center;
			border-top: 1px solid #f2efe7; background: #faf7ef;
		}
		.admin-notif-foot a { font-size: 12.5px; color: var(--brand); font-weight: 500; }
		.admin-notif-foot a:hover { color: var(--brand-dark); text-decoration: none; }
		.admin-user { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 4px 8px 4px 4px; border-radius: 999px; transition: background .15s; }
		.admin-user:hover { background: #faf6ed; }
		.admin-user-caret { color: #9a9288; font-size: 11px; margin-left: 2px; transition: transform .2s; }
		.admin-user-wrap { position: relative; }
		.admin-user-wrap.open .admin-user-caret { transform: rotate(180deg); }
		.admin-user-dropdown {
			position: absolute; top: calc(100% + 6px); right: 0;
			width: 240px; background: #fff;
			border: 1px solid #ece8de; border-radius: 8px;
			box-shadow: 0 10px 30px rgba(0,0,0,.10);
			padding: 6px 0; z-index: 1100;
			opacity: 0; visibility: hidden; transform: translateY(-6px);
			transition: opacity .15s, transform .15s, visibility 0s linear .15s;
		}
		.admin-user-wrap.open .admin-user-dropdown {
			opacity: 1; visibility: visible; transform: translateY(0);
			transition: opacity .15s, transform .15s;
		}
		.admin-user-dropdown:before {
			content: ''; position: absolute; top: -7px; right: 14px;
			width: 12px; height: 12px; background: #fff;
			border-top: 1px solid #ece8de; border-left: 1px solid #ece8de;
			transform: rotate(45deg);
		}
		.admin-user-dropdown-head { padding: 12px 16px; border-bottom: 1px solid #f2efe7; }
		.admin-user-dropdown-name { font-size: 13.5px; font-weight: 600; color: #2d2a26; }
		.admin-user-dropdown-email { font-size: 12px; color: #9a9288; padding-top: 2px; word-break: break-all; }
		.admin-user-dropdown-link {
			display: flex; align-items: center; gap: 10px;
			padding: 10px 16px; font-size: 13.5px; color: #4d4640;
			text-decoration: none; cursor: pointer;
			background: none; border: 0; width: 100%; text-align: left;
			font-family: inherit;
			transition: background .12s, color .12s;
		}
		.admin-user-dropdown-link:hover { background: #faf6ed; color: var(--brand); text-decoration: none; }
		.admin-user-dropdown-link i { width: 16px; text-align: center; font-size: 14px; }
		.admin-user-dropdown-link.danger { color: #a5432f; }
		.admin-user-dropdown-link.danger:hover { background: #fbe4df; color: #a5432f; }
		.admin-user-dropdown-divider { height: 1px; background: #f2efe7; margin: 4px 0; }
		.admin-user-avatar {
			width: 38px; height: 38px; border-radius: 50%;
			background: var(--brand); color: #fff;
			display: inline-flex; align-items: center; justify-content: center;
			font-weight: 600; font-size: 14px;
		}
		.admin-user-meta { line-height: 1.2; }
		.admin-user-name { font-size: 13.5px; font-weight: 600; color: #2d2a26; }
		.admin-user-role { font-size: 11.5px; color: #9a9288; }
		.admin-logout-btn { background: none; border: 0; padding: 0; cursor: pointer; color: #9a9288; font-size: 13px; }
		.admin-logout-btn:hover { color: var(--brand); }

		.admin-content { padding: 28px; flex: 1; }

		/* Cards */
		.admin-card {
			background: #fff;
			border: 1px solid #ece8de;
			border-radius: 6px;
			padding: 22px 24px;
		}
		.admin-card + .admin-card { margin-top: 22px; }
		.admin-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
		.admin-card-title { font-size: 15.5px; font-weight: 600; color: #2d2a26; margin: 0; }
		.admin-card-sub { font-size: 12.5px; color: #9a9288; }

		/* Stat cards */
		.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; }
		.stat-card {
			background: #fff;
			border: 1px solid #ece8de;
			border-radius: 6px;
			padding: 20px 22px;
			display: flex;
			align-items: center;
			gap: 16px;
		}
		.stat-card-icon {
			width: 52px; height: 52px; flex-shrink: 0;
			display: inline-flex; align-items: center; justify-content: center;
			border-radius: 8px; font-size: 22px; color: #fff;
		}
		.stat-card-icon.bg-brand { background: var(--brand); }
		.stat-card-icon.bg-blue { background: #4f79c2; }
		.stat-card-icon.bg-green { background: #56a676; }
		.stat-card-icon.bg-red { background: #d86a59; }
		.stat-card-label { font-size: 12.5px; color: #9a9288; text-transform: uppercase; letter-spacing: 1px; }
		.stat-card-value { font-size: 22px; font-weight: 600; color: #2d2a26; margin-top: 2px; }
		.stat-card-trend { font-size: 12px; color: #56a676; margin-top: 3px; }
		.stat-card-trend.down { color: #d86a59; }

		/* Tables */
		.admin-table { width: 100%; border-collapse: collapse; }
		.admin-table th {
			text-align: left;
			padding: 12px 14px;
			font-size: 12px;
			color: #6c665e;
			text-transform: uppercase;
			letter-spacing: .8px;
			background: #faf7ef;
			border-bottom: 1px solid #ece8de;
			font-weight: 600;
		}
		.admin-table td {
			padding: 14px;
			font-size: 13.5px;
			border-bottom: 1px solid #f2efe7;
			color: #4d4640;
			vertical-align: middle;
		}
		.admin-table tbody tr:hover { background: #fbf8f1; }
		.admin-table tbody tr:last-child td { border-bottom: 0; }

		.table-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 4px; background: #f2efe7; }

		/* Badges */
		.badge-pill {
			display: inline-block;
			padding: 4px 10px;
			border-radius: 999px;
			font-size: 11.5px;
			font-weight: 500;
			letter-spacing: .3px;
		}
		.badge-success { background: #e3f3e9; color: #2f7a4c; }
		.badge-warning { background: #fcf1d9; color: #a87318; }
		.badge-danger  { background: #fbe4df; color: #a5432f; }
		.badge-info    { background: #e1ecf8; color: #3a5fa0; }
		.badge-muted   { background: #ece8de; color: #6c665e; }
		.badge-brand   { background: #f5ecd7; color: #8a6b2b; }

		/* Buttons */
		.btn-admin {
			display: inline-flex; align-items: center; gap: 6px;
			padding: 9px 16px; border-radius: 4px; font-size: 13px; font-weight: 500;
			border: 1px solid transparent; cursor: pointer; transition: background .15s, color .15s, border-color .15s;
			background: var(--brand); color: #fff;
		}
		.btn-admin:hover { background: var(--brand-dark); color: #fff; text-decoration: none; }
		.btn-admin-outline { background: #fff; color: #4d4640; border-color: #ddd6c6; }
		.btn-admin-outline:hover { background: #faf7ef; color: var(--brand); border-color: var(--brand); }
		.btn-admin-sm { padding: 6px 10px; font-size: 12px; }
		.btn-admin-icon {
			width: 32px; height: 32px; padding: 0;
			display: inline-flex; align-items: center; justify-content: center;
			background: #faf7ef; color: #6c665e; border-radius: 4px;
			border: 1px solid transparent;
		}
		.btn-admin-icon:hover { background: var(--brand); color: #fff; }
		.btn-admin-icon.danger:hover { background: #d86a59; color: #fff; }

		/* Forms */
		.form-control-admin {
			width: 100%;
			padding: 9px 12px;
			font-size: 13.5px;
			border: 1px solid #e0dbcf;
			border-radius: 4px;
			background: #fff;
			transition: border-color .15s, box-shadow .15s;
		}
		.form-control-admin:focus {
			outline: none;
			border-color: var(--brand);
			box-shadow: 0 0 0 3px rgba(194,158,92,.15);
		}
		.form-label-admin { font-size: 12.5px; font-weight: 600; color: #4d4640; margin-bottom: 6px; display: block; letter-spacing: .2px; }

		.toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 18px; }
		.toolbar-search { flex: 1; min-width: 220px; position: relative; }
		.toolbar-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9a9288; }
		.toolbar-search input { padding-left: 34px; }

		/* Flash */
		.flash {
			background: #edf7ef; border: 1px solid #cfe6d6; color: #2f7a4c;
			padding: 10px 14px; border-radius: 4px; font-size: 13px; margin-bottom: 16px;
		}

		/* Responsive */
		@media (max-width: 900px) {
			.admin-sidebar { width: 70px; }
			.admin-sidebar-brand-text, .admin-sidebar-brand-sub, .admin-sidebar-section,
			.admin-sidebar-menu li a span, .admin-sidebar-footer { display: none; }
			.admin-sidebar-menu li a { justify-content: center; padding: 12px 0; }
			.admin-sidebar-menu li a i { margin: 0; }
		}
		@media (max-width: 640px) {
			.admin-content { padding: 18px; }
			.admin-topbar { padding: 0 14px; }
			.admin-user-meta { display: none; }
		}
	</style>
	@stack('styles')
</head>
<body>

<div class="admin-wrap">

	<!-- Sidebar -->
	<aside class="admin-sidebar">
		<div class="admin-sidebar-brand">
			<img src="{{ asset('image/logo.png') }}" alt="Batik Penawo">
			<div>
				<div class="admin-sidebar-brand-text">Batik Penawo</div>
				<div class="admin-sidebar-brand-sub">Admin</div>
			</div>
		</div>

		<div class="admin-sidebar-section">Menu Utama</div>
		<ul class="admin-sidebar-menu">
			<li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
				<a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i><span>Dashboard</span></a>
			</li>
			<li class="{{ request()->routeIs('admin.produk*') ? 'active' : '' }}">
				<a href="{{ route('admin.produk') }}"><i class="fa fa-shopping-basket"></i><span>Kelola Produk</span></a>
			</li>
			<li>
				<a href="{{ route('admin.cms') }}#tab-kategori"><i class="fa fa-tags"></i><span>Kategori</span></a>
			</li>
			<li class="{{ request()->routeIs('admin.pesanan*') ? 'active' : '' }}">
				<a href="{{ route('admin.pesanan') }}">
					<i class="fa fa-file-text-o"></i><span>Pesanan</span>
					@if(($pendingOrdersCount ?? 0) > 0)
						<span class="sidebar-badge">{{ $pendingOrdersCount > 99 ? '99+' : $pendingOrdersCount }}</span>
					@endif
				</a>
			</li>
			<li class="{{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
				<a href="{{ route('admin.laporan') }}"><i class="fa fa-bar-chart"></i><span>Laporan Stok</span></a>
			</li>
		</ul>

		<div class="admin-sidebar-section">Pengaturan</div>
		<ul class="admin-sidebar-menu">
			<li class="{{ request()->routeIs('admin.user*') ? 'active' : '' }}">
				<a href="{{ route('admin.user') }}"><i class="fa fa-users"></i><span>Kelola User</span></a>
			</li>
			<li class="{{ request()->routeIs('admin.cms*') ? 'active' : '' }}">
				<a href="{{ route('admin.cms') }}"><i class="fa fa-file-text-o"></i><span>CMS</span></a>
			</li>
		</ul>

		<div class="admin-sidebar-footer">
			<a href="{{ url('/') }}" style="color:#c29e5c;"><i class="fa fa-external-link"></i> Lihat Toko</a>
		</div>
	</aside>

	<!-- Main -->
	<div class="admin-main">
		<header class="admin-topbar">
			<div class="admin-topbar-title">
				@yield('page_title', 'Dashboard')
				<small>@yield('page_subtitle', 'Selamat datang kembali di panel admin')</small>
			</div>

			<div class="admin-topbar-right">
				<div class="admin-notif-wrap" id="adminNotifWrap">
					<button type="button" class="admin-notif-trigger" id="adminNotifTrigger" title="Notifikasi pesanan masuk">
						<i class="fa fa-bell-o"></i>
						@if(($pendingOrdersCount ?? 0) > 0)
							<span class="count-badge">{{ $pendingOrdersCount > 99 ? '99+' : $pendingOrdersCount }}</span>
						@endif
					</button>

					<div class="admin-notif-dropdown">
						<div class="admin-notif-head">
							<strong>Pesanan Masuk</strong>
							<span class="head-meta">{{ $pendingOrdersCount ?? 0 }} perlu diproses</span>
						</div>

						<ul class="admin-notif-list">
							@forelse($recentPendingOrders ?? [] as $o)
								@php
									$isPaid = $o->status === 'diproses';
								@endphp
								<a href="{{ route('admin.pesanan') }}?q={{ $o->invoice_number }}" class="admin-notif-item">
									<div class="admin-notif-icon {{ $isPaid ? 'diproses' : '' }}">
										<i class="fa {{ $isPaid ? 'fa-check' : 'fa-clock-o' }}"></i>
									</div>
									<div class="admin-notif-body">
										<div class="admin-notif-title">{{ $o->customer_name }}</div>
										<div class="admin-notif-meta">
											<span>{{ $o->invoice_number }}</span>
											<span>·</span>
											<span>{{ $isPaid ? 'Sudah dibayar' : 'Menunggu bayar' }}</span>
											<span>·</span>
											<span>{{ $o->created_at->diffForHumans() }}</span>
										</div>
									</div>
									<div class="admin-notif-amount">Rp{{ number_format($o->total, 0, ',', '.') }}</div>
								</a>
							@empty
								<li class="admin-notif-empty">
									<i class="fa fa-inbox"></i>
									Belum ada pesanan baru.
								</li>
							@endforelse
						</ul>

						@if(($pendingOrdersCount ?? 0) > 0)
							<div class="admin-notif-foot">
								<a href="{{ route('admin.pesanan') }}">Lihat semua pesanan &rarr;</a>
							</div>
						@endif
					</div>
				</div>

				<a href="#" class="admin-topbar-icon" title="Pesan">
					<i class="fa fa-envelope-o"></i>
				</a>

				<div class="admin-user-wrap" id="adminUserWrap">
					<div class="admin-user" id="adminUserTrigger">
						<div class="admin-user-avatar">{{ strtoupper(substr($authUser['name'] ?? 'AD', 0, 2)) }}</div>
						<div class="admin-user-meta">
							<div class="admin-user-name">{{ $authUser['name'] ?? 'Admin Penawo' }}</div>
							<div class="admin-user-role">Administrator</div>
						</div>
						<i class="fa fa-chevron-down admin-user-caret"></i>
					</div>

					<div class="admin-user-dropdown">
						<div class="admin-user-dropdown-head">
							<div class="admin-user-dropdown-name">{{ $authUser['name'] ?? 'Admin Penawo' }}</div>
							<div class="admin-user-dropdown-email">{{ $authUser['email'] ?? '' }}</div>
						</div>

						<a href="{{ route('home') }}" target="_blank" class="admin-user-dropdown-link">
							<i class="fa fa-external-link"></i> Lihat Toko
						</a>
						<a href="{{ route('admin.user') }}" class="admin-user-dropdown-link">
							<i class="fa fa-users"></i> Kelola User
						</a>
						<a href="{{ route('admin.cms') }}" class="admin-user-dropdown-link">
							<i class="fa fa-cog"></i> Pengaturan Situs
						</a>

						<div class="admin-user-dropdown-divider"></div>

						<form action="{{ route('logout') }}" method="POST" style="margin:0;">
							@csrf
							<button type="submit" class="admin-user-dropdown-link danger">
								<i class="fa fa-sign-out"></i> Keluar
							</button>
						</form>
					</div>
				</div>
			</div>
		</header>

		<main class="admin-content">
			@if(session('status'))
				<div class="flash">{{ session('status') }}</div>
			@endif

			@yield('content')
		</main>
	</div>
</div>

<script src="{{ asset('frontend/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
<script>
	(function(){
		var wrap    = document.getElementById('adminUserWrap');
		var trigger = document.getElementById('adminUserTrigger');
		if (!wrap || !trigger) return;

		trigger.addEventListener('click', function(e){
			e.stopPropagation();
			wrap.classList.toggle('open');
		});
		document.addEventListener('click', function(e){
			if (!wrap.contains(e.target)) wrap.classList.remove('open');
		});
		document.addEventListener('keydown', function(e){
			if (e.key === 'Escape') wrap.classList.remove('open');
		});
	})();

	(function(){
		var wrap    = document.getElementById('adminNotifWrap');
		var trigger = document.getElementById('adminNotifTrigger');
		if (!wrap || !trigger) return;

		trigger.addEventListener('click', function(e){
			e.stopPropagation();
			wrap.classList.toggle('open');
			// Tutup dropdown user kalau sedang terbuka
			var userWrap = document.getElementById('adminUserWrap');
			if (userWrap) userWrap.classList.remove('open');
		});
		document.addEventListener('click', function(e){
			if (!wrap.contains(e.target)) wrap.classList.remove('open');
		});
		document.addEventListener('keydown', function(e){
			if (e.key === 'Escape') wrap.classList.remove('open');
		});
	})();
</script>
@stack('scripts')
</body>
</html>
