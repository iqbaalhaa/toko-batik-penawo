@extends('layouts.auth')

@section('title', 'Batik Penawuo | Reset Kata Sandi')

@push('styles')
<style>
	.auth-wrap { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px; background: #faf6ed; }
	.auth-card { width: 100%; max-width: 440px; background: #fff; border: 1px solid #eee; box-shadow: 0 4px 30px rgba(0,0,0,.05); padding: 40px 36px; }
	.auth-title { font-size: 22px; color: #333; margin-bottom: 6px; font-weight: 600; text-align: center; }
	.auth-subtitle { font-size: 13.5px; color: #888; margin-bottom: 28px; text-align: center; }
	.auth-field { margin-bottom: 18px; }
	.auth-label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; }
	.auth-input { width: 100%; padding: 12px 14px; border: 1px solid #d9d9d9; border-radius: 3px; font-size: 14px; color: #333; transition: border-color .15s; background: #fff; }
	.auth-input:focus { outline: 0; border-color: #c29e5c; }
	.auth-error { color: #c0392b; font-size: 12px; margin-top: 4px; }
	.auth-submit { width: 100%; background: #c29e5c; color: #fff; border: 0; padding: 13px; font-size: 14px; font-weight: 600; border-radius: 3px; cursor: pointer; transition: background .15s; }
	.auth-submit:hover { background: #a88541; }
	.auth-flash-err { background:#fbe4df; border:1px solid #f2c6be; border-left:4px solid #a5432f; color:#a5432f; padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:13.5px; }
	.auth-back { display:block; text-align:center; margin-top:20px; font-size:13px; color:#c29e5c; text-decoration:none; }
	.auth-back:hover { text-decoration:underline; }
</style>
@endpush

@section('content')
	<div class="auth-wrap">
		<div class="auth-card">
			<h2 class="auth-title">Buat Kata Sandi Baru</h2>
			<p class="auth-subtitle">Masukkan kata sandi baru untuk akun <strong>{{ $email }}</strong></p>

			@if($errors->any())
				<div class="auth-flash-err">
					<i class="fa fa-exclamation-circle"></i> {{ $errors->first() }}
				</div>
			@endif

			<form action="{{ route('reset-password.proses') }}" method="POST">
				@csrf
				<input type="hidden" name="token" value="{{ $token }}">
				<input type="hidden" name="email" value="{{ $email }}">

				<div class="auth-field">
					<label class="auth-label" for="password">Kata Sandi Baru <span style="color:#c0392b;">*</span></label>
					<input class="auth-input" type="password" id="password" name="password" minlength="6" placeholder="Minimal 6 karakter" required autofocus>
					@error('password')<div class="auth-error">{{ $message }}</div>@enderror
				</div>

				<div class="auth-field">
					<label class="auth-label" for="password_confirmation">Konfirmasi Kata Sandi <span style="color:#c0392b;">*</span></label>
					<input class="auth-input" type="password" id="password_confirmation" name="password_confirmation" minlength="6" placeholder="Ulangi kata sandi baru" required>
				</div>

				<button type="submit" class="auth-submit">Simpan Kata Sandi Baru</button>
			</form>

			<a href="{{ route('login') }}" class="auth-back"><i class="fa fa-arrow-left"></i> Kembali ke halaman masuk</a>
		</div>
	</div>
@endsection
