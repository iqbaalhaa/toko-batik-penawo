@extends('layouts.app')

@section('title', 'Batik Penawo | Beranda')

@section('content')
	<!-- Slider -->
	<section class="section-slide">
		<div class="wrap-slick1">
			<div class="slick1">
				<div class="item-slick1" style="background-image: url({{ asset('frontend/images/slide-01.jpg') }});">
					<div class="container h-full">
						<div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
							<div class="layer-slick1 animated visible-false" data-appear="fadeInDown" data-delay="0">
								<span class="ltext-101 cl2 respon2">Koleksi Batik Wanita</span>
							</div>

							<div class="layer-slick1 animated visible-false" data-appear="fadeInUp" data-delay="800">
								<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1">KOLEKSI BARU</h2>
							</div>

							<div class="layer-slick1 animated visible-false" data-appear="zoomIn" data-delay="1600">
								<a href="{{ route('produk') }}" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">Belanja Sekarang</a>
							</div>
						</div>
					</div>
				</div>

				<div class="item-slick1" style="background-image: url({{ asset('frontend/images/slide-02.jpg') }});">
					<div class="container h-full">
						<div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
							<div class="layer-slick1 animated visible-false" data-appear="rollIn" data-delay="0">
								<span class="ltext-101 cl2 respon2">Batik Pria Eksklusif</span>
							</div>

							<div class="layer-slick1 animated visible-false" data-appear="lightSpeedIn" data-delay="800">
								<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1">Kemeja & Blazer Batik</h2>
							</div>

							<div class="layer-slick1 animated visible-false" data-appear="slideInUp" data-delay="1600">
								<a href="{{ route('produk') }}" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">Belanja Sekarang</a>
							</div>
						</div>
					</div>
				</div>

				<div class="item-slick1" style="background-image: url({{ asset('frontend/images/slide-03.jpg') }});">
					<div class="container h-full">
						<div class="flex-col-l-m h-full p-t-100 p-b-30 respon5">
							<div class="layer-slick1 animated visible-false" data-appear="rotateInDownLeft" data-delay="0">
								<span class="ltext-101 cl2 respon2">Batik Penawo</span>
							</div>

							<div class="layer-slick1 animated visible-false" data-appear="rotateInUpRight" data-delay="800">
								<h2 class="ltext-201 cl2 p-t-19 p-b-43 respon1">Produk Terbaru</h2>
							</div>

							<div class="layer-slick1 animated visible-false" data-appear="rotateIn" data-delay="1600">
								<a href="{{ route('produk') }}" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">Belanja Sekarang</a>
							</div>
						</div>
					</div>
				</div>
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
