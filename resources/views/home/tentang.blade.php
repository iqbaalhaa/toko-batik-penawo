@extends('layouts.app')

@section('title', 'Batik Penawo | Tentang Kami')

@section('content')
	<!-- Title page -->
	<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('{{ asset('frontend/images/bg-01.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">{{ $setting('about_title', 'Tentang Kami') }}</h2>
		@if($setting('about_subtitle'))
			<p class="stext-107 cl0 p-t-12" style="opacity:.85;">{{ $setting('about_subtitle') }}</p>
		@endif
	</section>

	<!-- Content page -->
	<section class="bg0 p-t-75 p-b-120">
		<div class="container">
			<div class="row p-b-148">
				<div class="col-md-7 col-lg-8">
					<div class="p-t-7 p-r-85 p-r-15-lg p-r-0-md">
						<h3 class="mtext-111 cl2 p-b-16">Cerita Kami</h3>

						<div class="stext-113 cl6 p-b-26" style="white-space:pre-line;">{{ $setting('about_story', 'Batik Penawo lahir dari kecintaan pada warisan budaya Nusantara. Kami memulai perjalanan sebagai usaha keluarga di Kerinci, merangkul para pengrajin batik lokal untuk menghadirkan kain-kain bermotif klasik maupun kontemporer.') }}</div>

						<p class="stext-113 cl6 p-b-26">
							Ada pertanyaan? Kunjungi toko kami di {{ $setting('contact_address', 'Jl. Malioboro No. 123, Kerinci') }} atau hubungi kami di {{ $setting('contact_phone', '(+62) 812-3456-7890') }}.
						</p>
					</div>
				</div>

				<div class="col-11 col-md-5 col-lg-4 m-lr-auto">
					<div class="how-bor1 ">
						<div class="hov-img0">
							<img src="{{ asset('frontend/images/about-01.jpg') }}" alt="Cerita Batik Penawo">
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="order-md-2 col-md-7 col-lg-8 p-b-30">
					<div class="p-t-7 p-l-85 p-l-15-lg p-l-0-md">
						<h3 class="mtext-111 cl2 p-b-16">Misi Kami</h3>

						<div class="stext-113 cl6 p-b-26" style="white-space:pre-line;">{{ $setting('about_mission', 'Misi kami sederhana: melestarikan batik Indonesia dan memberdayakan para pengrajin lokal.') }}</div>

						@if($setting('about_quote'))
						<div class="bor16 p-l-29 p-b-9 m-t-22">
							<p class="stext-114 cl6 p-r-40 p-b-11">
								{{ $setting('about_quote') }}
							</p>

							<span class="stext-111 cl8">
								- Tim {{ $setting('store_name', 'Batik Penawo') }}
							</span>
						</div>
						@endif
					</div>
				</div>

				<div class="order-md-1 col-11 col-md-5 col-lg-4 m-lr-auto p-b-30">
					<div class="how-bor2">
						<div class="hov-img0">
							<img src="{{ asset('frontend/images/about-02.jpg') }}" alt="Misi Batik Penawo">
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection
