@extends('layouts.app')

@section('title', 'Batik Penawo | Keranjang Saya')

@push('styles')
<style>
	.column-check { width: 48px; text-align: center; padding: 0 8px; }
	.column-action { width: 44px; text-align: center; padding: 0 8px; }
	.cart-remove-btn {
		width: 32px; height: 32px; border-radius: 50%;
		background: transparent; color: #9a9288; border: 0;
		display: inline-flex; align-items: center; justify-content: center;
		cursor: pointer; transition: background .15s, color .15s; padding: 0;
	}
	.cart-remove-btn:hover { background: #fbe4df; color: #a5432f; }
	.cart-remove-btn i { font-size: 18px; }
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
	.cart-variant { margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap; }
	.cart-variant-chip {
		display: inline-block;
		padding: 2px 9px;
		font-size: 11.5px; color: #6c665e;
		background: #f5ecd7; border: 1px solid #e4d5aa;
		border-radius: 999px;
		letter-spacing: .2px;
	}
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
	<div class="bg0 p-t-75 p-b-85">
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
										<th class="column-action"></th>
									</tr>

									@foreach($cartItems as $i => $item)
									<tr class="table_row" data-price="{{ $item['price'] }}" data-cart-key="{{ $item['cart_key'] }}">
										<td class="column-check">
											<label class="cart-checkbox-label">
												<input type="checkbox" name="selected[]" value="{{ $item['cart_key'] }}" class="cart-item-check">
												<span></span>
											</label>
										</td>
										<td class="column-1">
											<div class="how-itemcart1">
												<a href="{{ route('produk.detail', $item['slug']) }}">
													<img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
												</a>
											</div>
										</td>
										<td class="column-2">
											<a href="{{ route('produk.detail', $item['slug']) }}" class="cl2 hov-cl1 trans-04">
												{{ $item['name'] }}
											</a>
											@if($item['size'] || $item['color'])
												<div class="cart-variant">
													@if($item['size'])<span class="cart-variant-chip">Ukuran: {{ $item['size'] }}</span>@endif
													@if($item['color'])<span class="cart-variant-chip">Warna: {{ $item['color'] }}</span>@endif
												</div>
											@endif
										</td>
										<td class="column-3">{{ $rupiah($item['price']) }}</td>
										<td class="column-4">
											<form action="{{ route('keranjang.update', $item['cart_key']) }}" method="POST" class="js-qty-form">
												@csrf
												@method('PATCH')
												<div class="wrap-num-product flex-w m-l-auto m-r-0">
													<div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m">
														<i class="fs-16 fa fa-minus"></i>
													</div>

													<input class="mtext-104 cl3 txt-center num-product" type="number" name="qty" value="{{ $item['qty'] }}" min="1" max="99">

													<div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m">
														<i class="fs-16 fa fa-plus"></i>
													</div>
												</div>
											</form>
										</td>
										<td class="column-5 row-total">{{ $rupiah($item['price'] * $item['qty']) }}</td>
										<td class="column-action">
											<form action="{{ route('keranjang.remove', $item['cart_key']) }}" method="POST" style="margin:0;"
												data-confirm-title="Hapus dari keranjang?"
												data-confirm-message="Hapus {{ $item['name'] }} dari keranjang Anda."
												data-confirm-ok="Hapus">
												@csrf
												@method('DELETE')
												<button type="submit" class="cart-remove-btn" title="Hapus"><i class="fa fa-trash-o"></i></button>
											</form>
										</td>
									</tr>
									@endforeach
								</table>
							</div>

							<div style="padding-top:14px; text-align:right;">
								<form action="{{ route('keranjang.clear') }}" method="POST" style="display:inline; margin:0;"
									data-confirm-title="Kosongkan keranjang?"
									data-confirm-message="Seluruh produk akan dihapus dari keranjang Anda."
									data-confirm-ok="Kosongkan">
									@csrf
									<button type="submit" class="stext-106 cl6 hov-cl1 trans-04" style="background:none; border:0; padding:0; cursor:pointer; text-decoration:underline;">
										<i class="fa fa-trash-o"></i> Kosongkan Keranjang
									</button>
								</form>
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

							<div class="flex-w flex-t p-t-27 p-b-33">
								<div class="size-208">
									<span class="mtext-101 cl2">Total:</span>
								</div>

								<div class="size-209 p-t-1">
									<span class="mtext-110 cl2" id="cart-total">Rp0</span>
								</div>
							</div>

							<button type="button" id="checkout-btn" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer" disabled>
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
	</div>
@endsection

@push('scripts')
<script>
	(function () {
		const selectAll    = document.getElementById('select-all');
		const rows         = Array.from(document.querySelectorAll('.table_row'));
		const subtotalEl   = document.getElementById('cart-subtotal');
		const totalEl      = document.getElementById('cart-total');
		const countEl      = document.getElementById('cart-selected-count');
		const checkoutBtn  = document.getElementById('checkout-btn');
		if (!rows.length) return;

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
			const qtyForm = row.querySelector('form.js-qty-form');
			let submitTimer = null;
			const scheduleSubmit = () => {
				clearTimeout(submitTimer);
				submitTimer = setTimeout(() => qtyForm.submit(), 700);
			};

			qtyIn.addEventListener('input', () => { recalcSummary(); scheduleSubmit(); });

			row.querySelector('.btn-num-product-up').addEventListener('click', () => {
				qtyIn.value = Math.min(99, (parseInt(qtyIn.value) || 0) + 1);
				recalcSummary(); scheduleSubmit();
			});
			row.querySelector('.btn-num-product-down').addEventListener('click', () => {
				qtyIn.value = Math.max(1, (parseInt(qtyIn.value) || 1) - 1);
				recalcSummary(); scheduleSubmit();
			});
		});

		recalcSummary();

		// Checkout: kumpulkan slug terpilih, submit ke POST /checkout
		if (checkoutBtn) {
			checkoutBtn.addEventListener('click', function() {
				if (checkoutBtn.disabled) return;
				var selected = rows
					.filter(function(r){ return r.querySelector('.cart-item-check').checked; })
					.map(function(r){ return r.dataset.cartKey; });
				if (selected.length === 0) return;

				var token = document.querySelector('input[name="_token"]');
				var f = document.createElement('form');
				f.method = 'POST';
				f.action = @json(route('checkout'));
				if (token) {
					var t = document.createElement('input');
					t.type = 'hidden'; t.name = '_token'; t.value = token.value;
					f.appendChild(t);
				}
				selected.forEach(function(slug){
					var i = document.createElement('input');
					i.type = 'hidden'; i.name = 'selected[]'; i.value = slug;
					f.appendChild(i);
				});
				document.body.appendChild(f);
				f.submit();
			});
		}
	})();
</script>
@endpush
