@extends('layouts.app')

@section('title', 'Batik Penawo | Beranda')

@push('styles')
<style>
	.section-slide .item-slick1.hero-split {
		height: auto !important;
		min-height: 540px;
		background: transparent !important;
		background-image: none !important;
		padding: 50px 0 30px;
	}
	.hero-row { align-items: center; min-height: 440px; }
	.hero-text-col { padding: 20px 24px; }
	.hero-image-col { padding: 0 24px; display: flex; justify-content: center; align-items: center; }
	.hero-image-wrap { width: 100%; text-align: center; background: transparent; }
	.hero-image-wrap img {
		width: auto; max-width: 100%; height: auto;
		display: inline-block;
		background: transparent;
	}
	/* CTA hero — lebar lebih pendek, tinggi tetap */
	.hero-text-col .layer-slick1 .size-101 {
		min-width: 0;
		padding: 0 22px;
	}
	@media (max-width: 991px) {
		.section-slide .item-slick1.hero-split { min-height: auto; padding: 30px 0; }
		.hero-row { min-height: 0; }
		.hero-image-col { order: -1; margin-bottom: 18px; padding: 0 16px; }
		.hero-text-col { padding: 10px 16px 28px; text-align: center; }
	}
</style>
@endpush

@section('content')
	<!-- Slider -->
	<section class="section-slide">
		<div class="wrap-slick1">
			<div class="slick1">
				@forelse($banners as $banner)
					<div class="item-slick1 hero-split">
						<div class="container h-full">
							<div class="row hero-row">
								<div class="col-md-6 hero-text-col">
									@if($banner->subtitle)
									<div class="layer-slick1 animated visible-false" data-appear="fadeInDown" data-delay="0">
										<span class="ltext-101 cl2 respon2">
											{{ $banner->subtitle }}
										</span>
									</div>
									@endif

									<div class="layer-slick1 animated visible-false" data-appear="fadeInUp" data-delay="800">
										<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1">
											{{ $banner->title }}
										</h2>
									</div>

									<div class="layer-slick1 animated visible-false" data-appear="zoomIn" data-delay="1600">
										<a href="{{ $banner->link ?: route('produk') }}" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">
											{{ $banner->cta_text ?: 'Belanja Sekarang' }}
										</a>
									</div>
								</div>

								<div class="col-md-6 hero-image-col">
									<div class="hero-image-wrap layer-slick1 animated visible-false" data-appear="fadeIn" data-delay="400">
										<img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" style="max-height: {{ $banner->image_max_height ?? 480 }}px;">
									</div>
								</div>
							</div>
						</div>
					</div>
				@empty
					<div class="item-slick1 hero-split">
						<div class="container h-full">
							<div class="row hero-row">
								<div class="col-md-6 hero-text-col">
									<div class="layer-slick1 animated visible-false" data-appear="fadeInDown" data-delay="0">
										<span class="ltext-101 cl2 respon2">Selamat Datang</span>
									</div>

									<div class="layer-slick1 animated visible-false" data-appear="fadeInUp" data-delay="800">
										<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1">{{ $setting('store_name', 'Batik Penawo') }}</h2>
									</div>

									<div class="layer-slick1 animated visible-false" data-appear="zoomIn" data-delay="1600">
										<a href="{{ route('produk') }}" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">Belanja Sekarang</a>
									</div>
								</div>

								<div class="col-md-6 hero-image-col">
									<div class="hero-image-wrap layer-slick1 animated visible-false" data-appear="fadeIn" data-delay="400">
										<img src="{{ asset('frontend/images/slide-01.jpg') }}" alt="Batik Penawo" style="max-height: 480px;">
									</div>
								</div>
							</div>
						</div>
					</div>
				@endforelse
			</div>
		</div>
	</section>

	<!-- Banner -->
	<div class="sec-banner bg0 p-t-80 p-b-50">
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-xl-4 p-b-30 m-lr-auto">
					<div class="block1 wrap-pic-w">
						<img src="{{ asset('frontend/images/banner-01.jpg') }}" alt="BANNER">
						<a href="{{ route('produk') }}" class="block1-txt ab-t-l s-full flex-col-l-sb p-lr-38 p-tb-34 trans-03 respon3">
							<div class="block1-txt-child1 flex-col-l">
								<span class="block1-name ltext-102 trans-04 p-b-8">Batik Wanita</span>
								<span class="block1-info stext-102 trans-04">Koleksi 2026</span>
							</div>

							<div class="block1-txt-child2 p-b-4 trans-05">
								<div class="block1-link stext-101 cl0 trans-09">Belanja Sekarang</div>
							</div>
						</a>
					</div>
				</div>

				<div class="col-md-6 col-xl-4 p-b-30 m-lr-auto">
					<div class="block1 wrap-pic-w">
						<img src="{{ asset('frontend/images/banner-02.jpg') }}" alt="BANNER">
						<a href="{{ route('produk') }}" class="block1-txt ab-t-l s-full flex-col-l-sb p-lr-38 p-tb-34 trans-03 respon3">
							<div class="block1-txt-child1 flex-col-l">
								<span class="block1-name ltext-102 trans-04 p-b-8">Batik Pria</span>
								<span class="block1-info stext-102 trans-04">Koleksi 2026</span>
							</div>

							<div class="block1-txt-child2 p-b-4 trans-05">
								<div class="block1-link stext-101 cl0 trans-09">Belanja Sekarang</div>
							</div>
						</a>
					</div>
				</div>

				<div class="col-md-6 col-xl-4 p-b-30 m-lr-auto">
					<div class="block1 wrap-pic-w">
						<img src="{{ asset('frontend/images/banner-03.jpg') }}" alt="BANNER">
						<a href="{{ route('produk') }}" class="block1-txt ab-t-l s-full flex-col-l-sb p-lr-38 p-tb-34 trans-03 respon3">
							<div class="block1-txt-child1 flex-col-l">
								<span class="block1-name ltext-102 trans-04 p-b-8">Aksesoris</span>
								<span class="block1-info stext-102 trans-04">Tren Terbaru</span>
							</div>

							<div class="block1-txt-child2 p-b-4 trans-05">
								<div class="block1-link stext-101 cl0 trans-09">Belanja Sekarang</div>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Product -->
	<section class="bg0 p-t-23 p-b-140">
		<div class="container">
			<div class="p-b-10">
				<h3 class="ltext-103 cl5">Produk Unggulan</h3>
			</div>

			@include('home.partials.product-filter')
			@include('home.partials.product-grid')

			<!-- Load more -->
			<div class="flex-c-m flex-w w-full p-t-45">
				<a href="{{ route('produk') }}" class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04">Muat Lebih Banyak</a>
			</div>
		</div>
	</section>
@endsection
