@extends('layouts.app')

@section('title', 'Batik Penawo | Pengaturan')

@push('styles')
<style>
	.akun-wrap { display: grid; grid-template-columns: 280px 1fr; gap: 24px; }
	@media (max-width: 900px) { .akun-wrap { grid-template-columns: 1fr; } }
	.akun-sidebar { background:#fff; border:1px solid #ece8de; border-radius:6px; overflow:hidden; }
	.akun-sidebar-header { padding:20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid #ece8de; background:#faf7ef; }
	.akun-avatar { width:48px; height:48px; border-radius:50%; background:#c29e5c; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:15px; flex-shrink:0; }
	.akun-sidebar-name { font-size:14px; font-weight:600; color:#2d2a26; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
	.akun-sidebar-email { font-size:12px; color:#9a9288; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
	.akun-sidebar-menu { list-style:none; margin:0; padding:8px 0; }
	.akun-sidebar-menu li a { display:flex; align-items:center; gap:10px; padding:11px 20px; color:#4d4640; font-size:13.5px; transition:background .15s, color .15s; border-left:3px solid transparent; }
	.akun-sidebar-menu li a i { width:18px; font-size:14px; color:#9a9288; }
	.akun-sidebar-menu li a small { font-size:10.5px; color:#bdb7ab; margin-left:auto; text-transform:uppercase; letter-spacing:0.3px; }
	.akun-sidebar-menu li a:hover { background:#faf7ef; color:#c29e5c; text-decoration:none; }
	.akun-sidebar-menu li a:hover i { color:#c29e5c; }
	.akun-sidebar-menu li.active a { background:#faf7ef; color:#c29e5c; border-left-color:#c29e5c; font-weight:500; }
	.akun-sidebar-menu li.active i { color:#c29e5c; }
	.akun-logout-btn { background:none; border:0; padding:0; color:#a5432f; font-size:13px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
	.akun-logout-btn:hover { color:#8a2f20; }

	.akun-card { background:#fff; border:1px solid #ece8de; border-radius:6px; padding:24px 28px; }
	.akun-card + .akun-card { margin-top: 18px; }
	.akun-card-title { font-size:16px; font-weight:600; color:#2d2a26; margin:0 0 6px; }
	.akun-card-sub { font-size:12.5px; color:#9a9288; margin-bottom: 16px; }
	.akun-flash { background:#edf7ef; border:1px solid #cfe6d6; border-left:4px solid #56a676; color:#2f7a4c; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-size:13px; }
	.akun-btn { background:#c29e5c; color:#fff; padding:11px 22px; border:0; border-radius:4px; font-size:13.5px; font-weight:500; cursor:pointer; transition:background .15s; }
	.akun-btn:hover { background:#a88541; color:#fff; }

	/* iOS-style toggle switch */
	.toggle-row {
		display: flex; justify-content: space-between; align-items: center; gap: 14px;
		padding: 14px 0; border-bottom: 1px solid #f2efe7;
	}
	.toggle-row:last-child { border-bottom: 0; }
	.toggle-row .body { flex: 1; min-width: 0; }
	.toggle-row .body strong { display: block; font-size: 13.5px; color: #2d2a26; margin-bottom: 2px; }
	.toggle-row .body small { display: block; font-size: 12px; color: #9a9288; line-height: 1.5; }

	.switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
	.switch input { opacity: 0; width: 0; height: 0; }
	.switch .slider {
		position: absolute; cursor: pointer; inset: 0;
		background: #d8d1bf; border-radius: 24px;
		transition: background .2s;
	}
	.switch .slider::before {
		content: ""; position: absolute; height: 18px; width: 18px;
		left: 3px; top: 3px; background: #fff; border-radius: 50%;
		transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
	}
	.switch input:checked + .slider { background: #c29e5c; }
	.switch input:checked + .slider::before { transform: translateX(20px); }
	.switch input:focus-visible + .slider { box-shadow: 0 0 0 3px rgba(194,158,92,.25); }

	.info-row {
		display: flex; justify-content: space-between; align-items: center; gap: 14px;
		padding: 12px 0; border-bottom: 1px solid #f2efe7; font-size: 13px;
	}
	.info-row:last-child { border-bottom: 0; }
	.info-row .label { color:#6c665e; }
	.info-row .value { color:#2d2a26; font-weight:500; }
</style>
@endpush

@section('content')
	<section class="bg-img1 txt-center p-lr-15 p-tb-70" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Pengaturan</h2>
	</section>

	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda <i class="fa fa-angle-right m-l-9 m-r-10"></i>
			</a>
			<span class="stext-109 cl4">Pengaturan</span>
		</div>
	</div>

	<section class="bg0 p-t-30 p-b-80">
		<div class="container">
			<div class="akun-wrap">
				@include('home.akun._sidebar')

				<main>
					@if(session('status'))
						<div class="akun-flash"><i class="fa fa-check-circle"></i> {{ session('status') }}</div>
					@endif

					<form action="{{ route('akun.pengaturan.update') }}" method="POST">
						@csrf

						<div class="akun-card">
							<h3 class="akun-card-title">Preferensi Notifikasi</h3>
							<p class="akun-card-sub">Atur kapan kami boleh mengirim email kepada Anda.</p>

							<div class="toggle-row">
								<div class="body">
									<strong>Update Pesanan</strong>
									<small>Kirim email saat status pesanan berubah (diproses, dikirim, selesai). <em>Disarankan tetap aktif.</em></small>
								</div>
								<label class="switch">
									<input type="checkbox" name="notify_order_updates" value="1" @checked($user->notify_order_updates)>
									<span class="slider"></span>
								</label>
							</div>

							<div class="toggle-row">
								<div class="body">
									<strong>Promo &amp; Penawaran</strong>
									<small>Email berkala tentang produk baru, diskon, dan event spesial.</small>
								</div>
								<label class="switch">
									<input type="checkbox" name="notify_promo" value="1" @checked($user->notify_promo)>
									<span class="slider"></span>
								</label>
							</div>

							<div style="padding-top:16px; margin-top:8px; border-top:1px solid #f2efe7;">
								<button type="submit" class="akun-btn"><i class="fa fa-floppy-o m-r-6"></i> Simpan Preferensi</button>
							</div>
						</div>
					</form>

					<div class="akun-card">
						<h3 class="akun-card-title">Informasi Akun</h3>
						<p class="akun-card-sub">Detail akun Anda — ubah lewat halaman Profil.</p>

						<div class="info-row">
							<span class="label">Email Login</span>
							<span class="value">{{ $user->email }}</span>
						</div>
						<div class="info-row">
							<span class="label">Bahasa</span>
							<span class="value">Indonesia</span>
						</div>
						<div class="info-row">
							<span class="label">Mata Uang</span>
							<span class="value">Rupiah (IDR)</span>
						</div>
						<div class="info-row">
							<span class="label">Bergabung Sejak</span>
							<span class="value">{{ $user->created_at?->translatedFormat('d F Y') }}</span>
						</div>

						<div style="padding-top:16px; margin-top:6px; border-top:1px solid #f2efe7; display:flex; gap:10px; flex-wrap:wrap;">
							<a href="{{ route('akun.profil') }}" class="akun-btn" style="background:#fff; color:#4d4640; border:1px solid #ddd6c6;"><i class="fa fa-user m-r-6"></i> Edit Profil</a>
							<form action="{{ route('logout') }}" method="POST" style="margin:0;">
								@csrf
								<button type="submit" class="akun-btn" style="background:#fff; color:#a5432f; border:1px solid #f2c6be;"
									data-confirm-title="Keluar dari akun?"
									data-confirm-message="Anda akan dialihkan ke beranda. Login lagi untuk akses akun."
									data-confirm-ok="Keluar">
									<i class="fa fa-sign-out m-r-6"></i> Keluar dari Akun Ini
								</button>
							</form>
						</div>
					</div>

					{{-- ======================== Ubah Password ======================== --}}
					<div class="akun-card">
						<h3 class="akun-card-title">Ubah Password</h3>
						<p class="akun-card-sub">Pakai password yang kuat — kombinasi huruf, angka, &amp; simbol minimal 6 karakter.</p>

						<form action="{{ route('akun.pengaturan.password') }}" method="POST">
							@csrf
							<div class="row">
								<div class="col-md-12" style="margin-bottom:14px;">
									<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Password Saat Ini <span style="color:#a5432f;">*</span></label>
									<input type="password" name="current_password" class="akun-input" autocomplete="current-password" required>
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Password Baru <span style="color:#a5432f;">*</span></label>
									<input type="password" name="new_password" class="akun-input" minlength="6" autocomplete="new-password" required>
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Konfirmasi Password Baru <span style="color:#a5432f;">*</span></label>
									<input type="password" name="new_password_confirmation" class="akun-input" minlength="6" autocomplete="new-password" required>
								</div>
							</div>
							<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:6px;">
								<button type="submit" class="akun-btn"><i class="fa fa-key m-r-6"></i> Perbarui Password</button>
							</div>
						</form>
					</div>

					{{-- ======================== Hapus Akun (zona berbahaya) ======================== --}}
					<div class="akun-card" style="border-color:#f2c6be; background:#fffaf9;">
						<h3 class="akun-card-title" style="color:#a5432f;">
							<i class="fa fa-exclamation-triangle m-r-6"></i> Hapus Akun Permanen
						</h3>
						<p class="akun-card-sub" style="color:#7a5848;">
							Tindakan ini tidak dapat dibatalkan. Data berikut akan dihapus permanen:
						</p>
						<ul style="margin: 0 0 16px 22px; font-size:12.5px; color:#6c665e; line-height:1.8;">
							<li>Profil &amp; data pribadi</li>
							<li>Semua alamat tersimpan</li>
							<li>Wishlist &amp; preferensi notifikasi</li>
						</ul>
						<p style="font-size:12px; color:#9a9288; margin-bottom:14px;">
							<i class="fa fa-info-circle"></i> Pesanan yang sudah pernah Anda buat tetap tersimpan di sistem toko sebagai catatan, tetapi tidak lagi terhubung ke akun Anda.
						</p>

						<form action="{{ route('akun.pengaturan.hapus-akun') }}" method="POST" id="formHapusAkun">
							@csrf @method('DELETE')

							<div style="margin-bottom:12px;">
								<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Konfirmasi Password <span style="color:#a5432f;">*</span></label>
								<input type="password" name="password" class="akun-input" autocomplete="current-password" required placeholder="Password akun Anda">
							</div>

							<div style="margin-bottom:14px;">
								<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">
									Ketik <code style="background:#f5ecd7; padding:2px 6px; border-radius:3px; font-size:12px; color:#a5432f;">HAPUS AKUN SAYA</code> untuk konfirmasi <span style="color:#a5432f;">*</span>
								</label>
								<input type="text" name="confirm_phrase" id="alConfirmPhrase" class="akun-input" required placeholder="HAPUS AKUN SAYA" autocomplete="off">
							</div>

							<button type="submit" id="btnHapusAkun" class="akun-btn" disabled
								style="background:#a5432f; color:#fff; border:1px solid #a5432f; opacity:.6; cursor:not-allowed;"
								data-confirm-title="Hapus akun secara permanen?"
								data-confirm-message="Tindakan ini TIDAK DAPAT DIBATALKAN. Akun, alamat, dan wishlist Anda akan hilang selamanya."
								data-confirm-ok="Ya, Hapus Akun Saya">
								<i class="fa fa-trash m-r-6"></i> Hapus Akun Saya
							</button>
						</form>

						<script>
						// Aktifkan tombol hanya bila frasa konfirmasi cocok persis.
						(function () {
							var input = document.getElementById('alConfirmPhrase');
							var btn   = document.getElementById('btnHapusAkun');
							if (!input || !btn) return;
							input.addEventListener('input', function () {
								var ok = input.value === 'HAPUS AKUN SAYA';
								btn.disabled = !ok;
								btn.style.opacity = ok ? '1' : '.6';
								btn.style.cursor  = ok ? 'pointer' : 'not-allowed';
							});
						})();
						</script>
					</div>
				</main>
			</div>
		</div>
	</section>
@endsection
