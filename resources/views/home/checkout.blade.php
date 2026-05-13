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

	.store-block { padding: 14px 0; border-bottom: 1px dashed #ece8de; }
	.store-block:last-child { border-bottom: 0; padding-bottom: 0; }
	.store-block:first-child { padding-top: 0; }
	.store-name { font-size: 13.5px; font-weight: 600; color: #2d2a26; display:flex; align-items:center; gap:8px; margin-bottom:8px; }
	.zone-tag {
		display: inline-block; padding: 2px 8px; border-radius: 999px;
		font-size: 11px; font-weight: 500; letter-spacing: .3px;
	}
	.zone-tag.same_district     { background:#edf7ef; color:#2f7a4c; } /* hijau — paling murah */
	.zone-tag.same_city         { background:#fff4d6; color:#8a6b2b; } /* kuning */
	.zone-tag.same_province     { background:#fde8d4; color:#a3621a; } /* oranye */
	.zone-tag.outside_province  { background:#fbe4df; color:#a5432f; } /* merah — paling jauh */
	.store-meta { font-size: 12px; color:#9a9288; margin-bottom: 8px; }
	.store-shipping-row {
		display:flex; justify-content:space-between; align-items:center;
		font-size: 13px; padding: 4px 0; color:#4d4640;
	}
	.store-shipping-row strong { color:#2d2a26; }
	.store-error {
		background:#fbe4df; border:1px solid #f2c6be; color:#a5432f;
		padding:8px 10px; border-radius:4px; font-size:12.5px; margin-top:6px;
	}

	/* Picker kurir per toko */
	.ship-picker-label { font-size:12.5px; font-weight:600; color:#4d4640; margin:10px 0 8px; display:flex; align-items:center; gap:6px; }
	.ship-picker {
		display: grid; grid-template-columns: 1fr; gap: 8px;
		margin-bottom: 10px;
	}
	@media (min-width: 540px) {
		.ship-picker { grid-template-columns: 1fr 1fr; }
	}
	.ship-option {
		display: flex; flex-direction: column; gap: 4px;
		padding: 10px 12px;
		background: #fff;
		border: 1px solid #e0dbcf; border-radius: 4px;
		text-decoration: none; color: #4d4640;
		transition: border-color .15s, background .15s, box-shadow .15s;
		cursor: pointer;
	}
	.ship-option:hover { border-color: #c29e5c; text-decoration: none; color: #4d4640; }
	.ship-option.is-active {
		border-color: #c29e5c; background: #faf6ed;
		box-shadow: 0 0 0 3px rgba(194,158,92,.12);
	}
	.ship-option-head {
		display: flex; gap: 6px; align-items: baseline;
		font-size: 13px;
	}
	.ship-option-courier { font-weight: 600; color: #2d2a26; }
	.ship-option-service {
		font-size: 11px; padding: 2px 8px;
		background: #f5ecd7; color: #8a6b2b;
		border-radius: 999px; letter-spacing: .3px;
	}
	.ship-option-meta {
		font-size: 11.5px; color: #9a9288;
		display: flex; gap: 8px; align-items: center;
	}
	.ship-option-etd { display:inline-flex; align-items:center; gap:4px; }
	.ship-option-price { font-size: 13.5px; font-weight: 600; color: #c29e5c; margin-top: 2px; }
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
							<h3 class="checkout-card-title" id="addressCardTitle"><i class="fa fa-map-marker m-r-6" style="color:#c29e5c;"></i> Alamat Pengiriman</h3>
							<div id="codNotice" style="display:none; background:#faf6ed; border:1px solid #e4d5aa; border-left:3px solid #c29e5c; padding:10px 14px; border-radius:4px; font-size:12.5px; color:#6c665e; margin-bottom:14px;">
								<i class="fa fa-info-circle" style="color:#c29e5c;"></i>
								<strong style="color:#8a6b2b;">Pesanan Jemput Sendiri</strong> — pesanan akan dipersiapkan di toko. Tidak perlu alamat pengiriman; bawa nomor invoice saat datang.
							</div>

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
								<div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px;">
									<label class="checkout-label" style="margin:0;">Pilih Alamat Tersimpan <span style="color:#a5432f;">*</span></label>
									{{-- Tombol type=button supaya tidak submit form checkout. --}}
									<button type="button"
										onclick="openAlamatModal()"
										@disabled($addresses->count() >= \App\Models\Address::MAX_PER_USER)
										title="{{ $addresses->count() >= \App\Models\Address::MAX_PER_USER ? 'Sudah mencapai batas '.\App\Models\Address::MAX_PER_USER.' alamat — hapus salah satu di profil dulu' : 'Tambah alamat baru' }}"
										style="background:#fff; color:#c29e5c; border:1px solid #c29e5c; padding:6px 12px; border-radius:4px; font-size:12.5px; font-weight:500; cursor:pointer; white-space:nowrap;">
										<i class="fa fa-plus"></i> Tambah Alamat
									</button>
								</div>
								<select name="address_id" id="checkoutAddressSelect" class="checkout-input" required
									onchange="window.location = '{{ route('checkout.show') }}?address_id=' + encodeURIComponent(this.value);">
									@foreach($addresses as $addr)
										<option value="{{ $addr->id }}" @selected($selectedAddress->id === $addr->id)>
											{{ $addr->label }}{{ $addr->is_default ? ' (Utama)' : '' }} — Kec. {{ $addr->district_name }}, {{ $addr->city_name }}
										</option>
									@endforeach
								</select>
								<div style="margin-top:10px; padding:12px 14px; background:#faf7ef; border:1px solid #ece8de; border-radius:4px; font-size:13px; color:#4d4640; line-height:1.6;">
									<strong>{{ $selectedAddress->label }}</strong>{!! $selectedAddress->is_default ? ' <span style="background:#c29e5c;color:#fff;font-size:10px;padding:1px 7px;border-radius:999px;letter-spacing:.5px;">UTAMA</span>' : '' !!}<br>
									{{ $selectedAddress->full_address }}<br>
									<span style="color:#9a9288;">Kec. {{ $selectedAddress->district_name }}, {{ $selectedAddress->city_name }}, {{ $selectedAddress->province_name }}</span>
								</div>
								<div style="font-size:11.5px; color:#9a9288; margin-top:6px;">
									<i class="fa fa-info-circle"></i> {{ $addresses->count() }}/{{ \App\Models\Address::MAX_PER_USER }} alamat tersimpan. Kelola di <a href="{{ route('akun.profil') }}" style="color:#c29e5c;">profil</a>.
								</div>
							</div>
							<div>
								<label class="checkout-label">Catatan untuk Penjual <small style="color:#9a9288;">(opsional)</small></label>
								<textarea name="note" class="checkout-textarea" placeholder="Contoh: tolong dibungkus kado">{{ old('note') }}</textarea>
							</div>
						</div>

						<div class="checkout-card">
							<h3 class="checkout-card-title"><i class="fa fa-credit-card m-r-6" style="color:#c29e5c;"></i> Metode Pembayaran</h3>

							<label class="payment-option" id="optMidtrans">
								<input type="radio" name="payment_method" value="Midtrans" {{ old('payment_method', 'Midtrans') === 'Midtrans' ? 'checked' : '' }}>
								<span class="payment-option-body">
									<i class="fa fa-credit-card m-r-6" style="color:#6c665e;"></i>
									Bayar Online
									<div style="font-size:11.5px; color:#9a9288; margin-top:3px;">
										Kartu Kredit, Transfer Bank / VA, E-Wallet (GoPay, OVO, ShopeePay, Dana), QRIS, dan lainnya.
									</div>
								</span>
							</label>

							<label class="payment-option" id="optCOD">
								<input type="radio" name="payment_method" value="COD" {{ old('payment_method') === 'COD' ? 'checked' : '' }}>
								<span class="payment-option-body">
									<i class="fa fa-shopping-bag m-r-6" style="color:#6c665e;"></i>
									Bayar di Toko (Jemput Sendiri)
									<div style="font-size:11.5px; color:#9a9288; margin-top:3px;">
										Pesanan dipersiapkan, lalu Anda ambil & bayar tunai langsung di toko Batik Penawo.
									</div>
								</span>
							</label>
						</div>
					</div>

					<!-- Right: Ringkasan per toko + grand total -->
					<div class="col-lg-5 p-b-30">
						<div class="checkout-card" style="position:sticky; top:20px;">
							<h3 class="checkout-card-title">Ringkasan Pesanan</h3>

							@if(! $summary['all_available'])
								<div class="checkout-errors">
									<strong>Pengiriman bermasalah:</strong>
									<ul style="margin:6px 0 0 18px; padding:0;">
										@foreach($summary['errors'] as $err)<li>{{ $err }}</li>@endforeach
									</ul>
								</div>
							@endif

							@foreach($summary['stores'] as $store)
								@php
									$sh = $store['shipping'];
									$storeId = (string) $store['store_id'];
									// Builder URL untuk ganti opsi pengiriman toko ini sambil
									// mempertahankan pilihan toko-toko lain (multi-store ready).
									$buildShippingUrl = function (string $optCode) use ($storeId, $shippingSelections, $selectedAddress) {
										$sel = $shippingSelections;
										$sel[$storeId] = $optCode;
										return route('checkout.show', [
											'address_id' => $selectedAddress->id,
											'shipping'   => $sel,
										]);
									};
								@endphp
								<div class="store-block">
									<div class="store-name">
										<i class="fa fa-shopping-bag" style="color:#c29e5c;"></i>
										{{ $store['store_name'] }}
										<span class="zone-tag {{ $sh['zone'] }}">{{ $sh['zone_label'] }}</span>
									</div>
									<div class="store-meta">
										{{ count($store['items']) }} produk · Total berat
										@if(isset($sh['weight_grams']))
											{{ number_format($sh['weight_grams'], 0, ',', '.') }} g
										@else
											{{ $sh['total_weight_kg'] }} kg
										@endif
									</div>

									<ul class="checkout-items">
										@foreach($store['items'] as $item)
										<li>
											@if(! empty($item['image_url']))<img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">@endif
											<div>
												<div class="checkout-item-name">{{ $item['name'] }}</div>
												@php $variantParts = array_filter([$item['size'] ?? null, $item['color'] ?? null]); @endphp
												@if($variantParts)
													<div style="font-size:11.5px; color:#9a9288; margin-top:1px;">{{ implode(' · ', $variantParts) }}</div>
												@endif
												<div class="checkout-item-qty">{{ $item['qty'] }} × {{ $rupiah($item['unit_price']) }} · {{ $item['weight_kg'] }} kg/pcs</div>
											</div>
											<div class="checkout-item-sub">{{ $rupiah($item['subtotal']) }}</div>
										</li>
										@endforeach
									</ul>

									{{-- Picker kurir + service per toko. Klik kartu → reload halaman
									     dengan ?shipping[{storeId}]=courier:service. Pilihan terkirim
									     juga via hidden input saat form di-submit. --}}
									@if($sh['available'] && ! empty($sh['options']))
										<div class="ship-picker-label">
											<i class="fa fa-truck" style="color:#c29e5c;"></i> Pilih Kurir &amp; Layanan
										</div>
										<div class="ship-picker">
											@foreach($sh['options'] as $opt)
												@php $isActive = ($opt['code'] === ($sh['option_code'] ?? null)); @endphp
												<a href="{{ $buildShippingUrl($opt['code']) }}"
													class="ship-option {{ $isActive ? 'is-active' : '' }}">
													<div class="ship-option-head">
														<span class="ship-option-courier">{{ $opt['courier_name'] ?: 'Kurir' }}</span>
														<span class="ship-option-service">{{ $opt['service_name'] ?: '—' }}</span>
													</div>
													<div class="ship-option-meta">
														{{ $opt['service_desc'] ?: '—' }}
														@if(! empty($opt['etd']))
															<span class="ship-option-etd"><i class="fa fa-clock-o"></i> {{ $opt['etd'] }}</span>
														@endif
													</div>
													<div class="ship-option-price">
														{{ $rupiah($opt['cost']) }}
														@if($isActive)<i class="fa fa-check-circle" style="color:#2f7a4c; margin-left:6px;"></i>@endif
													</div>
												</a>
											@endforeach
										</div>
										{{-- Forward selection ke form submit checkout.confirm. --}}
										<input type="hidden" form="checkoutForm" name="shipping[{{ $storeId }}]" value="{{ $sh['option_code'] }}">
									@endif

									<div class="store-shipping-row">
										<span>Subtotal toko</span>
										<strong>{{ $rupiah($store['subtotal']) }}</strong>
									</div>
									<div class="store-shipping-row">
										<span>
											Ongkir terpilih
											@if($sh['available'] && ($sh['source'] ?? 'local') === 'rajaongkir')
												<small style="color:#9a9288;">
													({{ $sh['courier_name'] ?: 'Kurir' }} · {{ $sh['service_name'] ?: '—' }}@if(! empty($sh['etd'])), estimasi {{ $sh['etd'] }}@endif)
												</small>
											@elseif($sh['available'])
												<small style="color:#9a9288;">({{ $rupiah($sh['base_fee']) }} dasar {{ $sh['base_weight_kg'] }} kg + {{ $rupiah($sh['extra_fee_per_kg']) }}/kg)</small>
											@endif
										</span>
										<strong>{{ $sh['available'] ? $rupiah($sh['shipping_cost']) : '—' }}</strong>
									</div>
									@if(! $sh['available'])
										<div class="store-error">
											<i class="fa fa-exclamation-circle"></i>
											{{ $sh['message'] }}
											@if(! empty($sh['debug_reason']) && (($authUser['role'] ?? null) === 'admin' || config('app.debug')))
												{{-- Detail teknis hanya untuk admin atau saat APP_DEBUG=true. --}}
												<details style="margin-top:6px; font-size:11px; color:#9a9288;">
													<summary style="cursor:pointer;">Detail teknis (admin)</summary>
													<code style="display:block; margin-top:4px; padding:6px 8px; background:#faf7ef; border-radius:3px; word-break:break-word;">{{ $sh['debug_reason'] }}</code>
												</details>
											@endif
										</div>
									@endif
								</div>
							@endforeach

							<div style="margin-top:14px; padding-top:12px; border-top:1px solid #ece8de;">
								<div class="summary-row">
									<span>Subtotal Produk</span>
									<span>{{ $rupiah($summary['subtotal_products']) }}</span>
								</div>
								<div class="summary-row">
									<span>Total Ongkir</span>
									<span>{{ $rupiah($summary['shipping_total']) }}</span>
								</div>
								<div class="summary-row total">
									<span>Total Bayar</span>
									<span>{{ $rupiah($summary['grand_total']) }}</span>
								</div>
							</div>

							<button type="submit" class="checkout-btn-pay" id="btnBayar" @disabled(! $summary['all_available'])>
								{{ $summary['all_available'] ? 'Bayar Sekarang' : 'Tidak Dapat Dikirim' }}
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

			{{-- Modal tambah alamat — diletakkan di luar form checkout supaya tidak nested. --}}
			@include('partials._alamat_modal', [
				'redirectTo' => 'checkout.show',
				'inputClass' => 'checkout-input',
				'btnClass'   => 'checkout-btn-pay',
			])
			{{-- JS cascading dropdown wilayah dipakai oleh modal di atas. --}}
			@include('partials._wilayah_cascade')
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

		// Toggle UI antar metode pembayaran
		var titleEl     = document.getElementById('addressCardTitle');
		var notice      = document.getElementById('codNotice');
		var addrSelect  = document.getElementById('checkoutAddressSelect');
		var radios      = form.querySelectorAll('input[name="payment_method"]');
		var shippingRow = null;
		document.querySelectorAll('.summary-row').forEach(function(r){
			if (r.textContent.trim().indexOf('Ongkir') === 0 || r.textContent.indexOf('Total Ongkir') > -1) shippingRow = r;
		});

		function applyPaymentUI() {
			var pm = (form.querySelector('input[name="payment_method"]:checked') || {}).value;
			var isCod = pm === 'COD';

			if (titleEl) {
				titleEl.innerHTML = isCod
					? '<i class="fa fa-shopping-bag m-r-6" style="color:#c29e5c;"></i> Informasi Penerima'
					: '<i class="fa fa-map-marker m-r-6" style="color:#c29e5c;"></i> Alamat Pengiriman';
			}
			if (notice) notice.style.display = isCod ? 'block' : 'none';

			// Sembunyikan blok pemilih alamat saat COD (alamat tidak diperlukan).
			if (addrSelect) {
				var addrBlock = addrSelect.closest('div[style*="margin-bottom:14px"]');
				if (addrBlock) addrBlock.style.display = isCod ? 'none' : '';
				addrSelect.required = !isCod;
			}

			// Sembunyikan baris ongkir & sesuaikan total saat COD.
			if (shippingRow) shippingRow.style.display = isCod ? 'none' : '';
			if (btn) {
				btn.disabled = false;
				btn.innerHTML = isCod
					? '<i class="fa fa-shopping-bag m-r-6"></i> Pesan & Jemput di Toko'
					: '<i class="fa fa-lock m-r-6"></i> Bayar Sekarang';
			}
		}
		radios.forEach(function(r){ r.addEventListener('change', applyPaymentUI); });
		applyPaymentUI();

		function showError(msg){
			errBox.style.display = 'block';
			errBox.innerHTML = '<strong>Gagal:</strong> ' + msg;
			btn.disabled = false;
			btn.innerHTML = '<i class="fa fa-lock m-r-6"></i> Bayar Sekarang';
			window.scrollTo({ top: errBox.getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth' });
		}

		form.addEventListener('submit', function(e){
			var pm = (form.querySelector('input[name="payment_method"]:checked') || {}).value;
			// COD jemput: biarkan submit normal — server redirect ke halaman pesanan.
			if (pm === 'COD') return;

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
				// Util: tambahkan param query ke URL apa pun (relatif atau absolut).
				function withParam(url, key, value){
					var sep = url.indexOf('?') >= 0 ? '&' : '?';
					return url + sep + encodeURIComponent(key) + '=' + encodeURIComponent(value);
				}
				window.snap.pay(res.body.snap_token, {
					onSuccess: function(){ window.location.href = res.body.redirect_url; },
					onPending: function(){ window.location.href = res.body.redirect_url; },
					onError:   function(){ showError('Pembayaran gagal. Silakan coba lagi.'); },
					onClose:   function(){
						// User menutup popup tanpa membayar — beri tahu backend
						// supaya page sync TIDAK memanggil Midtrans API (mencegah
						// status keliru jadi "Dibayar" karena resolusi sandbox).
						window.location.href = withParam(res.body.redirect_url, 'cancelled', '1');
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
