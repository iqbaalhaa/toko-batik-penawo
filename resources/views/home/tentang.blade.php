@extends('layouts.app')

@section('title', 'Batik Penawo | Tentang Kami')

@section('content')
	<!-- Title page -->
	<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('{{ asset('frontend/images/bg-01.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Tentang Kami</h2>
	</section>

	<!-- Content page -->
	<section class="bg0 p-t-75 p-b-120">
		<div class="container">
			<div class="row p-b-148">
				<div class="col-md-7 col-lg-8">
					<div class="p-t-7 p-r-85 p-r-15-lg p-r-0-md">
						<h3 class="mtext-111 cl2 p-b-16">Cerita Kami</h3>

						<p class="stext-113 cl6 p-b-26">
							Batik Penawo lahir dari kecintaan pada warisan budaya Nusantara. Kami memulai perjalanan sebagai usaha keluarga di Yogyakarta, merangkul para pengrajin batik lokal untuk menghadirkan kain-kain bermotif klasik maupun kontemporer. Setiap helai batik kami ditulis atau dicap dengan tangan, memastikan setiap detailnya memiliki jiwa dan cerita.
						</p>

						<p class="stext-113 cl6 p-b-26">
							Nama "Penawo" diambil dari kata Jawa yang bermakna "penawar" — harapan kami bahwa setiap batik yang kami produksi mampu menjadi penawar rindu akan keindahan tradisi, sekaligus menjadi kebanggaan saat dikenakan pada berbagai kesempatan. Dari kemeja harian, gamis modern, hingga kain panjang untuk acara istimewa, Batik Penawo hadir menemani setiap momen berharga Anda.
						</p>

						<p class="stext-113 cl6 p-b-26">
							Ada pertanyaan? Kunjungi toko kami di Jl. Malioboro No. 123, Yogyakarta atau hubungi kami di (+62) 812-3456-7890.
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

						<p class="stext-113 cl6 p-b-26">
							Misi kami sederhana: melestarikan batik Indonesia dan memberdayakan para pengrajin lokal. Kami bekerja sama langsung dengan pembatik di Yogyakarta, Solo, Pekalongan, dan Cirebon untuk memastikan setiap produk yang Anda beli memberi dampak positif bagi mereka yang melestarikan seni ini. Kami percaya batik bukan sekadar kain — ia adalah identitas, cerita, dan warisan yang pantas diteruskan ke generasi berikutnya.
						</p>

						<div class="bor16 p-l-29 p-b-9 m-t-22">
							<p class="stext-114 cl6 p-r-40 p-b-11">
								Batik bukan hanya pakaian, tetapi bahasa budaya yang menuturkan siapa kita, dari mana kita berasal, dan kebanggaan apa yang kita bawa.
							</p>

							<span class="stext-111 cl8">
								- Tim Batik Penawo
							</span>
						</div>
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
