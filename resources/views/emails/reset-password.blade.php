<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { margin:0; padding:0; background:#f5f0e8; font-family: Arial, sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:8px; overflow:hidden; border:1px solid #e8e0d0; }
  .header { background:#2d2a26; padding:28px 36px; text-align:center; }
  .header h1 { color:#c29e5c; font-size:20px; margin:0; letter-spacing:1px; }
  .body { padding:36px 36px 28px; }
  .body p { color:#4d4640; font-size:14.5px; line-height:1.8; margin:0 0 16px; }
  .btn-wrap { text-align:center; margin:28px 0; }
  .btn { display:inline-block; background:#c29e5c; color:#fff !important; text-decoration:none; padding:14px 36px; border-radius:4px; font-size:15px; font-weight:600; }
  .url-box { background:#f8f3e9; border:1px solid #e8dfc8; border-radius:4px; padding:12px 16px; font-size:12px; color:#6c665e; word-break:break-all; margin-top:8px; }
  .footer { background:#f8f3e9; padding:18px 36px; text-align:center; font-size:12px; color:#9a9288; border-top:1px solid #ece8de; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>Batik Penawuo</h1>
  </div>
  <div class="body">
    <p>Halo, <strong>{{ $recipientName }}</strong>.</p>
    <p>Kami menerima permintaan untuk mereset kata sandi akun Anda. Klik tombol di bawah untuk membuat kata sandi baru:</p>
    <div class="btn-wrap">
      <a href="{{ $resetUrl }}" class="btn">Reset Kata Sandi</a>
    </div>
    <p style="font-size:13px; color:#888;">Jika tombol tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:</p>
    <div class="url-box">{{ $resetUrl }}</div>
    <p style="margin-top:24px; font-size:13px; color:#888;">
      Tautan ini akan kedaluwarsa dalam <strong>60 menit</strong>.<br>
      Jika Anda tidak meminta reset kata sandi, abaikan email ini — akun Anda tetap aman.
    </p>
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Batik Penawuo &nbsp;|&nbsp; Email ini dikirim otomatis, jangan dibalas.
  </div>
</div>
</body>
</html>
