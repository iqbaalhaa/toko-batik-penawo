@extends('layouts.admin')

@section('title', 'Kelola Produk')
@section('page_title', 'Kelola Produk')
@section('page_subtitle', 'Kelola katalog produk batik yang dijual')

@section('content')
	@php
		$nextSku = 'BP-' . str_pad((string) (\App\Models\Product::max('id') + 1), 3, '0', STR_PAD_LEFT);
	@endphp

	@if($errors->any())
		<div class="flash" style="background:#fbe4df; border-color:#f2c6be; color:#a5432f;">
			<strong>Gagal menyimpan:</strong>
			<ul style="margin:6px 0 0 18px; padding:0;">
				@foreach($errors->all() as $err)
					<li>{{ $err }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Daftar Produk</h3>
				<div class="admin-card-sub">Total {{ $products->total() }} produk terdaftar</div>
			</div>
			<button type="button" class="btn-admin js-produk-add" data-toggle="modal" data-target="#modalProduk">
				<i class="fa fa-plus"></i> Tambah Produk
			</button>
		</div>

		<form method="GET" action="{{ route('admin.produk') }}" class="toolbar">
			<div class="toolbar-search">
				<i class="fa fa-search"></i>
				<input type="text" name="q" value="{{ request('q') }}" class="form-control-admin" placeholder="Cari produk berdasarkan nama atau SKU...">
			</div>

			<select name="category_id" class="form-control-admin" style="max-width:180px;" onchange="this.form.submit()">
				<option value="">Semua Kategori</option>
				@foreach($categories as $cat)
					<option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
				@endforeach
			</select>

			<select name="status" class="form-control-admin" style="max-width:150px;" onchange="this.form.submit()">
				<option value="">Semua Status</option>
				<option value="aktif"  @selected(request('status') === 'aktif')>Aktif</option>
				<option value="habis"  @selected(request('status') === 'habis')>Habis</option>
				<option value="arsip"  @selected(request('status') === 'arsip')>Arsip</option>
			</select>

			<button type="submit" class="btn-admin"><i class="fa fa-search"></i> Cari</button>
			@if(request()->hasAny(['q','category_id','status']))
				<a href="{{ route('admin.produk') }}" class="btn-admin btn-admin-outline"><i class="fa fa-times"></i> Reset</a>
			@endif
		</form>

		<div style="overflow-x:auto;">
			<table class="admin-table">
				<thead>
					<tr>
						<th>Produk</th>
						<th>SKU</th>
						<th>Kategori</th>
						<th>Harga</th>
						<th>Stok</th>
						<th>Status</th>
						<th style="width:160px;">Aksi</th>
					</tr>
				</thead>
				<tbody id="produkTableBody">
					@forelse($products as $p)
					<tr>
						<td>
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="table-thumb">
								<div>
									<div style="font-weight:500; color:#2d2a26;">{{ $p->name }}</div>
									<div style="font-size:12px; color:#9a9288;">{{ Str::limit($p->description, 50) }}</div>
								</div>
							</div>
						</td>
						<td><code style="background:#f5f2ea; padding:2px 6px; border-radius:3px; font-size:12px;">{{ $p->sku }}</code></td>
						<td>{{ $p->category?->name ?? '—' }}</td>
						<td><strong>{{ $rupiah($p->price) }}</strong></td>
						<td>
							<span style="{{ $p->isLowStock() ? 'color:#a5432f;font-weight:600;' : '' }}">{{ $p->stock }}</span>
						</td>
						<td>
							@if($p->status === 'aktif')
								<span class="badge-pill badge-success">Aktif</span>
							@elseif($p->status === 'habis')
								<span class="badge-pill badge-warning">Habis</span>
							@else
								<span class="badge-pill badge-muted">Arsip</span>
							@endif
						</td>
						<td>
							<a href="{{ route('produk.detail', $p->slug) }}" target="_blank" class="btn-admin-icon" title="Lihat"><i class="fa fa-eye"></i></a>
							<button
								type="button"
								class="btn-admin-icon js-produk-edit"
								title="Edit"
								data-toggle="modal"
								data-target="#modalProduk"
								data-id="{{ $p->id }}"
								data-name="{{ $p->name }}"
								data-sku="{{ $p->sku }}"
								data-category="{{ $p->category_id }}"
								data-price="{{ $p->price }}"
								data-stock="{{ $p->stock }}"
								data-stock-min="{{ $p->stock_min }}"
								data-description="{{ $p->description }}"
								data-weight="{{ $p->weight }}"
								data-material="{{ $p->material }}"
								data-colors="{{ implode(', ', $p->colors ?? []) }}"
								data-sizes="{{ implode(', ', $p->sizes ?? []) }}"
								data-status="{{ $p->status }}"
								@php
									$rawPaths = is_array($p->images) && count($p->images) ? $p->images : ($p->image ? [$p->image] : []);
									$imagePairs = [];
									foreach ($rawPaths as $i => $path) {
										$imagePairs[] = ['path' => $path, 'url' => $p->image_urls[$i] ?? ''];
									}
								@endphp
								data-images="{{ json_encode($imagePairs) }}"
							><i class="fa fa-pencil-square-o"></i></button>
							<form action="{{ route('admin.produk.destroy', $p) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus produk &quot;{{ $p->name }}&quot;? Tindakan ini tidak dapat dibatalkan.');">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></button>
							</form>
						</td>
					</tr>
					@empty
					<tr><td colspan="7" style="text-align:center; padding:24px; color:#9a9288;">
						@if(request()->hasAny(['q','category_id','status']))
							Tidak ada produk yang cocok dengan filter. <a href="{{ route('admin.produk') }}">Reset</a>
						@else
							Belum ada produk. Klik <strong>Tambah Produk</strong> untuk memulai.
						@endif
					</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if($products->total() > 0)
		<div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px; border-top:1px solid #f2efe7; margin-top:8px; flex-wrap:wrap; gap:10px;">
			<div style="font-size:13px; color:#9a9288;">
				Menampilkan {{ $products->firstItem() }} - {{ $products->lastItem() }} dari {{ $products->total() }} produk
			</div>

			@if($products->hasPages())
			<div class="admin-pager">
				@if($products->onFirstPage())
					<span class="admin-pager-btn disabled"><i class="fa fa-chevron-left"></i></span>
				@else
					<a href="{{ $products->previousPageUrl() }}" class="admin-pager-btn"><i class="fa fa-chevron-left"></i></a>
				@endif

				@foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
					@if($page == $products->currentPage())
						<span class="admin-pager-btn active">{{ $page }}</span>
					@else
						<a href="{{ $url }}" class="admin-pager-btn">{{ $page }}</a>
					@endif
				@endforeach

				@if($products->hasMorePages())
					<a href="{{ $products->nextPageUrl() }}" class="admin-pager-btn"><i class="fa fa-chevron-right"></i></a>
				@else
					<span class="admin-pager-btn disabled"><i class="fa fa-chevron-right"></i></span>
				@endif
			</div>
			@endif
		</div>
		@endif
	</div>

	<!-- Modal Tambah/Edit Produk -->
	<div class="modal fade" id="modalProduk" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content" style="border-radius:6px;">
				<form id="produkForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.produk.store') }}">
					@csrf
					<input type="hidden" name="_method" value="POST" id="produkMethod">

					<div class="modal-header" style="border-bottom:1px solid #ece8de;">
						<h5 class="modal-title" id="produkModalTitle" style="font-weight:600;">Tambah Produk Baru</h5>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
					</div>
					<div class="modal-body" style="padding:24px;">
						<div class="row">
							<div class="col-md-8">
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">Nama Produk <span style="color:#a5432f;">*</span></label>
									<input type="text" class="form-control-admin" name="name" id="f_name" value="{{ old('name') }}" required>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">SKU <span style="color:#a5432f;">*</span></label>
											<input type="text" class="form-control-admin" name="sku" id="f_sku" value="{{ old('sku', $nextSku) }}" required>
										</div>
									</div>
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Kategori <span style="color:#a5432f;">*</span></label>
											<select class="form-control-admin" name="category_id" id="f_category" required>
												@foreach($categories as $cat)
													<option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Harga (Rp) <span style="color:#a5432f;">*</span></label>
											<div style="position:relative;">
												<span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9a9288; font-size:13.5px; pointer-events:none;">Rp</span>
												<input type="text" inputmode="numeric" autocomplete="off" class="form-control-admin rupiah-input" name="price" id="f_price" value="{{ old('price') }}" required placeholder="0" style="padding-left:34px;">
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Stok <span style="color:#a5432f;">*</span></label>
											<input type="number" class="form-control-admin" name="stock" id="f_stock" min="0" value="{{ old('stock', 0) }}" required>
										</div>
									</div>
									<div class="col-md-3">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Stok Min</label>
											<input type="number" class="form-control-admin" name="stock_min" id="f_stock_min" min="0" value="{{ old('stock_min', 10) }}">
										</div>
									</div>
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Berat</label>
											<input type="text" class="form-control-admin" name="weight" id="f_weight" placeholder="0,3 kg" value="{{ old('weight') }}">
										</div>
									</div>
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Bahan</label>
											<input type="text" class="form-control-admin" name="material" id="f_material" placeholder="Katun Primisima" value="{{ old('material') }}">
										</div>
									</div>
								</div>
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">Warna <small style="color:#9a9288;">(pisahkan dengan koma)</small></label>
									<input type="text" class="form-control-admin" name="colors" id="f_colors" placeholder="Cokelat Sogan, Hitam" value="{{ old('colors') }}">
								</div>
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">Ukuran</label>
									<div id="sizeChips" class="chip-box"></div>
									<div style="display:flex; gap:6px; margin-top:6px;">
										<input type="text" class="form-control-admin" id="f_sizes_text" placeholder="Ketik ukuran lalu Enter (mis. XL, All Size, 30x25x10 cm)" autocomplete="off">
										<button type="button" class="btn-admin btn-admin-outline" id="f_sizes_add" tabindex="-1">Tambah</button>
									</div>
									<div class="size-presets">
										<span class="size-presets-label">Pakaian:</span>
										@foreach(['S','M','L','XL','XXL','XXXL','All Size'] as $s)
											<button type="button" class="size-preset" data-size="{{ $s }}">{{ $s }}</button>
										@endforeach
									</div>
									<div class="size-presets">
										<span class="size-presets-label">Sepatu:</span>
										@foreach(['38','39','40','41','42','43','44'] as $s)
											<button type="button" class="size-preset" data-size="{{ $s }}">{{ $s }}</button>
										@endforeach
									</div>
									<input type="hidden" name="sizes" id="f_sizes" value="{{ old('sizes') }}">
								</div>
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">Deskripsi <span style="color:#a5432f;">*</span></label>
									<textarea rows="4" class="form-control-admin" name="description" id="f_description" required>{{ old('description') }}</textarea>
								</div>
							</div>
							<div class="col-md-4">
								<label class="form-label-admin">Foto Produk <small style="color:#9a9288;">(maks. 7 foto, 2 MB / foto)</small></label>

								<div id="photoGrid" class="photo-grid"></div>

								<label for="f_images" class="photo-add-btn" id="photoAddBtn">
									<i class="fa fa-camera"></i>
									<span>Tambah Foto</span>
								</label>
								<input type="file" name="images[]" id="f_images" accept="image/jpeg,image/png,image/webp" multiple style="display:none;">

								<div class="photo-counter"><span id="photoCount">0</span> / 7 foto</div>
								<div id="photoError" class="photo-error" style="display:none;"></div>

								<div style="margin-top:18px;">
									<label class="form-label-admin">Status <span style="color:#a5432f;">*</span></label>
									<select class="form-control-admin" name="status" id="f_status" required>
										<option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
										<option value="arsip" {{ old('status') === 'arsip' ? 'selected' : '' }}>Arsip</option>
										<option value="habis" id="f_status_habis" {{ old('status') === 'habis' ? 'selected' : '' }}>Habis</option>
									</select>
									<div style="font-size:11px; color:#9a9288; margin-top:4px;">Jika stok 0, status otomatis <em>Habis</em>.</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer" style="border-top:1px solid #ece8de;">
						<button type="button" class="btn-admin btn-admin-outline" data-dismiss="modal">Batal</button>
						<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Produk</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
<style>
	.photo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 10px; min-height: 4px; }
	.photo-tile { position: relative; aspect-ratio: 1 / 1; background: #faf7ef; border: 1px solid #e0dbcf; border-radius: 4px; overflow: hidden; }
	.photo-tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
	.photo-tile-remove {
		position: absolute; top: 4px; right: 4px;
		width: 22px; height: 22px; border-radius: 50%;
		background: rgba(45,42,38,.75); color: #fff; border: 0;
		display: inline-flex; align-items: center; justify-content: center;
		cursor: pointer; font-size: 14px; line-height: 1;
		transition: background .15s;
	}
	.photo-tile-remove:hover { background: #a5432f; }
	.photo-tile-badge {
		position: absolute; bottom: 4px; left: 4px;
		background: rgba(194,158,92,.95); color: #fff;
		font-size: 10px; padding: 2px 6px; border-radius: 3px;
		text-transform: uppercase; letter-spacing: .5px; font-weight: 600;
	}
	.photo-add-btn {
		display: flex; flex-direction: column; align-items: center; justify-content: center;
		padding: 18px 10px; border: 2px dashed #d8d1bf; border-radius: 6px;
		color: #9a9288; font-size: 13px; cursor: pointer;
		background: #faf7ef; margin: 0;
		transition: border-color .15s, color .15s, background .15s;
	}
	.photo-add-btn:hover { border-color: #c29e5c; color: #c29e5c; background: #f5ecd7; }
	.photo-add-btn i { font-size: 24px; color: #c29e5c; margin-bottom: 4px; }
	.photo-add-btn.disabled { opacity: .5; cursor: not-allowed; pointer-events: none; }
	.photo-counter { font-size: 11.5px; color: #9a9288; margin-top: 6px; text-align: right; }
	.photo-error { color: #a5432f; font-size: 12px; margin-top: 6px; padding: 6px 10px; background: #fbe4df; border-radius: 3px; }

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

	.chip-box {
		min-height: 38px;
		padding: 6px;
		border: 1px solid #e0dbcf;
		border-radius: 4px;
		background: #fff;
		display: flex; flex-wrap: wrap; gap: 6px;
		align-items: flex-start;
	}
	.chip-box:empty::before {
		content: 'Belum ada ukuran dipilih';
		color: #bdb7ab; font-size: 12.5px; padding: 4px 6px;
	}
	.chip {
		display: inline-flex; align-items: center; gap: 6px;
		padding: 4px 4px 4px 10px;
		background: #f5ecd7; color: #8a6b2b;
		border: 1px solid #e4d5aa; border-radius: 999px;
		font-size: 12.5px; font-weight: 500; letter-spacing: .2px;
	}
	.chip-remove {
		width: 18px; height: 18px; border-radius: 50%;
		background: rgba(138,107,43,.15); color: #8a6b2b;
		display: inline-flex; align-items: center; justify-content: center;
		border: 0; cursor: pointer; font-size: 12px; line-height: 1;
		transition: background .12s;
	}
	.chip-remove:hover { background: #a5432f; color: #fff; }

	.size-presets { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
	.size-presets-label { font-size: 11.5px; color: #9a9288; text-transform: uppercase; letter-spacing: .5px; margin-right: 4px; }
	.size-preset {
		padding: 4px 12px; font-size: 12.5px;
		background: #fff; color: #6c665e;
		border: 1px solid #e0dbcf; border-radius: 999px;
		cursor: pointer; transition: all .12s;
	}
	.size-preset:hover { border-color: #c29e5c; color: #c29e5c; }
	.size-preset.active { background: #c29e5c; border-color: #c29e5c; color: #fff; font-weight: 500; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('frontend/vendor/bootstrap/js/popper.js') }}"></script>
<script src="{{ asset('frontend/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script>
$(function() {
	var storeUrl = @json(route('admin.produk.store'));
	var updateUrlTpl = @json(url('admin/produk')) + '/:id';
	var MAX_PHOTOS = 7;
	var MAX_BYTES = 2 * 1024 * 1024;

	// ==== Rupiah input helper ====
	function formatRupiah(v) {
		var digits = String(v == null ? '' : v).replace(/\D/g, '');
		return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
	}
	function unformatRupiah(v) { return String(v == null ? '' : v).replace(/\D/g, ''); }

	$(document).on('input', '.rupiah-input', function() {
		var el = this;
		var before = el.value;
		var caret = el.selectionStart;
		var digitsBeforeCaret = before.slice(0, caret).replace(/\D/g, '').length;
		el.value = formatRupiah(before);
		// restore caret: find position where N digits have been seen
		var seen = 0, pos = 0;
		for (; pos < el.value.length && seen < digitsBeforeCaret; pos++) {
			if (/\d/.test(el.value[pos])) seen++;
		}
		try { el.setSelectionRange(pos, pos); } catch (e) { /* some input types don't support selection */ }
	});

	$('#produkForm').on('submit', function() {
		$(this).find('.rupiah-input').each(function() { this.value = unformatRupiah(this.value); });
	});

	// format existing old() value after initial render
	$('.rupiah-input').each(function() { this.value = formatRupiah(this.value); });

	// ==== Size chip helper ====
	var sizes = [];
	function renderSizes() {
		var $box = $('#sizeChips').empty();
		sizes.forEach(function(s) {
			var $chip = $('<span class="chip"></span>').text(s);
			$('<button type="button" class="chip-remove" aria-label="Hapus">&times;</button>')
				.on('click', function() { removeSize(s); })
				.appendTo($chip);
			$box.append($chip);
		});
		$('#f_sizes').val(sizes.join(', '));
		$('.size-preset').each(function() {
			$(this).toggleClass('active', sizes.indexOf($(this).data('size').toString()) !== -1);
		});
	}
	function addSize(raw) {
		var v = (raw || '').trim();
		if (!v) return;
		if (sizes.indexOf(v) !== -1) return;
		sizes.push(v);
		renderSizes();
	}
	function removeSize(v) {
		sizes = sizes.filter(function(s) { return s !== v; });
		renderSizes();
	}
	function parseSizes(str) {
		return (str || '').split(',').map(function(s){ return s.trim(); }).filter(Boolean);
	}
	function resetSizes(str) {
		sizes = parseSizes(str);
		renderSizes();
	}

	$('#f_sizes_text').on('keydown', function(e) {
		if (e.key === 'Enter' || e.key === ',') {
			e.preventDefault();
			addSize(this.value);
			this.value = '';
		} else if (e.key === 'Backspace' && !this.value && sizes.length) {
			sizes.pop();
			renderSizes();
		}
	});
	$('#f_sizes_add').on('click', function() {
		addSize($('#f_sizes_text').val());
		$('#f_sizes_text').val('').focus();
	});
	$('.size-preset').on('click', function() {
		var v = $(this).data('size').toString();
		sizes.indexOf(v) === -1 ? addSize(v) : removeSize(v);
	});
	resetSizes('{{ old('sizes') }}');

	// State untuk uploader
	var existingImages = []; // [{path, url}]
	var newFiles = [];        // File[]

	function renderPhotos() {
		var $grid = $('#photoGrid').empty();

		existingImages.forEach(function(img, i) {
			var $tile = $('<div class="photo-tile"></div>');
			$tile.append($('<img>').attr('src', img.url));
			if (i === 0) $tile.append('<span class="photo-tile-badge">Cover</span>');
			$tile.append($('<input type="hidden" name="existing_images[]">').val(img.path));
			$tile.append(
				$('<button type="button" class="photo-tile-remove" title="Hapus"><i class="fa fa-times"></i></button>')
					.on('click', function() { existingImages.splice(i, 1); renderPhotos(); })
			);
			$grid.append($tile);
		});

		newFiles.forEach(function(file, i) {
			var $tile = $('<div class="photo-tile"></div>');
			var $img = $('<img>');
			var reader = new FileReader();
			reader.onload = function(e) { $img.attr('src', e.target.result); };
			reader.readAsDataURL(file);
			$tile.append($img);
			if (existingImages.length === 0 && i === 0) $tile.append('<span class="photo-tile-badge">Cover</span>');
			$tile.append('<span class="photo-tile-badge" style="right:4px;left:auto;background:#56a676;">Baru</span>');
			$tile.append(
				$('<button type="button" class="photo-tile-remove" title="Hapus"><i class="fa fa-times"></i></button>')
					.on('click', function() { newFiles.splice(i, 1); syncInputFiles(); renderPhotos(); })
			);
			$grid.append($tile);
		});

		var total = existingImages.length + newFiles.length;
		$('#photoCount').text(total);
		$('#photoAddBtn').toggleClass('disabled', total >= MAX_PHOTOS);
	}

	function syncInputFiles() {
		var dt = new DataTransfer();
		newFiles.forEach(function(f) { dt.items.add(f); });
		$('#f_images')[0].files = dt.files;
	}

	function showPhotoError(msg) {
		if (!msg) { $('#photoError').hide(); return; }
		$('#photoError').text(msg).show();
	}

	$('#f_images').on('change', function(e) {
		var picked = Array.from(e.target.files || []);
		showPhotoError('');
		var spaceLeft = MAX_PHOTOS - (existingImages.length + newFiles.length);
		var rejected = [];

		for (var i = 0; i < picked.length; i++) {
			var f = picked[i];
			if (newFiles.length + existingImages.length >= MAX_PHOTOS) {
				rejected.push(f.name + ' (melebihi batas 7 foto)');
				continue;
			}
			if (f.size > MAX_BYTES) {
				rejected.push(f.name + ' (lebih dari 2 MB)');
				continue;
			}
			if (!/^image\/(jpeg|png|webp)$/.test(f.type)) {
				rejected.push(f.name + ' (format tidak didukung)');
				continue;
			}
			newFiles.push(f);
		}

		syncInputFiles();
		renderPhotos();
		if (rejected.length) {
			showPhotoError('Dilewati: ' + rejected.join(', '));
		}
	});

	function resetForm() {
		var $f = $('#produkForm');
		$f[0].reset();
		$('#produkMethod').val('POST');
		$f.attr('action', storeUrl);
		$('#produkModalTitle').text('Tambah Produk Baru');
		$('#f_sku').val(@json($nextSku));
		$('#f_stock').val(0);
		$('#f_stock_min').val(10);
		$('#f_status').val('aktif');
		existingImages = [];
		newFiles = [];
		syncInputFiles();
		renderPhotos();
		showPhotoError('');
		resetSizes('');
	}

	$('.js-produk-add').on('click', resetForm);

	$('.js-produk-edit').on('click', function() {
		var d = $(this).data();
		resetForm();
		$('#produkMethod').val('PUT');
		$('#produkForm').attr('action', updateUrlTpl.replace(':id', d.id));
		$('#produkModalTitle').text('Edit Produk — ' + d.name);
		$('#f_name').val(d.name);
		$('#f_sku').val(d.sku);
		$('#f_category').val(d.category);
		$('#f_price').val(formatRupiah(d.price));
		$('#f_stock').val(d.stock);
		$('#f_stock_min').val(d.stockMin);
		$('#f_description').val(d.description);
		$('#f_weight').val(d.weight);
		$('#f_material').val(d.material);
		$('#f_colors').val(d.colors);
		resetSizes(d.sizes);
		$('#f_status').val(d.status);
		existingImages = Array.isArray(d.images) ? d.images.slice() : [];
		renderPhotos();
	});

	// Auto-open modal jika ada validation errors
	@if($errors->any())
		$('#modalProduk').modal('show');
		@if(old('_method') === 'PUT')
			$('#produkMethod').val('PUT');
		@endif
	@endif

});
</script>
@endpush
