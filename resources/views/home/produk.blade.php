@extends('layouts.app')

@section('title', 'Batik Penawo | Produk')

@section('content')
	<!-- Title page -->
	<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Produk</h2>
	</section>

	<!-- breadcrumb -->
	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>

			<span class="stext-109 cl4">Produk</span>
		</div>
	</div>

	<!-- Product -->
	<div class="bg0 m-t-23 p-b-140">
		<div class="container">
			@include('home.partials.product-filter')
			@include('home.partials.product-grid')

			<!-- Pagination -->
			@if($products->hasPages())
			<div class="flex-c-m flex-w w-full p-t-38">
				@if(! $products->onFirstPage())
					<a href="{{ $products->previousPageUrl() }}" class="flex-c-m how-pagination1 trans-04 m-all-7" aria-label="Sebelumnya">&laquo;</a>
				@endif

				@foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
					@if($page == $products->currentPage())
						<span class="flex-c-m how-pagination1 m-all-7 active-pagination1">{{ $page }}</span>
					@else
						<a href="{{ $url }}" class="flex-c-m how-pagination1 trans-04 m-all-7">{{ $page }}</a>
					@endif
				@endforeach

				@if($products->hasMorePages())
					<a href="{{ $products->nextPageUrl() }}" class="flex-c-m how-pagination1 trans-04 m-all-7" aria-label="Selanjutnya">&raquo;</a>
				@endif
			</div>
			@endif
		</div>
	</div>
@endsection
