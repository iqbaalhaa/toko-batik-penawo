@extends('layouts.admin')

@section('title', 'Kategori')
@section('page_title', 'Kategori Produk')
@section('page_subtitle', 'Kelompok produk yang ditampilkan di filter katalog')

@section('content')
	@if($errors->any())
		<div class="flash" style="background:#fbe4df; border-color:#f2c6be; color:#a5432f;">
			<i class="fa fa-exclamation-circle"></i> {{ $errors->first() }}
		</div>
	@endif

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Daftar Kategori</h3>
				<div class="admin-card-sub">Kategori dipakai sebagai filter di beranda &amp; halaman produk</div>
			</div>
			<button type="button" class="btn-admin" onclick="openCategoryModal()"><i class="fa fa-plus"></i> Tambah Kategori</button>
		</div>
		<table class="admin-table">
			<thead>
				<tr>
					<th>Nama Kategori</th>
					<th>Slug</th>
					<th>Jumlah Produk</th>
					<th>Urutan</th>
					<th style="width:140px;">Aksi</th>
				</tr>
			</thead>
			<tbody>
				@forelse($categories as $c)
				<tr>
					<td style="font-weight:500;">{{ $c->name }}</td>
					<td><code style="background:#f5f2ea; padding:2px 6px; border-radius:3px; font-size:12px;">{{ $c->slug }}</code></td>
					<td>{{ $c->products_count }} produk</td>
					<td>{{ $c->sort_order }}</td>
					<td>
						<button type="button" class="btn-admin-icon" title="Edit"
							onclick='openCategoryModal(@json($c))'><i class="fa fa-pencil-square-o"></i></button>
						<form action="{{ route('admin.kategori.destroy', $c) }}" method="POST" style="display:inline;"
							data-confirm-title="Hapus kategori?"
							data-confirm-message='Kategori "{{ $c->name }}" akan dihapus. Hanya bisa dihapus jika belum dipakai produk.'
							data-confirm-ok="Hapus Kategori">
							@csrf @method('DELETE')
							<button type="submit" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></button>
						</form>
					</td>
				</tr>
				@empty
				<tr><td colspan="5" style="text-align:center; padding:16px; color:#9a9288;">Belum ada kategori</td></tr>
				@endforelse
			</tbody>
		</table>
	</div>

	<!-- Category Modal -->
	<div class="cms-modal-overlay" id="categoryModal" style="display:none;">
		<div class="cms-modal" style="max-width:480px;">
			<div class="cms-modal-head">
				<h4 id="categoryModalTitle">Tambah Kategori</h4>
				<button type="button" class="cms-modal-close" onclick="closeCategoryModal()"><i class="fa fa-times"></i></button>
			</div>
			<form id="categoryForm" action="{{ route('admin.kategori.store') }}" method="POST">
				@csrf
				<input type="hidden" name="_method" id="categoryMethod" value="POST">
				<div style="margin-bottom:12px;">
					<label class="form-label-admin">Nama Kategori <span style="color:#a5432f;">*</span></label>
					<input type="text" name="name" id="categoryName" class="form-control-admin" required>
				</div>
				<div style="margin-bottom:6px;">
					<label class="form-label-admin">Urutan</label>
					<input type="number" name="sort_order" id="categorySort" class="form-control-admin" value="0" min="0">
				</div>
				<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:14px; text-align:right;">
					<button type="button" class="btn-admin btn-admin-outline" onclick="closeCategoryModal()">Batal</button>
					<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan</button>
				</div>
			</form>
		</div>
	</div>
@endsection

@push('styles')
<style>
	.cms-modal-overlay {
		position: fixed; inset: 0; background: rgba(0,0,0,.45);
		z-index: 1200; display: flex; align-items: flex-start; justify-content: center;
		padding: 60px 20px; overflow-y: auto;
	}
	.cms-modal {
		background: #fff; border-radius: 8px; width: 100%; max-width: 720px;
		padding: 22px 24px; box-shadow: 0 20px 60px rgba(0,0,0,.25);
	}
	.cms-modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f2efe7; }
	.cms-modal-head h4 { margin: 0; font-size: 16px; color: #2d2a26; font-weight: 600; }
	.cms-modal-close { background: none; border: 0; font-size: 18px; color: #9a9288; cursor: pointer; }
	.cms-modal-close:hover { color: #2d2a26; }
</style>
@endpush

@push('scripts')
<script>
	function openCategoryModal(c) {
		var form = document.getElementById('categoryForm');
		var title = document.getElementById('categoryModalTitle');
		if (c) {
			title.textContent = 'Edit Kategori';
			form.action = '{{ url('admin/kategori') }}/' + c.id;
			document.getElementById('categoryMethod').value = 'PUT';
			document.getElementById('categoryName').value = c.name || '';
			document.getElementById('categorySort').value = c.sort_order || 0;
		} else {
			title.textContent = 'Tambah Kategori';
			form.action = '{{ route('admin.kategori.store') }}';
			document.getElementById('categoryMethod').value = 'POST';
			form.reset();
			document.getElementById('categorySort').value = 0;
		}
		document.getElementById('categoryModal').style.display = 'flex';
	}
	function closeCategoryModal() { document.getElementById('categoryModal').style.display = 'none'; }
</script>
@endpush
