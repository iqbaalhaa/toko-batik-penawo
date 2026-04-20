@extends('layouts.admin')

@section('title', 'CMS')
@section('page_title', 'Content Management System')
@section('page_subtitle', 'Kelola konten halaman, banner, dan informasi toko')

@section('content')

	<!-- Tabs -->
	<div class="admin-card" style="padding:0;">
		<div style="border-bottom:1px solid #ece8de; padding:0 24px;">
			<ul style="list-style:none; margin:0; padding:0; display:flex; gap:4px; flex-wrap:wrap;">
				<li><a href="#tab-banner" class="cms-tab active" data-tab="banner"><i class="fa fa-picture-o"></i> Banner Beranda</a></li>
				<li><a href="#tab-pages" class="cms-tab" data-tab="pages"><i class="fa fa-file-text-o"></i> Halaman</a></li>
				<li><a href="#tab-tentang" class="cms-tab" data-tab="tentang"><i class="fa fa-info-circle"></i> Tentang Kami</a></li>
				<li><a href="#tab-kontak" class="cms-tab" data-tab="kontak"><i class="fa fa-envelope-o"></i> Info Kontak</a></li>
				<li><a href="#tab-kategori" class="cms-tab" data-tab="kategori"><i class="fa fa-folder-open-o"></i> Kategori</a></li>
				<li><a href="#tab-footer" class="cms-tab" data-tab="footer"><i class="fa fa-align-justify"></i> Footer</a></li>
			</ul>
		</div>
	</div>

	<!-- Tab: Banner -->
	<div class="cms-panel" id="tab-banner">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Banner Beranda</h3>
					<div class="admin-card-sub">Slide yang ditampilkan di halaman depan</div>
				</div>
				<button type="button" class="btn-admin"><i class="fa fa-plus"></i> Tambah Banner</button>
			</div>

			@php
				$banners = [
					['title' => 'Koleksi Batik Wanita 2026', 'sub' => 'Motif klasik dengan sentuhan modern', 'img' => 'slide-01.jpg', 'active' => true],
					['title' => 'Batik Pria Premium',         'sub' => 'Untuk setiap momen penting Anda', 'img' => 'slide-02.jpg', 'active' => true],
					['title' => 'Aksesoris &amp; Scarf',      'sub' => 'Lengkapi gaya nusantara Anda',   'img' => 'slide-03.jpg', 'active' => false],
				];
			@endphp

			<table class="admin-table">
				<thead>
					<tr>
						<th>Gambar</th>
						<th>Judul</th>
						<th>Sub Judul</th>
						<th>Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@foreach($banners as $b)
					<tr>
						<td><img src="{{ asset('frontend/images/'.$b['img']) }}" alt="{{ $b['title'] }}" style="width:80px; height:44px; object-fit:cover; border-radius:4px;" onerror="this.style.background='#f2efe7'"></td>
						<td style="font-weight:500;">{!! $b['title'] !!}</td>
						<td style="color:#6c665e;">{!! $b['sub'] !!}</td>
						<td>
							@if($b['active'])
								<span class="badge-pill badge-success">Aktif</span>
							@else
								<span class="badge-pill badge-muted">Nonaktif</span>
							@endif
						</td>
						<td>
							<a href="#" class="btn-admin-icon" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
							<a href="#" class="btn-admin-icon" title="Atur Urutan"><i class="fa fa-sort"></i></a>
							<a href="#" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></a>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	<!-- Tab: Halaman -->
	<div class="cms-panel" id="tab-pages" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Halaman Statis</h3>
					<div class="admin-card-sub">Kelola konten halaman toko</div>
				</div>
				<button type="button" class="btn-admin"><i class="fa fa-plus"></i> Tambah Halaman</button>
			</div>
			<table class="admin-table">
				<thead>
					<tr>
						<th>Judul Halaman</th>
						<th>URL</th>
						<th>Terakhir Diperbarui</th>
						<th>Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@php
						$pages = [
							['title' => 'Tentang Kami',        'url' => '/tentang',          'updated' => '12 Apr 2026', 'status' => 'Publish'],
							['title' => 'Kontak',              'url' => '/kontak',           'updated' => '08 Apr 2026', 'status' => 'Publish'],
							['title' => 'FAQ',                 'url' => '/faq',              'updated' => '02 Apr 2026', 'status' => 'Publish'],
							['title' => 'Kebijakan Privasi',   'url' => '/kebijakan-privasi','updated' => '15 Mar 2026', 'status' => 'Publish'],
							['title' => 'Syarat &amp; Ketentuan','url' => '/syarat-ketentuan','updated' => '15 Mar 2026', 'status' => 'Publish'],
							['title' => 'Panduan Ukuran',      'url' => '/panduan-ukuran',   'updated' => '—',           'status' => 'Draft'],
						];
					@endphp
					@foreach($pages as $page)
					<tr>
						<td style="font-weight:500;">{!! $page['title'] !!}</td>
						<td><code style="background:#f5f2ea; padding:2px 6px; border-radius:3px; font-size:12px;">{{ $page['url'] }}</code></td>
						<td>{{ $page['updated'] }}</td>
						<td>
							@if($page['status'] === 'Publish')
								<span class="badge-pill badge-success">Publish</span>
							@else
								<span class="badge-pill badge-warning">Draft</span>
							@endif
						</td>
						<td>
							<a href="#" class="btn-admin-icon" title="Lihat"><i class="fa fa-eye"></i></a>
							<a href="#" class="btn-admin-icon" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
							<a href="#" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></a>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	<!-- Tab: Tentang -->
	<div class="cms-panel" id="tab-tentang" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Halaman Tentang Kami</h3>
					<div class="admin-card-sub">Edit konten yang tampil di halaman /tentang</div>
				</div>
			</div>

			<div style="margin-bottom:14px;">
				<label class="form-label-admin">Judul Utama</label>
				<input type="text" class="form-control-admin" value="Cerita Batik Penawo">
			</div>
			<div style="margin-bottom:14px;">
				<label class="form-label-admin">Sub Judul</label>
				<input type="text" class="form-control-admin" value="Dari pengrajin lokal untuk gaya nusantara modern">
			</div>
			<div style="margin-bottom:14px;">
				<label class="form-label-admin">Konten</label>
				<textarea rows="8" class="form-control-admin">Batik Penawo didirikan dengan misi mengangkat kerajinan batik asli Indonesia. Kami bekerja sama langsung dengan para pengrajin di Yogyakarta, Solo, Pekalongan, dan Cirebon untuk memastikan setiap produk yang sampai kepada Anda memiliki nilai seni, kualitas, dan keaslian yang terjaga.</textarea>
			</div>
			<div style="margin-bottom:14px;">
				<label class="form-label-admin">Gambar Sampul</label>
				<div style="border:2px dashed #e0dbcf; border-radius:6px; padding:24px; text-align:center; color:#9a9288;">
					<i class="fa fa-cloud-upload" style="font-size:32px; color:#c29e5c;"></i>
					<div style="font-size:13px; margin-top:6px;">Upload gambar sampul</div>
				</div>
			</div>
			<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:8px;">
				<button type="button" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Perubahan</button>
				<button type="button" class="btn-admin btn-admin-outline">Pratinjau</button>
			</div>
		</div>
	</div>

	<!-- Tab: Kontak -->
	<div class="cms-panel" id="tab-kontak" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Informasi Kontak</h3>
					<div class="admin-card-sub">Detail yang tampil di halaman kontak dan footer</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div style="margin-bottom:14px;">
						<label class="form-label-admin">Nama Toko</label>
						<input type="text" class="form-control-admin" value="Batik Penawo">
					</div>
					<div style="margin-bottom:14px;">
						<label class="form-label-admin">Email</label>
						<input type="email" class="form-control-admin" value="halo@penawo.id">
					</div>
					<div style="margin-bottom:14px;">
						<label class="form-label-admin">No. Telepon / WhatsApp</label>
						<input type="text" class="form-control-admin" value="+62 812-3456-7890">
					</div>
				</div>
				<div class="col-md-6">
					<div style="margin-bottom:14px;">
						<label class="form-label-admin">Alamat</label>
						<textarea rows="3" class="form-control-admin">Jl. Malioboro No. 123, Yogyakarta, DIY 55213</textarea>
					</div>
					<div style="margin-bottom:14px;">
						<label class="form-label-admin">Jam Operasional</label>
						<input type="text" class="form-control-admin" value="Senin - Sabtu, 09.00 - 20.00 WIB">
					</div>
					<div style="margin-bottom:14px;">
						<label class="form-label-admin">Google Maps (embed URL)</label>
						<input type="text" class="form-control-admin" value="https://maps.google.com/...">
					</div>
				</div>
			</div>
			<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:8px;">
				<button type="button" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Perubahan</button>
			</div>
		</div>

		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Media Sosial</h3>
					<div class="admin-card-sub">Link yang tampil di footer dan halaman kontak</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div style="margin-bottom:14px;">
						<label class="form-label-admin"><i class="fa fa-facebook" style="color:#3b5998;"></i> Facebook</label>
						<input type="text" class="form-control-admin" value="https://facebook.com/batikpenawo">
					</div>
					<div style="margin-bottom:14px;">
						<label class="form-label-admin"><i class="fa fa-instagram" style="color:#c32aa3;"></i> Instagram</label>
						<input type="text" class="form-control-admin" value="https://instagram.com/batikpenawo">
					</div>
				</div>
				<div class="col-md-6">
					<div style="margin-bottom:14px;">
						<label class="form-label-admin"><i class="fa fa-pinterest-p" style="color:#bd081c;"></i> Pinterest</label>
						<input type="text" class="form-control-admin" value="https://pinterest.com/batikpenawo">
					</div>
					<div style="margin-bottom:14px;">
						<label class="form-label-admin"><i class="fa fa-youtube-play" style="color:#ff0000;"></i> YouTube</label>
						<input type="text" class="form-control-admin" placeholder="https://youtube.com/@batikpenawo">
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Tab: Kategori -->
	<div class="cms-panel" id="tab-kategori" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Kategori Produk</h3>
					<div class="admin-card-sub">Kelompok produk yang ditampilkan di filter</div>
				</div>
				<button type="button" class="btn-admin"><i class="fa fa-plus"></i> Tambah Kategori</button>
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
							<a href="#" class="btn-admin-icon" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
							<a href="#" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></a>
						</td>
					</tr>
					@empty
					<tr><td colspan="5" style="text-align:center; padding:16px; color:#9a9288;">Belum ada kategori</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

	<!-- Tab: Footer -->
	<div class="cms-panel" id="tab-footer" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Pengaturan Footer</h3>
					<div class="admin-card-sub">Link dan teks yang tampil di footer</div>
				</div>
			</div>
			<div style="margin-bottom:14px;">
				<label class="form-label-admin">Teks Hak Cipta</label>
				<input type="text" class="form-control-admin" value="Hak Cipta © 2026 Batik Penawo. Semua hak dilindungi.">
			</div>
			<div style="margin-bottom:14px;">
				<label class="form-label-admin">Teks Newsletter</label>
				<input type="text" class="form-control-admin" value="Berlangganan newsletter untuk penawaran spesial">
			</div>
			<div style="margin-bottom:14px;">
				<label class="form-label-admin">Teks Promo Topbar</label>
				<input type="text" class="form-control-admin" value="Gratis ongkir untuk pembelian di atas Rp500.000">
			</div>
			<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:8px;">
				<button type="button" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Perubahan</button>
			</div>
		</div>
	</div>

@endsection

@push('styles')
<style>
	.cms-tab {
		display: inline-flex; align-items: center; gap: 8px;
		padding: 14px 18px;
		color: #6c665e;
		font-size: 13.5px;
		font-weight: 500;
		border-bottom: 2px solid transparent;
		transition: color .15s, border-color .15s;
	}
	.cms-tab:hover { color: #c29e5c; text-decoration: none; }
	.cms-tab.active { color: #c29e5c; border-bottom-color: #c29e5c; }
	.cms-tab i { font-size: 15px; }
	.cms-panel { margin-top: 22px; }
</style>
@endpush

@push('scripts')
<script>
	$(function() {
		$('.cms-tab').on('click', function(e) {
			e.preventDefault();
			var tab = $(this).data('tab');
			$('.cms-tab').removeClass('active');
			$(this).addClass('active');
			$('.cms-panel').hide();
			$('#tab-' + tab).show();
		});
	});
</script>
@endpush
