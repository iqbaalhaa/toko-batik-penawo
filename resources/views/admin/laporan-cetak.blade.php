<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<title>Laporan Mutasi Stok — {{ \App\Models\SiteSetting::get('store_name', config('app.name')) }}</title>
	<style>
		* { box-sizing: border-box; margin: 0; padding: 0; }
		html, body { background: #ececec; }
		body {
			font-family: 'Helvetica Neue', Arial, sans-serif;
			font-size: 12px;
			line-height: 1.45;
			color: #1c1c1c;
			padding: 20px 0;
		}

		.sheet {
			width: 297mm;          /* A4 landscape width */
			min-height: 210mm;
			margin: 0 auto;
			background: #fff;
			padding: 14mm 14mm 16mm;
			box-shadow: 0 4px 18px rgba(0,0,0,.08);
		}

		.header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding-bottom: 12px; border-bottom: 2px solid #1c1c1c; }
		.brand { flex: 1; }
		.brand-name { font-size: 22px; font-weight: 700; letter-spacing: .5px; }
		.brand-tagline { font-size: 11px; color: #555; margin-top: 2px; }
		.brand-meta { font-size: 10.5px; color: #555; margin-top: 6px; }
		.report-title { text-align: right; }
		.report-title h1 { font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; }
		.report-title .printed-at { font-size: 10.5px; color: #555; margin-top: 4px; }

		.filters-card { margin: 14px 0 12px; padding: 9px 12px; background: #f5f2ea; border-left: 3px solid #c29e5c; font-size: 11px; }
		.filters-card .label { font-weight: 600; margin-right: 4px; }

		.totals { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 12px 0 14px; }
		.total-card { border: 1px solid #ddd6c6; padding: 10px 12px; border-radius: 4px; }
		.total-card .label { font-size: 10px; text-transform: uppercase; letter-spacing: .8px; color: #6c665e; }
		.total-card .value { font-size: 18px; font-weight: 700; margin-top: 4px; }
		.total-card.in     .value { color: #2f7a4c; }
		.total-card.out    .value { color: #a5432f; }
		.total-card.saldo  .value { color: #1c1c1c; }
		.total-card .sub   { font-size: 10.5px; color: #6c665e; margin-top: 2px; }

		table.report { width: 100%; border-collapse: collapse; margin-top: 8px; }
		table.report thead { background: #1c1c1c; color: #fff; }
		table.report th { padding: 8px 9px; text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
		table.report td { padding: 7px 9px; border-bottom: 1px solid #ece8de; vertical-align: top; }
		table.report tr:nth-child(even) td { background: #fafaf6; }
		table.report .num { text-align: right; white-space: nowrap; }
		table.report .center { text-align: center; }
		table.report .sku { font-family: 'Consolas', 'Courier New', monospace; font-size: 11px; color: #4d4640; }
		table.report .qty-in  { color: #2f7a4c; font-weight: 700; }
		table.report .qty-out { color: #a5432f; font-weight: 700; }
		table.report .badge {
			display: inline-block; padding: 2px 8px; border-radius: 3px;
			font-size: 10px; font-weight: 600; letter-spacing: .3px;
		}
		table.report .badge.in  { background: #e7f3ec; color: #2f7a4c; border: 1px solid #cfe6d6; }
		table.report .badge.out { background: #fbe4df; color: #a5432f; border: 1px solid #f2c6be; }
		table.report tfoot td { background: #f5f2ea; font-weight: 700; font-size: 12px; padding: 9px; border-top: 2px solid #1c1c1c; }

		.empty-row td { text-align: center; color: #888; font-style: italic; padding: 18px; }

		.signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; margin-top: 38px; }
		.sig { text-align: center; font-size: 11px; }
		.sig .place { color: #555; }
		.sig .role { margin-top: 60px; color: #555; }
		.sig .name { margin-top: 4px; font-weight: 700; border-top: 1px solid #1c1c1c; padding-top: 4px; min-width: 180px; display: inline-block; }

		.footer-note { margin-top: 26px; padding-top: 8px; border-top: 1px dashed #c0baa9; font-size: 10px; color: #6c665e; text-align: center; }

		.print-toolbar {
			width: 297mm; max-width: calc(100% - 32px); margin: 0 auto 14px;
			display: flex; gap: 8px; justify-content: flex-end;
		}
		.print-toolbar button, .print-toolbar a {
			padding: 8px 14px; font-size: 12px;
			border: 1px solid #c29e5c; background: #c29e5c; color: #fff;
			border-radius: 4px; cursor: pointer; text-decoration: none;
			font-family: inherit; font-weight: 500;
		}
		.print-toolbar a.secondary { background: #fff; color: #4d4640; border-color: #ddd6c6; }
		.print-toolbar button:hover { background: #a88541; border-color: #a88541; }

		@media print {
			html, body { background: #fff !important; padding: 0 !important; }
			.print-toolbar { display: none !important; }
			.sheet { box-shadow: none; padding: 8mm 10mm; width: auto; min-height: auto; }
			table.report tr { page-break-inside: avoid; }
			thead { display: table-header-group; }   /* repeat header tiap halaman */
			tfoot { display: table-footer-group; }
		}
		@page { size: A4 landscape; margin: 8mm; }
	</style>
</head>
<body>

	<div class="print-toolbar">
		<button type="button" onclick="window.print()"><i></i> Cetak</button>
		<a class="secondary" href="{{ url()->previous() }}">Kembali</a>
		<a class="secondary" href="javascript:window.close()">Tutup</a>
	</div>

	@php
		$storeName    = \App\Models\SiteSetting::get('store_name', config('app.name', 'Toko'));
		$storeAddr    = \App\Models\SiteSetting::get('contact_address');
		$storePhone   = \App\Models\SiteSetting::get('contact_phone');
		$now          = now()->translatedFormat('d F Y, H:i');
		$periodLabel  = ($filters['from'] || $filters['to'])
			? trim(($filters['from'] ? \Illuminate\Support\Carbon::parse($filters['from'])->translatedFormat('d M Y') : '—')
				. ' s.d. '
				. ($filters['to'] ? \Illuminate\Support\Carbon::parse($filters['to'])->translatedFormat('d M Y') : '—'))
			: 'Seluruh periode';
		$saldo = $totalIn - $totalOut;
	@endphp

	<div class="sheet">
		{{-- ================= Header ================= --}}
		<div class="header">
			<div class="brand">
				<div class="brand-name">{{ $storeName }}</div>
				<div class="brand-tagline">Laporan internal — Mutasi Stok</div>
				@if($storeAddr || $storePhone)
					<div class="brand-meta">
						@if($storeAddr) {{ $storeAddr }} @endif
						@if($storePhone) · {{ $storePhone }} @endif
					</div>
				@endif
			</div>
			<div class="report-title">
				<h1>Laporan Mutasi Stok</h1>
				<div class="printed-at">Dicetak {{ $now }}</div>
			</div>
		</div>

		{{-- ================= Filter aktif ================= --}}
		<div class="filters-card">
			<span class="label">Periode:</span>{{ $periodLabel }}
			@if($filters['type'])
				<span style="margin:0 8px; color:#c0baa9;">|</span>
				<span class="label">Jenis:</span>{{ $filters['type'] === 'masuk' ? 'Barang Masuk' : 'Barang Keluar' }}
			@endif
			@if($filters['q'])
				<span style="margin:0 8px; color:#c0baa9;">|</span>
				<span class="label">Pencarian:</span>"{{ $filters['q'] }}"
			@endif
			<span style="float:right; color:#6c665e;">{{ $movements->count() }} baris</span>
		</div>

		{{-- ================= Total ringkas ================= --}}
		<div class="totals">
			<div class="total-card in">
				<div class="label">Barang Masuk</div>
				<div class="value">+{{ number_format($totalIn, 0, ',', '.') }}</div>
				<div class="sub">{{ $trxIn }} transaksi</div>
			</div>
			<div class="total-card out">
				<div class="label">Barang Keluar</div>
				<div class="value">−{{ number_format($totalOut, 0, ',', '.') }}</div>
				<div class="sub">{{ $trxOut }} transaksi</div>
			</div>
			<div class="total-card saldo">
				<div class="label">Saldo Periode</div>
				<div class="value">{{ $saldo >= 0 ? '+' : '' }}{{ number_format($saldo, 0, ',', '.') }}</div>
				<div class="sub">Selisih masuk − keluar</div>
			</div>
			<div class="total-card">
				<div class="label">Total Transaksi</div>
				<div class="value">{{ $trxIn + $trxOut }}</div>
				<div class="sub">Masuk + Keluar</div>
			</div>
		</div>

		{{-- ================= Tabel ================= --}}
		<table class="report">
			<thead>
				<tr>
					<th style="width:34px;" class="center">#</th>
					<th style="width:120px;">Tanggal</th>
					<th style="width:90px;">SKU</th>
					<th>Produk</th>
					<th style="width:90px;" class="center">Jenis</th>
					<th style="width:80px;" class="num">Jumlah</th>
					<th style="width:140px;">Referensi</th>
					<th>Keterangan</th>
					<th style="width:120px;">Petugas</th>
				</tr>
			</thead>
			<tbody>
				@forelse($movements as $i => $m)
				<tr>
					<td class="center">{{ $i + 1 }}</td>
					<td>{{ $m->occurred_at?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
					<td class="sku">{{ $m->product?->sku ?? '—' }}</td>
					<td>{{ $m->product?->name ?? '—' }}</td>
					<td class="center">
						<span class="badge {{ $m->type === 'masuk' ? 'in' : 'out' }}">
							{{ $m->type === 'masuk' ? 'MASUK' : 'KELUAR' }}
						</span>
					</td>
					<td class="num {{ $m->type === 'masuk' ? 'qty-in' : 'qty-out' }}">
						{{ $m->type === 'masuk' ? '+' : '−' }}{{ number_format($m->qty, 0, ',', '.') }}
					</td>
					<td>{{ $m->reference ?: '—' }}</td>
					<td>{{ $m->note ?: '—' }}</td>
					<td>{{ $m->user?->name ?? '—' }}</td>
				</tr>
				@empty
				<tr class="empty-row"><td colspan="9">Tidak ada mutasi pada filter ini.</td></tr>
				@endforelse
			</tbody>
			@if($movements->count() > 0)
			<tfoot>
				<tr>
					<td colspan="5" style="text-align:right;">Total Periode</td>
					<td class="num qty-in">+{{ number_format($totalIn, 0, ',', '.') }}</td>
					<td class="num qty-out">−{{ number_format($totalOut, 0, ',', '.') }}</td>
					<td colspan="2" style="text-align:right;">
						Saldo: <strong>{{ $saldo >= 0 ? '+' : '' }}{{ number_format($saldo, 0, ',', '.') }}</strong>
					</td>
				</tr>
			</tfoot>
			@endif
		</table>

		{{-- ================= Tanda tangan ================= --}}
		<div class="signatures">
			<div class="sig">
				<div class="place">Dibuat oleh,</div>
				<div class="role"></div>
				<div class="name">{{ session('auth_user.name') ?? 'Admin' }}</div>
			</div>
			<div class="sig">
				<div class="place">Mengetahui,</div>
				<div class="role"></div>
				<div class="name">(...........................)</div>
			</div>
		</div>

		<div class="footer-note">
			Dokumen ini dihasilkan otomatis dari sistem {{ $storeName }}. Cetak ulang dapat memperlihatkan data terbaru.
		</div>
	</div>

	<script>
		// Auto-trigger print dialog supaya UX-nya satu klik dari halaman laporan.
		window.addEventListener('load', function () {
			setTimeout(function () { window.print(); }, 350);
		});
	</script>
</body>
</html>
