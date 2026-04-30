@extends('layouts.admin')

@section('title', 'Profil Admin')
@section('page_title', 'Profil Saya')
@section('page_subtitle', 'Kelola data akun admin')

@section('content')
<div class="container-fluid" style="max-width:780px; padding:0;">

	@if(session('status'))
		<div style="background:#edf7ef; border:1px solid #cfe6d6; border-left:4px solid #56a676; color:#2f7a4c; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-size:13px;">
			<i class="fa fa-check-circle"></i> {{ session('status') }}
		</div>
	@endif
	@if($errors->any())
		<div style="background:#fbe4df; border:1px solid #f2c6be; color:#a5432f; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-size:13px;">
			<strong>Gagal menyimpan:</strong>
			<ul style="margin:6px 0 0 18px; padding:0;">
				@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
			</ul>
		</div>
	@endif

	<form action="{{ route('admin.profil.update') }}" method="POST">
		@csrf
		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Informasi Akun</h3>
					<div class="admin-card-sub">Nama dan email yang tampil di bagian header admin.</div>
				</div>
				<span style="background:#f5ecd7; color:#8a6b2b; padding:3px 10px; border-radius:999px; font-size:11.5px; font-weight:500; text-transform:uppercase; letter-spacing:.5px;">{{ ucfirst($user->role) }}</span>
			</div>

			<div class="row">
				<div class="col-md-6" style="margin-bottom:14px;">
					<label class="form-label-admin">Nama Lengkap <span style="color:#a5432f;">*</span></label>
					<input type="text" name="name" class="form-control-admin" value="{{ old('name', $user->name) }}" required>
				</div>
				<div class="col-md-6" style="margin-bottom:14px;">
					<label class="form-label-admin">Email <span style="color:#a5432f;">*</span></label>
					<input type="email" name="email" class="form-control-admin" value="{{ old('email', $user->email) }}" required>
				</div>
				<div class="col-md-6" style="margin-bottom:14px;">
					<label class="form-label-admin">No. Telepon</label>
					<input type="text" name="phone" class="form-control-admin" placeholder="08xx xxxx xxxx" value="{{ old('phone', $user->phone) }}">
				</div>
				<div class="col-md-6" style="margin-bottom:14px;">
					<label class="form-label-admin">Bergabung Sejak</label>
					<input type="text" class="form-control-admin" value="{{ $user->created_at?->translatedFormat('d F Y') }}" readonly style="background:#faf7ef; color:#9a9288;">
				</div>
			</div>
		</div>

		<div class="admin-card">
			<div class="admin-card-header">
				<div>
					<h3 class="admin-card-title">Ubah Password</h3>
					<div class="admin-card-sub">Kosongkan bila tidak ingin mengubah password.</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12" style="margin-bottom:14px;">
					<label class="form-label-admin">Password Saat Ini</label>
					<input type="password" name="current_password" class="form-control-admin" autocomplete="current-password">
				</div>
				<div class="col-md-6" style="margin-bottom:14px;">
					<label class="form-label-admin">Password Baru</label>
					<input type="password" name="new_password" class="form-control-admin" minlength="6" autocomplete="new-password">
				</div>
				<div class="col-md-6" style="margin-bottom:14px;">
					<label class="form-label-admin">Konfirmasi Password Baru</label>
					<input type="password" name="new_password_confirmation" class="form-control-admin" minlength="6" autocomplete="new-password">
				</div>
			</div>
			<div style="padding-top:12px; border-top:1px solid #f2efe7; margin-top:6px;">
				<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan Perubahan</button>
			</div>
		</div>
	</form>

</div>
@endsection
