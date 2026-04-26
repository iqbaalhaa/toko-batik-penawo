<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<title>Cetak Invoice Massal — {{ $orders->count() }} pesanan</title>
	<style>
		* { box-sizing: border-box; margin: 0; padding: 0; }
		html, body { background: #ececec; }
		body {
			font-family: 'Consolas', 'Courier New', monospace;
			font-size: 11px;
			line-height: 1.4;
			color: #000;
			padding: 20px 0;
		}

		.receipt {
			width: 80mm;
			margin: 0 auto 18px;
			background: #fff;
			padding: 8mm 6mm;
			color: #000;
			page-break-after: always;
			break-after: page;
		}
		.receipt:last-child { page-break-after: auto; break-after: auto; margin-bottom: 0; }

		.center { text-align: center; }
		.bold   { font-weight: 700; }
		.right  { text-align: right; }

		.store-name { font-size: 14px; font-weight: 700; letter-spacing: 0.5px; }
		.store-info { font-size: 10px; margin-top: 2px; }

		.divider { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
		.divider-solid { border-top: 1px solid #000; }

		.row { display: flex; justify-content: space-between; gap: 6px; }

		.items { width: 100%; border-collapse: collapse; }
		.items td { padding: 2px 0; vertical-align: top; }
		.items .item-name { padding-bottom: 0; }
		.items .item-qty  { text-align: right; white-space: nowrap; padding-left: 6px; }
		.items .item-variant { padding-bottom: 4px; padding-left: 6px; font-size: 10px; color: #333; font-style: italic; }

		.total-row { font-size: 12px; font-weight: 700; }
		.thanks { margin-top: 10px; text-align: center; font-size: 10px; line-height: 1.5; }

		.print-toolbar {
			max-width: 80mm; margin: 0 auto 14px;
			display: flex; gap: 8px;
			padding: 0 6mm;
			position: sticky; top: 0; z-index: 10;
		}
		.print-toolbar button, .print-toolbar a {
			flex: 1;
			padding: 8px 12px; font-size: 12px;
			border: 1px solid #c29e5c; background: #c29e5c; color: #fff;
			border-radius: 4px; cursor: pointer; text-decoration: none;
			text-align: center; font-family: inherit;
		}
		.print-toolbar a.secondary { background: #fff; color: #4d4640; border-color: #ddd6c6; }
		.print-toolbar button:hover { background: #a88541; border-color: #a88541; }

		.bulk-info {
			max-width: 80mm; margin: 0 auto 10px; padding: 6px 6mm;
			text-align: center; font-size: 11px; color: #6c665e;
		}

		@media print {
			html, body { background: #fff !important; padding: 0 !important; }
			.print-toolbar, .bulk-info { display: none !important; }
			.receipt { padding: 4mm; margin: 0; width: 80mm; }
		}
		@page { size: 80mm auto; margin: 0; }
	</style>
</head>
<body>

	<div class="print-toolbar">
		<button type="button" onclick="window.print()">Cetak {{ $orders->count() }} Invoice</button>
		<a class="secondary" href="javascript:window.close()">Tutup</a>
	</div>

	<div class="bulk-info">Mencetak {{ $orders->count() }} invoice. Setiap struk pada halaman terpisah.</div>

	@foreach($orders as $order)
		@include('admin._struk', ['order' => $order])
	@endforeach

	<script>
		window.addEventListener('load', function () {
			setTimeout(function () { window.print(); }, 350);
		});
	</script>
</body>
</html>
