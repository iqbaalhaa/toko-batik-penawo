<div class="flex-w flex-sb-m p-b-52">
	<div class="flex-w flex-l-m filter-tope-group m-tb-10">
		{{-- Link server-side (bookmarkable) + tetap pakai class isotope (.how-active1)
		     untuk kompat dengan style template. Active state = bandingkan slug
		     dengan ?kategori di URL. --}}
		@php $activeSlug = ($activeCategory ?? null)?->slug; @endphp
		<a href="{{ route('produk') }}"
			class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5 {{ $activeSlug ? '' : 'how-active1' }}"
			data-filter="*">Semua Produk</a>
		@foreach(($categories ?? []) as $cat)
			<a href="{{ route('produk', ['kategori' => $cat->slug]) }}"
				class="stext-106 cl6 hov1 bor3 trans-04 m-r-32 m-tb-5 {{ $activeSlug === $cat->slug ? 'how-active1' : '' }}"
				data-filter=".{{ $cat->slug }}">{{ $cat->name }}</a>
		@endforeach
	</div>

	@php $term = $searchTerm ?? ''; @endphp
	@if($term !== '')
		<!-- Info hasil pencarian aktif + tombol hapus -->
		<div class="w-full p-b-10">
			<div class="flex-w flex-m" style="gap:10px; background:#f6f3ec; border:1px solid #e6e0d2; border-radius:6px; padding:10px 16px;">
				<i class="fa fa-search cl6"></i>
				<span class="stext-106 cl2">
					{{ $products->total() }} produk ditemukan untuk &ldquo;<strong>{{ $term }}</strong>&rdquo;@if($activeSlug) di kategori <strong>{{ $activeCategory->name }}</strong>@endif
				</span>
				<a href="{{ $activeSlug ? route('produk', ['kategori' => $activeSlug]) : route('produk') }}"
					class="stext-106 cl6 hov-cl1 trans-04" style="margin-left:auto;">
					<i class="fa fa-times m-r-4"></i>Hapus pencarian
				</a>
			</div>
		</div>
	@endif

</div>
