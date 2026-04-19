@extends('layouts.auth')

@section('title', 'Batik Penawo | Daftar')
@section('auth-heading', 'Daftar')

@push('styles')
<style>
	.auth-wrap { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px; background: #faf6ed; }
	.auth-card { width: 100%; max-width: 460px; background: #fff; border: 1px solid #eee; box-shadow: 0 4px 30px rgba(0,0,0,.05); padding: 40px 36px; }
	.auth-title { font-size: 22px; color: #333; margin-bottom: 6px; font-weight: 600; text-align: center; }
	.auth-subtitle { font-size: 13.5px; color: #888; margin-bottom: 28px; text-align: center; }
	.auth-field { margin-bottom: 18px; }
	.auth-label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; }
	.auth-input { width: 100%; padding: 12px 14px; border: 1px solid #d9d9d9; border-radius: 3px; font-size: 14px; color: #333; transition: border-color .15s; background: #fff; }
	.auth-input:focus { outline: 0; border-color: #c29e5c; }
	.auth-error { color: #c0392b; font-size: 12px; margin-top: 4px; }
	.auth-hint { color: #999; font-size: 11.5px; margin-top: 4px; }
	.auth-submit { width: 100%; background: #c29e5c; color: #fff; border: 0; padding: 13px; font-size: 14px; font-weight: 600; border-radius: 3px; cursor: pointer; transition: background .15s; margin-top: 6px; }
	.auth-submit:hover { background: #a88541; }
	.auth-footer { margin-top: 24px; text-align: center; font-size: 13.5px; color: #777; }
	.auth-footer a { color: #c29e5c; font-weight: 600; text-decoration: none; }
	.auth-footer a:hover { text-decoration: underline; }
	.auth-terms { font-size: 12px; color: #888; margin-top: 14px; text-align: center; line-height: 1.5; }
	.auth-terms a { color: #c29e5c; }
</style>
@endpush

@section('content')
	<div class="auth-wrap">
		<div class="auth-card">
			<h2 class="auth-title">Buat Akun Baru</h2>
			<p class="auth-subtitle">Bergabunglah dan nikmati promo eksklusif Batik Penawo</p>

			<form action="{{ route('register.submit') }}" method="POST">
				@csrf

				<div class="auth-field">
					<label class="auth-label" for="name">Nama Lengkap</label>
					<input class="auth-input" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap Anda" required autofocus>
					@error('name')<div class="auth-error">{{ $message }}</div>@enderror
				</div>

				<div class="auth-field">
					<label class="auth-label" for="email">Email</label>
					<input class="auth-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@contoh.com" required>
					@error('email')<div class="auth-error">{{ $message }}</div>@enderror
				</div>

				<div class="auth-field">
					<label class="auth-label" for="password">Kata Sandi</label>
					<input class="auth-input" type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
					<div class="auth-hint">Gunakan minimal 6 karakter.</div>
					@error('password')<div class="auth-error">{{ $message }}</div>@enderror
				</div>

				<div class="auth-field">
					<label class="auth-label" for="password_confirmation">Ulangi Kata Sandi</label>
					<input class="auth-input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" required>
				</div>

				<button type="submit" class="auth-submit">Daftar</button>

				<div class="auth-terms">
					Dengan mendaftar, Anda menyetujui <a href="#">Syarat & Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> kami.
				</div>
			</form>

			<div class="auth-footer">
				Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
			</div>
		</div>
	</div>
@endsection
