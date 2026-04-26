@extends('layouts.admin')

@section('title', 'Laporan Stok')
@section('page_title', 'Laporan Barang Masuk &amp; Keluar')
@section('page_subtitle', 'Pantau dan catat mutasi stok produk')

@push('styles')
<style>
	.admin-pager { display: inline-flex; gap: 4px; }
	.admin-pager-btn {
		min-width: 34px; height: 34px; padding: 0 10px;
		display: inline-flex; align-items: center; justify-content: center;
		border: 1px solid #e0dbcf; background: #fff; color: #4d4640;
		border-radius: 4px; font-size: 13px; text-decoration: none;
		transition: all .15s;
	}
	.admin-pager-btn:hover { border-color: #c29e5c; color: #c29e5c; text-decoration: none; }
	.admin-pager-btn.active { background: #c29e5c; border-color: #c29e5c; color: #fff; font-weight: 600; }
	.admin-pager-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }

	.type-toggle {
		display: flex; gap: 8px; margin-bottom: 14px;
	}
	.type-toggle input[type="radio"] { display: none; }
	.type-toggle label {
		flex: 1; padding: 12px; border: 1px solid #e0dbcf;
		border-radius: 4px; text-align: center; cursor: pointer;
		font-size: 13.5px; font-weight: 500; color: #6c665e;
		transition: all .15s;
	}
	.type-toggle label:hover { border-color: #c29e5c; color: #c29e5c; }
	.type-toggle input[type="radio"]:checked + label.masuk { background: #edf7ef; border-color: #56a676; color: #2f7a4c; }
	.type-toggle input[type="radio"]:checked + label.keluar { background: #fbe4df; border-color: #d86a59; color: #a5432f; }
	.type-toggle label i { margin-right: 6px; }
</style>
@endpush

@section('content')

	@if($errors->any())
		<div class="flash" style="background:#fbe4df; border-color:#f2c6be; color:#a5432f;">
			<strong>Gagal:</strong>
			<ul style="margin:6px 0 0 18px; padding:0;">
				@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
			</ul>
		</div>
	@endif

	<!-- Summary -->
	<div class="stat-grid" style="margin-bottom:22px;">
		<div class="stat-card">
			<div class="stat-card-icon bg-green"><i class="fa fa-download"></i></div>
			<div>
				<div class="stat-card-label">Barang Masuk</div>
				<div class="stat-card-value">+{{ $totalIn }}</div>
				<div class="stat-card-trend">{{ $trxIn }} transaksi</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-red"><i class="fa fa-upload"></i></div>
			<div>
				<div class="stat-card-label">Barang Keluar</div>
				<div class="stat-card-value">&minus;{{ $totalOut }}</div>
				<div class="stat-card-trend down">{{ $trxOut }} transaksi</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-brand"><i class="fa fa-exchange"></i></div>
			<div>
				<div class="stat-card-label">Saldo Periode</div>
				<div class="stat-card-value">{{ ($totalIn - $totalOut) >= 0 ? '+' : '' }}{{ $totalIn - $totalOut }}</div>
				<div class="stat-card-trend">Selisih masuk - keluar</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-blue"><i class="fa fa-exclamation-triangle"></i></div>
			<div>
				<div class="stat-card-label">Stok Kritis</div>
				<div class="stat-card-value">{{ $lowStock->count() }}</div>
				<div class="stat-card-trend down">Produk di bawah stok minimum</div>
			</div>
		</div>
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Mutasi Stok</h3>
				<div class="admin-card-sub">Riwayat barang masuk dan keluar</div>
			</div>
			<div>
				<button type="button" class="btn-admin btn-admin-outline" onclick="window.print()"><i class="fa fa-print"></i> Cetak</button>
				<button type="button" class="btn-admin js-mutasi-btn" data-toggle="modal" data-target="#modalMutasi"><i class="fa fa-plus"></i> Tambah Mutasi</button>
			</div>
		</div>

		<form method="GET" action="{{ route('admin.laporan') }}" class="toolbar">
			<div class="toolbar-search">
				<i class="fa fa-search"></i>
				<input type="text" name="q" value="{{ request('q') }}" class="form-control-admin" placeholder="Cari SKU atau nama produk...">
			</div>
			<select name="type" class="form-control-admin" style="max-width:170px;" onchange="this.form.submit()">
				<option value="">Semua Jenis</option>
				<option value="masuk"  @selected(request('type') === 'masuk')>Barang Masuk</option>
				<option value="keluar" @selected(request('type') === 'keluar')>Barang Keluar</option>
			</select>
			<input type="date" name="from" value="{{ request('from') }}" class="form-control-admin" style="max-width:160px;" title="Dari tanggal" onchange="this.form.submit()">
			<input type="date" name="to"   value="{{ request('to') }}"   class="form-control-admin" style="max-width:160px;" title="Sampai tanggal" onchange="this.form.submit()">
			<button type="submit" class="btn-admin"><i class="fa fa-filter"></i> Filter</button>
			@if(request()->hasAny(['q','type','from','to']))
				<a href="{{ route('admin.laporan') }}" class="btn-admin btn-admin-outline"><i class="fa fa-times"></i> Reset</a>
			@endif
		</form>

		<div style="overflow-x:auto;">
			<table class="admin-table">
				<thead>
					<tr>
						<th>Tanggal</th>
						<th>SKU</th>
						<th>Produk</th>
						<th>Jenis</th>
						<th>Jumlah</th>
						<th>Referensi</th>
						<th>Keterangan</th>
						<th>Petugas</th>
					</tr>
				</thead>
				<tbody>
					@forelse($movements as $m)
					<tr>
						<td>{{ $m->occurred_at?->format('d M Y, H:i') ?? '—' }}</td>
						<td><code style="background:#f5f2ea; padding:2px 6px; border-radius:3px; font-size:12px;">{{ $m->product?->sku ?? '—' }}</code></td>
						<td style="font-weight:500;">{{ $m->product?->name ?? '—' }}</td>
						<td>
							@if($m->type === 'masuk')
								<span class="badge-pill badge-success"><i class="fa fa-arrow-down"></i> Masuk</span>
							@else
								<span class="badge-pill badge-danger"><i class="fa fa-arrow-up"></i> Keluar</span>
							@endif
						</td>
						<td>
							<strong style="color: {{ $m->type === 'masuk' ? '#2f7a4c' : '#a5432f' }};">
								{{ $m->type === 'masuk' ? '+' : '−' }}{{ $m->qty }}
							</strong>
						</td>
						<td style="font-size:12.5px; color:#6c665e;">{{ $m->reference ?? '—' }}</td>
						<td style="font-size:12.5px; color:#6c665e;">{{ $m->note }}</td>
						<td style="font-size:12.5px; color:#6c665e;">{{ $m->user?->name ?? '—' }}</td>
					</tr>
					@empty
					<tr><td colspan="8" style="text-align:center; padding:24px; color:#9a9288;">
						@if(request()->hasAny(['q','type','from','to']))
							Tidak ada mutasi yang cocok dengan filter. <a href="{{ route('admin.laporan') }}">Reset</a>
						@else
							Belum ada mutasi stok. Klik <strong>Tambah Mutasi</strong> untuk mencatat masuk/keluar.
						@endif
					</td></tr>
					@endforelse
				</tbody>
				<tfoot>
					<tr style="background:#faf7ef;">
						<td colspan="4" style="text-align:right; font-weight:600; color:#2d2a26;">Total Periode:</td>
						<td colspan="4">
							<span style="color:#2f7a4c; font-weight:600; margin-right:16px;">Masuk: +{{ $totalIn }}</span>
							<span style="color:#a5432f; font-weight:600;">Keluar: −{{ $totalOut }}</span>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>

		@if($movements->total() > 0 && $movements->hasPages())
		<div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px; border-top:1px solid #f2efe7; margin-top:8px; flex-wrap:wrap; gap:10px;">
			<div style="font-size:13px; color:#9a9288;">
				Menampilkan {{ $movements->firstItem() }} - {{ $movements->lastItem() }} dari {{ $movements->total() }} mutasi
			</div>
			<div class="admin-pager">
				@if($movements->onFirstPage())
					<span class="admin-pager-btn disabled"><i class="fa fa-chevron-left"></i></span>
				@else
					<a href="{{ $movements->previousPageUrl() }}" class="admin-pager-btn"><i class="fa fa-chevron-left"></i></a>
				@endif
				@foreach($movements->getUrlRange(1, $movements->lastPage()) as $page => $url)
					@if($page == $movements->currentPage())
						<span class="admin-pager-btn active">{{ $page }}</span>
					@else
						<a href="{{ $url }}" class="admin-pager-btn">{{ $page }}</a>
					@endif
				@endforeach
				@if($movements->hasMorePages())
					<a href="{{ $movements->nextPageUrl() }}" class="admin-pager-btn"><i class="fa fa-chevron-right"></i></a>
				@else
					<span class="admin-pager-btn disabled"><i class="fa fa-chevron-right"></i></span>
				@endif
			</div>
		</div>
		@endif
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Peringatan Stok Rendah</h3>
				<div class="admin-card-sub">Produk yang perlu segera direstok</div>
			</div>
		</div>
		<table class="admin-table">
			<thead>
				<tr>
					<th>Produk</th>
					<th>SKU</th>
					<th>Stok Tersisa</th>
					<th>Stok Minimum</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				@forelse($lowStock as $l)
				<tr>
					<td style="font-weight:500;">{{ $l->name }}</td>
					<td><code style="background:#f5f2ea; padding:2px 6px; border-radius:3px; font-size:12px;">{{ $l->sku }}</code></td>
					<td><strong style="color:#a5432f;">{{ $l->stock }}</strong></td>
					<td>{{ $l->stock_min }}</td>
					<td>
						<button type="button" class="btn-admin btn-admin-sm js-restok-btn"
							data-toggle="modal" data-target="#modalMutasi"
							data-product-id="{{ $l->id }}"
							data-product-name="{{ $l->name }}"
							data-recommended-qty="{{ max(1, $l->stock_min - $l->stock) }}"
						><i class="fa fa-plus"></i> Restok</button>
					</td>
				</tr>
				@empty
				<tr><td colspan="5" style="text-align:center; padding:16px; color:#9a9288;">Semua produk stok aman</td></tr>
				@endforelse
			</tbody>
		</table>
	</div>

	<!-- Modal Tambah Mutasi -->
	<div class="modal fade" id="modalMutasi" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content" style="border-radius:6px;">
				<form method="POST" action="{{ route('admin.laporan.mutasi') }}">
					@csrf
					<div class="modal-header" style="border-bottom:1px solid #ece8de;">
						<h5 class="modal-title" id="modalMutasiTitle" style="font-weight:600;">Tambah Mutasi Stok</h5>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
					</div>
					<div class="modal-body" style="padding:22px 24px;">
						<div style="margin-bottom:14px;">
							<label class="form-label-admin">Jenis Mutasi <span style="color:#a5432f;">*</span></label>
							<div class="type-toggle">
								<div style="flex:1;">
									<input type="radio" name="type" value="masuk" id="typeMasuk" checked>
									<label for="typeMasuk" class="masuk"><i class="fa fa-arrow-down"></i> Barang Masuk</label>
								</div>
								<div style="flex:1;">
									<input type="radio" name="type" value="keluar" id="typeKeluar">
									<label for="typeKeluar" class="keluar"><i class="fa fa-arrow-up"></i> Barang Keluar</label>
								</div>
							</div>
						</div>

						<div style="margin-bottom:14px;">
							<label class="form-label-admin">Produk <span style="color:#a5432f;">*</span></label>
							<select name="product_id" id="mutasiProduct" class="form-control-admin" required>
								<option value="">— Pilih produk —</option>
								@foreach($products as $p)
									<option value="{{ $p->id }}" data-stock="{{ $p->stock }}">{{ $p->sku }} · {{ $p->name }} (stok: {{ $p->stock }})</option>
								@endforeach
							</select>
							<div id="mutasiStockHint" style="font-size:11.5px; color:#9a9288; margin-top:4px;"></div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">Jumlah <span style="color:#a5432f;">*</span></label>
									<input type="number" name="qty" id="mutasiQty" class="form-control-admin" min="1" max="9999" value="1" required>
								</div>
							</div>
							<div class="col-md-6">
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">No. Referensi</label>
									<input type="text" name="reference" class="form-control-admin" placeholder="Mis. PO-2026-01 / INV-...">
								</div>
							</div>
						</div>

						<div style="margin-bottom:4px;">
							<label class="form-label-admin">Keterangan</label>
							<textarea name="note" rows="2" class="form-control-admin" placeholder="Contoh: restok dari pengrajin Kerinci, koreksi stok opname, dll."></textarea>
						</div>
					</div>
					<div class="modal-footer" style="border-top:1px solid #ece8de;">
						<button type="button" class="btn-admin btn-admin-outline" data-dismiss="modal">Batal</button>
						<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Mutasi</button>
					</div>
				</form>
			</div>
		</div>
	</div>

@endsection

@push('scripts')
<script src="{{ asset('frontend/vendor/bootstrap/js/popper.js') }}"></script>
<script src="{{ asset('frontend/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script>
$(function() {
	var $productSel = $('#mutasiProduct');
	var $qty = $('#mutasiQty');
	var $hint = $('#mutasiStockHint');
	var $title = $('#modalMutasiTitle');
	var $typeMasuk = $('#typeMasuk'), $typeKeluar = $('#typeKeluar');

	function updateHint() {
		var opt = $productSel.find('option:selected');
		var stock = opt.data('stock');
		if (stock == null || !opt.val()) { $hint.text(''); return; }
		var type = $('input[name=type]:checked').val();
		if (type === 'keluar') {
			$hint.html('Stok saat ini: <strong>' + stock + '</strong> · Maksimal keluar: <strong>' + stock + '</strong>');
			$qty.attr('max', stock);
		} else {
			$hint.html('Stok saat ini: <strong>' + stock + '</strong>');
			$qty.attr('max', 9999);
		}
	}

	$productSel.on('change', updateHint);
	$('input[name=type]').on('change', updateHint);

	// Reset form saat tombol default "Tambah Mutasi"
	$('.js-mutasi-btn').on('click', function() {
		$title.text('Tambah Mutasi Stok');
		$typeMasuk.prop('checked', true);
		$productSel.val('');
		$qty.val(1);
		$('input[name=reference]').val('');
		$('textarea[name=note]').val('');
		$hint.text('');
	});

	// Restok shortcut: pre-fill produk + type=masuk + qty rekomendasi
	$('.js-restok-btn').on('click', function() {
		var d = $(this).data();
		$title.text('Restok: ' + d.productName);
		$typeMasuk.prop('checked', true);
		$productSel.val(d.productId);
		$qty.val(d.recommendedQty || 1);
		$('input[name=reference]').val('');
		$('textarea[name=note]').val('Restok otomatis — stok di bawah minimum');
		updateHint();
	});

	@if($errors->any())
		$('#modalMutasi').modal('show');
	@endif
});
</script>
@endpush
