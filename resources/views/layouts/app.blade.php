<!DOCTYPE html>
<html lang="id">
<head>
	<title>@yield('title', 'Batik Penawo')</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/x-icon" href="{{ asset('image/favicon.ico') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/animate/animate.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/css-hamburgers/hamburgers.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/animsition/css/animsition.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/select2/select2.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/daterangepicker/daterangepicker.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/slick/slick.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/MagnificPopup/magnific-popup.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/vendor/perfect-scrollbar/perfect-scrollbar.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/util.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/main.css') }}">
	<style>
		.cart-wrap { position: relative; cursor: pointer; }
		.cart-wrap > .cart-trigger { color: inherit; text-decoration: none; display: block; }
		.cart-dropdown {
			position: absolute;
			top: 100%;
			right: -15px;
			width: 360px;
			background: #fff;
			border: 1px solid #e6e6e6;
			box-shadow: 0 10px 30px rgba(0,0,0,.12);
			padding: 18px 20px 14px;
			opacity: 0;
			visibility: hidden;
			transform: translateY(10px);
			transition: opacity .2s ease, transform .2s ease, visibility 0s linear .2s;
			z-index: 1100;
		}
		.cart-wrap:hover .cart-dropdown {
			opacity: 1;
			visibility: visible;
			transform: translateY(0);
			transition: opacity .2s ease, transform .2s ease;
		}
		.cart-dropdown:before {
			content: '';
			position: absolute;
			top: -8px;
			right: 28px;
			width: 14px;
			height: 14px;
			background: #fff;
			border-top: 1px solid #e6e6e6;
			border-left: 1px solid #e6e6e6;
			transform: rotate(45deg);
		}
		.cart-dropdown-title { color: #888; font-size: 13px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
		.cart-dropdown-list { list-style: none; padding: 0; margin: 0; max-height: 340px; overflow-y: auto; }
		.cart-dropdown-item { padding: 12px 0; border-bottom: 1px solid #f4f4f4; }
		.cart-dropdown-item:last-child { border-bottom: 0; }
		.cart-dropdown-item-link { display: flex; align-items: center; text-decoration: none; color: inherit; }
		.cart-dropdown-item-link:hover { text-decoration: none; }
		.cart-dropdown-item-img { flex: 0 0 50px; width: 50px; height: 50px; margin-right: 12px; overflow: hidden; background: #f4f4f4; }
		.cart-dropdown-item-img img { width: 100%; height: 100%; object-fit: cover; }
		.cart-dropdown-item-info { flex: 1 1 auto; min-width: 0; }
		.cart-dropdown-item-name { font-size: 13.5px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
		.cart-dropdown-item-qty { font-size: 12px; color: #999; padding-top: 2px; }
		.cart-dropdown-item-price { flex: 0 0 auto; color: #c29e5c; font-size: 13.5px; margin-left: 12px; font-weight: 500; white-space: nowrap; }
		.cart-dropdown-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid #eee; margin-top: 4px; }
		.cart-dropdown-footer-info { color: #888; font-size: 13px; }
		.cart-dropdown-btn { background: #c29e5c; color: #fff; padding: 9px 20px; font-size: 13px; font-weight: 500; border-radius: 2px; text-decoration: none; transition: background .2s; }
		.cart-dropdown-btn:hover { background: #a88541; color: #fff; text-decoration: none; }
		.cart-dropdown-empty { padding: 36px 0; text-align: center; color: #888; font-size: 14px; }

		.profile-wrap { position: relative; cursor: pointer; }
		.profile-wrap > .profile-trigger { color: inherit; text-decoration: none; display: block; }
		.profile-dropdown {
			position: absolute;
			top: 100%;
			right: -15px;
			width: 240px;
			background: #fff;
			border: 1px solid #e6e6e6;
			box-shadow: 0 10px 30px rgba(0,0,0,.12);
			padding: 6px 0;
			opacity: 0;
			visibility: hidden;
			transform: translateY(10px);
			transition: opacity .2s ease, transform .2s ease, visibility 0s linear .2s;
			z-index: 1100;
		}
		.profile-wrap:hover .profile-dropdown {
			opacity: 1;
			visibility: visible;
			transform: translateY(0);
			transition: opacity .2s ease, transform .2s ease;
		}
		.profile-dropdown:before {
			content: '';
			position: absolute;
			top: -8px;
			right: 28px;
			width: 14px;
			height: 14px;
			background: #fff;
			border-top: 1px solid #e6e6e6;
			border-left: 1px solid #e6e6e6;
			transform: rotate(45deg);
		}
		.profile-dropdown-header { padding: 14px 18px 12px; border-bottom: 1px solid #eee; }
		.profile-dropdown-greet { font-size: 14px; font-weight: 600; color: #333; }
		.profile-dropdown-sub { font-size: 12px; color: #999; padding-top: 3px; }
		.profile-dropdown-link {
			display: flex;
			align-items: center;
			padding: 10px 18px;
			color: #555;
			font-size: 13.5px;
			text-decoration: none;
			transition: background .15s, color .15s;
		}
		.profile-dropdown-link:hover { background: #faf6ed; color: #c29e5c; text-decoration: none; }
		.profile-dropdown-link i { font-size: 16px; margin-right: 12px; width: 18px; text-align: center; }
		.profile-dropdown-link-accent { color: #c29e5c; font-weight: 500; }
		.profile-dropdown-link-admin {
			background: #faf6ed; color: #8a6b2b !important; font-weight: 600;
			border-left: 3px solid #c29e5c;
		}
		.profile-dropdown-link-admin:hover {
			background: #f5ecd7 !important; color: #8a6b2b !important;
		}
		.profile-dropdown-link-admin i { color: #c29e5c; }
		.profile-dropdown-badge {
			margin-left: auto;
			padding: 2px 8px; border-radius: 999px;
			background: #c29e5c; color: #fff;
			font-size: 10px; font-weight: 600;
			letter-spacing: .5px; text-transform: uppercase;
		}
		.profile-dropdown-divider { height: 1px; background: #eee; margin: 6px 0; }
		.profile-dropdown form { margin: 0; padding: 0; }
		.profile-dropdown form button { width: 100%; background: none; border: 0; text-align: left; cursor: pointer; font: inherit; }

		/* Flash banner (session status) */
		.flash-banner {
			display: flex; align-items: center; gap: 10px;
			background: #edf7ef; color: #2f7a4c;
			border: 1px solid #cfe6d6; border-left: 4px solid #56a676;
			padding: 12px 16px; border-radius: 4px;
			font-size: 14px;
		}
		.flash-banner i { font-size: 18px; }
		.flash-banner span { flex: 1; }
		.flash-close {
			background: none; border: 0; color: #2f7a4c;
			cursor: pointer; font-size: 20px; line-height: 1;
			padding: 0 4px; opacity: .6;
		}
		.flash-close:hover { opacity: 1; }

		/* Toast notifikasi keranjang */
		.cart-toast {
			position: fixed; top: 24px; right: 24px;
			width: 340px; max-width: calc(100vw - 32px);
			background: #fff;
			border: 1px solid #ece8de;
			border-left: 4px solid #56a676;
			border-radius: 6px;
			box-shadow: 0 14px 38px rgba(45, 42, 38, .22);
			padding: 14px 36px 14px 16px;
			display: flex; gap: 12px;
			z-index: 9999;
			animation: cartToastIn .35s cubic-bezier(.2,.8,.3,1.2);
		}
		.cart-toast.hide { animation: cartToastOut .25s ease-out forwards; }
		@keyframes cartToastIn {
			from { transform: translateX(24px); opacity: 0; }
			to { transform: translateX(0); opacity: 1; }
		}
		@keyframes cartToastOut {
			to { transform: translateX(24px); opacity: 0; }
		}
		.cart-toast-img-wrap { position: relative; flex-shrink: 0; }
		.cart-toast-img {
			width: 56px; height: 56px;
			border-radius: 4px; object-fit: cover;
			background: #f5f2ea; display: block;
		}
		.cart-toast-badge {
			position: absolute; top: -8px; left: -8px;
			width: 24px; height: 24px; border-radius: 50%;
			background: #56a676; color: #fff;
			display: flex; align-items: center; justify-content: center;
			font-size: 12px;
			box-shadow: 0 2px 6px rgba(0,0,0,.18);
		}
		.cart-toast-body { flex: 1; min-width: 0; }
		.cart-toast-title { font-size: 13px; font-weight: 600; color: #2d2a26; }
		.cart-toast-name {
			font-size: 12.5px; color: #6c665e;
			margin-top: 2px;
			white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
		}
		.cart-toast-actions { display: flex; gap: 8px; margin-top: 10px; }
		.cart-toast-btn {
			padding: 6px 12px; font-size: 11.5px; font-weight: 500;
			border-radius: 3px; text-decoration: none;
			border: 1px solid transparent; cursor: pointer;
			font-family: inherit; line-height: 1.4;
			transition: background .15s, color .15s, border-color .15s;
		}
		.cart-toast-btn.primary { background: #c29e5c; color: #fff; }
		.cart-toast-btn.primary:hover { background: #a88541; color: #fff; text-decoration: none; }
		.cart-toast-btn.outline { background: #fff; color: #6c665e; border-color: #ddd6c6; }
		.cart-toast-btn.outline:hover { color: #2d2a26; border-color: #c29e5c; }
		.cart-toast-close {
			position: absolute; top: 6px; right: 8px;
			background: none; border: 0; padding: 2px 6px;
			color: #c8c2b6; cursor: pointer;
			font-size: 20px; line-height: 1;
		}
		.cart-toast-close:hover { color: #2d2a26; }

		/* Global topbar */
		.topbar-global { background: #2d2d2d; color: #e2e2e2; font-size: 12.5px; }
		.topbar-global .topbar-inner { display: flex; justify-content: space-between; align-items: center; height: 36px; }
		.topbar-global .topbar-left { color: #b8b8b8; }
		.topbar-global .topbar-right { display: flex; align-items: center; }
		.topbar-global .topbar-link { display: inline-flex; align-items: center; color: #e2e2e2; text-decoration: none; padding: 0 12px; height: 36px; transition: color .15s; background: none; border: 0; font: inherit; cursor: pointer; }
		.topbar-global .topbar-link:hover { color: #c29e5c; text-decoration: none; }
		.topbar-global .topbar-link i { margin-right: 6px; font-size: 15px; }
		.topbar-global .topbar-link-strong { font-weight: 600; }
		.topbar-global .topbar-sep { color: #4a4a4a; padding: 0 2px; user-select: none; }
		.topbar-global form.topbar-logout { margin: 0; padding: 0; display: inline-flex; }
		@media (max-width: 768px) { .topbar-global .topbar-left { display: none; } }
	</style>
	@stack('styles')
</head>
<body class="animsition">

	<!-- Header -->
	<header>
		<!-- Topbar -->
		<div class="topbar-global top-bar">
			<div class="container">
				<div class="topbar-inner">
					<div class="topbar-left">
						@if($setting('footer_topbar_promo'))
							<i class="fa fa-bullhorn" style="margin-right:6px;"></i>{{ $setting('footer_topbar_promo') }}
						@endif
					</div>

					<div class="topbar-right">
						<a href="{{ route('kontak') }}" class="topbar-link"><i class="fa fa-question-circle-o"></i>Bantuan</a>
						<span class="topbar-sep">|</span>
						<a href="#" class="topbar-link"><i class="fa fa-globe"></i>Bahasa Indonesia</a>

						@if(!$authUser)
							<span class="topbar-sep">|</span>
							<a href="{{ route('register') }}" class="topbar-link topbar-link-strong">Daftar</a>
							<span class="topbar-sep">|</span>
							<a href="{{ route('login') }}" class="topbar-link topbar-link-strong">Masuk</a>
						@else
							<span class="topbar-sep">|</span>
							@if(($authUser['role'] ?? null) === 'admin')
								<a href="{{ route('admin.dashboard') }}" class="topbar-link topbar-link-strong" title="Buka Dashboard Admin">
									<i class="fa fa-user-o"></i>Halo, {{ $authUser['name'] }}
								</a>
							@else
								<span class="topbar-link"><i class="fa fa-user-o"></i>Halo, {{ $authUser['name'] }}</span>
							@endif
							<span class="topbar-sep">|</span>
							<form action="{{ route('logout') }}" method="POST" class="topbar-logout">
								@csrf
								<button type="submit" class="topbar-link topbar-link-strong">Keluar</button>
							</form>
						@endif
					</div>
				</div>
			</div>
		</div>

		<div class="container-menu-desktop">

			<div class="wrap-menu-desktop">
				<nav class="limiter-menu-desktop container">
					<a href="{{ url('/') }}" class="logo">
						<img src="{{ asset('image/logox.png') }}" alt="BATIK PENAWO">
					</a>

					<div class="menu-desktop">
						<ul class="main-menu">
							<li class="{{ request()->routeIs('home') ? 'active-menu' : '' }}">
								<a href="{{ route('home') }}">Beranda</a>
							</li>

							<li class="{{ request()->routeIs('produk') ? 'active-menu' : '' }}">
								<a href="{{ route('produk') }}">Produk</a>
							</li>

							<li class="{{ request()->routeIs('tentang') ? 'active-menu' : '' }}">
								<a href="{{ route('tentang') }}">Tentang</a>
							</li>

							<li class="{{ request()->routeIs('kontak') ? 'active-menu' : '' }}">
								<a href="{{ route('kontak') }}">Kontak</a>
							</li>
						</ul>
					</div>

					<div class="wrap-icon-header flex-w flex-r-m">
						<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
							<i class="fa fa-search"></i>
						</div>

						@if($authUser)
							<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti cart-wrap" data-notify="{{ count($cartItems) }}">
								<a href="{{ route('keranjang') }}" class="cart-trigger">
									<i class="fa fa-shopping-cart"></i>
								</a>

								<div class="cart-dropdown">
									<div class="cart-dropdown-title">Baru Ditambahkan</div>

									@if(count($cartItems))
										<ul class="cart-dropdown-list">
											@foreach($cartItems as $item)
											<li class="cart-dropdown-item">
												<a href="{{ route('produk.detail', $item['slug']) }}" class="cart-dropdown-item-link">
													<div class="cart-dropdown-item-img">
														<img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
													</div>
													<div class="cart-dropdown-item-info">
														<div class="cart-dropdown-item-name">{{ $item['name'] }}</div>
														<div class="cart-dropdown-item-qty">{{ $item['qty'] }} x {{ $rupiah($item['price']) }}</div>
													</div>
													<div class="cart-dropdown-item-price">{{ $rupiah($item['price'] * $item['qty']) }}</div>
												</a>
											</li>
											@endforeach
										</ul>

										<div class="cart-dropdown-footer">
											<span class="cart-dropdown-footer-info">{{ count($cartItems) }} Produk di Keranjang</span>
											<a href="{{ route('keranjang') }}" class="cart-dropdown-btn">Tampilkan Keranjang</a>
										</div>
									@else
										<div class="cart-dropdown-empty">Keranjang Anda masih kosong.</div>
									@endif
								</div>
							</div>

							<div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 profile-wrap">
								<a href="#" class="profile-trigger">
									<i class="fa fa-user-o"></i>
								</a>

								<div class="profile-dropdown">
									<div class="profile-dropdown-header">
										<div class="profile-dropdown-greet">Halo, {{ $authUser['name'] }}</div>
										<div class="profile-dropdown-sub">{{ $authUser['email'] }}</div>
									</div>

									@if(($authUser['role'] ?? null) === 'admin')
										<a href="{{ route('admin.dashboard') }}" class="profile-dropdown-link profile-dropdown-link-admin">
											<i class="fa fa-tachometer"></i>Dashboard Admin
											<span class="profile-dropdown-badge">Admin</span>
										</a>
										<div class="profile-dropdown-divider"></div>
									@endif

									<a href="{{ route('akun.profil') }}" class="profile-dropdown-link"><i class="fa fa-user"></i>Profil Saya</a>
									<a href="{{ route('akun.pesanan') }}" class="profile-dropdown-link"><i class="fa fa-file-text-o"></i>Pesanan Saya</a>
									@php
										$_wlCount = session('auth_user.id')
											? \App\Models\Wishlist::where('user_id', session('auth_user.id'))->count()
											: 0;
									@endphp
									<a href="{{ route('akun.wishlist') }}" class="profile-dropdown-link">
										<i class="fa fa-heart-o"></i>Wishlist
										@if($_wlCount > 0)<span class="profile-dropdown-badge" style="background:#c29e5c; color:#fff;">{{ $_wlCount }}</span>@endif
									</a>
									<a href="{{ route('akun.pengaturan') }}" class="profile-dropdown-link"><i class="fa fa-cog"></i>Pengaturan</a>

									<div class="profile-dropdown-divider"></div>

									<form action="{{ route('logout') }}" method="POST">
										@csrf
										<button type="submit" class="profile-dropdown-link profile-dropdown-link-accent"><i class="fa fa-sign-in"></i>Keluar</button>
									</form>
								</div>
							</div>
						@endif
					</div>
				</nav>
			</div>
		</div>

		<!-- Header Mobile -->
		<div class="wrap-header-mobile">
			<div class="logo-mobile">
				<a href="{{ url('/') }}"><img src="{{ asset('image/logox.png') }}" alt="BATIK PENAWO"></a>
			</div>

			<div class="wrap-icon-header flex-w flex-r-m m-r-15">
				<div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 js-show-modal-search">
					<i class="fa fa-search"></i>
				</div>

				@if($authUser)
					<a href="{{ route('keranjang') }}" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti" data-notify="{{ count($cartItems) }}">
						<i class="fa fa-shopping-cart"></i>
					</a>

					<a href="#" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10">
						<i class="fa fa-user-o"></i>
					</a>
				@else
					<a href="{{ route('login') }}" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10">
						<i class="fa fa-sign-in"></i>
					</a>
				@endif
			</div>

			<div class="btn-show-menu-mobile hamburger hamburger--squeeze">
				<span class="hamburger-box">
					<span class="hamburger-inner"></span>
				</span>
			</div>
		</div>

		<!-- Menu Mobile -->
		<div class="menu-mobile">
			<ul class="topbar-mobile">
				<li>
					<div class="left-top-bar">
						 
					</div>
				</li>

				<li>
					<div class="right-top-bar flex-w h-full">
						<a href="#" class="flex-c-m p-lr-10 trans-04">Bantuan & FAQ</a>
						<a href="#" class="flex-c-m p-lr-10 trans-04">Akun Saya</a>
						<a href="#" class="flex-c-m p-lr-10 trans-04">ID</a>
						<a href="#" class="flex-c-m p-lr-10 trans-04">IDR</a>
					</div>
				</li>
			</ul>

			<ul class="main-menu-m">
				<li><a href="{{ route('home') }}">Beranda</a></li>
				<li><a href="{{ route('produk') }}">Produk</a></li>
				<li><a href="{{ route('tentang') }}">Tentang</a></li>
				<li><a href="{{ route('kontak') }}">Kontak</a></li>

				@if(!$authUser)
					<li><a href="{{ route('login') }}">Masuk</a></li>
					<li><a href="{{ route('register') }}">Daftar</a></li>
				@else
					<li><a href="{{ route('keranjang') }}">Keranjang</a></li>
					<li>
						<form action="{{ route('logout') }}" method="POST" style="padding:0;margin:0;">
							@csrf
							<button type="submit" style="background:none;border:0;padding:0;color:inherit;font:inherit;cursor:pointer;">Keluar</button>
						</form>
					</li>
				@endif
			</ul>
		</div>

		<!-- Modal Search -->
		<div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">
			<div class="container-search-header">
				<button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
					<img src="{{ asset('frontend/images/icons/icon-close2.png') }}" alt="TUTUP">
				</button>

				<form class="wrap-search-header flex-w p-l-15">
					<button class="flex-c-m trans-04">
						<i class="fa fa-search"></i>
					</button>
					<input class="plh3" type="text" name="search" placeholder="Cari produk batik...">
				</form>
			</div>
		</div>
	</header>

	@if(session('status'))
		<div class="container" style="padding-top:18px;">
			<div class="flash-banner" id="flashBanner">
				<i class="fa fa-check-circle"></i>
				<span>{{ session('status') }}</span>
				<button type="button" class="flash-close" onclick="this.parentElement.style.display='none'" aria-label="Tutup">&times;</button>
			</div>
		</div>
		<script>setTimeout(function(){ var b=document.getElementById('flashBanner'); if(b){ b.style.transition='opacity .4s'; b.style.opacity='0'; setTimeout(function(){b.style.display='none';},400); } }, 4000);</script>
	@endif

	@if(session('cart_added'))
		@php $ca = session('cart_added'); @endphp
		<div class="cart-toast" id="cartToast" role="alert" aria-live="polite">
			<button type="button" class="cart-toast-close" onclick="hideCartToast()" aria-label="Tutup">&times;</button>
			<div class="cart-toast-img-wrap">
				<img src="{{ $ca['image'] ?? asset('frontend/images/product-01.jpg') }}" alt="{{ $ca['name'] }}" class="cart-toast-img">
				<span class="cart-toast-badge"><i class="fa fa-check"></i></span>
			</div>
			<div class="cart-toast-body">
				<div class="cart-toast-title">Berhasil ditambahkan!</div>
				<div class="cart-toast-name">{{ $ca['qty'] }}× {{ $ca['name'] }}</div>
				@php $variantParts = array_filter([$ca['size'] ?? null, $ca['color'] ?? null]); @endphp
				@if($variantParts)
					<div style="font-size:11px; color:#9a9288; margin-top:2px;">{{ implode(' · ', $variantParts) }}</div>
				@endif
				<div class="cart-toast-actions">
					<a href="{{ route('keranjang') }}" class="cart-toast-btn primary"><i class="fa fa-shopping-cart"></i> Lihat Keranjang</a>
					<button type="button" class="cart-toast-btn outline" onclick="hideCartToast()">Lanjut Belanja</button>
				</div>
			</div>
		</div>
		<script>
			function hideCartToast() {
				var t = document.getElementById('cartToast');
				if (!t || t.classList.contains('hide')) return;
				t.classList.add('hide');
				setTimeout(function(){ t.style.display = 'none'; }, 260);
			}
			setTimeout(hideCartToast, 5000);
		</script>
	@endif

	@yield('content')

	<!-- Footer -->
	<footer class="bg3 p-t-75 p-b-32">
		<div class="container">
			<div class="row">
				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">Kategori</h4>
					<ul>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">Batik Wanita</a></li>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">Batik Pria</a></li>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">Batik Anak</a></li>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">Aksesoris</a></li>
					</ul>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">Bantuan</h4>
					<ul>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">Lacak Pesanan</a></li>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">Pengembalian</a></li>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">Pengiriman</a></li>
						<li class="p-b-10"><a href="#" class="stext-107 cl7 hov-cl1 trans-04">FAQ</a></li>
					</ul>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">HUBUNGI KAMI</h4>
					<p class="stext-107 cl7 size-201">
						Ada pertanyaan? Kunjungi toko kami di {{ $setting('contact_address', 'Jl. Malioboro No. 123, Kerinci') }} atau hubungi kami di {{ $setting('contact_phone', '+62 812-3456-7890') }}
					</p>

					<div class="p-t-27">
						@if($setting('social_facebook'))
							<a href="{{ $setting('social_facebook') }}" target="_blank" class="fs-18 cl7 hov-cl1 trans-04 m-r-16"><i class="fa fa-facebook"></i></a>
						@endif
						@if($setting('social_instagram'))
							<a href="{{ $setting('social_instagram') }}" target="_blank" class="fs-18 cl7 hov-cl1 trans-04 m-r-16"><i class="fa fa-instagram"></i></a>
						@endif
						@if($setting('social_pinterest'))
							<a href="{{ $setting('social_pinterest') }}" target="_blank" class="fs-18 cl7 hov-cl1 trans-04 m-r-16"><i class="fa fa-pinterest-p"></i></a>
						@endif
						@if($setting('social_youtube'))
							<a href="{{ $setting('social_youtube') }}" target="_blank" class="fs-18 cl7 hov-cl1 trans-04 m-r-16"><i class="fa fa-youtube-play"></i></a>
						@endif
					</div>
				</div>

				<div class="col-sm-6 col-lg-3 p-b-50">
					<h4 class="stext-301 cl0 p-b-30">Buletin</h4>
					<p class="stext-107 cl7 size-201" style="margin-bottom:14px;">
						{{ $setting('footer_newsletter_text', 'Berlangganan newsletter untuk penawaran spesial') }}
					</p>
					<form>
						<div class="wrap-input1 w-full p-b-4">
							<input class="input1 bg-none plh1 stext-107 cl7" type="text" name="email" placeholder="email@contoh.com">
							<div class="focus-input1 trans-04"></div>
						</div>

						<div class="p-t-18">
							<button class="flex-c-m stext-101 cl0 size-103 bg1 bor1 hov-btn2 p-lr-15 trans-04">Berlangganan</button>
						</div>
					</form>
				</div>
			</div>

			<div class="p-t-40">
				<div class="flex-c-m flex-w p-b-18">
					<a href="#" class="m-all-1"><img src="{{ asset('frontend/images/icons/icon-pay-01.png') }}" alt="IKON"></a>
					<a href="#" class="m-all-1"><img src="{{ asset('frontend/images/icons/icon-pay-02.png') }}" alt="IKON"></a>
					<a href="#" class="m-all-1"><img src="{{ asset('frontend/images/icons/icon-pay-03.png') }}" alt="IKON"></a>
					<a href="#" class="m-all-1"><img src="{{ asset('frontend/images/icons/icon-pay-04.png') }}" alt="IKON"></a>
					<a href="#" class="m-all-1"><img src="{{ asset('frontend/images/icons/icon-pay-05.png') }}" alt="IKON"></a>
				</div>

				<p class="stext-107 cl6 txt-center">
					{{ $setting('footer_copyright', 'Hak Cipta © ' . date('Y') . ' Batik Penawo. Semua hak dilindungi.') }}
				</p>
			</div>
		</div>
	</footer>

	<!-- Back to top -->
	<div class="btn-back-to-top" id="myBtn">
		<span class="symbol-btn-back-to-top">
			<i class="fa fa-chevron-up"></i>
		</span>
	</div>

	<!-- Modal1 (Quick View) — dinamis, diisi via JS dari tombol "Lihat Cepat" -->
	<div class="wrap-modal1 js-modal1 p-t-60 p-b-20">
		<div class="overlay-modal1 js-hide-modal1"></div>

		<div class="container">
			<div class="bg0 p-t-60 p-b-30 p-lr-15-lg how-pos3-parent">
				<button class="how-pos3 hov3 trans-04 js-hide-modal1">
					<img src="{{ asset('frontend/images/icons/icon-close.png') }}" alt="TUTUP">
				</button>

				<div class="row">
					<div class="col-md-6 col-lg-7 p-b-30">
						<div class="p-l-25 p-r-30 p-lr-0-lg">
							<div class="wrap-pic-w pos-relative" style="background:#faf7ef;">
								<img id="qv-image" src="{{ asset('frontend/images/product-01.jpg') }}" alt="Gambar produk" style="width:100%; display:block;">
							</div>
						</div>
					</div>

					<div class="col-md-6 col-lg-5 p-b-30">
						<div class="p-r-50 p-t-5 p-lr-0-lg">
							<h4 class="mtext-105 cl2 js-name-detail p-b-14" id="qv-name">—</h4>
							<span class="mtext-106 cl2" id="qv-price">Rp0</span>

							<p class="stext-102 cl3 p-t-23" id="qv-description">—</p>

							<form id="qv-form" action="{{ route('keranjang.add') }}" method="POST" class="p-t-33">
								@csrf
								<input type="hidden" name="slug" id="qv-slug" value="">

								<div class="flex-w flex-r-m p-b-10" id="qv-size-row">
									<div class="size-203 flex-c-m respon6">Ukuran</div>
									<div class="size-204 respon6-next">
										<div class="rs1-select2 bor8 bg0">
											<select name="size" id="qv-size-select" style="width:100%; padding:8px;">
												<option>Pilih ukuran</option>
											</select>
										</div>
									</div>
								</div>

								<div class="flex-w flex-r-m p-b-10" id="qv-color-row">
									<div class="size-203 flex-c-m respon6">Warna</div>
									<div class="size-204 respon6-next">
										<div class="rs1-select2 bor8 bg0">
											<select name="color" id="qv-color-select" style="width:100%; padding:8px;">
												<option>Pilih warna</option>
											</select>
										</div>
									</div>
								</div>

								<div class="flex-w flex-r-m p-b-10">
									<div class="size-204 flex-w flex-m respon6-next">
										<div class="wrap-num-product flex-w m-r-20 m-tb-10">
											<div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m">
												<i class="fs-16 fa fa-minus"></i>
											</div>

											<input class="mtext-104 cl3 txt-center num-product" type="number" name="qty" value="1" min="1" max="99">

											<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
												<i class="fs-16 fa fa-plus"></i>
											</div>
										</div>

										<button type="submit" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">Tambah ke Keranjang</button>
									</div>
								</div>

								<div class="p-t-15">
									<a href="#" id="qv-detail-link" class="stext-106 cl6 hov-cl1 trans-04" style="text-decoration:underline;">Lihat detail lengkap &rarr;</a>
								</div>
							</form>

							<div class="flex-w flex-m p-l-100 p-t-40 respon7">
								<div class="flex-m bor9 p-r-10 m-r-11">
									<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 js-addwish-detail tooltip100" data-tooltip="Tambah ke Favorit">
										<i class="fa fa-heart"></i>
									</a>
								</div>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100" data-tooltip="Facebook">
									<i class="fa fa-facebook"></i>
								</a>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100" data-tooltip="Twitter">
									<i class="fa fa-twitter"></i>
								</a>

								<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100" data-tooltip="Google Plus">
									<i class="fa fa-google-plus"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	@include('partials.confirm-modal')

	<script src="{{ asset('frontend/vendor/jquery/jquery-3.2.1.min.js') }}"></script>
	<script src="{{ asset('frontend/vendor/animsition/js/animsition.min.js') }}"></script>
	<script src="{{ asset('frontend/vendor/bootstrap/js/popper.js') }}"></script>
	<script src="{{ asset('frontend/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('frontend/vendor/select2/select2.min.js') }}"></script>
	<script>
		$(".js-select2").each(function(){
			$(this).select2({
				minimumResultsForSearch: 20,
				dropdownParent: $(this).next('.dropDownSelect2')
			});
		})
	</script>
	<script src="{{ asset('frontend/vendor/daterangepicker/moment.min.js') }}"></script>
	<script src="{{ asset('frontend/vendor/daterangepicker/daterangepicker.js') }}"></script>
	<script src="{{ asset('frontend/vendor/slick/slick.min.js') }}"></script>
	<script src="{{ asset('frontend/js/slick-custom.js') }}"></script>
	<script src="{{ asset('frontend/vendor/parallax100/parallax100.js') }}"></script>
	<script>$('.parallax100').parallax100();</script>
	<script src="{{ asset('frontend/vendor/MagnificPopup/jquery.magnific-popup.min.js') }}"></script>
	<script>
		$('.gallery-lb').each(function() {
			$(this).magnificPopup({
				delegate: 'a',
				type: 'image',
				gallery: { enabled:true },
				mainClass: 'mfp-fade'
			});
		});
	</script>
	<script src="{{ asset('frontend/vendor/isotope/isotope.pkgd.min.js') }}"></script>
	<script src="{{ asset('frontend/vendor/sweetalert/sweetalert.min.js') }}"></script>
	<script>
		$('.js-addwish-b2').on('click', function(e){ e.preventDefault(); });

		$('.js-addwish-b2').each(function(){
			var nameProduct = $(this).parent().parent().find('.js-name-b2').html();
			$(this).on('click', function(){
				swal(nameProduct, "berhasil ditambahkan ke favorit!", "success");
				$(this).addClass('js-addedwish-b2');
				$(this).off('click');
			});
		});

		$('.js-addwish-detail').each(function(){
			var nameProduct = $(this).parent().parent().parent().find('.js-name-detail').html();
			$(this).on('click', function(){
				swal(nameProduct, "berhasil ditambahkan ke favorit!", "success");
				$(this).addClass('js-addedwish-detail');
				$(this).off('click');
			});
		});

		$('.js-addcart-detail').each(function(){
			var nameProduct = $(this).parent().parent().parent().parent().find('.js-name-detail').html();
			$(this).on('click', function(){
				swal(nameProduct, "berhasil ditambahkan ke keranjang!", "success");
			});
		});

		// Quick View: populate modal dengan data produk yang diklik
		$(document).on('click', '.js-show-modal1', function(e) {
			var $btn = $(this);
			var data = $btn.data();
			if (!data.slug) return; // tombol tanpa data (mis. di halaman detail) — abaikan

			$('#qv-name').text(data.name || '—');
			$('#qv-price').text(data.price || '');
			$('#qv-description').text(data.description || '');
			$('#qv-image').attr('src', data.image || '');
			$('#qv-slug').val(data.slug);
			$('#qv-detail-link').attr('href', data.detailUrl || '#');

			function fillSelect($sel, items, placeholder) {
				$sel.empty().append($('<option>').text(placeholder));
				(items || []).forEach(function(v) { $sel.append($('<option>').text(v)); });
				$sel.closest('.flex-w.flex-r-m').toggle(items && items.length > 0);
			}
			fillSelect($('#qv-size-select'),  data.sizes,  'Pilih ukuran');
			fillSelect($('#qv-color-select'), data.colors, 'Pilih warna');

			$('#qv-form input[name="qty"]').val(1);
		});
	</script>
	<script src="{{ asset('frontend/vendor/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
	<script>
		$('.js-pscroll').each(function(){
			$(this).css('position','relative');
			$(this).css('overflow','hidden');
			var ps = new PerfectScrollbar(this, {
				wheelSpeed: 1,
				scrollingThreshold: 1000,
				wheelPropagation: false,
			});

			$(window).on('resize', function(){ ps.update(); })
		});
	</script>
	<script src="{{ asset('frontend/js/main.js') }}"></script>
	@stack('scripts')
</body>
</html>
