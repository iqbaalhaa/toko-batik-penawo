@extends('layouts.app')

@section('title', 'Batik Penawo | Keranjang Saya')

@push('styles')
<style>
	.column-check { width: 48px; text-align: center; padding: 0 8px; }
	.cart-checkbox-label { display: inline-flex; align-items: center; cursor: pointer; margin: 0; }
	.cart-checkbox-label input[type="checkbox"] { position: absolute; opacity: 0; pointer-events: none; }
	.cart-checkbox-label span {
		width: 18px; height: 18px;
		border: 1.5px solid #c8c8c8; border-radius: 3px;
		display: inline-block; position: relative;
		transition: all .15s ease;
		background: #fff;
	}
	.cart-checkbox-label:hover span { border-color: #c29e5c; }
	.cart-checkbox-label input[type="checkbox"]:checked + span { background: #c29e5c; border-color: #c29e5c; }
	.cart-checkbox-label input[type="checkbox"]:checked + span:after {
		content: ''; position: absolute;
		left: 5px; top: 1px;
		width: 5px; height: 10px;
		border: solid #fff; border-width: 0 2px 2px 0;
		transform: rotate(45deg);
	}
	#checkout-btn[disabled] { opacity: .55; cursor: not-allowed; }
	.table_row.row-selected { background: #fffaf0; }
</style>
@endpush

@section('content')
	<!-- Title page -->
	<section class="bg-img1 txt-center p-lr-15 p-tb-92" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Keranjang Saya</h2>
	</section>

	<!-- breadcrumb -->
	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>

			<span class="stext-109 cl4">Keranjang</span>
		</div>
	</div>

	<!-- Shopping Cart -->
	<form class="bg0 p-t-75 p-b-85" action="#" method="POST">
		@csrf
		<div class="container">
			@if(count($cartItems))
				<div class="row">
					<div class="col-lg-10 col-xl-7 m-lr-auto m-b-50">
						<div class="m-l-25 m-r--38 m-lr-0-xl">
							<div class="wrap-table-shopping-cart">
								<table class="table-shopping-cart">
									<tr class="table_head">
										<th class="column-check">
											<label class="cart-checkbox-label">
												<input type="checkbox" id="select-all" class="cart-check">
												<span></span>
											</label>
										</th>
										<th class="column-1">Produk</th>
										<th class="column-2"></th>
										<th class="column-3">Harga</th>
										<th class="column-4">Jumlah</th>
										<th class="column-5">Total</th>
									</tr>

									@foreach($cartItems as $i => $item)
									<tr class="table_row" data-price="{{ $item['price'] }}">
										<td class="column-check">
											<label class="cart-checkbox-label">
												<input type="checkbox" name="selected[]" value="{{ $i }}" class="cart-item-check">
												<span></span>
											</label>
										</td>
										<td class="column-1">
											<div class="how-itemcart1">
												<a href="{{ route('produk.detail', $item['slug']) }}">
													<img src="{{ asset('frontend/images/'.$item['img']) }}" alt="{{ $item['name'] }}">
												</a>
											</div>
										</td>
										<td class="column-2">
											<a href="{{ route('produk.detail', $item['slug']) }}" class="cl2 hov-cl1 trans-04">
												{{ $item['name'] }}
											</a>
										</td>
										<td class="column-3">{{ $rupiah($item['price']) }}</td>
										<td class="column-4">
											<div class="wrap-num-product flex-w m-l-auto m-r-0">
												<div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m">
													<i class="fs-16 zmdi zmdi-minus"></i>
												</div>

												<input class="mtext-104 cl3 txt-center num-product" type="number" name="qty[{{ $i }}]" value="{{ $item['qty'] }}" min="1">

												<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
													<i class="fs-16 zmdi zmdi-plus"></i>
												</div>
											</div>
										</td>
										<td class="column-5 row-total">{{ $rupiah($item['price'] * $item['qty']) }}</td>
									</tr>
									@endforeach
								</table>
							</div>
						</div>
					</div>

					<div class="col-sm-10 col-lg-7 col-xl-5 m-lr-auto m-b-50">
						<div class="bor10 p-lr-40 p-t-30 p-b-40 m-l-63 m-r-40 m-lr-0-xl p-lr-15-sm">
							<h4 class="mtext-109 cl2 p-b-30">Ringkasan Belanja</h4>

							<div class="flex-w flex-t bor12 p-b-13">
								<div class="size-208">
									<span class="stext-110 cl2">Subtotal (<span id="cart-selected-count">0</span> produk):</span>
								</div>

								<div class="size-209">
									<span class="mtext-110 cl2" id="cart-subtotal">Rp0</span>
								</div>
							</div>

							<div class="flex-w flex-m m-r-20 m-tb-5">
									<input class="stext-104 cl2 plh4 size-117 bor13 p-lr-20 m-r-10 m-tb-5" type="text" name="coupon" placeholder="Kode Voucher">

									<button type="button" class="flex-c-m stext-101 cl2 size-118 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer m-tb-5">
										Terapkan Voucher
									</button>
								</div>

							{{-- <div class="flex-w flex-t bor12 p-t-15 p-b-30">
								<div class="size-208 w-full-ssm">
									<span class="stext-110 cl2">Ongkos Kirim:</span>
								</div>

								<div class="size-209 p-r-18 p-r-0-sm w-full-ssm">
									<p class="stext-111 cl6 p-t-2" id="cart-shipping-note">
										Pilih produk untuk melihat ongkos kirim.
									</p>

									<div class="p-t-15">
										<span class="stext-112 cl8">Hitung Ongkos Kirim</span>

										<div class="rs1-select2 rs2-select2 bor8 bg0 m-b-12 m-t-9">
											<select class="js-select2" name="province">
												<option>Pilih provinsi...</option>
												<option>DI Yogyakarta</option>
												<option>Jawa Tengah</option>
												<option>Jawa Barat</option>
												<option>DKI Jakarta</option>
												<option>Jawa Timur</option>
											</select>
											<div class="dropDownSelect2"></div>
										</div>

										<div class="bor8 bg0 m-b-12">
											<input class="stext-111 cl8 plh3 size-111 p-lr-15" type="text" name="city" placeholder="Kota / Kabupaten">
										</div>

										<div class="bor8 bg0 m-b-22">
											<input class="stext-111 cl8 plh3 size-111 p-lr-15" type="text" name="postcode" placeholder="Kode Pos">
										</div>

										<div class="flex-w">
											<button type="button" class="flex-c-m stext-101 cl2 size-115 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer">
												Perbarui Total
											</button>
										</div>
									</div>
								</div>
							</div> --}}

							<div class="flex-w flex-t p-t-27 p-b-33">
								<div class="size-208">
									<span class="mtext-101 cl2">Total:</span>
								</div>

								<div class="size-209 p-t-1">
									<span class="mtext-110 cl2" id="cart-total">Rp0</span>
								</div>
							</div>

							<button type="submit" id="checkout-btn" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer" disabled>
								Lanjut ke Pembayaran
							</button>
						</div>
					</div>
				</div>
			@else
				<div class="txt-center p-t-50 p-b-50">
					<h4 class="mtext-109 cl2 p-b-20">Keranjang Anda masih kosong</h4>
					<p class="stext-113 cl6 p-b-30">Yuk lihat koleksi batik kami dan tambahkan produk favorit Anda.</p>
					<a href="{{ route('produk') }}" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04 dis-inline-block">
						Mulai Belanja
					</a>
				</div>
			@endif
		</div>
	</form>
@endsection

@push('scripts')
<script>
	(function () {
		const selectAll    = document.getElementById('select-all');
		const rows         = Array.from(document.querySelectorAll('.table_row'));
		const subtotalEl   = document.getElementById('cart-subtotal');
		const totalEl      = document.getElementById('cart-total');
		const countEl      = document.getElementById('cart-selected-count');
		const shippingEl   = document.getElementById('cart-shipping-note');
		const checkoutBtn  = document.getElementById('checkout-btn');
		if (!rows.length) return;

		const FREE_SHIPPING_MIN = 500000;
		const fmt = n => 'Rp' + Math.round(n).toLocaleString('id-ID');

		function recalcRow(row) {
			const price = parseFloat(row.dataset.price) || 0;
			const qtyIn = row.querySelector('input.num-product');
			let qty = parseInt(qtyIn.value) || 0;
			if (qty < 1) { qty = 1; qtyIn.value = 1; }
			const total = price * qty;
			row.querySelector('.row-total').textContent = fmt(total);
			return total;
		}

		function recalcSummary() {
			let subtotal = 0;
			let count = 0;
			rows.forEach(row => {
				const rowTotal = recalcRow(row);
				const checked = row.querySelector('.cart-item-check').checked;
				row.classList.toggle('row-selected', checked);
				if (checked) { subtotal += rowTotal; count += 1; }
			});

			subtotalEl.textContent = fmt(subtotal);
			totalEl.textContent    = fmt(subtotal);
			countEl.textContent    = count;

			if (count === 0) {
				shippingEl.textContent = 'Pilih produk untuk melihat ongkos kirim.';
			} else if (subtotal >= FREE_SHIPPING_MIN) {
				shippingEl.textContent = 'Gratis ongkir reguler untuk pesanan ini.';
			} else {
				shippingEl.textContent = 'Tambahkan ' + fmt(FREE_SHIPPING_MIN - subtotal) + ' lagi untuk mendapatkan gratis ongkir.';
			}

			checkoutBtn.disabled = count === 0;

			const items = rows.map(r => r.querySelector('.cart-item-check'));
			const allChecked = items.every(i => i.checked);
			const noneChecked = items.every(i => !i.checked);
			if (selectAll) {
				selectAll.checked = allChecked;
				selectAll.indeterminate = !allChecked && !noneChecked;
			}
		}

		if (selectAll) {
			selectAll.addEventListener('change', function () {
				rows.forEach(row => row.querySelector('.cart-item-check').checked = selectAll.checked);
				recalcSummary();
			});
		}

		rows.forEach(row => {
			row.querySelector('.cart-item-check').addEventListener('change', recalcSummary);

			const qtyIn = row.querySelector('input.num-product');
			qtyIn.addEventListener('input', recalcSummary);

			row.querySelector('.btn-num-product-up')  .addEventListener('click', () => setTimeout(recalcSummary, 0));
			row.querySelector('.btn-num-product-down').addEventListener('click', () => setTimeout(recalcSummary, 0));
		});

		recalcSummary();
	})();
</script>
@endpush
