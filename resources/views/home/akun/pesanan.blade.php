@extends('layouts.app')

@section('title', 'Batik Penawo | Pesanan Saya')

@push('styles')
<style>
	/* reuse styles — duplicate minimum biar standalone */
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
	.akun-sidebar-menu li.disabled a { color:#bdb7ab; cursor:not-allowed; }
	.akun-sidebar-menu li.disabled a:hover { background:transparent; color:#bdb7ab; }
	.akun-sidebar-menu li.disabled i { color:#d8d1bf !important; }
	.akun-logout-btn { background:none; border:0; padding:0; color:#a5432f; font-size:13px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:color .15s; }
	.akun-logout-btn:hover { color:#8a2f20; }

	.akun-card { background:#fff; border:1px solid #ece8de; border-radius:6px; padding:22px 24px; }
	.akun-card-title { font-size:16px; font-weight:600; color:#2d2a26; margin:0; }
	.akun-card-sub { font-size:12.5px; color:#9a9288; margin-top:4px; }

	.order-card {
		background:#fff; border:1px solid #ece8de; border-radius:6px;
		padding:18px 20px; margin-bottom:14px;
		transition: border-color .15s, box-shadow .15s;
	}
	.order-card:hover { border-color:#c29e5c; box-shadow: 0 2px 8px rgba(194,158,92,.08); }
	.order-head { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; padding-bottom:12px; border-bottom:1px solid #f2efe7; margin-bottom:12px; }
	.order-inv { font-size:14px; color:#2d2a26; font-weight:600; letter-spacing:.3px; }
	.order-date { font-size:12px; color:#9a9288; margin-top:2px; }

	.order-status { display:inline-block; padding:4px 12px; border-radius:999px; font-size:11.5px; font-weight:500; letter-spacing:.3px; }
	.st-menunggu { background:#fcf1d9; color:#a87318; }
	.st-diproses { background:#fcf1d9; color:#a87318; }
	.st-dikirim  { background:#e1ecf8; color:#3a5fa0; }
	.st-selesai  { background:#e3f3e9; color:#2f7a4c; }
	.st-dibatalkan { background:#fbe4df; color:#a5432f; }

	.order-items-preview { display:flex; gap:10px; margin-bottom:12px; align-items:center; }
	.order-items-preview .preview-name { font-size:13.5px; color:#4d4640; }
	.order-items-preview .preview-count { font-size:12px; color:#9a9288; }

	.order-foot { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
	.order-total-label { font-size:12px; color:#9a9288; }
	.order-total-value { font-size:16px; color:#c29e5c; font-weight:600; }
	.order-btn {
		padding:8px 16px; background:#fff; color:#4d4640;
		border:1px solid #ddd6c6; border-radius:4px;
		font-size:12.5px; font-weight:500; text-decoration:none;
		transition: all .15s;
	}
	.order-btn:hover { border-color:#c29e5c; color:#c29e5c; text-decoration:none; }
	.order-btn-primary { background:#c29e5c; border-color:#c29e5c; color:#fff; }
	.order-btn-primary:hover { background:#a88541; color:#fff; }

	.akun-empty { text-align:center; padding:60px 20px; }
	.akun-empty i { font-size:48px; color:#d8d1bf; margin-bottom:14px; }
	.akun-empty h4 { font-size:16px; color:#4d4640; margin-bottom:6px; }
	.akun-empty p { font-size:13.5px; color:#9a9288; margin-bottom:20px; }

	.akun-pager { display:flex; gap:4px; justify-content:center; margin-top:18px; }
	.akun-pager-btn { min-width:34px; height:34px; padding:0 10px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #e0dbcf; background:#fff; color:#4d4640; border-radius:4px; font-size:13px; text-decoration:none; transition:all .15s; }
	.akun-pager-btn:hover { border-color:#c29e5c; color:#c29e5c; text-decoration:none; }
	.akun-pager-btn.active { background:#c29e5c; border-color:#c29e5c; color:#fff; font-weight:600; }
	.akun-pager-btn.disabled { opacity:.4; cursor:not-allowed; pointer-events:none; }
</style>
@endpush

@section('content')
	@php
		$statusMap = [
			'menunggu_bayar' => ['label' => 'Menunggu Pembayaran', 'class' => 'st-menunggu'],
			'diproses'       => ['label' => 'Diproses',            'class' => 'st-diproses'],
			'dikirim'        => ['label' => 'Dikirim',              'class' => 'st-dikirim'],
			'selesai'        => ['label' => 'Selesai',              'class' => 'st-selesai'],
			'dibatalkan'     => ['label' => 'Dibatalkan',           'class' => 'st-dibatalkan'],
		];
	@endphp

	<section class="bg-img1 txt-center p-lr-15 p-tb-70" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Pesanan Saya</h2>
	</section>

	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda <i class="fa fa-angle-right m-l-9 m-r-10"></i>
			</a>
			<span class="stext-109 cl4">Pesanan Saya</span>
		</div>
	</div>

	<section class="bg0 p-t-30 p-b-80">
		<div class="container">
			<div class="akun-wrap">
				@include('home.akun._sidebar')

				<main>
					<div class="akun-card" style="margin-bottom:18px;">
						<h3 class="akun-card-title">Daftar Pesanan</h3>
						<p class="akun-card-sub">
							@if($orders->total() > 0)
								Total {{ $orders->total() }} pesanan
							@else
								Belum ada pesanan
							@endif
						</p>
					</div>

					@forelse($orders as $order)
						@php $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'st-menunggu']; @endphp
						<div class="order-card">
							<div class="order-head">
								<div>
									<div class="order-inv">{{ $order->invoice_number }}</div>
									<div class="order-date">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</div>
								</div>
								<span class="order-status {{ $s['class'] }}">{{ $s['label'] }}</span>
							</div>

							<div class="order-items-preview">
								<div style="flex:1; min-width:0;">
									@foreach($order->items->take(2) as $it)
										<div class="preview-name">{{ $it->product_name }} <span style="color:#9a9288; font-size:12px;">× {{ $it->qty }}</span></div>
									@endforeach
									@if($order->items->count() > 2)
										<div class="preview-count">+ {{ $order->items->count() - 2 }} produk lainnya</div>
									@endif
								</div>
							</div>

							<div class="order-foot">
								<div>
									<span class="order-total-label">Total</span>
									<span class="order-total-value">{{ $rupiah($order->total) }}</span>
									@php
										$paymentLabel = match($order->payment_method) {
											'Midtrans' => 'Bayar Online',
											'COD'      => 'Bayar di Tempat',
											default    => $order->payment_method ?? '—',
										};
									@endphp
									<span style="color:#9a9288; font-size:12px; margin-left:8px;">· {{ $paymentLabel }}</span>
								</div>
								<a href="{{ route('pesanan.sukses', $order->invoice_number) }}" class="order-btn order-btn-primary">
									<i class="fa fa-eye m-r-4"></i> Lihat Detail
								</a>
							</div>
						</div>
					@empty
						<div class="akun-card">
							<div class="akun-empty">
								<i class="fa fa-file-text-o"></i>
								<h4>Belum ada pesanan</h4>
								<p>Yuk mulai belanja batik nusantara favorit Anda.</p>
								<a href="{{ route('produk') }}" class="order-btn order-btn-primary" style="padding:11px 22px;">
									<i class="fa fa-shopping-basket m-r-4"></i> Belanja Sekarang
								</a>
							</div>
						</div>
					@endforelse

					@if($orders->hasPages())
					<div class="akun-pager">
						@if($orders->onFirstPage())
							<span class="akun-pager-btn disabled"><i class="fa fa-chevron-left"></i></span>
						@else
							<a href="{{ $orders->previousPageUrl() }}" class="akun-pager-btn"><i class="fa fa-chevron-left"></i></a>
						@endif
						@foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
							@if($page == $orders->currentPage())
								<span class="akun-pager-btn active">{{ $page }}</span>
							@else
								<a href="{{ $url }}" class="akun-pager-btn">{{ $page }}</a>
							@endif
						@endforeach
						@if($orders->hasMorePages())
							<a href="{{ $orders->nextPageUrl() }}" class="akun-pager-btn"><i class="fa fa-chevron-right"></i></a>
						@else
							<span class="akun-pager-btn disabled"><i class="fa fa-chevron-right"></i></span>
						@endif
					</div>
					@endif
				</main>
			</div>
		</div>
	</section>
@endsection
