<!DOCTYPE html>
<html lang="id">
<head>
	<title>@yield('title', 'Batik Penawo')</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="{{ asset('frontend/images/icons/favicon.png') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/util.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/main.css') }}">
	<style>
		body { background: #faf6ed; min-height: 100vh; display: flex; flex-direction: column; }

		.topbar-global { background: #2d2d2d; color: #e2e2e2; font-size: 12.5px; }
		.topbar-global .topbar-inner { display: flex; justify-content: space-between; align-items: center; height: 36px; }
		.topbar-global .topbar-left { color: #b8b8b8; }
		.topbar-global .topbar-right { display: flex; align-items: center; }
		.topbar-global .topbar-link { display: inline-flex; align-items: center; color: #e2e2e2; text-decoration: none; padding: 0 12px; height: 36px; transition: color .15s; background: none; border: 0; font: inherit; cursor: pointer; }
		.topbar-global .topbar-link:hover { color: #c29e5c; text-decoration: none; }
		.topbar-global .topbar-link i { margin-right: 6px; font-size: 15px; }
		.topbar-global .topbar-link-strong { font-weight: 600; }
		.topbar-global .topbar-sep { color: #4a4a4a; padding: 0 2px; user-select: none; }
		@media (max-width: 768px) { .topbar-global .topbar-left { display: none; } }
	</style>
	@stack('styles')
</head>
<body>
	<div class="topbar-global">
		<div class="container">
			<div class="topbar-inner">
				<div class="topbar-left">
					Gratis ongkir untuk pembelian di atas Rp500.000
				</div>

				<div class="topbar-right">
					<a href="#" class="topbar-link"><i class="fa fa-bell-o"></i>Notifikasi</a>
					<span class="topbar-sep">|</span>
					<a href="{{ route('kontak') }}" class="topbar-link"><i class="fa fa-question-circle-o"></i>Bantuan</a>
					<span class="topbar-sep">|</span>
					<a href="#" class="topbar-link"><i class="fa fa-globe"></i>Bahasa Indonesia</a>
					<span class="topbar-sep">|</span>
					<a href="{{ route('register') }}" class="topbar-link topbar-link-strong">Daftar</a>
					<span class="topbar-sep">|</span>
					<a href="{{ route('login') }}" class="topbar-link topbar-link-strong">Masuk</a>
				</div>
			</div>
		</div>
	</div>

	<main style="flex: 1 0 auto;">
		@yield('content')
	</main>

	<script src="{{ asset('frontend/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
	@stack('scripts')
</body>
</html>
