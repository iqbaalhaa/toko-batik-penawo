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
					<input type="text" name="about_title" class="form-control-admin" value="{{ $setting('about_title', 'Cerita Batik Penawuo') }}">
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Sub Judul</label>
					<input type="text" name="about_subtitle" class="form-control-admin" value="{{ $setting('about_subtitle', 'Dari pengrajin lokal untuk gaya nusantara modern') }}">
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Cerita Kami</label>
					<textarea name="about_story" rows="6" class="form-control-admin">{{ $setting('about_story', 'Batik Penawuo lahir dari kecintaan pada warisan budaya Nusantara. Kami memulai perjalanan sebagai usaha keluarga di Kerinci, merangkul para pengrajin batik lokal untuk menghadirkan kain-kain bermotif klasik maupun kontemporer.') }}</textarea>
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
							<input type="text" name="store_name" class="form-control-admin" value="{{ $setting('store_name', 'Batik Penawuo') }}">
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin">Email</label>
							<input type="email" name="contact_email" class="form-control-admin" value="{{ $setting('contact_email', 'halo@batikpenawuo.id') }}">
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
							<label class="form-label-admin"><i class="fa fa-music" style="color:#010101;"></i> TikTok</label>
							<input type="text" name="social_tiktok" class="form-control-admin" value="{{ $setting('social_tiktok', '') }}" placeholder="https://tiktok.com/@batikpenawuo">
						</div>
						<div style="margin-bottom:14px;">
							<label class="form-label-admin"><i class="fa fa-youtube-play" style="color:#ff0000;"></i> YouTube</label>
							<input type="text" name="social_youtube" class="form-control-admin" value="{{ $setting('social_youtube', '') }}" placeholder="https://youtube.com/@batikpenawuo">
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

			<div class="admin-card" style="background:#faf7ef; border-color:#e4d5aa;">
				<div style="display:flex; gap:12px; align-items:flex-start;">
					<i class="fa fa-bolt" style="color:#c29e5c; font-size:18px; margin-top:2px;"></i>
					<div style="font-size:13px; color:#6c665e; line-height:1.6;">
						<strong style="color:#8a6b2b;">Tarif ongkir dihitung otomatis oleh RajaOngkir.</strong><br>
						Alamat toko di atas dipakai sebagai titik asal pengiriman. Tarif & estimasi waktu kirim diambil real-time dari kurir (JNE/JNT/POS) berdasarkan jarak ke alamat pembeli, jadi tidak perlu mengatur zona/tarif manual lagi.
					</div>
				</div>
			</div>

			<div style="padding-top:14px;">
				<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Alamat Toko</button>
			</div>
		</form>
	</div>

	<!-- ====================== Tab: Footer ====================== -->
	<div class="cms-panel" id="tab-footer" style="display:none;">
		@php
			// Decode JSON list dari SiteSetting; jaga-jaga kalau format rusak.
			$decodeList = function (string $key, array $default) use ($setting) {
				$raw = $setting($key);
				if (! $raw) return $default;
				$data = json_decode($raw, true);
				return is_array($data) ? $data : $default;
			};
			$col1Links = $decodeList('footer_col1_links', [
				['label' => 'Batik Wanita', 'url' => '/produk?kategori=wanita'],
				['label' => 'Batik Pria',   'url' => '/produk?kategori=pria'],
				['label' => 'Batik Anak',   'url' => '/produk?kategori=anak'],
				['label' => 'Aksesoris',    'url' => '/produk?kategori=aksesoris'],
			]);
			$col2Links = $decodeList('footer_col2_links', [
				['label' => 'Lacak Pesanan',  'url' => '/akun/pesanan'],
				['label' => 'Pengembalian',   'url' => '/kontak'],
				['label' => 'Pengiriman',     'url' => '/kontak'],
				['label' => 'FAQ',            'url' => '/kontak'],
			]);
			$payIcons = $decodeList('footer_payment_icons', [
				['image_url' => 'frontend/images/icons/icon-pay-01.png', 'alt' => 'Visa'],
				['image_url' => 'frontend/images/icons/icon-pay-02.png', 'alt' => 'Mastercard'],
				['image_url' => 'frontend/images/icons/icon-pay-03.png', 'alt' => 'PayPal'],
				['image_url' => 'frontend/images/icons/icon-pay-04.png', 'alt' => 'Maestro'],
				['image_url' => 'frontend/images/icons/icon-pay-05.png', 'alt' => 'Discover'],
			]);
			$showPayments = $setting('footer_show_payments', '1') === '1';
		@endphp

		<form action="{{ route('admin.cms.settings.save', 'footer') }}" method="POST" id="footerForm">
			@csrf

			<!-- Topbar -->
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Topbar</h3>
						<div class="admin-card-sub">Strip bar di atas header — kosongkan untuk menyembunyikan</div>
					</div>
				</div>
				<div>
					<label class="form-label-admin">Teks Promo Topbar</label>
					<input type="text" name="footer_topbar_promo" class="form-control-admin"
						value="{{ $setting('footer_topbar_promo', '') }}"
						placeholder="Mis. Diskon spesial untuk pembelian di atas Rp500.000">
				</div>
			</div>

			<!-- Kolom 1 (Kategori) -->
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Kolom 1 — Daftar Tautan</h3>
						<div class="admin-card-sub">Mis. kategori produk</div>
					</div>
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Judul Kolom</label>
					<input type="text" name="footer_col1_title" class="form-control-admin"
						value="{{ $setting('footer_col1_title', 'Kategori') }}">
				</div>
				<label class="form-label-admin">Tautan</label>
				<div class="footer-rows" data-rows="footer_col1_links">
					@foreach($col1Links as $i => $row)
						<div class="footer-row">
							<input type="text" name="footer_col1_links[{{ $i }}][label]" class="form-control-admin" placeholder="Label" value="{{ $row['label'] ?? '' }}">
							<input type="text" name="footer_col1_links[{{ $i }}][url]"   class="form-control-admin" placeholder="URL (mis. /produk)" value="{{ $row['url'] ?? '' }}">
							<button type="button" class="btn-admin-icon danger footer-row-del" title="Hapus baris"><i class="fa fa-trash-o"></i></button>
						</div>
					@endforeach
				</div>
				<button type="button" class="btn-admin btn-admin-outline footer-row-add" data-target="footer_col1_links">
					<i class="fa fa-plus"></i> Tambah Tautan
				</button>
			</div>

			<!-- Kolom 2 (Bantuan) -->
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Kolom 2 — Daftar Tautan</h3>
						<div class="admin-card-sub">Mis. bantuan / customer service</div>
					</div>
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Judul Kolom</label>
					<input type="text" name="footer_col2_title" class="form-control-admin"
						value="{{ $setting('footer_col2_title', 'Bantuan') }}">
				</div>
				<label class="form-label-admin">Tautan</label>
				<div class="footer-rows" data-rows="footer_col2_links">
					@foreach($col2Links as $i => $row)
						<div class="footer-row">
							<input type="text" name="footer_col2_links[{{ $i }}][label]" class="form-control-admin" placeholder="Label" value="{{ $row['label'] ?? '' }}">
							<input type="text" name="footer_col2_links[{{ $i }}][url]"   class="form-control-admin" placeholder="URL (mis. /kontak)" value="{{ $row['url'] ?? '' }}">
							<button type="button" class="btn-admin-icon danger footer-row-del" title="Hapus baris"><i class="fa fa-trash-o"></i></button>
						</div>
					@endforeach
				</div>
				<button type="button" class="btn-admin btn-admin-outline footer-row-add" data-target="footer_col2_links">
					<i class="fa fa-plus"></i> Tambah Tautan
				</button>
			</div>

			<!-- Kolom 3 (Hubungi Kami) -->
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Kolom 3 — Hubungi Kami</h3>
						<div class="admin-card-sub">Alamat, telepon, dan ikon sosial dikelola di tab <a href="#tab-kontak" onclick="$('.cms-tab[data-tab=kontak]').click(); return false;">Info Kontak</a>. Di sini hanya teks pengantar.</div>
					</div>
				</div>
				<div style="margin-bottom:14px;">
					<label class="form-label-admin">Judul Kolom</label>
					<input type="text" name="footer_col3_title" class="form-control-admin"
						value="{{ $setting('footer_col3_title', 'Hubungi Kami') }}">
				</div>
				<div>
					<label class="form-label-admin">Teks Pengantar</label>
					<textarea name="footer_col3_text" rows="3" class="form-control-admin"
						placeholder="Kosongkan untuk menampilkan kalimat default berisi alamat & telepon dari tab Info Kontak.">{{ $setting('footer_col3_text', '') }}</textarea>
					<small style="color:#9a9288; font-size:11.5px;">Placeholder yang didukung: <code>{address}</code>, <code>{phone}</code>, <code>{email}</code>.</small>
				</div>
			</div>

			<!-- Ikon Pembayaran -->
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Ikon Pembayaran</h3>
						<div class="admin-card-sub">Strip ikon di bawah footer (mis. Visa, Mastercard, OVO)</div>
					</div>
				</div>
				<label style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
					<input type="checkbox" name="footer_show_payments" value="1" @checked($showPayments)>
					<span>Tampilkan strip ikon pembayaran di footer</span>
				</label>
				<label class="form-label-admin">Daftar Ikon</label>
				<div class="footer-rows" data-rows="footer_payment_icons">
					@foreach($payIcons as $i => $row)
						<div class="footer-row footer-row-icon">
							<div class="footer-icon-preview">
								@if(! empty($row['image_url']))
									<img src="{{ \Illuminate\Support\Str::startsWith($row['image_url'], ['http://', 'https://', '/']) ? $row['image_url'] : asset($row['image_url']) }}" alt="">
								@endif
							</div>
							<input type="text" name="footer_payment_icons[{{ $i }}][image_url]" class="form-control-admin" placeholder="URL gambar (mis. uploads/footer/visa.png atau https://...)" value="{{ $row['image_url'] ?? '' }}">
							<input type="text" name="footer_payment_icons[{{ $i }}][alt]"       class="form-control-admin" placeholder="Alt teks" value="{{ $row['alt'] ?? '' }}" style="max-width:140px;">
							<button type="button" class="btn-admin-icon danger footer-row-del" title="Hapus baris"><i class="fa fa-trash-o"></i></button>
						</div>
					@endforeach
				</div>
				<button type="button" class="btn-admin btn-admin-outline footer-row-add" data-target="footer_payment_icons">
					<i class="fa fa-plus"></i> Tambah Ikon
				</button>
				<small style="display:block; color:#9a9288; font-size:11.5px; margin-top:8px;">
					Tip: simpan file ikon ke <code>public/uploads/footer/</code> lalu masukkan path relatif (mis. <code>uploads/footer/visa.png</code>). URL absolut juga didukung.
				</small>
			</div>

			<!-- Hak Cipta -->
			<div class="admin-card">
				<div class="admin-card-header">
					<div>
						<h3 class="admin-card-title">Baris Hak Cipta</h3>
						<div class="admin-card-sub">Teks paling bawah footer</div>
					</div>
				</div>
				<input type="text" name="footer_copyright" class="form-control-admin"
					value="{{ $setting('footer_copyright', 'Hak Cipta © ' . date('Y') . ' Batik Penawuo. Semua hak dilindungi.') }}">
			</div>

			<div class="admin-card" style="text-align:right;">
				<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Semua Pengaturan Footer</button>
			</div>
		</form>
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

	/* Footer CMS — dynamic rows */
	.footer-rows { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
	.footer-row {
		display: flex; gap: 8px; align-items: center;
		padding: 8px; background: #faf7ef; border: 1px solid #ece8de; border-radius: 4px;
	}
	.footer-row .form-control-admin { flex: 1; margin: 0; }
	.footer-row .btn-admin-icon { flex-shrink: 0; }
	.footer-row-icon .footer-icon-preview {
		width: 56px; height: 36px; border-radius: 4px; background: #fff;
		border: 1px solid #ece8de; display: flex; align-items: center; justify-content: center;
		flex-shrink: 0; overflow: hidden;
	}
	.footer-row-icon .footer-icon-preview img { max-width: 100%; max-height: 100%; }
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

		// ----- Footer CMS: dynamic rows (links + payment icons) -----
		// Indeks per-target diturunkan dari jumlah baris awal supaya tidak bentrok
		// dengan baris yang sudah dirender server-side.
		var rowCounters = {};
		$('.footer-rows').each(function() {
			rowCounters[$(this).data('rows')] = $(this).children('.footer-row').length;
		});

		$(document).on('click', '.footer-row-add', function() {
			var target = $(this).data('target');
			var $rows  = $('.footer-rows[data-rows="' + target + '"]');
			var i      = rowCounters[target]++;
			var html;
			if (target === 'footer_payment_icons') {
				html = ''
					+ '<div class="footer-row footer-row-icon">'
					+   '<div class="footer-icon-preview"></div>'
					+   '<input type="text" name="' + target + '[' + i + '][image_url]" class="form-control-admin" placeholder="URL gambar (mis. uploads/footer/visa.png atau https://...)">'
					+   '<input type="text" name="' + target + '[' + i + '][alt]" class="form-control-admin" placeholder="Alt teks" style="max-width:140px;">'
					+   '<button type="button" class="btn-admin-icon danger footer-row-del" title="Hapus baris"><i class="fa fa-trash-o"></i></button>'
					+ '</div>';
			} else {
				html = ''
					+ '<div class="footer-row">'
					+   '<input type="text" name="' + target + '[' + i + '][label]" class="form-control-admin" placeholder="Label">'
					+   '<input type="text" name="' + target + '[' + i + '][url]" class="form-control-admin" placeholder="URL (mis. /produk)">'
					+   '<button type="button" class="btn-admin-icon danger footer-row-del" title="Hapus baris"><i class="fa fa-trash-o"></i></button>'
					+ '</div>';
			}
			$rows.append(html);
		});

		$(document).on('click', '.footer-row-del', function() {
			$(this).closest('.footer-row').remove();
		});

		// Live preview untuk URL ikon pembayaran.
		$(document).on('input', '.footer-row-icon input[name$="[image_url]"]', function() {
			var $row = $(this).closest('.footer-row-icon');
			var $img = $row.find('.footer-icon-preview img');
			var url  = $(this).val().trim();
			if (! url) { $img.remove(); return; }
			var resolved = (/^https?:\/\//i.test(url) || url.charAt(0) === '/') ? url : '{{ asset('') }}' + url;
			if ($img.length) {
				$img.attr('src', resolved);
			} else {
				$row.find('.footer-icon-preview').html('<img src="' + resolved + '" alt="">');
			}
		});
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

</script>
@endpush
