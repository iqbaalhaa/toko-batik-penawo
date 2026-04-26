@extends('layouts.app')

@section('title', 'Batik Penawo | Pembayaran')

@push('styles')
<style>
	.checkout-card {
		background: #fff;
		border: 1px solid #ece8de;
		border-radius: 6px;
		padding: 24px 26px;
	}
	.checkout-card + .checkout-card { margin-top: 18px; }
	.checkout-card-title { font-size: 16px; font-weight: 600; color: #2d2a26; margin: 0 0 16px; }
	.checkout-label { display: block; font-size: 13px; font-weight: 500; color: #4d4640; margin-bottom: 6px; }
	.checkout-input, .checkout-textarea {
		width: 100%;
		padding: 10px 12px;
		font-size: 14px;
		border: 1px solid #e0dbcf;
		border-radius: 4px;
		background: #fff;
		transition: border-color .15s, box-shadow .15s;
		font-family: inherit;
	}
	.checkout-input:focus, .checkout-textarea:focus {
		outline: none; border-color: #c29e5c;
		box-shadow: 0 0 0 3px rgba(194,158,92,.15);
	}
	.checkout-textarea { min-height: 80px; resize: vertical; }

	.payment-option {
		display: flex; align-items: center; gap: 12px;
		padding: 12px 14px;
		border: 1px solid #e0dbcf; border-radius: 4px;
		cursor: pointer; transition: border-color .15s, background .15s;
		margin-bottom: 8px;
	}
	.payment-option:hover { border-color: #c29e5c; }
	.payment-option input[type="radio"] { margin: 0; }
	.payment-option input[type="radio"]:checked ~ .payment-option-body { color: #c29e5c; font-weight: 500; }
	.payment-option:has(input:checked) { border-color: #c29e5c; background: #faf6ed; }
	.payment-option-body { flex: 1; font-size: 13.5px; color: #4d4640; }
	.payment-group-label { font-size: 11.5px; color: #9a9288; text-transform: uppercase; letter-spacing: 1px; margin: 14px 0 8px; font-weight: 600; }
	.payment-group-label:first-child { margin-top: 0; }

	.checkout-items { list-style: none; padding: 0; margin: 0; }
	.checkout-items li { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f2efe7; }
	.checkout-items li:last-child { border-bottom: 0; }
	.checkout-items img { width: 56px; height: 56px; object-fit: cover; border-radius: 4px; background: #f5f2ea; }
	.checkout-item-name { font-size: 13.5px; color: #2d2a26; }
	.checkout-item-qty { font-size: 12px; color: #9a9288; margin-top: 2px; }
	.checkout-item-sub { font-size: 13.5px; color: #c29e5c; font-weight: 500; margin-left: auto; align-self: center; white-space: nowrap; }

	.summary-row { display: flex; justify-content: space-between; font-size: 13.5px; padding: 8px 0; color: #4d4640; }
	.summary-row.total { border-top: 1px solid #ece8de; margin-top: 6px; padding-top: 14px; font-size: 16px; font-weight: 600; color: #2d2a26; }
	.summary-row .free { color: #2f7a4c; font-weight: 500; }

	.checkout-btn-pay {
		display: block; width: 100%;
		background: #c29e5c; color: #fff;
		padding: 14px 20px; border: 0; border-radius: 4px;
		font-size: 14px; font-weight: 600; letter-spacing: .3px;
		cursor: pointer; margin-top: 14px;
		transition: background .15s;
	}
	.checkout-btn-pay:hover { background: #a88541; color: #fff; text-decoration: none; }

	.checkout-errors {
		background: #fbe4df; border: 1px solid #f2c6be;
		color: #a5432f; padding: 10px 14px; border-radius: 4px;
		margin-bottom: 18px; font-size: 13px;
	}
</style>
@endpush

@section('content')
	<!-- Title page -->
	<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Pembayaran</h2>
	</section>

	<!-- breadcrumb -->
	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>
			<a href="{{ route('keranjang') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Keranjang
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>
			<span class="stext-109 cl4">Pembayaran</span>
		</div>
	</div>

	<section class="bg0 p-t-40 p-b-80">
		<div class="container">

			@if($errors->any())
				<div class="checkout-errors">
					<strong>Mohon periksa kembali:</strong>
					<ul style="margin:6px 0 0 18px; padding:0;">
						@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
					</ul>
				</div>
			@endif

			<form action="{{ route('checkout.confirm') }}" method="POST" id="checkoutForm">
				@csrf
				<div class="row">
					<!-- Left: Alamat + Metode Bayar -->
					<div class="col-lg-7 p-b-30">
						<div class="checkout-card">
							<h3 class="checkout-card-title"><i class="fa fa-map-marker m-r-6" style="color:#c29e5c;"></i> Alamat Pengiriman</h3>

							@php
								$prefillAddress = trim(implode(', ', array_filter([
									$user?->address,
									$user?->city,
									$user?->province,
									$user?->postal_code,
								])));
							@endphp
							<div class="row">
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="checkout-label">Nama Penerima <span style="color:#a5432f;">*</span></label>
									<input type="text" name="recipient_name" class="checkout-input" value="{{ old('recipient_name', $user?->name ?? session('auth_user.name')) }}" required>
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="checkout-label">No. Telepon / WhatsApp <span style="color:#a5432f;">*</span></label>
									<input type="text" name="recipient_phone" class="checkout-input" placeholder="08xx xxxx xxxx" value="{{ old('recipient_phone', $user?->phone) }}" required>
								</div>
							</div>
							<div style="margin-bottom:14px;">
								<label class="checkout-label">Alamat Lengkap <span style="color:#a5432f;">*</span></label>
								<textarea name="shipping_address" class="checkout-textarea" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, provinsi, kode pos" required>{{ old('shipping_address', $prefillAddress) }}</textarea>
								@if(! $prefillAddress)
									<div style="font-size:11.5px; color:#9a9288; margin-top:4px;">
										<i class="fa fa-info-circle"></i> Lengkapi alamat di <a href="{{ route('akun.profil') }}" style="color:#c29e5c;">profil Anda</a> agar otomatis terisi di checkout berikutnya.
									</div>
								@endif
							</div>
							<div>
								<label class="checkout-label">Catatan untuk Penjual <small style="color:#9a9288;">(opsional)</small></label>
								<textarea name="note" class="checkout-textarea" placeholder="Contoh: tolong dibungkus kado">{{ old('note') }}</textarea>
							</div>
						</div>

						<div class="checkout-card">
							<h3 class="checkout-card-title"><i class="fa fa-credit-card m-r-6" style="color:#c29e5c;"></i> Metode Pembayaran</h3>

							<label class="payment-option" style="border-color:#c29e5c; background:#faf6ed;">
								<input type="radio" name="payment_method" value="Midtrans" checked>
								<span class="payment-option-body">
									<i class="fa fa-credit-card m-r-6" style="color:#6c665e;"></i>
									Bayar Online
									<div style="font-size:11.5px; color:#9a9288; margin-top:3px;">
										Kartu Kredit, Transfer Bank / VA, E-Wallet (GoPay, OVO, ShopeePay, Dana), QRIS, dan lainnya.
									</div>
								</span>
							</label>
						</div>
					</div>

					<!-- Right: Ringkasan -->
					<div class="col-lg-5 p-b-30">
						<div class="checkout-card" style="position:sticky; top:20px;">
							<h3 class="checkout-card-title">Ringkasan Pesanan</h3>

							<ul class="checkout-items">
								@foreach($items as $item)
								<li>
									<img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
									<div>
										<div class="checkout-item-name">{{ $item['name'] }}</div>
										@php $variantParts = array_filter([$item['size'] ?? null, $item['color'] ?? null]); @endphp
										@if($variantParts)
											<div style="font-size:11.5px; color:#9a9288; margin-top:1px;">{{ implode(' · ', $variantParts) }}</div>
										@endif
										<div class="checkout-item-qty">{{ $item['qty'] }} × {{ $rupiah($item['price']) }}</div>
									</div>
									<div class="checkout-item-sub">{{ $rupiah($item['subtotal']) }}</div>
								</li>
								@endforeach
							</ul>

							<div style="margin-top:14px;">
								<div class="summary-row">
									<span>Subtotal ({{ count($items) }} produk)</span>
									<span>{{ $rupiah($subtotal) }}</span>
								</div>
								<div class="summary-row total">
									<span>Total Bayar</span>
									<span>{{ $rupiah($total) }}</span>
								</div>
							</div>

							<button type="submit" class="checkout-btn-pay" id="btnBayar">
								<i class="fa fa-lock m-r-6"></i> Bayar Sekarang
							</button>

							<div id="checkoutError" class="checkout-errors" style="display:none; margin-top:12px;"></div>

							<div style="margin-top:10px; text-align:center;">
								<a href="{{ route('keranjang') }}" class="stext-106 cl6 hov-cl1 trans-04" style="font-size:12.5px;">
									<i class="fa fa-chevron-left"></i> Kembali ke Keranjang
								</a>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</section>

	<script src="https://app.{{ config('services.midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js"
		data-client-key="{{ config('services.midtrans.client_key') }}"></script>
	<script>
	(function(){
		var form = document.getElementById('checkoutForm');
		var btn  = document.getElementById('btnBayar');
		var errBox = document.getElementById('checkoutError');
		if (!form) return;

		function showError(msg){
			errBox.style.display = 'block';
			errBox.innerHTML = '<strong>Gagal:</strong> ' + msg;
			btn.disabled = false;
			btn.innerHTML = '<i class="fa fa-lock m-r-6"></i> Bayar Sekarang';
			window.scrollTo({ top: errBox.getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth' });
		}

		form.addEventListener('submit', function(e){
			e.preventDefault();
			errBox.style.display = 'none';
			btn.disabled = true;
			btn.innerHTML = '<i class="fa fa-spinner fa-spin m-r-6"></i> Menyiapkan pembayaran...';

			var data = new FormData(form);
			fetch(form.action, {
				method: 'POST',
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest'
				},
				body: data,
				credentials: 'same-origin'
			})
			.then(function(r){ return r.json().then(function(j){ return { ok: r.ok, body: j }; }); })
			.then(function(res){
				if (!res.ok || !res.body.snap_token) {
					showError(res.body.error || 'Tidak dapat memuat modul pembayaran.');
					return;
				}
				if (!window.snap) { showError('Script pembayaran gagal dimuat.'); return; }
				btn.innerHTML = '<i class="fa fa-lock m-r-6"></i> Buka Pembayaran...';
				window.snap.pay(res.body.snap_token, {
					onSuccess: function(){ window.location.href = res.body.redirect_url; },
					onPending: function(){ window.location.href = res.body.redirect_url; },
					onError:   function(){ showError('Pembayaran gagal. Silakan coba lagi.'); },
					onClose:   function(){
						// user menutup popup; arahkan ke halaman pesanan agar bisa retry
						window.location.href = res.body.redirect_url;
					}
				});
			})
			.catch(function(err){
				showError('Tidak dapat terhubung ke server. ' + (err.message || ''));
			});
		});
	})();
	</script>
@endsection
