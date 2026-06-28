@extends('layouts.auth')

@section('title', 'Batik Penawuo | Lupa Kata Sandi')

@push('styles')
<style>
	.auth-wrap { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px; background: #faf6ed; }
	.auth-card { width: 100%; max-width: 440px; background: #fff; border: 1px solid #eee; box-shadow: 0 4px 30px rgba(0,0,0,.05); padding: 40px 36px; }
	.auth-title { font-size: 22px; color: #333; margin-bottom: 6px; font-weight: 600; text-align: center; }
	.auth-subtitle { font-size: 13.5px; color: #888; margin-bottom: 28px; text-align: center; line-height: 1.6; }
	.auth-field { margin-bottom: 18px; }
	.auth-label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; }
	.auth-input { width: 100%; padding: 12px 14px; border: 1px solid #d9d9d9; border-radius: 3px; font-size: 14px; color: #333; transition: border-color .15s; background: #fff; }
	.auth-input:focus { outline: 0; border-color: #c29e5c; }
	.auth-error { color: #c0392b; font-size: 12px; margin-top: 4px; }
	.auth-submit { width: 100%; background: #c29e5c; color: #fff; border: 0; padding: 13px; font-size: 14px; font-weight: 600; border-radius: 3px; cursor: pointer; transition: background .15s; }
	.auth-submit:hover { background: #a88541; }
	.auth-flash-ok { background:#edf7ef; border:1px solid #cfe6d6; border-left:4px solid #56a676; color:#2f7a4c; padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:13.5px; text-align:center; }
	.auth-back { display:block; text-align:center; margin-top:20px; font-size:13px; color:#c29e5c; text-decoration:none; }
	.auth-back:hover { text-decoration:underline; }
</style>
@endpush

@section('content')
	<div class="auth-wrap">
		<div class="auth-card">
			<h2 class="auth-title">Lupa Kata Sandi?</h2>
			<p class="auth-subtitle">Masukkan email akun Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>

			@if(session('status'))
				<div class="auth-flash-ok">
					<i class="fa fa-check-circle"></i> {{ session('status') }}
				</div>
			@endif

			@if(!session('status'))
				<form action="{{ route('lupa-password.kirim') }}" method="POST">
					@csrf
					<div class="auth-field">
						<label class="auth-label" for="email">Email</label>
						<input class="auth-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@contoh.com" required autofocus>
						@error('email')<div class="auth-error">{{ $message }}</div>@enderror
					</div>
					<button type="submit" class="auth-submit">Kirim Tautan Reset</button>
				</form>
			@endif

			<a href="{{ route('login') }}" class="auth-back"><i class="fa fa-arrow-left"></i> Kembali ke halaman masuk</a>
		</div>
	</div>
@endsection
