@extends('layouts.app')

@section('title', 'Batik Penawo | Pesanan ' . $order->invoice_number)

@push('styles')
<style>
	.success-wrap { max-width: 720px; margin: 0 auto; }
	.success-card {
		background: #fff; border: 1px solid #ece8de; border-radius: 8px;
		padding: 36px 32px; text-align: center;
	}
	.success-check {
		width: 72px; height: 72px; border-radius: 50%;
		background: #e3f3e9; color: #2f7a4c;
		display: inline-flex; align-items: center; justify-content: center;
		font-size: 32px; margin-bottom: 18px;
	}
	.success-title { font-size: 22px; font-weight: 600; color: #2d2a26; margin: 0 0 6px; }
	.success-subtitle { font-size: 14px; color: #6c665e; margin-bottom: 20px; }
	.invoice-box {
		background: #faf6ed; border: 1px dashed #d8c998; border-radius: 6px;
		padding: 14px 18px; display: inline-flex; gap: 10px; align-items: center;
		font-size: 14px; color: #8a6b2b; letter-spacing: .3px; margin-bottom: 22px;
	}
	.invoice-box strong { font-weight: 600; letter-spacing: .5px; }

	.detail-card {
		background: #fff; border: 1px solid #ece8de; border-radius: 6px;
		padding: 22px 24px; text-align: left; margin-top: 18px;
	}
	.detail-card h4 { font-size: 14px; font-weight: 600; color: #2d2a26; margin: 0 0 12px; letter-spacing: .2px; }
	.detail-row { display: flex; padding: 8px 0; font-size: 13.5px; border-bottom: 1px solid #f2efe7; }
	.detail-row:last-child { border-bottom: 0; }
	.detail-row .label { width: 160px; color: #9a9288; flex-shrink: 0; }
	.detail-row .value { color: #4d4640; flex: 1; }
	.detail-row .value strong { color: #2d2a26; }

	.status-pill {
		display: inline-block; padding: 4px 12px; border-radius: 999px;
		font-size: 12px; font-weight: 500; letter-spacing: .3px;
	}
	.status-menunggu { background: #fcf1d9; color: #a87318; }
	.status-diproses { background: #fcf1d9; color: #a87318; }
	.status-dikirim  { background: #e1ecf8; color: #3a5fa0; }
	.status-selesai  { background: #e3f3e9; color: #2f7a4c; }

	.order-items { list-style: none; margin: 0; padding: 0; }
	.order-items li { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f2efe7; font-size: 13.5px; }
	.order-items li:last-child { border-bottom: 0; }
	.order-items .name { flex: 1; color: #4d4640; }
	.order-items .qty { color: #9a9288; }
	.order-items .sub { color: #c29e5c; font-weight: 500; white-space: nowrap; }

	.success-actions { margin-top: 26px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
	.btn-primary-custom {
		background: #c29e5c; color: #fff; padding: 11px 22px;
		border-radius: 4px; font-size: 13.5px; font-weight: 500;
		text-decoration: none; transition: background .15s;
	}
	.btn-primary-custom:hover { background: #a88541; color: #fff; text-decoration: none; }
	.btn-outline-custom {
		background: #fff; color: #4d4640; padding: 11px 22px;
		border: 1px solid #ddd6c6; border-radius: 4px;
		font-size: 13.5px; font-weight: 500; text-decoration: none;
		transition: all .15s;
	}
	.btn-outline-custom:hover { border-color: #c29e5c; color: #c29e5c; text-decoration: none; }

	.payment-instructions {
		background: #fff8e7; border-left: 4px solid #d8c998;
		padding: 14px 18px; margin-top: 18px; border-radius: 4px;
		font-size: 13px; color: #6c665e; text-align: left;
	}
	.payment-instructions strong { color: #8a6b2b; }

	/* Upload bukti transfer */
	.proof-card {
		background: #fff; border: 1px solid #ece8de; border-radius: 6px;
		padding: 22px 24px; margin-top: 18px; text-align: left;
	}
	.proof-card h4 { font-size: 14px; font-weight: 600; color: #2d2a26; margin: 0 0 6px; letter-spacing: .2px; }
	.proof-card p.hint { font-size: 12.5px; color: #9a9288; margin-bottom: 14px; }
	.proof-dropzone {
		border: 2px dashed #d8c998; border-radius: 6px;
		padding: 22px 16px; text-align: center;
		background: #fff8e7; transition: border-color .15s, background .15s;
		cursor: pointer;
	}
	.proof-dropzone:hover { border-color: #c29e5c; background: #faf0d7; }
	.proof-dropzone i { font-size: 32px; color: #c29e5c; margin-bottom: 8px; }
	.proof-dropzone-label { font-size: 13px; color: #4d4640; font-weight: 500; }
	.proof-dropzone-sub { font-size: 11.5px; color: #9a9288; margin-top: 4px; }
	#proofInput { display: none; }
	#proofPreview { max-width: 100%; max-height: 280px; border-radius: 4px; display: none; margin: 10px auto 0; border: 1px solid #e0dbcf; }
	.proof-submit-btn {
		background: #c29e5c; color: #fff;
		padding: 11px 22px; border: 0; border-radius: 4px;
		font-size: 13.5px; font-weight: 500; cursor: pointer;
		margin-top: 12px; display: none;
		transition: background .15s;
	}
	.proof-submit-btn:hover { background: #a88541; }
	.proof-submit-btn.ready { display: inline-block; }
	.proof-error { color: #a5432f; font-size: 12.5px; margin-top: 8px; }
	.proof-filename { font-size: 12px; color: #6c665e; margin-top: 8px; }

	/* Existing uploaded proof */
	.proof-existing {
		border: 1px solid #cfe6d6; background: #edf7ef; border-radius: 6px;
		padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start;
	}
	.proof-existing-icon { color: #2f7a4c; font-size: 20px; flex-shrink: 0; margin-top: 2px; }
	.proof-existing-text { flex: 1; }
	.proof-existing-text strong { color: #2d2a26; font-size: 13.5px; display: block; }
	.proof-existing-text .meta { font-size: 12px; color: #5b8d70; margin-top: 2px; }
	.proof-existing-text a { color: #2f7a4c; font-size: 12.5px; text-decoration: underline; margin-right: 10px; }
	.proof-existing-text a:hover { color: #1f5633; }
	.proof-thumb {
		width: 100px; height: 100px; object-fit: cover;
		border-radius: 4px; border: 1px solid #cfe6d6; cursor: pointer;
		transition: opacity .15s;
	}
	.proof-thumb:hover { opacity: .85; }
</style>
@endpush

@section('content')
	@php
		$statusMap = [
			'menunggu_bayar' => ['label' => 'Menunggu Pembayaran', 'class' => 'status-menunggu'],
			'diproses'       => ['label' => 'Diproses',            'class' => 'status-diproses'],
			'dikirim'        => ['label' => 'Dikirim',              'class' => 'status-dikirim'],
			'selesai'        => ['label' => 'Selesai',              'class' => 'status-selesai'],
			'dibatalkan'     => ['label' => 'Dibatalkan',           'class' => 'status-menunggu'],
		];
		$s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'status-menunggu'];
	@endphp

	<section class="bg-img1 txt-center p-lr-15 p-tb-70" style="background-image: url('{{ asset('frontend/images/bg-02.jpg') }}');">
		<h2 class="ltext-105 cl0 txt-center">Pesanan Berhasil</h2>
	</section>

	<section class="bg0 p-t-40 p-b-80">
		<div class="container">
			<div class="success-wrap">
				<div class="success-card">
					<div class="success-check"><i class="fa fa-check"></i></div>
					<h1 class="success-title">Terima kasih, {{ $order->customer_name }}!</h1>
					<p class="success-subtitle">Pesanan Anda telah kami terima dan siap diproses.</p>

					<div class="invoice-box">
						<i class="fa fa-file-text-o"></i>
						<span>No. Pesanan: <strong>{{ $order->invoice_number }}</strong></span>
					</div>

					<div>
						<span class="status-pill {{ $s['class'] }}">{{ $s['label'] }}</span>
					</div>
				</div>

				<div class="detail-card">
					<h4>Informasi Pesanan</h4>
					<div class="detail-row">
						<span class="label">Tanggal</span>
						<span class="value">{{ $order->created_at->translatedFormat('l, d F Y H:i') }} WIB</span>
					</div>
					<div class="detail-row">
						<span class="label">Email</span>
						<span class="value">{{ $order->customer_email }}</span>
					</div>
					<div class="detail-row">
						<span class="label">Alamat Pengiriman</span>
						<span class="value">{{ $order->shipping_address }}</span>
					</div>
					@if($order->note)
					<div class="detail-row">
						<span class="label">Catatan</span>
						<span class="value">{{ $order->note }}</span>
					</div>
					@endif
					<div class="detail-row">
						<span class="label">Metode Pembayaran</span>
						<span class="value"><strong>{{ $order->payment_method }}</strong></span>
					</div>
				</div>

				<div class="detail-card">
					<h4>Rincian Produk</h4>
					<ul class="order-items">
						@foreach($order->items as $it)
						<li>
							<span class="name">{{ $it->product_name }}</span>
							<span class="qty">{{ $it->qty }} × {{ $rupiah($it->price) }}</span>
							<span class="sub">{{ $rupiah($it->qty * $it->price) }}</span>
						</li>
						@endforeach
					</ul>
					<div class="detail-row" style="border-top:1px solid #ece8de; margin-top:6px; padding-top:12px; font-weight:600;">
						<span class="label">Total Bayar</span>
						<span class="value"><strong style="color:#c29e5c; font-size:15px;">{{ $rupiah($order->total) }}</strong></span>
					</div>
				</div>

				@if($order->payment_method !== 'COD' && $order->status === 'menunggu_bayar')
					<div class="payment-instructions">
						<strong>Petunjuk Pembayaran:</strong><br>
						Segera lakukan pembayaran sebesar <strong>{{ $rupiah($order->total) }}</strong> melalui <strong>{{ $order->payment_method }}</strong>.
						Pesanan akan diproses setelah bukti transfer Anda kami verifikasi.
					</div>

					<div class="proof-card">
						<h4><i class="fa fa-upload m-r-6" style="color:#c29e5c;"></i> Unggah Bukti Transfer</h4>
						<p class="hint">Upload screenshot atau foto bukti transfer Anda (JPG/PNG/WebP, maksimal 2 MB).</p>

						@if($errors->has('proof'))
							<div class="proof-error"><i class="fa fa-exclamation-triangle"></i> {{ $errors->first('proof') }}</div>
						@endif

						<form action="{{ route('pesanan.bayar', $order->invoice_number) }}" method="POST" enctype="multipart/form-data" id="proofForm">
							@csrf
							<label for="proofInput" class="proof-dropzone" id="proofDropzone">
								<i class="fa fa-cloud-upload"></i>
								<div class="proof-dropzone-label">Klik untuk pilih file bukti transfer</div>
								<div class="proof-dropzone-sub">atau drag & drop foto di sini</div>
							</label>
							<input type="file" name="proof" id="proofInput" accept="image/jpeg,image/png,image/webp" required>
							<img id="proofPreview" alt="Preview bukti transfer">
							<div class="proof-filename" id="proofFilename"></div>
							<button type="submit" class="proof-submit-btn" id="proofSubmit">
								<i class="fa fa-check m-r-6"></i> Kirim Bukti Transfer
							</button>
						</form>
					</div>

					<script>
					(function(){
						var input = document.getElementById('proofInput');
						var preview = document.getElementById('proofPreview');
						var filenameEl = document.getElementById('proofFilename');
						var submit = document.getElementById('proofSubmit');
						var dropzone = document.getElementById('proofDropzone');
						if (!input) return;

						function handleFile(file) {
							if (!file) return;
							if (file.size > 2 * 1024 * 1024) {
								alert('Ukuran file melebihi 2 MB.'); input.value = ''; return;
							}
							if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
								alert('Format tidak didukung. Gunakan JPG, PNG, atau WebP.'); input.value = ''; return;
							}
							var reader = new FileReader();
							reader.onload = function(e) {
								preview.src = e.target.result;
								preview.style.display = 'block';
								filenameEl.textContent = file.name + ' · ' + (file.size / 1024).toFixed(1) + ' KB';
								submit.classList.add('ready');
							};
							reader.readAsDataURL(file);
						}

						input.addEventListener('change', function(e){ handleFile(e.target.files[0]); });

						// Drag & drop
						['dragenter','dragover'].forEach(function(ev){
							dropzone.addEventListener(ev, function(e){ e.preventDefault(); dropzone.style.borderColor = '#c29e5c'; dropzone.style.background = '#faf0d7'; });
						});
						['dragleave','drop'].forEach(function(ev){
							dropzone.addEventListener(ev, function(e){ e.preventDefault(); dropzone.style.borderColor = ''; dropzone.style.background = ''; });
						});
						dropzone.addEventListener('drop', function(e){
							e.preventDefault();
							if (e.dataTransfer.files && e.dataTransfer.files[0]) {
								input.files = e.dataTransfer.files;
								handleFile(e.dataTransfer.files[0]);
							}
						});
					})();
					</script>
				@elseif($order->payment_proof)
					<div class="proof-card">
						<h4><i class="fa fa-check-circle m-r-6" style="color:#2f7a4c;"></i> Bukti Transfer Terkirim</h4>
						<div class="proof-existing">
							<a href="{{ $order->payment_proof_url }}" target="_blank">
								<img src="{{ $order->payment_proof_url }}" alt="Bukti transfer" class="proof-thumb">
							</a>
							<div class="proof-existing-text">
								<strong>Terima kasih, bukti transfer sudah kami terima.</strong>
								<div class="meta">Diunggah pada {{ $order->paid_at?->translatedFormat('d F Y, H:i') }} WIB · Pesanan sedang diverifikasi.</div>
								<div style="margin-top:8px;">
									<a href="{{ $order->payment_proof_url }}" target="_blank"><i class="fa fa-external-link"></i> Buka gambar</a>
								</div>
							</div>
						</div>
					</div>
				@endif
				@if($order->payment_method === 'COD')
					<div class="payment-instructions">
						<strong>Pembayaran COD:</strong><br>
						Siapkan uang tunai sebesar <strong>{{ $rupiah($order->total) }}</strong> saat kurir tiba. Pesanan Anda akan segera kami kemas.
					</div>
				@endif

				<div class="success-actions">
					<a href="{{ route('produk') }}" class="btn-primary-custom">
						<i class="fa fa-shopping-basket m-r-6"></i> Lanjut Belanja
					</a>
					<a href="{{ route('keranjang') }}" class="btn-outline-custom">
						<i class="fa fa-shopping-cart m-r-6"></i> Lihat Keranjang
					</a>
				</div>
			</div>
		</div>
	</section>
@endsection
