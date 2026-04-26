@extends('layouts.app')

@section('title', 'Batik Penawo | '.$product->name)

@php
	// Pakai semua foto produk; jika hanya satu, lengkapi dengan gambar template agar slider tidak kosong.
	$gallery = $product->image_urls;
	if (count($gallery) < 2) {
		$gallery = array_merge($gallery, [
			asset('frontend/images/product-detail-02.jpg'),
			asset('frontend/images/product-detail-03.jpg'),
		]);
	}
	$related = \App\Models\Product::where('id', '!=', $product->id)
		->where('status', '!=', 'arsip')
		->inRandomOrder()
		->take(6)
		->get();
@endphp

@section('content')
	<!-- breadcrumb -->
	<div class="container p-t-80">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>

			<a href="{{ route('produk') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Produk
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>

			<span class="stext-109 cl4">{{ $product->name }}</span>
		</div>
	</div>

	<!-- Product Detail -->
	<section class="sec-product-detail bg0 p-t-65 p-b-60">
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-lg-7 p-b-30">
					<div class="p-l-25 p-r-30 p-lr-0-lg">
						<div class="wrap-slick3 flex-sb flex-w">
							<div class="wrap-slick3-dots"></div>
							<div class="wrap-slick3-arrows flex-sb-m flex-w"></div>

							<div class="slick3 gallery-lb">
								@foreach($gallery as $img)
								<div class="item-slick3" data-thumb="{{ $img }}">
									<div class="wrap-pic-w pos-relative">
										<img src="{{ $img }}" alt="{{ $product->name }}">

										<a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="{{ $img }}">
											<i class="fa fa-expand"></i>
										</a>
									</div>
								</div>
								@endforeach
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-6 col-lg-5 p-b-30">
					<div class="p-r-50 p-t-5 p-lr-0-lg">
						<h4 class="mtext-105 cl2 js-name-detail p-b-14">{{ $product->name }}</h4>

						<span class="mtext-106 cl2">{{ $rupiah($product->price) }}</span>

						<p class="stext-102 cl3 p-t-23">
							{{ $product->description }}
						</p>

						<div class="p-t-33">
							<div class="flex-w flex-r-m p-b-10">
								<div class="size-203 flex-c-m respon6">Ukuran</div>
								<div class="size-204 respon6-next">
									<div class="rs1-select2 bor8 bg0">
										<select class="js-select2" name="size">
											<option>Pilih ukuran</option>
											@foreach(($product->sizes ?? []) as $size)
												<option>{{ $size }}</option>
											@endforeach
										</select>
										<div class="dropDownSelect2"></div>
									</div>
								</div>
							</div>

							<div class="flex-w flex-r-m p-b-10">
								<div class="size-203 flex-c-m respon6">Warna</div>
								<div class="size-204 respon6-next">
									<div class="rs1-select2 bor8 bg0">
										<select class="js-select2" name="color">
											<option>Pilih warna</option>
											@foreach(($product->colors ?? []) as $color)
												<option>{{ $color }}</option>
											@endforeach
										</select>
										<div class="dropDownSelect2"></div>
									</div>
								</div>
							</div>

							<form action="{{ route('keranjang.add') }}" method="POST" class="flex-w flex-r-m p-b-10">
								@csrf
								<input type="hidden" name="slug" value="{{ $product->slug }}">
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
							</form>
						</div>

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

							<a href="#" class="fs-14 cl3 hov-cl1 trans-04 lh-10 p-lr-5 p-tb-2 m-r-8 tooltip100" data-tooltip="WhatsApp">
								<i class="fa fa-whatsapp"></i>
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="bor10 m-t-50 p-t-43 p-b-40">
				<!-- Tab01 -->
				<div class="tab01">
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item p-b-10">
							<a class="nav-link active" data-toggle="tab" href="#description" role="tab">Deskripsi</a>
						</li>

						<li class="nav-item p-b-10">
							<a class="nav-link" data-toggle="tab" href="#information" role="tab">Informasi Tambahan</a>
						</li>

						<li class="nav-item p-b-10">
							<a class="nav-link" data-toggle="tab" href="#reviews" role="tab">Ulasan (1)</a>
						</li>
					</ul>

					<div class="tab-content p-t-43">
						<div class="tab-pane fade show active" id="description" role="tabpanel">
							<div class="how-pos2 p-lr-15-md">
								<p class="stext-102 cl6">
									{{ $product->description }} Setiap produk Batik Penawo dibuat dengan ketelitian oleh pengrajin batik Indonesia menggunakan bahan berkualitas. Pola dapat sedikit berbeda antar produk karena proses handmade.
								</p>
							</div>
						</div>

						<div class="tab-pane fade" id="information" role="tabpanel">
							<div class="row">
								<div class="col-sm-10 col-md-8 col-lg-6 m-lr-auto">
									<ul class="p-lr-28 p-lr-15-sm">
										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">Berat</span>
											<span class="stext-102 cl6 size-206">{{ $product->weight ?? '—' }}</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">Bahan</span>
											<span class="stext-102 cl6 size-206">{{ $product->material ?? '—' }}</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">Warna</span>
											<span class="stext-102 cl6 size-206">{{ implode(', ', $product->colors ?? []) }}</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">Ukuran</span>
											<span class="stext-102 cl6 size-206">{{ implode(', ', $product->sizes ?? []) }}</span>
										</li>

										<li class="flex-w flex-t p-b-7">
											<span class="stext-102 cl3 size-205">Kategori</span>
											<span class="stext-102 cl6 size-206">{{ $product->categories->pluck('name')->join(', ') ?: '—' }}</span>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="tab-pane fade" id="reviews" role="tabpanel">
							<div class="row">
								<div class="col-sm-10 col-md-8 col-lg-6 m-lr-auto">
									<div class="p-b-30 m-lr-15-sm">
										<div class="flex-w flex-t p-b-68">
											<div class="wrap-pic-s size-109 bor0 of-hidden m-r-18 m-t-6">
												<img src="{{ asset('frontend/images/avatar-01.jpg') }}" alt="AVATAR">
											</div>

											<div class="size-207">
												<div class="flex-w flex-sb-m p-b-17">
													<span class="mtext-107 cl2 p-r-20">Siti Ramadhani</span>

													<span class="fs-18 cl11">
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star-half-o"></i>
													</span>
												</div>

												<p class="stext-102 cl6">
													Bahannya adem dan motifnya rapi banget. Pengirimannya cepat dan packaging-nya rapih. Pasti repeat order!
												</p>
											</div>
										</div>

										<form class="w-full" action="#" method="POST">
											@csrf
											<h5 class="mtext-108 cl2 p-b-7">Tulis Ulasan</h5>

											<p class="stext-102 cl6">
												Alamat email Anda tidak akan dipublikasikan. Kolom wajib ditandai *
											</p>

											<div class="flex-w flex-m p-t-50 p-b-23">
												<span class="stext-102 cl3 m-r-16">Rating Anda</span>

												<span class="wrap-rating fs-18 cl11 pointer">
													<i class="item-rating pointer fa fa-star-o"></i>
													<i class="item-rating pointer fa fa-star-o"></i>
													<i class="item-rating pointer fa fa-star-o"></i>
													<i class="item-rating pointer fa fa-star-o"></i>
													<i class="item-rating pointer fa fa-star-o"></i>
													<input class="dis-none" type="number" name="rating">
												</span>
											</div>

											<div class="row p-b-25">
												<div class="col-12 p-b-5">
													<label class="stext-102 cl3" for="review">Ulasan Anda</label>
													<textarea class="size-110 bor8 stext-102 cl2 p-lr-20 p-tb-10" id="review" name="review"></textarea>
												</div>

												<div class="col-sm-6 p-b-5">
													<label class="stext-102 cl3" for="name">Nama</label>
													<input class="size-111 bor8 stext-102 cl2 p-lr-20" id="name" type="text" name="name">
												</div>

												<div class="col-sm-6 p-b-5">
													<label class="stext-102 cl3" for="email">Email</label>
													<input class="size-111 bor8 stext-102 cl2 p-lr-20" id="email" type="email" name="email">
												</div>
											</div>

											<button type="submit" class="flex-c-m stext-101 cl0 size-112 bg7 bor11 hov-btn3 p-lr-15 trans-04 m-b-10">Kirim Ulasan</button>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="bg6 flex-c-m flex-w size-302 m-t-73 p-tb-15">
			<span class="stext-107 cl6 p-lr-25">SKU: {{ $product->sku }}</span>
			<span class="stext-107 cl6 p-lr-25">Kategori: {{ $product->categories->pluck('name')->join(', ') ?: '—' }}</span>
		</div>
	</section>

	<!-- Related Products -->
	<section class="sec-relate-product bg0 p-t-45 p-b-105">
		<div class="container">
			<div class="p-b-45">
				<h3 class="ltext-106 cl5 txt-center">Produk Terkait</h3>
			</div>

			<div class="wrap-slick2">
				<div class="slick2">
					@foreach($related as $rp)
					<div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
						<div class="block2">
							<div class="block2-pic hov-img0">
								<a href="{{ route('produk.detail', $rp->slug) }}" class="dis-block">
									<img src="{{ $rp->image_url }}" alt="{{ $rp->name }}">
								</a>

								<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">Lihat Cepat</a>
							</div>

							<div class="block2-txt flex-w flex-t p-t-14">
								<div class="block2-txt-child1 flex-col-l ">
									<a href="{{ route('produk.detail', $rp->slug) }}" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">{{ $rp->name }}</a>
									<span class="stext-105 cl3">{{ $rupiah($rp->price) }}</span>
								</div>

								<div class="block2-txt-child2 flex-r p-t-3">
									<a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
										<img class="icon-heart1 dis-block trans-04" src="{{ asset('frontend/images/icons/icon-heart-01.png') }}" alt="ICON">
										<img class="icon-heart2 dis-block trans-04 ab-t-l" src="{{ asset('frontend/images/icons/icon-heart-02.png') }}" alt="ICON">
									</a>
								</div>
							</div>
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</div>
	</section>
@endsection
