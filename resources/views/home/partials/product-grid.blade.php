@once
	@push('styles')
	<style>
		/* Normalisasi kartu produk: rasio gambar tetap (1:1) supaya seluruh kartu
		   seragam, tidak ikut tinggi gambar aslinya. Gambar pakai object-fit cover. */
		.block2-pic { position: relative; aspect-ratio: 1 / 1; width: 100%; background: #faf7ef; overflow: hidden; }
		.block2-pic > a.dis-block,
		.block2-pic > a.dis-block img { width: 100%; height: 100%; display: block; }
		.block2-pic > a.dis-block img { object-fit: cover; object-position: center; }
		/* Fallback untuk browser tanpa aspect-ratio (Safari <15) */
		@supports not (aspect-ratio: 1 / 1) {
			.block2-pic { padding-top: 100%; height: 0; }
			.block2-pic > a.dis-block { position: absolute; inset: 0; }
		}
	</style>
	@endpush
@endonce
@if(count($products) === 0)
	<div class="txt-center p-t-50 p-b-50">
		<i class="fa fa-search fs-40" style="color:#d9d2c2;"></i>
		<p class="mtext-111 cl2 p-t-20">Produk tidak ditemukan</p>
		<p class="stext-107 cl6 p-t-6">Coba kata kunci lain atau lihat semua produk.</p>
		<a href="{{ route('produk') }}" class="flex-c-m stext-101 cl0 size-103 bg1 bor1 hov-btn1 p-lr-15 trans-04 m-t-20" style="display:inline-flex;">
			Lihat Semua Produk
		</a>
	</div>
@endif
<div class="row isotope-grid">
	@foreach($products as $p)
	<div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item {{ $p->categories->pluck('slug')->join(' ') }}">
		<div class="block2">
			<div class="block2-pic hov-img0">
				<a href="{{ route('produk.detail', $p->slug) }}" class="dis-block">
					<img src="{{ $p->image_url }}" alt="{{ $p->name }}">
				</a>

				<a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1"
					data-slug="{{ $p->slug }}"
					data-name="{{ $p->name }}"
					data-price="{{ $rupiah($p->price) }}"
					data-description="{{ $p->description }}"
					data-image="{{ $p->image_url }}"
					data-images="{{ json_encode($p->image_urls) }}"
					data-sizes="{{ json_encode($p->sizes ?? []) }}"
					data-colors="{{ json_encode($p->colors ?? []) }}"
					data-detail-url="{{ route('produk.detail', $p->slug) }}"
				>Lihat Cepat</a>
			</div>

			<div class="block2-txt flex-w flex-t p-t-14">
				<div class="block2-txt-child1 flex-col-l ">
					<a href="{{ route('produk.detail', $p->slug) }}" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">{{ $p->name }}</a>
					<span class="stext-105 cl3">{{ $rupiah($p->price) }}</span>
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
