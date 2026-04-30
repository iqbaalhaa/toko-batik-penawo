@extends('layouts.app')

@section('title', 'Batik Penawo | Profil Saya')

@push('styles')
<style>
	.akun-wrap { display: grid; grid-template-columns: 280px 1fr; gap: 24px; }
	@media (max-width: 900px) { .akun-wrap { grid-template-columns: 1fr; } }

	.akun-sidebar {
		background: #fff; border: 1px solid #ece8de; border-radius: 6px;
		padding: 0; overflow: hidden;
	}
	.akun-sidebar-header {
		padding: 20px; display: flex; align-items: center; gap: 12px;
		border-bottom: 1px solid #ece8de; background: #faf7ef;
	}
	.akun-avatar {
		width: 48px; height: 48px; border-radius: 50%;
		background: #c29e5c; color: #fff;
		display: inline-flex; align-items: center; justify-content: center;
		font-weight: 600; font-size: 15px; flex-shrink: 0;
	}
	.akun-sidebar-name { font-size: 14px; font-weight: 600; color: #2d2a26; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.akun-sidebar-email { font-size: 12px; color: #9a9288; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.akun-sidebar-menu { list-style: none; margin: 0; padding: 8px 0; }
	.akun-sidebar-menu li a {
		display: flex; align-items: center; gap: 10px;
		padding: 11px 20px; color: #4d4640; font-size: 13.5px;
		transition: background .15s, color .15s;
		border-left: 3px solid transparent;
	}
	.akun-sidebar-menu li a i { width: 18px; font-size: 14px; color: #9a9288; }
	.akun-sidebar-menu li a small { font-size: 10.5px; color: #bdb7ab; margin-left: auto; text-transform: uppercase; letter-spacing: 0.3px; }
	.akun-sidebar-menu li a:hover { background: #faf7ef; color: #c29e5c; text-decoration: none; }
	.akun-sidebar-menu li a:hover i { color: #c29e5c; }
	.akun-sidebar-menu li.active a {
		background: #faf7ef; color: #c29e5c;
		border-left-color: #c29e5c; font-weight: 500;
	}
	.akun-sidebar-menu li.active i { color: #c29e5c; }
	.akun-sidebar-menu li.disabled a { color: #bdb7ab; cursor: not-allowed; }
	.akun-sidebar-menu li.disabled a:hover { background: transparent; color: #bdb7ab; }
	.akun-sidebar-menu li.disabled i { color: #d8d1bf !important; }

	.akun-logout-btn {
		background: none; border: 0; padding: 0;
		color: #a5432f; font-size: 13px; font-weight: 500;
		cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
		transition: color .15s;
	}
	.akun-logout-btn:hover { color: #8a2f20; }

	.akun-card {
		background: #fff; border: 1px solid #ece8de; border-radius: 6px;
		padding: 26px 28px;
	}
	.akun-card + .akun-card { margin-top: 18px; }
	.akun-card-title { font-size: 16px; font-weight: 600; color: #2d2a26; margin: 0 0 6px; }
	.akun-card-sub { font-size: 12.5px; color: #9a9288; margin-bottom: 18px; }
	.akun-label { display: block; font-size: 13px; font-weight: 500; color: #4d4640; margin-bottom: 6px; }
	.akun-input {
		width: 100%; padding: 10px 12px; font-size: 14px;
		border: 1px solid #e0dbcf; border-radius: 4px; background: #fff;
		transition: border-color .15s, box-shadow .15s; font-family: inherit;
	}
	.akun-input:focus {
		outline: none; border-color: #c29e5c;
		box-shadow: 0 0 0 3px rgba(194,158,92,.15);
	}
	.akun-input[readonly], .akun-input:disabled { background: #faf7ef; color: #9a9288; cursor: not-allowed; }

	.akun-btn {
		background: #c29e5c; color: #fff;
		padding: 11px 22px; border: 0; border-radius: 4px;
		font-size: 13.5px; font-weight: 500; cursor: pointer;
		transition: background .15s;
	}
	.akun-btn:hover { background: #a88541; color: #fff; }

	.akun-flash {
		background: #edf7ef; border: 1px solid #cfe6d6;
		border-left: 4px solid #56a676; color: #2f7a4c;
		padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 13px;
	}
	.akun-errors {
		background: #fbe4df; border: 1px solid #f2c6be;
		color: #a5432f; padding: 10px 14px; border-radius: 4px;
		margin-bottom: 16px; font-size: 13px;
	}

	.role-badge {
		display: inline-block; padding: 3px 10px; border-radius: 999px;
		font-size: 11.5px; font-weight: 500; background: #f5ecd7; color: #8a6b2b;
		text-transform: uppercase; letter-spacing: 0.5px;
	}
</style>
@endpush

@section('content')
	<section class="bg-img1 txt-center p-lr-15 p-tb-70" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Akun Saya</h2>
	</section>

	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="{{ route('home') }}" class="stext-109 cl8 hov-cl1 trans-04">
				Beranda <i class="fa fa-angle-right m-l-9 m-r-10"></i>
			</a>
			<span class="stext-109 cl4">Profil Saya</span>
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

					@if($errors->any())
						<div class="akun-errors">
							<strong>Gagal menyimpan:</strong>
							<ul style="margin:6px 0 0 18px; padding:0;">
								@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
							</ul>
						</div>
					@endif

					<form action="{{ route('akun.profil.update') }}" method="POST">
						@csrf
						<div class="akun-card">
							<h3 class="akun-card-title">Informasi Pribadi</h3>
							<p class="akun-card-sub">Kelola nama, email, dan kontak Anda. <span class="role-badge">{{ ucfirst($user->role) }}</span></p>

							<div class="row">
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">Nama Lengkap <span style="color:#a5432f;">*</span></label>
									<input type="text" name="name" class="akun-input" value="{{ old('name', $user->name) }}" required>
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">Email <span style="color:#a5432f;">*</span></label>
									<input type="email" name="email" class="akun-input" value="{{ old('email', $user->email) }}" required>
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">No. Telepon</label>
									<input type="text" name="phone" class="akun-input" placeholder="08xx xxxx xxxx" value="{{ old('phone', $user->phone) }}">
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">Bergabung Sejak</label>
									<input type="text" class="akun-input" value="{{ $user->created_at?->translatedFormat('d F Y') }}" readonly>
								</div>
							</div>
						</div>

						<div class="akun-card">
							<h3 class="akun-card-title">Detail Tambahan</h3>
							<p class="akun-card-sub">Opsional — bantu kami menyajikan rekomendasi yang lebih sesuai.</p>

							<div class="row">
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">Tanggal Lahir</label>
									<input type="date" name="birth_date" class="akun-input" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">Jenis Kelamin</label>
									<select name="gender" class="akun-input">
										<option value="">— Tidak ingin menyebutkan —</option>
										<option value="pria"   @selected(old('gender', $user->gender) === 'pria')>Pria</option>
										<option value="wanita" @selected(old('gender', $user->gender) === 'wanita')>Wanita</option>
									</select>
								</div>
							</div>
						</div>

						<div class="akun-card">
							<h3 class="akun-card-title">Ubah Password</h3>
							<p class="akun-card-sub">Kosongkan bila tidak ingin mengubah password.</p>

							<div class="row">
								<div class="col-md-12" style="margin-bottom:14px;">
									<label class="akun-label">Password Saat Ini</label>
									<input type="password" name="current_password" class="akun-input" autocomplete="current-password">
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">Password Baru</label>
									<input type="password" name="new_password" class="akun-input" minlength="6" autocomplete="new-password">
								</div>
								<div class="col-md-6" style="margin-bottom:14px;">
									<label class="akun-label">Konfirmasi Password Baru</label>
									<input type="password" name="new_password_confirmation" class="akun-input" minlength="6" autocomplete="new-password">
								</div>
							</div>

							<div style="padding-top:12px; border-top:1px solid #f2efe7; margin-top:6px;">
								<button type="submit" class="akun-btn"><i class="fa fa-floppy-o m-r-6"></i> Simpan Perubahan</button>
							</div>
						</div>
					</form>

					{{-- ======================== Daftar Alamat (max 3) ======================== --}}
					<div class="akun-card" style="margin-top:18px;">
						<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
							<div>
								<h3 class="akun-card-title">Alamat Pengiriman</h3>
								<p class="akun-card-sub" style="margin-bottom:0;">
									Maksimal {{ \App\Models\Address::MAX_PER_USER }} alamat. Alamat utama dipakai sebagai pilihan default saat checkout.
									<span style="color:#c29e5c; font-weight:500;">{{ $addresses->count() }}/{{ \App\Models\Address::MAX_PER_USER }} tersimpan.</span>
								</p>
							</div>
							<button type="button" class="akun-btn"
								@if($addresses->count() >= \App\Models\Address::MAX_PER_USER) disabled title="Sudah mencapai batas maksimal" @endif
								onclick="openAlamatModal()">
								<i class="fa fa-plus m-r-6"></i> Tambah Alamat
							</button>
						</div>

						@if($addresses->isEmpty())
							<div style="padding:24px; text-align:center; background:#faf7ef; border-radius:6px; margin-top:14px; color:#9a9288; font-size:13px;">
								<i class="fa fa-map-marker" style="font-size:24px; color:#d8d1bf; display:block; margin-bottom:8px;"></i>
								Belum ada alamat tersimpan. Tambahkan minimal satu alamat agar dapat checkout.
							</div>
						@else
							<div style="margin-top:14px; display:grid; gap:12px;">
								@foreach($addresses as $addr)
								<div style="border:1px solid {{ $addr->is_default ? '#c29e5c' : '#ece8de' }}; border-radius:6px; padding:14px 16px; {{ $addr->is_default ? 'background:#faf6ed;' : '' }}">
									<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
										<div style="flex:1; min-width:0;">
											<div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
												<strong style="font-size:14px; color:#2d2a26;">{{ $addr->label }}</strong>
												@if($addr->is_default)
													<span style="background:#c29e5c; color:#fff; font-size:10.5px; padding:2px 8px; border-radius:999px; letter-spacing:.5px; font-weight:500;">UTAMA</span>
												@endif
											</div>
											<div style="font-size:13px; color:#4d4640; line-height:1.6;">
												{{ $addr->full_address }}<br>
												<span style="color:#9a9288;">Kec. {{ $addr->district_name }}, {{ $addr->city_name }}, {{ $addr->province_name }}</span>
											</div>
										</div>
										<div style="display:flex; gap:6px; flex-shrink:0;">
											@if(! $addr->is_default)
												<form method="POST" action="{{ route('akun.alamat.default', $addr->id) }}" style="display:inline;">
													@csrf @method('PATCH')
													<button type="submit" class="akun-btn" style="background:#fff; color:#c29e5c; border:1px solid #c29e5c; padding:6px 12px; font-size:12px;" title="Jadikan alamat utama"><i class="fa fa-star-o"></i></button>
												</form>
											@endif
											<button type="button" class="akun-btn" style="background:#fff; color:#4d4640; border:1px solid #e0dbcf; padding:6px 12px; font-size:12px;"
												onclick='openAlamatModal(@json($addr))' title="Edit"><i class="fa fa-pencil"></i></button>
											<form method="POST" action="{{ route('akun.alamat.destroy', $addr->id) }}" style="display:inline;"
												data-confirm-title="Hapus alamat?"
												data-confirm-message='Alamat "{{ $addr->label }}" akan dihapus.'
												data-confirm-ok="Hapus">
												@csrf @method('DELETE')
												<button type="submit" class="akun-btn" style="background:#fff; color:#a5432f; border:1px solid #f2c6be; padding:6px 12px; font-size:12px;" title="Hapus"><i class="fa fa-trash-o"></i></button>
											</form>
										</div>
									</div>
								</div>
								@endforeach
							</div>
						@endif
					</div>

					@include('partials._alamat_modal', ['redirectTo' => 'akun.profil'])
				</main>
			</div>
		</div>
	</section>

	@push('scripts')
		@include('partials._wilayah_cascade')
	@endpush
@endsection
