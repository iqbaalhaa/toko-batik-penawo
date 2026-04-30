@extends('layouts.app')

@section('title', 'Batik Penawo | Wishlist Saya')

@push('styles')
<style>
	.akun-wrap { display: grid; grid-template-columns: 280px 1fr; gap: 24px; }
	@media (max-width: 900px) { .akun-wrap { grid-template-columns: 1fr; } }
	.akun-sidebar { background:#fff; border:1px solid #ece8de; border-radius:6px; overflow:hidden; }
	.akun-sidebar-header { padding:20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid #ece8de; background:#faf7ef; }
	.akun-avatar { width:48px; height:48px; border-radius:50%; background:#c29e5c; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:15px; flex-shrink:0; }
	.akun-sidebar-name { font-size:14px; font-weight:600; color:#2d2a26; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
	.akun-sidebar-email { font-size:12px; color:#9a9288; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
	.akun-sidebar-menu { list-style:none; margin:0; padding:8px 0; }
	.akun-sidebar-menu li a { display:flex; align-items:center; gap:10px; padding:11px 20px; color:#4d4640; font-size:13.5px; transition:background .15s, color .15s; border-left:3px solid transparent; }
	.akun-sidebar-menu li a i { width:18px; font-size:14px; color:#9a9288; }
	.akun-sidebar-menu li a small { font-size:10.5px; color:#bdb7ab; margin-left:auto; text-transform:uppercase; letter-spacing:0.3px; }
	.akun-sidebar-menu li a:hover { background:#faf7ef; color:#c29e5c; text-decoration:none; }
	.akun-sidebar-menu li a:hover i { color:#c29e5c; }
	.akun-sidebar-menu li.active a { background:#faf7ef; color:#c29e5c; border-left-color:#c29e5c; font-weight:500; }
	.akun-sidebar-menu li.active i { color:#c29e5c; }
	.akun-logout-btn { background:none; border:0; padding:0; color:#a5432f; font-size:13px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
	.akun-logout-btn:hover { color:#8a2f20; }

	.akun-card { background:#fff; border:1px solid #ece8de; border-radius:6px; padding:22px 24px; }
	.akun-card-title { font-size:16px; font-weight:600; color:#2d2a26; margin:0; }
	.akun-card-sub { font-size:12.5px; color:#9a9288; margin-top:4px; }
	.akun-flash { background:#edf7ef; border:1px solid #cfe6d6; border-left:4px solid #56a676; color:#2f7a4c; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-size:13px; }

	.wishlist-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:16px; margin-top:18px; }
	.wishlist-item {
		background:#fff; border:1px solid #ece8de; border-radius:6px;
		overflow:hidden; transition: border-color .15s, box-shadow .15s, transform .15s;
		display:flex; flex-direction:column;
	}
	.wishlist-item:hover { border-color:#c29e5c; box-shadow:0 4px 14px rgba(194,158,92,.1); transform:translateY(-1px); }
	.wishlist-thumb { aspect-ratio: 1/1; overflow:hidden; background:#f5f2ea; position:relative; }
	.wishlist-thumb img { width:100%; height:100%; object-fit:cover; transition:transform .25s; }
	.wishlist-item:hover .wishlist-thumb img { transform:scale(1.04); }
	.wishlist-thumb-badge {
		position:absolute; top:8px; right:8px;
		background:rgba(255,255,255,.92); color:#a5432f;
		padding:4px 8px; border-radius:999px; font-size:10.5px; font-weight:600; letter-spacing:.5px;
	}
	.wishlist-info { padding:12px 14px; flex:1; display:flex; flex-direction:column; gap:6px; }
	.wishlist-info .name { font-size:13.5px; color:#2d2a26; font-weight:500; line-height:1.4; min-height:38px;
		display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
	.wishlist-info .name a { color:inherit; text-decoration:none; }
	.wishlist-info .name a:hover { color:#c29e5c; }
	.wishlist-info .price { font-size:14px; color:#c29e5c; font-weight:600; }
	.wishlist-info .stock-info { font-size:11.5px; color:#9a9288; }
	.wishlist-info .stock-info.low { color:#a5432f; }

	.wishlist-actions { display:flex; gap:6px; padding:10px 14px; border-top:1px solid #f2efe7; background:#faf9f5; }
	.wishlist-actions a, .wishlist-actions button {
		flex:1; padding:8px 10px; font-size:12px; text-align:center;
		border:1px solid #ddd6c6; background:#fff; color:#4d4640;
		border-radius:4px; cursor:pointer; text-decoration:none; font-weight:500;
		transition: all .15s; font-family:inherit;
	}
	.wishlist-actions a:hover { border-color:#c29e5c; color:#c29e5c; text-decoration:none; }
	.wishlist-actions .btn-buy { background:#c29e5c; border-color:#c29e5c; color:#fff; }
	.wishlist-actions .btn-buy:hover { background:#a88541; color:#fff; }
	.wishlist-actions .btn-remove { color:#a5432f; border-color:#f2c6be; }
	.wishlist-actions .btn-remove:hover { background:#fbe4df; border-color:#a5432f; color:#a5432f; }

	.akun-empty { text-align:center; padding:60px 20px; }
	.akun-empty i { font-size:48px; color:#d8d1bf; margin-bottom:14px; }
	.akun-empty h4 { font-size:16px; color:#4d4640; margin-bottom:6px; }
	.akun-empty p { font-size:13.5px; color:#9a9288; margin-bottom:20px; }
</style>
@endpush

@section('content')
	<section class="bg-img1 txt-center p-lr-15 p-tb-70" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Wishlist Saya</h2>
	</section>

	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda <i class="fa fa-angle-right m-l-9 m-r-10"></i>
			</a>
			<span class="stext-109 cl4">Wishlist</span>
		</div>
	</div>

	<section class="bg0 p-t-30 p-b-80">
		<div class="container">
			<div class="akun-wrap">
				@include('home.akun._sidebar')

				<main>
					@if(session('status'))
						<div class="akun-flash"><i class="fa fa-check-circle"></i> {{ session('status') }}</div>
					@endif

					<div class="akun-card">
						<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:6px;">
							<div>
								<h3 class="akun-card-title">Wishlist Saya</h3>
								<p class="akun-card-sub">{{ $items->count() }} produk tersimpan untuk nanti</p>
							</div>
							@if($items->count() > 0)
								<a href="{{ route('produk') }}" class="order-btn" style="display:inline-flex; align-items:center; gap:8px; padding:8px 16px; background:#fff; color:#4d4640; border:1px solid #ddd6c6; border-radius:4px; font-size:12.5px; font-weight:500; text-decoration:none; line-height:1;"><i class="fa fa-search"></i> Cari Lainnya</a>
							@endif
						</div>

						@if($items->isEmpty())
							<div class="akun-empty">
								<i class="fa fa-heart-o"></i>
								<h4>Wishlist masih kosong</h4>
								<p>Simpan produk yang Anda suka untuk dibeli nanti.</p>
								<a href="{{ route('produk') }}" class="order-btn" style="display:inline-flex; align-items:center; gap:8px; padding:10px 22px; background:#c29e5c; color:#fff; border:1px solid #c29e5c; border-radius:4px; font-size:13px; font-weight:500; text-decoration:none; line-height:1;">Jelajahi Produk</a>
							</div>
						@else
							<div class="wishlist-grid">
								@foreach($items as $w)
									@php $p = $w->product; @endphp
									<div class="wishlist-item">
										<a href="{{ route('produk.detail', $p->slug) }}" class="wishlist-thumb">
											<img src="{{ $p->image_url }}" alt="{{ $p->name }}">
											@if($p->stock <= 0)
												<span class="wishlist-thumb-badge">Habis</span>
											@elseif($p->isLowStock())
												<span class="wishlist-thumb-badge" style="color:#a87318; background:rgba(255,255,255,.92);">Stok terbatas</span>
											@endif
										</a>
										<div class="wishlist-info">
											<div class="name"><a href="{{ route('produk.detail', $p->slug) }}">{{ $p->name }}</a></div>
											<div class="price">{{ $rupiah($p->price) }}</div>
											<div class="stock-info {{ $p->stock <= 0 ? 'low' : '' }}">
												{{ $p->stock > 0 ? 'Stok: ' . $p->stock : 'Stok habis' }}
											</div>
										</div>
										<div class="wishlist-actions">
											<a href="{{ route('produk.detail', $p->slug) }}" class="btn-buy"><i class="fa fa-shopping-cart"></i> Beli</a>
											<form action="{{ route('akun.wishlist.destroy', $w->id) }}" method="POST" style="flex:1; margin:0;"
												data-confirm-title="Hapus dari wishlist?"
												data-confirm-message='Hapus "{{ $p->name }}" dari wishlist?'
												data-confirm-ok="Hapus">
												@csrf @method('DELETE')
												<button type="submit" class="btn-remove" style="width:100%;"><i class="fa fa-trash-o"></i> Hapus</button>
											</form>
										</div>
									</div>
								@endforeach
							</div>
						@endif
					</div>
				</main>
			</div>
		</div>
	</section>
@endsection
