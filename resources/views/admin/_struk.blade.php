@php
	$storeName    = $setting('store_name', 'Batik Penawo');
	$storeAddress = $setting('contact_address', 'Kerinci, Jambi');
	$storePhone   = $setting('contact_phone', '');

	$paymentLabel = match($order->payment_method) {
		'Midtrans' => 'Bayar Online',
		'COD'      => 'Bayar di Tempat',
		default    => $order->payment_method ?? '—',
	};
	$paidLabel = match($order->status) {
		'menunggu_bayar' => 'Belum Lunas',
		'diproses'       => $order->paid_at ? 'LUNAS' : 'Diproses',
		'dikirim'        => 'LUNAS / Dikirim',
		'selesai'        => 'LUNAS / Selesai',
		'dibatalkan'     => 'DIBATALKAN',
		default          => strtoupper($order->status),
	};

	$subtotal = collect($order->items)->sum(fn ($it) => $it->price * $it->qty);
@endphp

<div class="receipt">
	<div class="center">
		<div class="store-name">{{ strtoupper($storeName) }}</div>
		<div class="store-info">{{ $storeAddress }}</div>
		@if($storePhone)
			<div class="store-info">Telp: {{ $storePhone }}</div>
		@endif
	</div>

	<hr class="divider">

	<div class="row"><span>No. Invoice</span><span class="bold">{{ $order->invoice_number }}</span></div>
	<div class="row"><span>Tanggal</span><span>{{ $order->created_at->translatedFormat('d/m/Y H:i') }}</span></div>
	<div class="row"><span>Kasir</span><span>{{ $authUser['name'] ?? 'Admin' }}</span></div>

	<hr class="divider">

	<div><span class="bold">Pelanggan:</span></div>
	<div>{{ $order->customer_name }}</div>
	<div style="font-size:10px;">{{ $order->customer_email }}</div>
	@if($order->shipping_address)
		<div style="font-size:10px; margin-top:3px;">{{ $order->shipping_address }}</div>
	@endif

	<hr class="divider">

	<table class="items">
		@foreach($order->items as $it)
			@php $variantParts = array_filter([$it->size, $it->color]); @endphp
			<tr>
				<td class="item-name">{{ $it->product_name }}</td>
				<td class="item-qty">{{ $it->qty }} × {{ number_format($it->price, 0, ',', '.') }}</td>
			</tr>
			@if($variantParts)
				<tr>
					<td colspan="2" class="item-variant">› {{ implode(' / ', $variantParts) }}</td>
				</tr>
			@endif
			<tr>
				<td></td>
				<td class="item-qty">{{ number_format($it->qty * $it->price, 0, ',', '.') }}</td>
			</tr>
		@endforeach
	</table>

	<hr class="divider">

	<div class="row"><span>Subtotal</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>

	<hr class="divider divider-solid">

	<div class="row total-row"><span>TOTAL</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></div>

	<hr class="divider">

	<div class="row"><span>Pembayaran</span><span>{{ $paymentLabel }}</span></div>
	<div class="row"><span>Status</span><span class="bold">{{ $paidLabel }}</span></div>
	@if($order->paid_at)
		<div class="row"><span>Dibayar</span><span>{{ $order->paid_at->translatedFormat('d/m/Y H:i') }}</span></div>
	@endif

	<hr class="divider">

	<div class="thanks">
		Terima kasih atas pembelian Anda!<br>
		Selamat menikmati produk Batik Penawo.
		<br><br>
		-- {{ $order->invoice_number }} --
	</div>
</div>
