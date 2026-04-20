@php $currentRoute = request()->route()?->getName(); @endphp
<aside class="akun-sidebar">
	<div class="akun-sidebar-header">
		<div class="akun-avatar">{{ strtoupper(substr($authUser['name'] ?? 'P', 0, 2)) }}</div>
		<div style="flex:1; min-width:0;">
			<div class="akun-sidebar-name">{{ $authUser['name'] ?? 'Pelanggan' }}</div>
			<div class="akun-sidebar-email">{{ $authUser['email'] ?? '' }}</div>
		</div>
	</div>

	<ul class="akun-sidebar-menu">
		<li class="{{ $currentRoute === 'akun.profil' ? 'active' : '' }}">
			<a href="{{ route('akun.profil') }}"><i class="fa fa-user"></i> Profil Saya</a>
		</li>
		<li class="{{ $currentRoute === 'akun.pesanan' ? 'active' : '' }}">
			<a href="{{ route('akun.pesanan') }}"><i class="fa fa-file-text-o"></i> Pesanan Saya</a>
		</li>
		<li class="disabled" title="Segera hadir">
			<a href="#" onclick="return false;"><i class="fa fa-heart-o"></i> Wishlist <small>(segera)</small></a>
		</li>
		<li class="disabled" title="Segera hadir">
			<a href="#" onclick="return false;"><i class="fa fa-cog"></i> Pengaturan <small>(segera)</small></a>
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
