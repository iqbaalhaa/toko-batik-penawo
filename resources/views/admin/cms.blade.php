@extends('layouts.admin')

@section('title', 'CMS')
@section('page_title', 'Content Management System')
@section('page_subtitle', 'Kelola konten halaman, banner, dan informasi toko')

@section('content')

	@if($errors->any())
		<div class="admin-card" style="background:#fbe4df; border-color:#f2c6be; color:#a5432f;">
			<strong>Mohon periksa kembali:</strong>
			<ul style="margin:6px 0 0 18px;">
				@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
			</ul>
		</div>
	@endif

	<!-- Tabs -->
	<div class="admin-card" style="padding:0;">
		<div style="border-bottom:1px solid #ece8de; padding:0 24px;">
			<ul style="list-style:none; margin:0; padding:0; display:flex; gap:4px; flex-wrap:wrap;">
				<li><a href="#tab-banner" class="cms-tab active" data-tab="banner"><i class="fa fa-picture-o"></i> Banner Beranda</a></li>
				<li><a href="#tab-tentang" class="cms-tab" data-tab="tentang"><i class="fa fa-info-circle"></i> Tentang Kami</a></li>
				<li><a href="#tab-kontak" class="cms-tab" data-tab="kontak"><i class="fa fa-envelope-o"></i> Info Kontak</a></li>
				<li><a href="#tab-pengiriman" class="cms-tab" data-tab="pengiriman"><i class="fa fa-truck"></i> Pengiriman</a></li>
				<li><a href="#tab-kategori" class="cms-tab" data-tab="kategori"><i class="fa fa-folder-open-o"></i> Kategori</a></li>
				<li><a href="#tab-footer" class="cms-tab" data-tab="footer"><i class="fa fa-align-justify"></i> Footer</a></li>
			</ul>
		</div>
	</div>

	<!-- ====================== Tab: Banner ====================== -->
	<div class="cms-panel" id="tab-banner">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Banner Beranda</h3>
					<div class="admin-card-sub">Slide yang ditampilkan di slider halaman depan</div>
				</div>
				<button type="button" class="btn-admin" onclick="openBannerModal()"><i class="fa fa-plus"></i> Tambah Banner</button>
			</div>

			<table class="admin-table">
				<thead>
					<tr>
						<th style="width:110px;">Gambar</th>
						<th>Judul</th>
						<th>Sub Judul</th>
						<th style="width:90px;">Urutan</th>
						<th style="width:100px;">Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@forelse($banners as $b)
					<tr>
						<td><img src="{{ $b->image_url }}" alt="{{ $b->title }}" style="width:90px; height:50px; object-fit:cover; border-radius:4px;"></td>
						<td style="font-weight:500;">{{ $b->title }}</td>
						<td style="color:#6c665e;">{{ $b->subtitle }}</td>
						<td>{{ $b->sort_order }}</td>
						<td>
							@if($b->is_active)
								<span class="badge-pill badge-success">Aktif</span>
							@else
								<span class="badge-pill badge-muted">Nonaktif</span>
							@endif
						</td>
						<td>
							<button type="button" class="btn-admin-icon" title="Edit"
								onclick='openBannerModal(@json($b))'><i class="fa fa-pencil-square-o"></i></button>
							<form action="{{ route('admin.cms.banner.destroy', $b) }}" method="POST" style="display:inline;"
								data-confirm-title="Hapus banner?"
								data-confirm-message='Banner "{{ $b->title }}" akan dihapus dari slider beranda.'
								data-confirm-ok="Hapus Banner">
								@csrf @method('DELETE')
								<button type="submit" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></button>
							</form>
						</td>
					</tr>
					@empty
					<tr><td colspan="6" style="text-align:center; padding:18px; color:#9a9288;">Belum ada banner. Klik "Tambah Banner" untuk membuat slide pertama.</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

	<!-- ====================== Tab: Tentang ====================== -->
	<div class="cms-panel" id="tab-tentang" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Halaman Tentang Kami</h3>
					<div class="admin-card-sub">Konten yang tampil di halaman /tentang</div>
				</div>
			</div>
			<form action="{{ route('admin.cms.settings.save', 'tentang') }}" method="POST">
				@csrf
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Judul Utama</label>
					<input type="text" name="about_title" class="form-control-admin" value="{{ $setting('about_title', 'Cerita Batik Penawo') }}">
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Sub Judul</label>
					<input type="text" name="about_subtitle" class="form-control-admin" value="{{ $setting('about_subtitle', 'Dari pengrajin lokal untuk gaya nusantara modern') }}">
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Cerita Kami</label>
					<textarea name="about_story" rows="6" class="form-control-admin">{{ $setting('about_story', 'Batik Penawo lahir dari kecintaan pada warisan budaya Nusantara. Kami memulai perjalanan sebagai usaha keluarga di Kerinci, merangkul para pengrajin batik lokal untuk menghadirkan kain-kain bermotif klasik maupun kontemporer.') }}</textarea>
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Misi Kami</label>
					<textarea name="about_mission" rows="5" class="form-control-admin">{{ $setting('about_mission', 'Misi kami sederhana: melestarikan batik Indonesia dan memberdayakan para pengrajin lokal.') }}</textarea>
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Kutipan</label>
					<textarea name="about_quote" rows="3" class="form-control-admin">{{ $setting('about_quote', 'Batik bukan hanya pakaian, tetapi bahasa budaya yang menuturkan siapa kita.') }}</textarea>
				</div>
				<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:8px;">
					<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Perubahan</button>
					<a href="{{ route('tentang') }}" target="_blank" class="btn-admin btn-admin-outline">Pratinjau</a>
				</div>
			</form>
		</div>
	</div>

	<!-- ====================== Tab: Kontak ====================== -->
	<div class="cms-panel" id="tab-kontak" style="display:none;">
		<form action="{{ route('admin.cms.settings.save', 'kontak') }}" method="POST">
			@csrf
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
							<input type="text" name="store_name" class="form-control-admin" value="{{ $setting('store_name', 'Batik Penawo') }}">
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin">Email</label>
							<input type="email" name="contact_email" class="form-control-admin" value="{{ $setting('contact_email', 'halo@batikpenawo.id') }}">
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin">No. Telepon / WhatsApp</label>
							<input type="text" name="contact_phone" class="form-control-admin" value="{{ $setting('contact_phone', '+62 812-3456-7890') }}">
						</div>
					</div>
					<div class="col-md-6">
						<div style="margin-bottom:14px;">
							<label class="form-label-admin">Alamat</label>
							<textarea name="contact_address" rows="3" class="form-control-admin">{{ $setting('contact_address', 'Jl. Malioboro No. 123, Kerinci, Jambi 55213') }}</textarea>
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin">Jam Operasional</label>
							<input type="text" name="contact_hours" class="form-control-admin" value="{{ $setting('contact_hours', 'Senin - Sabtu, 09.00 - 20.00 WIB') }}">
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin">Google Maps (embed URL, opsional)</label>
							<input type="text" name="contact_maps_embed" class="form-control-admin" value="{{ $setting('contact_maps_embed', '') }}" placeholder="https://maps.google.com/...">
						</div>
					</div>
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
							<input type="text" name="social_facebook" class="form-control-admin" value="{{ $setting('social_facebook', '') }}">
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin"><i class="fa fa-instagram" style="color:#c32aa3;"></i> Instagram</label>
							<input type="text" name="social_instagram" class="form-control-admin" value="{{ $setting('social_instagram', '') }}">
						</div>
					</div>
					<div class="col-md-6">
						<div style="margin-bottom:14px;">
							<label class="form-label-admin"><i class="fa fa-pinterest-p" style="color:#bd081c;"></i> Pinterest</label>
							<input type="text" name="social_pinterest" class="form-control-admin" value="{{ $setting('social_pinterest', '') }}">
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin"><i class="fa fa-youtube-play" style="color:#ff0000;"></i> YouTube</label>
							<input type="text" name="social_youtube" class="form-control-admin" value="{{ $setting('social_youtube', '') }}" placeholder="https://youtube.com/@batikpenawo">
						</div>
					</div>
				</div>
				<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:8px;">
					<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Perubahan</button>
				</div>
			</div>
		</form>
	</div>

	<!-- ====================== Tab: Pengiriman (Alamat Toko untuk ongkir) ====================== -->
	<div class="cms-panel" id="tab-pengiriman" style="display:none;">
		<form action="{{ route('admin.cms.settings.save', 'pengiriman') }}" method="POST">
			@csrf
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title"><i class="fa fa-map-marker" style="color:#c29e5c;"></i> Alamat Toko</h3>
						<div class="admin-card-sub">Pilih wilayah berurutan: provinsi → kota/kabupaten → kecamatan. Dipakai kalkulator ongkir untuk menentukan zona pengiriman.</div>
					</div>
				</div>

				<div class="row" data-wilayah-form
					data-init-province="{{ $setting('store_province_id', '') }}"
					data-init-city="{{ $setting('store_city_id', '') }}"
					data-init-district="{{ $setting('store_district_id', '') }}">
					<div class="col-md-4" style="margin-bottom:14px;">
						<label class="form-label-admin">Provinsi <span style="color:#a5432f;">*</span></label>
						<select name="store_province_id" class="form-control-admin" data-role="province" required>
							<option value="">— Pilih provinsi —</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-bottom:14px;">
						<label class="form-label-admin">Kota / Kabupaten <span style="color:#a5432f;">*</span></label>
						<select name="store_city_id" class="form-control-admin" data-role="regency" required disabled>
							<option value="">— Pilih kota/kabupaten —</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-bottom:14px;">
						<label class="form-label-admin">Kecamatan <span style="color:#a5432f;">*</span></label>
						<select name="store_district_id" class="form-control-admin" data-role="district" required disabled>
							<option value="">— Pilih kecamatan —</option>
						</select>
					</div>
					<div class="col-md-12" style="margin-bottom:14px;">
						<label class="form-label-admin">Alamat Lengkap Toko</label>
						<textarea name="store_full_address" rows="3" class="form-control-admin" placeholder="Jalan, RT/RW, kelurahan...">{{ $setting('store_full_address', '') }}</textarea>
					</div>

					{{-- Hidden: nama wilayah disimpan supaya tidak perlu join saat invoice --}}
					<input type="hidden" name="store_province_name" data-role="province_name" value="{{ $setting('store_province_name', '') }}">
					<input type="hidden" name="store_city_name"     data-role="city_name"     value="{{ $setting('store_city_name', '') }}">
					<input type="hidden" name="store_district_name" data-role="district_name" value="{{ $setting('store_district_name', '') }}">
				</div>

				<div style="font-size:12px; color:#9a9288; padding:10px 12px; background:#faf7ef; border-radius:4px;">
					<i class="fa fa-info-circle"></i> Wilayah toko harus terisi lengkap supaya ongkir bisa dihitung. Zona pembeli ditentukan dari kesamaan provinsi/kabupaten/kecamatan dengan alamat toko ini.
				</div>
			</div>

			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Tarif Ongkos Kirim per Zona</h3>
						<div class="admin-card-sub">Ubah tarif di sini — perubahan langsung dipakai saat checkout berikutnya. Zona ditentukan otomatis dari perbandingan provinsi/kabupaten/kecamatan.</div>
					</div>
				</div>

				@php
					$zoneCfg     = \App\Services\ShippingCalculator::zones();
					$baseWeight  = \App\Services\ShippingCalculator::baseWeightKg();
					$defaultZone = \App\Services\ShippingCalculator::DEFAULT_ZONES;
				@endphp

				<div style="margin-bottom:18px;">
					<label class="form-label-admin">Berat Dasar (kg) <span style="color:#a5432f;">*</span></label>
					<input type="number" name="shipping_base_weight_kg" class="form-control-admin" min="1" max="50" required
						value="{{ $setting('shipping_base_weight_kg', $baseWeight) }}" style="max-width:200px;">
					<div style="font-size:11.5px; color:#9a9288; margin-top:4px;">
						Berat di bawah / sama dengan ini hanya dikenai tarif dasar. Lebih dari ini, kelebihannya dikalikan tarif tambahan.
					</div>
				</div>

				<table class="admin-table" style="margin-top:0;">
					<thead>
						<tr>
							<th style="width:30%;">Zona</th>
							<th>Tarif Dasar (Rp)</th>
							<th>Tarif Tambahan / kg (Rp)</th>
						</tr>
					</thead>
					<tbody>
						@foreach($zoneCfg as $zoneKey => $cfg)
						<tr>
							<td><strong>{{ $cfg['label'] }}</strong></td>
							<td>
								<input type="number" name="shipping_{{ $zoneKey }}_base_fee" class="form-control-admin"
									min="0" step="500" required
									value="{{ $setting('shipping_'.$zoneKey.'_base_fee', $cfg['base_fee']) }}"
									style="max-width:180px;">
								<div style="font-size:10.5px; color:#9a9288; margin-top:2px;">default: {{ $rupiah($defaultZone[$zoneKey]['base_fee']) }}</div>
							</td>
							<td>
								<input type="number" name="shipping_{{ $zoneKey }}_extra_fee" class="form-control-admin"
									min="0" step="500" required
									value="{{ $setting('shipping_'.$zoneKey.'_extra_fee', $cfg['extra_fee_per_kg']) }}"
									style="max-width:180px;">
								<div style="font-size:10.5px; color:#9a9288; margin-top:2px;">default: {{ $rupiah($defaultZone[$zoneKey]['extra_fee_per_kg']) }}</div>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>

				<div style="font-size:12px; color:#9a9288; padding:10px 12px; background:#faf7ef; border-radius:4px; margin-top:14px;">
					<i class="fa fa-info-circle"></i> Rumus: <code>shipping_cost = base_fee + max(0, total_weight_kg - base_weight) × extra_fee</code>. Total berat dibulatkan ke atas (ceil) sebelum dihitung.
				</div>

				<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:14px;">
					<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Pengaturan Pengiriman</button>
				</div>
			</div>
		</form>
	</div>

	<!-- ====================== Tab: Kategori ====================== -->
	<div class="cms-panel" id="tab-kategori" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Kategori Produk</h3>
					<div class="admin-card-sub">Kelompok produk yang ditampilkan di filter</div>
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
							<form action="{{ route('admin.cms.kategori.destroy', $c) }}" method="POST" style="display:inline;"
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
	</div>

	<!-- ====================== Tab: Footer ====================== -->
	<div class="cms-panel" id="tab-footer" style="display:none;">
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Pengaturan Footer</h3>
					<div class="admin-card-sub">Teks yang tampil di footer dan topbar</div>
				</div>
			</div>
			<form action="{{ route('admin.cms.settings.save', 'footer') }}" method="POST">
				@csrf
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Teks Hak Cipta</label>
					<input type="text" name="footer_copyright" class="form-control-admin" value="{{ $setting('footer_copyright', 'Hak Cipta © 2026 Batik Penawo. Semua hak dilindungi.') }}">
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Teks Newsletter</label>
					<input type="text" name="footer_newsletter_text" class="form-control-admin" value="{{ $setting('footer_newsletter_text', 'Berlangganan newsletter untuk penawaran spesial') }}">
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Teks Promo Topbar (opsional)</label>
					<input type="text" name="footer_topbar_promo" class="form-control-admin" value="{{ $setting('footer_topbar_promo', '') }}" placeholder="Mis. Diskon spesial untuk pembelian di atas Rp500.000">
				</div>
				<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:8px;">
					<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Perubahan</button>
				</div>
			</form>
		</div>
	</div>

	<!-- ====================== Banner Modal ====================== -->
	<div class="cms-modal-overlay" id="bannerModal" style="display:none;">
		<div class="cms-modal">
			<div class="cms-modal-head">
				<h4 id="bannerModalTitle">Tambah Banner</h4>
				<button type="button" class="cms-modal-close" onclick="closeBannerModal()"><i class="fa fa-times"></i></button>
			</div>
			<form id="bannerForm" action="{{ route('admin.cms.banner.store') }}" method="POST" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="_method" id="bannerMethod" value="POST">
				<div class="row">
					<div class="col-md-7">
						<div style="margin-bottom:12px;">
							<label class="form-label-admin">Judul <span style="color:#a5432f;">*</span></label>
							<input type="text" name="title" id="bannerTitle" class="form-control-admin" required>
						</div>
						<div style="margin-bottom:12px;">
							<label class="form-label-admin">Sub Judul</label>
							<input type="text" name="subtitle" id="bannerSubtitle" class="form-control-admin">
						</div>
						<div style="margin-bottom:12px;">
							<label class="form-label-admin">Link Tombol (URL)</label>
							<input type="text" name="link" id="bannerLink" class="form-control-admin" placeholder="/produk">
						</div>
						<div class="row">
							<div class="col-md-7" style="margin-bottom:12px;">
								<label class="form-label-admin">Teks Tombol</label>
								<input type="text" name="cta_text" id="bannerCta" class="form-control-admin" value="Belanja Sekarang">
							</div>
							<div class="col-md-5" style="margin-bottom:12px;">
								<label class="form-label-admin">Urutan</label>
								<input type="number" name="sort_order" id="bannerSort" class="form-control-admin" value="0" min="0">
							</div>
						</div>
						<div style="margin-bottom:6px;">
							<label class="form-label-admin">
								<input type="checkbox" name="is_active" id="bannerActive" value="1" checked>
								Tampilkan di slider beranda
							</label>
						</div>
					</div>
					<div class="col-md-5">
						<label class="form-label-admin">Gambar <span id="bannerImageReq" style="color:#a5432f;">*</span></label>
						<div class="banner-dropzone" onclick="document.getElementById('bannerImage').click()">
							<img id="bannerPreview" src="" alt="" style="display:none; max-width:100%; max-height:180px; border-radius:4px;">
							<div id="bannerPlaceholder">
								<i class="fa fa-cloud-upload" style="font-size:32px; color:#c29e5c;"></i>
								<div style="font-size:12.5px; margin-top:6px;">Klik untuk pilih gambar (PNG transparan disarankan)</div>
							</div>
						</div>
						<input type="file" name="image" id="bannerImage" accept="image/jpeg,image/png,image/webp" style="display:none;"
							onchange="previewBannerImage(this)">
						<small style="color:#9a9288; font-size:11.5px;">JPG/PNG/WebP, maks 3 MB.</small>

						<div style="margin-top:12px;">
							<label class="form-label-admin">Tinggi Maks Gambar (px)</label>
							<input type="number" name="image_max_height" id="bannerImageMaxHeight" class="form-control-admin" value="480" min="120" max="1200" step="10">
							<small style="color:#9a9288; font-size:11.5px;">Atur kalau gambar terlalu panjang/pendek di tampilan beranda. Default 480.</small>
						</div>
					</div>
				</div>
				<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:14px; text-align:right;">
					<button type="button" class="btn-admin btn-admin-outline" onclick="closeBannerModal()">Batal</button>
					<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan</button>
				</div>
			</form>
		</div>
	</div>

	<!-- ====================== Category Modal ====================== -->
	<div class="cms-modal-overlay" id="categoryModal" style="display:none;">
		<div class="cms-modal" style="max-width:480px;">
			<div class="cms-modal-head">
				<h4 id="categoryModalTitle">Tambah Kategori</h4>
				<button type="button" class="cms-modal-close" onclick="closeCategoryModal()"><i class="fa fa-times"></i></button>
			</div>
			<form id="categoryForm" action="{{ route('admin.cms.kategori.store') }}" method="POST">
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

	/* Modal */
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

	.banner-dropzone {
		border: 2px dashed #d8c998; background: #fff8e7;
		border-radius: 6px; padding: 20px; text-align: center; cursor: pointer;
		min-height: 180px; display: flex; align-items: center; justify-content: center;
		transition: border-color .15s, background .15s;
	}
	.banner-dropzone:hover { border-color: #c29e5c; background: #faf0d7; }
</style>
@endpush

@push('scripts')
@include('partials._wilayah_cascade')
<script>
	$(function() {
		// Tabs (with hash support)
		function activateTab(tab) {
			$('.cms-tab').removeClass('active');
			$('.cms-tab[data-tab="' + tab + '"]').addClass('active');
			$('.cms-panel').hide();
			$('#tab-' + tab).show();
		}
		$('.cms-tab').on('click', function(e) {
			e.preventDefault();
			activateTab($(this).data('tab'));
			history.replaceState(null, '', '#tab-' + $(this).data('tab'));
		});
		// Open tab from URL hash (mis. setelah submit form redirect)
		var hash = window.location.hash;
		if (hash && hash.indexOf('#tab-') === 0) {
			activateTab(hash.replace('#tab-', ''));
		}
	});

	// ----- Banner modal -----
	function openBannerModal(b) {
		var form = document.getElementById('bannerForm');
		var title = document.getElementById('bannerModalTitle');
		var imgReq = document.getElementById('bannerImageReq');
		var preview = document.getElementById('bannerPreview');
		var ph = document.getElementById('bannerPlaceholder');

		if (b) {
			title.textContent = 'Edit Banner';
			form.action = '{{ url('admin/cms/banner') }}/' + b.id;
			document.getElementById('bannerMethod').value = 'PUT';
			document.getElementById('bannerTitle').value = b.title || '';
			document.getElementById('bannerSubtitle').value = b.subtitle || '';
			document.getElementById('bannerLink').value = b.link || '';
			document.getElementById('bannerCta').value = b.cta_text || 'Belanja Sekarang';
			document.getElementById('bannerSort').value = b.sort_order || 0;
			document.getElementById('bannerImageMaxHeight').value = b.image_max_height || 480;
			document.getElementById('bannerActive').checked = !!b.is_active;
			document.getElementById('bannerImage').required = false;
			imgReq.style.display = 'none';
			if (b.image) {
				preview.src = '{{ asset('') }}' + b.image;
				preview.style.display = 'block';
				ph.style.display = 'none';
			} else {
				preview.style.display = 'none';
				ph.style.display = 'block';
			}
		} else {
			title.textContent = 'Tambah Banner';
			form.action = '{{ route('admin.cms.banner.store') }}';
			document.getElementById('bannerMethod').value = 'POST';
			form.reset();
			document.getElementById('bannerImageMaxHeight').value = 480;
			document.getElementById('bannerImage').required = true;
			imgReq.style.display = 'inline';
			preview.style.display = 'none';
			ph.style.display = 'block';
		}
		document.getElementById('bannerModal').style.display = 'flex';
	}
	function closeBannerModal() { document.getElementById('bannerModal').style.display = 'none'; }
	function previewBannerImage(input) {
		var preview = document.getElementById('bannerPreview');
		var ph = document.getElementById('bannerPlaceholder');
		if (!input.files || !input.files[0]) return;
		var reader = new FileReader();
		reader.onload = function(e){ preview.src = e.target.result; preview.style.display = 'block'; ph.style.display = 'none'; };
		reader.readAsDataURL(input.files[0]);
	}

	// ----- Category modal -----
	function openCategoryModal(c) {
		var form = document.getElementById('categoryForm');
		var title = document.getElementById('categoryModalTitle');
		if (c) {
			title.textContent = 'Edit Kategori';
			form.action = '{{ url('admin/cms/kategori') }}/' + c.id;
			document.getElementById('categoryMethod').value = 'PUT';
			document.getElementById('categoryName').value = c.name || '';
			document.getElementById('categorySort').value = c.sort_order || 0;
		} else {
			title.textContent = 'Tambah Kategori';
			form.action = '{{ route('admin.cms.kategori.store') }}';
			document.getElementById('categoryMethod').value = 'POST';
			form.reset();
			document.getElementById('categorySort').value = 0;
		}
		document.getElementById('categoryModal').style.display = 'flex';
	}
	function closeCategoryModal() { document.getElementById('categoryModal').style.display = 'none'; }
</script>
@endpush
