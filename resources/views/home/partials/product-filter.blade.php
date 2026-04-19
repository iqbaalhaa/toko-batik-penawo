<div class="flex-w flex-sb-m p-b-52">
	<div class="flex-w flex-l-m filter-tope-group m-tb-10">
		<button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5 how-active1" data-filter="*">Semua Produk</button>
		@foreach(($categories ?? []) as $cat)
			<button class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5" data-filter=".{{ $cat->slug }}">{{ $cat->name }}</button>
		@endforeach
	</div>

	<div class="flex-w flex-c-m m-tb-10">
		<div class="flex-c-m stext-106 cl6 size-104 bor4 pointer hov-btn3 trans-04 m-r-8 m-tb-4 js-show-filter">
			<i class="icon-filter cl2 m-r-6 fs-15 trans-04 zmdi zmdi-filter-list"></i>
			<i class="icon-close-filter cl2 m-r-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
			 Filter
		</div>

		<div class="flex-c-m stext-106 cl6 size-105 bor4 pointer hov-btn3 trans-04 m-tb-4 js-show-search">
			<i class="icon-search cl2 m-r-6 fs-15 trans-04 zmdi zmdi-search"></i>
			<i class="icon-close-search cl2 m-r-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
			Cari
		</div>
	</div>

	<!-- Search product -->
	<div class="dis-none panel-search w-full p-t-10 p-b-15">
		<div class="bor8 dis-flex p-l-15">
			<button class="size-113 flex-c-m fs-16 cl2 hov-cl1 trans-04">
				<i class="zmdi zmdi-search"></i>
			</button>

			<input class="mtext-107 cl2 size-114 plh2 p-r-15" type="text" name="search-product" placeholder="Cari produk">
		</div>
	</div>

	<!-- Filter -->
	<div class="dis-none panel-filter w-full p-t-10">
		<div class="wrap-filter flex-w bg6 w-full p-lr-40 p-t-27 p-lr-15-sm">
			<div class="filter-col1 p-r-15 p-b-27">
				<div class="mtext-102 cl2 p-b-15">Urutkan</div>
				<ul>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Default</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Populer</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Rating Tertinggi</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04 filter-link-active">Terbaru</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Harga: Terendah</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Harga: Tertinggi</a></li>
				</ul>
			</div>

			<div class="filter-col2 p-r-15 p-b-27">
				<div class="mtext-102 cl2 p-b-15">Harga</div>
				<ul>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04 filter-link-active">Semua</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Rp0 - Rp200.000</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Rp200.000 - Rp400.000</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Rp400.000 - Rp600.000</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Rp600.000 - Rp800.000</a></li>
					<li class="p-b-6"><a href="#" class="filter-link stext-106 trans-04">Rp800.000+</a></li>
				</ul>
			</div>

			<div class="filter-col3 p-r-15 p-b-27">
				<div class="mtext-102 cl2 p-b-15">Warna</div>
				<ul>
					<li class="p-b-6"><span class="fs-15 lh-12 m-r-6" style="color: #222;"><i class="zmdi zmdi-circle"></i></span><a href="#" class="filter-link stext-106 trans-04">Hitam</a></li>
					<li class="p-b-6"><span class="fs-15 lh-12 m-r-6" style="color: #4272d7;"><i class="zmdi zmdi-circle"></i></span><a href="#" class="filter-link stext-106 trans-04 filter-link-active">Biru</a></li>
					<li class="p-b-6"><span class="fs-15 lh-12 m-r-6" style="color: #b3b3b3;"><i class="zmdi zmdi-circle"></i></span><a href="#" class="filter-link stext-106 trans-04">Abu-abu</a></li>
					<li class="p-b-6"><span class="fs-15 lh-12 m-r-6" style="color: #00ad5f;"><i class="zmdi zmdi-circle"></i></span><a href="#" class="filter-link stext-106 trans-04">Hijau</a></li>
					<li class="p-b-6"><span class="fs-15 lh-12 m-r-6" style="color: #fa4251;"><i class="zmdi zmdi-circle"></i></span><a href="#" class="filter-link stext-106 trans-04">Merah</a></li>
					<li class="p-b-6"><span class="fs-15 lh-12 m-r-6" style="color: #aaa;"><i class="zmdi zmdi-circle-o"></i></span><a href="#" class="filter-link stext-106 trans-04">Putih</a></li>
				</ul>
			</div>

			<div class="filter-col4 p-b-27">
				<div class="mtext-102 cl2 p-b-15">Tag</div>
				<div class="flex-w p-t-4 m-r--5">
					<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5">Batik Tulis</a>
					<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5">Batik Cap</a>
					<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5">Sogan</a>
					<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5">Pesisir</a>
					<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5">Handmade</a>
				</div>
			</div>
		</div>
	</div>
</div>
