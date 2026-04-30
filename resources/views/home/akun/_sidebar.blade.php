@php $currentRoute = request()->route()?->getName(); @endphp
<aside class="akun-sidebar">
	<div class="akun-sidebar-header">
		<div class="akun-avatar">{{ strtoupper(substr($authUser['name'] ?? 'P', 0, 2)) }}</div>
		<div style="flex:1; min-width:0;">
			<div class="akun-sidebar-name">{{ $authUser['name'] ?? 'Pelanggan' }}</div>
			<div class="akun-sidebar-email">{{ $authUser['email'] ?? '' }}</div>
		</div>
	</div>

	@php
		$wishlistCount = isset($authUser['id'])
			? \App\Models\Wishlist::where('user_id', $authUser['id'])->count()
			: 0;
	@endphp
	<ul class="akun-sidebar-menu">
		<li class="{{ $currentRoute === 'akun.profil' ? 'active' : '' }}">
			<a href="{{ route('akun.profil') }}"><i class="fa fa-user"></i> Profil Saya</a>
		</li>
		<li class="{{ $currentRoute === 'akun.pesanan' ? 'active' : '' }}">
			<a href="{{ route('akun.pesanan') }}"><i class="fa fa-file-text-o"></i> Pesanan Saya</a>
		</li>
		<li class="{{ $currentRoute === 'akun.wishlist' ? 'active' : '' }}">
			<a href="{{ route('akun.wishlist') }}">
				<i class="fa fa-heart-o"></i> Wishlist
				@if($wishlistCount > 0)<small style="background:#c29e5c; color:#fff; padding:2px 8px; border-radius:999px; font-size:10px; letter-spacing:0;">{{ $wishlistCount }}</small>@endif
			</a>
		</li>
		<li class="{{ $currentRoute === 'akun.pengaturan' ? 'active' : '' }}">
			<a href="{{ route('akun.pengaturan') }}"><i class="fa fa-cog"></i> Pengaturan</a>
		</li>
	</ul>

	<div style="padding: 14px 20px; border-top: 1px solid #ece8de; margin-top: 8px;">
		<form action="{{ route('logout') }}" method="POST" style="margin:0;">
			@csrf
			<button type="submit" class="akun-logout-btn">
				<i class="fa fa-sign-in"></i> Keluar
			</button>
		</form>
	</div>
</aside>
