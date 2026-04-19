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
			<div class="flex-c-m flex-w w-full p-t-38">
				<a href="#" class="flex-c-m how-pagination1 trans-04 m-all-7 active-pagination1">1</a>
				<a href="#" class="flex-c-m how-pagination1 trans-04 m-all-7">2</a>
				<a href="#" class="flex-c-m how-pagination1 trans-04 m-all-7">3</a>
			</div>
		</div>
	</div>
@endsection
