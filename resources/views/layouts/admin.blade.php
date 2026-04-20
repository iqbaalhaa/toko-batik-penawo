<!DOCTYPE html>
<html lang="id">
<head>
	<title>@yield('title', 'Admin') &mdash; Batik Penawo</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="{{ asset('frontend/images/icons/favicon.png') }}"/>

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
		.admin-sidebar-brand img { width: 32px; height: 32px; object-fit: contain; background: #fff; padding: 3px; border-radius: 4px; }
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
		.admin-user { display: flex; align-items: center; gap: 10px; }
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
			<img src="{{ asset('frontend/images/icons/logo-01.png') }}" alt="Batik Penawo">
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
			<li class="{{ request()->routeIs('admin.pesanan*') ? 'active' : '' }}">
				<a href="{{ route('admin.pesanan') }}"><i class="fa fa-file-text-o"></i><span>Pesanan</span></a>
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
				<a href="#" class="admin-topbar-icon" title="Notifikasi">
					<i class="fa fa-bell-o"></i>
					<span class="dot"></span>
				</a>
				<a href="#" class="admin-topbar-icon" title="Pesan">
					<i class="fa fa-envelope-o"></i>
				</a>

				<div class="admin-user">
					<div class="admin-user-avatar">{{ strtoupper(substr($authUser['name'] ?? 'AD', 0, 2)) }}</div>
					<div class="admin-user-meta">
						<div class="admin-user-name">{{ $authUser['name'] ?? 'Admin Penawo' }}</div>
						<div class="admin-user-role">Administrator</div>
					</div>
				</div>

				<form action="{{ route('logout') }}" method="POST" style="margin:0;">
					@csrf
					<button type="submit" class="admin-logout-btn" title="Keluar"><i class="fa fa-power-off"></i></button>
				</form>
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
@stack('scripts')
</body>
</html>
