{{-- Modal konfirmasi global. Sertakan sekali di layout. --}}
{{-- Pakai dengan menambahkan atribut data-confirm-* pada <form>:
       data-confirm-title="..."   (judul modal, wajib agar interception aktif)
       data-confirm-message="..." (pesan)
       data-confirm-ok="..."      (label tombol konfirmasi, default "Hapus")
       data-confirm-variant="danger|success" (default "danger")
--}}
<style>
	.app-confirm-overlay {
		position: fixed; inset: 0;
		background: rgba(31, 29, 27, .55);
		display: none; align-items: center; justify-content: center;
		z-index: 1300; padding: 20px;
	}
	.app-confirm-overlay.show { display: flex; }
	.app-confirm-box {
		background: #fff; border-radius: 8px;
		max-width: 420px; width: 100%;
		box-shadow: 0 20px 60px rgba(0,0,0,.25);
		animation: appConfirmIn .2s ease-out;
		overflow: hidden;
	}
	@keyframes appConfirmIn {
		from { transform: translateY(-12px); opacity: 0; }
		to   { transform: translateY(0); opacity: 1; }
	}
	.app-confirm-icon {
		width: 56px; height: 56px; border-radius: 50%;
		background: #fbe4df; color: #a5432f;
		display: flex; align-items: center; justify-content: center;
		font-size: 26px; margin: 28px auto 16px;
	}
	.app-confirm-icon.success { background: #e3f3e9; color: #2f7a4c; font-size: 28px; }
	.app-confirm-title {
		font-size: 17px; font-weight: 600; color: #2d2a26;
		text-align: center; padding: 0 28px; margin: 0;
	}
	.app-confirm-message {
		font-size: 13.5px; color: #6c665e; text-align: center;
		padding: 8px 28px 22px; line-height: 1.55; margin: 0;
		white-space: pre-line;
	}
	.app-confirm-actions {
		display: flex; gap: 10px;
		padding: 14px 22px;
		border-top: 1px solid #f2efe7; background: #faf7ef;
	}
	.app-confirm-actions button {
		flex: 1; padding: 10px 16px;
		border-radius: 4px; font-size: 13px; font-weight: 500;
		cursor: pointer; font-family: inherit;
		transition: background .15s, color .15s, border-color .15s;
		display: inline-flex; align-items: center; justify-content: center; gap: 6px;
		border: 1px solid transparent;
	}
	.app-confirm-cancel { background: #fff; color: #4d4640; border-color: #ddd6c6; }
	.app-confirm-cancel:hover { background: #faf7ef; color: #2d2a26; border-color: #c29e5c; }
	.app-confirm-ok.danger  { background: #d86a59; color: #fff; }
	.app-confirm-ok.danger:hover  { background: #c3554a; }
	.app-confirm-ok.success { background: #56a676; color: #fff; }
	.app-confirm-ok.success:hover { background: #438a5e; }
	.app-confirm-ok:disabled { opacity: .65; cursor: not-allowed; }
</style>

<div class="app-confirm-overlay" id="appConfirmModal" role="dialog" aria-modal="true" aria-labelledby="appConfirmTitle">
	<div class="app-confirm-box">
		<div class="app-confirm-icon" id="appConfirmIcon"><i class="fa fa-exclamation-triangle"></i></div>
		<h3 class="app-confirm-title" id="appConfirmTitle">Konfirmasi</h3>
		<p class="app-confirm-message" id="appConfirmMessage">Apakah Anda yakin?</p>
		<div class="app-confirm-actions">
			<button type="button" class="app-confirm-cancel" id="appConfirmCancel">Batal</button>
			<button type="button" class="app-confirm-ok danger" id="appConfirmOk"><span id="appConfirmOkText">Hapus</span></button>
		</div>
	</div>
</div>

<script>
(function () {
	var modal     = document.getElementById('appConfirmModal');
	if (!modal) return;
	var iconWrap  = document.getElementById('appConfirmIcon');
	var iconI     = iconWrap.querySelector('i');
	var titleEl   = document.getElementById('appConfirmTitle');
	var messageEl = document.getElementById('appConfirmMessage');
	var okBtn     = document.getElementById('appConfirmOk');
	var okText    = document.getElementById('appConfirmOkText');
	var cancelBtn = document.getElementById('appConfirmCancel');

	var pendingForm = null;

	function open(form) {
		pendingForm = form;
		var d = form.dataset;
		titleEl.textContent   = d.confirmTitle   || 'Konfirmasi';
		messageEl.textContent = d.confirmMessage || 'Apakah Anda yakin?';
		okText.textContent    = d.confirmOk      || 'Hapus';

		var variant = (d.confirmVariant === 'success') ? 'success' : 'danger';
		iconWrap.className = 'app-confirm-icon ' + (variant === 'success' ? 'success' : '');
		iconI.className    = 'fa ' + (variant === 'success' ? 'fa-check-circle-o' : 'fa-exclamation-triangle');
		okBtn.className    = 'app-confirm-ok ' + variant;
		okBtn.disabled     = false;

		modal.classList.add('show');
		setTimeout(function () { cancelBtn.focus(); }, 0);
	}
	function close() {
		modal.classList.remove('show');
		pendingForm = null;
		okBtn.disabled = false;
	}

	cancelBtn.addEventListener('click', close);
	okBtn.addEventListener('click', function () {
		if (!pendingForm) return;
		okBtn.disabled = true;
		okText.textContent = 'Memproses...';
		pendingForm.dataset.confirmed = '1';
		pendingForm.submit();
	});
	modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && modal.classList.contains('show')) close();
	});

	// Intercept submit pada form dengan data-confirm-title
	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form || !form.matches || !form.matches('form[data-confirm-title]')) return;
		if (form.dataset.confirmed === '1') return;
		e.preventDefault();
		open(form);
	}, true);

	// Pemicu manual: button/link dengan data-confirm-trigger="<formId>"
	// Berguna untuk tombol di luar form atau yang men-submit lebih dari satu form.
	document.addEventListener('click', function (e) {
		var trig = e.target.closest('[data-confirm-trigger]');
		if (!trig) return;
		var form = document.getElementById(trig.getAttribute('data-confirm-trigger'));
		if (!form) return;
		e.preventDefault();
		// Pindahkan data-confirm-* dari trigger ke form sementara, supaya konfigurasi bisa per-tombol
		['confirmTitle', 'confirmMessage', 'confirmOk', 'confirmVariant'].forEach(function (k) {
			if (trig.dataset[k]) form.dataset[k] = trig.dataset[k];
		});
		open(form);
	});
})();
</script>
