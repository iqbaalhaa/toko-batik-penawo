@extends('layouts.admin')

@section('title', 'Kelola Produk')
@section('page_title', 'Kelola Produk')
@section('page_subtitle', 'Kelola katalog produk batik yang dijual')

@section('content')
	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Daftar Produk</h3>
				<div class="admin-card-sub">Total {{ $products->count() }} produk terdaftar</div>
			</div>
			<button type="button" class="btn-admin" data-toggle="modal" data-target="#modalProduk">
				<i class="zmdi zmdi-plus"></i> Tambah Produk
			</button>
		</div>

		<div class="toolbar">
			<div class="toolbar-search">
				<i class="zmdi zmdi-search"></i>
				<input type="text" class="form-control-admin" placeholder="Cari produk berdasarkan nama atau SKU...">
			</div>

			<select class="form-control-admin" style="max-width:180px;">
				<option>Semua Kategori</option>
				@foreach($categories as $cat)
					<option>{{ $cat->name }}</option>
				@endforeach
			</select>

			<select class="form-control-admin" style="max-width:150px;">
				<option>Semua Status</option>
				<option>Aktif</option>
				<option>Habis</option>
				<option>Arsip</option>
			</select>

			<button type="button" class="btn-admin btn-admin-outline">
				<i class="zmdi zmdi-download"></i> Ekspor
			</button>
		</div>

		<div style="overflow-x:auto;">
			<table class="admin-table">
				<thead>
					<tr>
						<th style="width:36px;"><input type="checkbox"></th>
						<th>Produk</th>
						<th>SKU</th>
						<th>Kategori</th>
						<th>Harga</th>
						<th>Stok</th>
						<th>Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@forelse($products as $p)
					<tr>
						<td><input type="checkbox"></td>
						<td>
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="{{ asset('frontend/images/'.$p->image) }}" alt="{{ $p->name }}" class="table-thumb">
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
							<a href="{{ route('produk.detail', $p->slug) }}" target="_blank" class="btn-admin-icon" title="Lihat"><i class="zmdi zmdi-eye"></i></a>
							<a href="#" class="btn-admin-icon" title="Edit"><i class="zmdi zmdi-edit"></i></a>
							<a href="#" class="btn-admin-icon danger" title="Hapus"><i class="zmdi zmdi-delete"></i></a>
						</td>
					</tr>
					@empty
					<tr><td colspan="8" style="text-align:center; padding:24px; color:#9a9288;">Belum ada produk</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px; border-top:1px solid #f2efe7; margin-top:8px;">
			<div style="font-size:13px; color:#9a9288;">Menampilkan 1 - {{ $products->count() }} dari {{ $products->count() }} produk</div>
			<div>
				<button type="button" class="btn-admin-icon" disabled><i class="zmdi zmdi-chevron-left"></i></button>
				<button type="button" class="btn-admin btn-admin-sm">1</button>
				<button type="button" class="btn-admin-icon"><i class="zmdi zmdi-chevron-right"></i></button>
			</div>
		</div>
	</div>

	<!-- Modal Tambah Produk -->
	<div class="modal fade" id="modalProduk" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content" style="border-radius:6px;">
				<div class="modal-header" style="border-bottom:1px solid #ece8de;">
					<h5 class="modal-title" style="font-weight:600;">Tambah Produk Baru</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body" style="padding:24px;">
					<form>
						<div class="row">
							<div class="col-md-8">
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">Nama Produk</label>
									<input type="text" class="form-control-admin" placeholder="Contoh: Batik Tulis Parang Klasik">
								</div>
								<div class="row">
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">SKU</label>
											<input type="text" class="form-control-admin" placeholder="BP-031">
										</div>
									</div>
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Kategori</label>
											<select class="form-control-admin">
												@foreach($categories as $cat)
													<option value="{{ $cat->id }}">{{ $cat->name }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Harga (Rp)</label>
											<input type="number" class="form-control-admin" placeholder="250000">
										</div>
									</div>
									<div class="col-md-6">
										<div style="margin-bottom:14px;">
											<label class="form-label-admin">Stok</label>
											<input type="number" class="form-control-admin" placeholder="0">
										</div>
									</div>
								</div>
								<div style="margin-bottom:14px;">
									<label class="form-label-admin">Deskripsi</label>
									<textarea rows="4" class="form-control-admin" placeholder="Deskripsi produk..."></textarea>
								</div>
							</div>
							<div class="col-md-4">
								<label class="form-label-admin">Gambar Produk</label>
								<div style="border:2px dashed #e0dbcf; border-radius:6px; padding:30px 10px; text-align:center; color:#9a9288;">
									<i class="zmdi zmdi-cloud-upload" style="font-size:36px; color:#c29e5c;"></i>
									<div style="font-size:13px; margin-top:8px;">Drag &amp; drop atau klik untuk upload</div>
									<div style="font-size:11px; margin-top:4px;">JPG, PNG max 2MB</div>
								</div>

								<div style="margin-top:14px;">
									<label class="form-label-admin">Status</label>
									<select class="form-control-admin">
										<option value="aktif">Aktif</option>
										<option value="arsip">Arsip</option>
									</select>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer" style="border-top:1px solid #ece8de;">
					<button type="button" class="btn-admin btn-admin-outline" data-dismiss="modal">Batal</button>
					<button type="button" class="btn-admin">Simpan Produk</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
<script src="{{ asset('frontend/vendor/bootstrap/js/popper.js') }}"></script>
<script src="{{ asset('frontend/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
@endpush
