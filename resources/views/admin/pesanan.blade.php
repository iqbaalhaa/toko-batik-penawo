@extends('layouts.admin')

@section('title', 'Pesanan')
@section('page_title', 'Pesanan')
@section('page_subtitle', 'Kelola pesanan pelanggan dan pantau status pengiriman')

@push('styles')
<style>
	.status-form { margin: 0; display: inline-block; }
	.status-select {
		appearance: none;
		-webkit-appearance: none;
		-moz-appearance: none;
		padding: 4px 26px 4px 10px;
		border-radius: 999px;
		font-size: 11.5px;
		font-weight: 500;
		letter-spacing: .3px;
		border: 1px solid transparent;
		cursor: pointer;
		background-repeat: no-repeat;
		background-position: right 8px center;
		background-size: 10px;
		transition: filter .15s, box-shadow .15s;
		font-family: inherit;
	}
	.status-select:hover { filter: brightness(0.95); box-shadow: 0 1px 4px rgba(0,0,0,.08); }
	.status-select:focus { outline: none; box-shadow: 0 0 0 3px rgba(194,158,92,.25); }

	/* Dropdown arrow warna berbeda per badge */
	.status-select.badge-success { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'><path fill='%232f7a4c' d='M5 6 0 0h10z'/></svg>"); }
	.status-select.badge-warning { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'><path fill='%23a87318' d='M5 6 0 0h10z'/></svg>"); }
	.status-select.badge-danger  { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'><path fill='%23a5432f' d='M5 6 0 0h10z'/></svg>"); }
	.status-select.badge-info    { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'><path fill='%233a5fa0' d='M5 6 0 0h10z'/></svg>"); }
	.status-select.badge-muted   { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'><path fill='%236c665e' d='M5 6 0 0h10z'/></svg>"); }
	.status-select.badge-brand   { background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 6'><path fill='%238a6b2b' d='M5 6 0 0h10z'/></svg>"); }

	/* Option text jangan mewarisi warna badge */
	.status-select option { color: #2d2a26; background: #fff; font-weight: 400; }

	/* Pagination (sama seperti admin/produk) */
	.admin-pager { display: inline-flex; gap: 4px; }
	.admin-pager-btn {
		min-width: 34px; height: 34px; padding: 0 10px;
		display: inline-flex; align-items: center; justify-content: center;
		border: 1px solid #e0dbcf; background: #fff; color: #4d4640;
		border-radius: 4px; font-size: 13px; text-decoration: none;
		transition: border-color .15s, background .15s, color .15s;
	}
	.admin-pager-btn:hover { border-color: #c29e5c; color: #c29e5c; text-decoration: none; }
	.admin-pager-btn.active { background: #c29e5c; border-color: #c29e5c; color: #fff; font-weight: 600; }
	.admin-pager-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }

	/* Bulk action bar */
	.bulk-bar {
		display: none;
		align-items: center; gap: 12px;
		padding: 10px 14px; margin-bottom: 14px;
		background: #fbf3df; border: 1px solid #ecd9a5; border-radius: 6px;
		color: #6e5824; font-size: 13px;
	}
	.bulk-bar.show { display: flex; }
	.bulk-bar .bulk-count { color: #2d2a26; font-weight: 600; }
	.bulk-bar .bulk-actions { margin-left: auto; display: flex; gap: 8px; }
	.btn-bulk {
		padding: 7px 14px; border-radius: 4px; font-size: 12.5px; font-weight: 500;
		border: 1px solid transparent; cursor: pointer;
		display: inline-flex; align-items: center; gap: 6px;
		font-family: inherit;
	}
	.btn-bulk.danger { background: #d86a59; color: #fff; }
	.btn-bulk.danger:hover { background: #c3554a; }
	.btn-bulk.primary { background: #c29e5c; color: #fff; }
	.btn-bulk.primary:hover { background: #a88541; }
	.btn-bulk.outline { background: #fff; color: #6c665e; border-color: #ddd6c6; }
	.btn-bulk.outline:hover { color: #2d2a26; border-color: #c29e5c; }

	/* Checkbox styling */
	.row-check, .check-all {
		width: 16px; height: 16px;
		accent-color: #c29e5c;
		cursor: pointer; vertical-align: middle;
	}
	.admin-table th.col-check, .admin-table td.col-check { width: 38px; text-align: center; padding-right: 6px; }
	.admin-table tr.row-selected { background: #fbf3df !important; }
</style>
@endpush

@section('content')
	@php
		$statusBadge = [
			'selesai'        => 'badge-success',
			'dikirim'        => 'badge-info',
			'diproses'       => 'badge-warning',
			'menunggu_bayar' => 'badge-muted',
			'dibatalkan'     => 'badge-danger',
		];
	@endphp

	<!-- Summary -->
	<div class="stat-grid" style="margin-bottom:22px;">
		<div class="stat-card">
			<div class="stat-card-icon bg-brand"><i class="fa fa-file-text-o"></i></div>
			<div>
				<div class="stat-card-label">Total Pesanan</div>
				<div class="stat-card-value">{{ $counts['total'] }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-blue"><i class="fa fa-truck"></i></div>
			<div>
				<div class="stat-card-label">Perlu Diproses</div>
				<div class="stat-card-value">{{ $counts['perlu_diproses'] }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-green"><i class="fa fa-check-square-o"></i></div>
			<div>
				<div class="stat-card-label">Selesai</div>
				<div class="stat-card-value">{{ $counts['selesai'] }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-red"><i class="fa fa-times-circle"></i></div>
			<div>
				<div class="stat-card-label">Dibatalkan</div>
				<div class="stat-card-value">{{ $counts['dibatalkan'] }}</div>
			</div>
		</div>
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Daftar Pesanan</h3>
				<div class="admin-card-sub">Urutan terbaru ke terlama</div>
			</div>
		</div>

		<form method="GET" action="{{ route('admin.pesanan') }}" class="toolbar">
			<div class="toolbar-search">
				<i class="fa fa-search"></i>
				<input type="text" name="q" value="{{ request('q') }}" class="form-control-admin" placeholder="Cari nomor pesanan, nama, atau email pelanggan...">
			</div>
			<select name="status" class="form-control-admin" style="max-width:180px;" onchange="this.form.submit()">
				<option value="">Semua Status</option>
				@foreach(\App\Models\Order::STATUS_LABELS as $val => $label)
					<option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
				@endforeach
			</select>
			<input type="date" name="date" value="{{ request('date') }}" class="form-control-admin" style="max-width:160px;" onchange="this.form.submit()">

			<button type="submit" class="btn-admin"><i class="fa fa-search"></i> Cari</button>
			@if(request()->hasAny(['q','status','date']))
				<a href="{{ route('admin.pesanan') }}" class="btn-admin btn-admin-outline"><i class="fa fa-times"></i> Reset</a>
			@endif
		</form>

		<!-- Hidden bulk delete form (checkbox terhubung lewat atribut form="bulkDeleteForm") -->
		<form id="bulkDeleteForm" action="{{ route('admin.pesanan.bulk-destroy') }}" method="POST" style="display:none;">
			@csrf
			@method('DELETE')
		</form>

		<!-- Bulk action bar -->
		<div class="bulk-bar" id="bulkBar">
			<i class="fa fa-check-square-o"></i>
			<span><span class="bulk-count" id="bulkCount">0</span> pesanan dipilih</span>
			<div class="bulk-actions">
				<button type="button" class="btn-bulk outline" id="bulkClearBtn"><i class="fa fa-times"></i> Batal</button>
				<button type="button" class="btn-bulk primary" id="bulkPrintBtn"><i class="fa fa-print"></i> Cetak Terpilih</button>
				<button type="button" class="btn-bulk danger" id="bulkDeleteBtn"
					data-confirm-trigger="bulkDeleteForm"
					data-confirm-title="Hapus pesanan terpilih?"
					data-confirm-message="Seluruh data pesanan beserta item di dalamnya akan dihapus permanen. Tindakan ini tidak bisa dibatalkan."
					data-confirm-ok="Hapus Semua"><i class="fa fa-trash-o"></i> Hapus Terpilih</button>
			</div>
		</div>

		<div style="overflow-x:auto;">
			<table class="admin-table">
				<thead>
					<tr>
						<th class="col-check"><input type="checkbox" id="checkAll" class="check-all" title="Pilih semua"></th>
						<th>No. Pesanan</th>
						<th>Tanggal</th>
						<th>Pelanggan</th>
						<th>Item</th>
						<th>Total</th>
						<th>Pembayaran</th>
						<th>Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@forelse($orders as $order)
					<tr>
						<td class="col-check">
							<input type="checkbox" name="ids[]" value="{{ $order->id }}" form="bulkDeleteForm" class="row-check" aria-label="Pilih pesanan {{ $order->invoice_number }}">
						</td>
						<td><strong style="color:#2d2a26;">{{ $order->invoice_number }}</strong></td>
						<td>{{ $order->created_at->format('d M Y') }}</td>
						<td>
							<div style="font-weight:500;">{{ $order->customer_name }}</div>
							<div style="font-size:12px; color:#9a9288;">{{ $order->customer_email }}</div>
						</td>
						<td>{{ $order->items->count() }} item</td>
						<td><strong>{{ $rupiah($order->total) }}</strong></td>
						<td>{{ $order->payment_method ?? '—' }}</td>
						<td>
							<form action="{{ route('admin.pesanan.status', $order) }}" method="POST" class="status-form">
								@csrf
								@method('PATCH')
								<select name="status" class="status-select {{ $statusBadge[$order->status] ?? 'badge-muted' }}" onchange="this.form.submit()" title="Klik untuk ubah status">
									@foreach(\App\Models\Order::STATUS_LABELS as $val => $label)
										<option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
									@endforeach
								</select>
							</form>
						</td>
						<td>
							<a href="{{ route('pesanan.sukses', $order->invoice_number) }}" target="_blank" class="btn-admin-icon" title="Detail"><i class="fa fa-eye"></i></a>
							<a href="{{ route('admin.pesanan.cetak', $order) }}" target="_blank" class="btn-admin-icon" title="Cetak Invoice"><i class="fa fa-print"></i></a>
							<form action="{{ route('admin.pesanan.destroy', $order) }}" method="POST" style="display:inline;"
								data-confirm-title="Hapus pesanan {{ $order->invoice_number }}?"
								data-confirm-message="Data pesanan beserta seluruh item di dalamnya akan dihapus permanen. Tindakan ini tidak bisa dibatalkan."
								data-confirm-ok="Hapus Pesanan">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></button>
							</form>
						</td>
					</tr>
					@empty
					<tr><td colspan="9" style="text-align:center; padding:24px; color:#9a9288;">
						@if(request()->hasAny(['q','status','date']))
							Tidak ada pesanan yang cocok dengan filter. <a href="{{ route('admin.pesanan') }}">Reset</a>
						@else
							Belum ada pesanan
						@endif
					</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if($orders->total() > 0)
		<div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px; border-top:1px solid #f2efe7; margin-top:8px; flex-wrap:wrap; gap:10px;">
			<div style="font-size:13px; color:#9a9288;">
				Menampilkan {{ $orders->firstItem() }} - {{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan
			</div>

			@if($orders->hasPages())
			<div class="admin-pager">
				@if($orders->onFirstPage())
					<span class="admin-pager-btn disabled"><i class="fa fa-chevron-left"></i></span>
				@else
					<a href="{{ $orders->previousPageUrl() }}" class="admin-pager-btn"><i class="fa fa-chevron-left"></i></a>
				@endif

				@foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
					@if($page == $orders->currentPage())
						<span class="admin-pager-btn active">{{ $page }}</span>
					@else
						<a href="{{ $url }}" class="admin-pager-btn">{{ $page }}</a>
					@endif
				@endforeach

				@if($orders->hasMorePages())
					<a href="{{ $orders->nextPageUrl() }}" class="admin-pager-btn"><i class="fa fa-chevron-right"></i></a>
				@else
					<span class="admin-pager-btn disabled"><i class="fa fa-chevron-right"></i></span>
				@endif
			</div>
			@endif
		</div>
		@endif
	</div>

@endsection

@push('scripts')
<script>
(function(){
	var checkAll = document.getElementById('checkAll');
	var rowChecks = Array.prototype.slice.call(document.querySelectorAll('.row-check'));
	var bulkBar = document.getElementById('bulkBar');
	var bulkCount = document.getElementById('bulkCount');
	var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
	var bulkClearBtn = document.getElementById('bulkClearBtn');

	function refreshBulk() {
		var checked = rowChecks.filter(function(cb){ return cb.checked; });
		var n = checked.length;
		bulkCount.textContent = n;
		bulkBar.classList.toggle('show', n > 0);
		rowChecks.forEach(function(cb){
			var tr = cb.closest('tr');
			if (tr) tr.classList.toggle('row-selected', cb.checked);
		});
		if (checkAll) {
			checkAll.checked = (rowChecks.length > 0 && n === rowChecks.length);
			checkAll.indeterminate = (n > 0 && n < rowChecks.length);
		}
		// Update pesan konfirmasi dengan jumlah dinamis
		if (bulkDeleteBtn) {
			bulkDeleteBtn.dataset.confirmTitle = 'Hapus ' + n + ' pesanan terpilih?';
		}
	}

	if (checkAll) {
		checkAll.addEventListener('change', function(){
			rowChecks.forEach(function(cb){ cb.checked = checkAll.checked; });
			refreshBulk();
		});
	}
	rowChecks.forEach(function(cb){ cb.addEventListener('change', refreshBulk); });

	if (bulkClearBtn) {
		bulkClearBtn.addEventListener('click', function(){
			rowChecks.forEach(function(cb){ cb.checked = false; });
			refreshBulk();
		});
	}

	// Bulk cetak invoice — buka tab baru dengan POST form dinamis
	var bulkPrintBtn = document.getElementById('bulkPrintBtn');
	if (bulkPrintBtn) {
		bulkPrintBtn.addEventListener('click', function(){
			var checked = rowChecks.filter(function(cb){ return cb.checked; });
			if (!checked.length) return;

			var f = document.createElement('form');
			f.method = 'POST';
			f.action = @json(route('admin.pesanan.cetak-massal'));
			f.target = '_blank';

			var csrf = document.createElement('input');
			csrf.type = 'hidden'; csrf.name = '_token';
			csrf.value = @json(csrf_token());
			f.appendChild(csrf);

			checked.forEach(function(cb){
				var i = document.createElement('input');
				i.type = 'hidden'; i.name = 'ids[]'; i.value = cb.value;
				f.appendChild(i);
			});

			document.body.appendChild(f);
			f.submit();
			document.body.removeChild(f);
		});
	}
})();
</script>
@endpush
