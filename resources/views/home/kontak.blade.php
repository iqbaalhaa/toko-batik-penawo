@extends('layouts.app')

@section('title', 'Batik Penawuo | Kontak')

@push('styles')
<style>
	.kontak-intro { max-width: 560px; margin: 0 auto; }
	.kontak-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
		gap: 22px;
		margin-top: 44px;
	}
	.kontak-card {
		border: 1px solid #ece8de;
		border-radius: 10px;
		background: #fff;
		padding: 34px 28px 30px;
		text-align: center;
		transition: box-shadow .25s, transform .25s, border-color .25s;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	.kontak-card:hover {
		border-color: #c29e5c;
		box-shadow: 0 10px 28px rgba(194, 158, 92, .12);
		transform: translateY(-3px);
	}
	.kontak-icon {
		width: 58px; height: 58px;
		border-radius: 50%;
		background: #f8f3e9;
		color: #c29e5c;
		display: flex; align-items: center; justify-content: center;
		font-size: 22px;
		margin-bottom: 18px;
	}
	.kontak-card h4 {
		font-family: Poppins-Medium;
		font-size: 16px;
		color: #2d2a26;
		margin-bottom: 10px;
	}
	.kontak-card p { font-size: 14px; color: #888; line-height: 1.7; margin: 0; }
	.kontak-card .kontak-utama { color: #555; font-family: Poppins-Medium; font-size: 15px; }
	.kontak-card .kontak-sub { font-size: 12.5px; color: #9a9288; margin-top: 6px; }
	.kontak-aksi {
		margin-top: auto;
		padding-top: 18px;
	}
	.kontak-btn {
		display: inline-flex; align-items: center; gap: 8px;
		font-size: 13px;
		padding: 9px 22px;
		border-radius: 999px;
		border: 1px solid #c29e5c;
		color: #c29e5c;
		transition: background .2s, color .2s;
	}
	.kontak-btn:hover { background: #c29e5c; color: #fff; text-decoration: none; }
	.kontak-btn--wa { background: #25d366; border-color: #25d366; color: #fff; }
	.kontak-btn--wa:hover { background: #1ebe5b; border-color: #1ebe5b; color: #fff; }
	.kontak-sosmed { display: flex; gap: 14px; justify-content: center; margin-top: auto; padding-top: 18px; }
	.kontak-sosmed a {
		width: 38px; height: 38px; border-radius: 50%;
		border: 1px solid #ece8de;
		display: flex; align-items: center; justify-content: center;
		color: #8d8579; font-size: 15px;
		transition: all .2s;
	}
	.kontak-sosmed a:hover { background: #c29e5c; border-color: #c29e5c; color: #fff; text-decoration: none; }
	.kontak-maps {
		margin-top: 50px;
		border-radius: 10px;
		overflow: hidden;
		border: 1px solid #ece8de;
	}
	.kontak-maps iframe { display: block; width: 100%; height: 380px; border: 0; }
</style>
@endpush

@section('content')
	<!-- Title page -->
	<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('{{ asset('frontend/images/bg-01.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Hubungi Kami</h2>
	</section>

	<!-- Content page -->
	<section class="bg0 p-t-75 p-b-100">
		<div class="container">
			<div class="kontak-intro txt-center">
				<h3 class="mtext-111 cl2 p-b-16">Kami Siap Membantu</h3>
				<p class="stext-113 cl6">
					Ada pertanyaan seputar produk, pesanan, atau pengiriman?
					Hubungi {{ $setting('store_name', 'Batik Penawuo') }} melalui salah satu kanal di bawah ini.
				</p>
			</div>

			@php
				// Normalisasi nomor telepon ke format wa.me: buang non-digit, 0 di depan → 62.
				$telp   = (string) $setting('contact_phone', '(+62) 812-3456-7890');
				$waNum  = preg_replace('/\D+/', '', $telp);
				if (str_starts_with($waNum, '0'))  $waNum = '62' . substr($waNum, 1);
				$email  = $setting('contact_email', 'halo@batikpenawuo.id');
				$maps   = trim((string) $setting('contact_maps_embed', ''));
				$sosmed = array_filter([
					'fa-facebook'  => $setting('social_facebook'),
					'fa-instagram' => $setting('social_instagram'),
					'fa-pinterest' => $setting('social_pinterest'),
					'fa-youtube-play' => $setting('social_youtube'),
				]);
			@endphp

			<div class="kontak-grid">
				<!-- Alamat -->
				<div class="kontak-card">
					<div class="kontak-icon"><span class="lnr lnr-map-marker"></span></div>
					<h4>Alamat Toko</h4>
					<p class="kontak-utama">{{ $setting('store_name', 'Batik Penawuo') }}</p>
					<p>{{ $setting('contact_address', 'Jl. Malioboro No. 123, Kerinci 55213, Indonesia') }}</p>
					@if($maps !== '')
						<div class="kontak-aksi">
							<a class="kontak-btn" href="{{ str_contains($maps, '/embed') ? 'https://maps.google.com/?q=' . urlencode($setting('contact_address', '')) : $maps }}" target="_blank" rel="noopener">
								<i class="fa fa-map-o"></i> Buka di Google Maps
							</a>
						</div>
					@endif
				</div>

				<!-- Telepon / WhatsApp -->
				<div class="kontak-card">
					<div class="kontak-icon"><span class="lnr lnr-phone-handset"></span></div>
					<h4>Telepon / WhatsApp</h4>
					<p class="kontak-utama">{{ $telp }}</p>
					@if($setting('contact_hours'))
						<p class="kontak-sub"><i class="fa fa-clock-o"></i> {{ $setting('contact_hours') }}</p>
					@endif
					<div class="kontak-aksi">
						<a class="kontak-btn kontak-btn--wa" href="https://wa.me/{{ $waNum }}" target="_blank" rel="noopener">
							<i class="fa fa-whatsapp"></i> Chat via WhatsApp
						</a>
					</div>
				</div>

				<!-- Email & Sosmed -->
				<div class="kontak-card">
					<div class="kontak-icon"><span class="lnr lnr-envelope"></span></div>
					<h4>Dukungan Pelanggan</h4>
					<p class="kontak-utama">{{ $email }}</p>
					<p class="kontak-sub">Kami membalas pada jam operasional toko.</p>
					@if($sosmed)
						<div class="kontak-sosmed">
							@foreach($sosmed as $icon => $url)
								<a href="{{ $url }}" target="_blank" rel="noopener" aria-label="Sosial media"><i class="fa {{ $icon }}"></i></a>
							@endforeach
						</div>
					@endif
				</div>
			</div>

			@if($maps !== '' && str_contains($maps, '/embed'))
				<!-- Peta hanya dirender bila admin mengisi URL embed Google Maps -->
				<div class="kontak-maps">
					<iframe src="{{ $maps }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				</div>
			@endif
		</div>
	</section>
@endsection
