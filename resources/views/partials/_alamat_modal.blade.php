{{--
    Modal tambah/edit alamat — bisa dipakai di profil maupun checkout.
    Variabel opsional:
      $redirectTo : nama route tujuan setelah simpan (default: 'akun.profil')
      $inputClass : class CSS untuk input (default: 'akun-input')
      $btnClass   : class CSS untuk tombol primer (default: 'akun-btn')

    Cara pakai:
      @include('partials._alamat_modal', ['redirectTo' => 'checkout.show'])
      lalu panggil `openAlamatModal()` atau `openAlamatModal({...address})` dari elemen lain.
--}}
@php
    $redirectTo = $redirectTo ?? 'akun.profil';
    $inputClass = $inputClass ?? 'akun-input';
    $btnClass   = $btnClass   ?? 'akun-btn';
@endphp

<div id="alamatModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1050; overflow-y:auto; -webkit-overflow-scrolling:touch;" onclick="if(event.target===this) closeAlamatModal();">
	<div style="min-height:100%; display:flex; flex-direction:column; padding:40px 16px; box-sizing:border-box;">
		<div style="margin:auto; background:#fff; border-radius:6px; max-width:640px; width:100%; padding:24px 26px; box-shadow:0 8px 30px rgba(0,0,0,.2);" onclick="event.stopPropagation();">
			<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
				<h3 id="alamatModalTitle" style="margin:0; font-size:16px; font-weight:600; color:#2d2a26;">Tambah Alamat</h3>
				<button type="button" onclick="closeAlamatModal()" style="background:none; border:0; font-size:18px; color:#9a9288; cursor:pointer;">&times;</button>
			</div>
			<form id="alamatForm" method="POST" action="">
				@csrf
				<input type="hidden" name="_method" id="alamatMethod" value="POST">
				{{-- redirect_to: di-whitelist server-side. --}}
				<input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

				<div class="row" data-wilayah-form id="alamatWilayah" data-init-province="" data-init-city="" data-init-district="">
					<div class="col-md-6" style="margin-bottom:14px;">
						<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Label Alamat <span style="color:#a5432f;">*</span></label>
						<input type="text" name="label" id="al_label" class="{{ $inputClass }}" placeholder="Mis. Rumah, Kantor" maxlength="30" required>
					</div>
					<div class="col-md-6" style="margin-bottom:14px; display:flex; align-items:flex-end;">
						<label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#4d4640; cursor:pointer;">
							<input type="checkbox" name="is_default" id="al_is_default" value="1">
							Jadikan alamat utama
						</label>
					</div>
					<div class="col-md-4" style="margin-bottom:14px;">
						<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Provinsi <span style="color:#a5432f;">*</span></label>
						<select name="province_id" class="{{ $inputClass }}" data-role="province" required>
							<option value="">— Pilih provinsi —</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-bottom:14px;">
						<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Kota / Kabupaten <span style="color:#a5432f;">*</span></label>
						<select name="city_id" class="{{ $inputClass }}" data-role="regency" required disabled>
							<option value="">— Pilih kota/kabupaten —</option>
						</select>
					</div>
					<div class="col-md-4" style="margin-bottom:14px;">
						<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Kecamatan <span style="color:#a5432f;">*</span></label>
						<select name="district_id" class="{{ $inputClass }}" data-role="district" required disabled>
							<option value="">— Pilih kecamatan —</option>
						</select>
					</div>
					<div class="col-md-12" style="margin-bottom:14px;">
						<label style="display:block; font-size:13px; font-weight:500; color:#4d4640; margin-bottom:6px;">Alamat Lengkap (jalan, no rumah, RT/RW, kelurahan) <span style="color:#a5432f;">*</span></label>
						<textarea name="full_address" id="al_full_address" class="{{ $inputClass }}" rows="3" required></textarea>
					</div>
					<input type="hidden" name="province_name" data-role="province_name">
					<input type="hidden" name="city_name"     data-role="city_name">
					<input type="hidden" name="district_name" data-role="district_name">
				</div>

				<div style="display:flex; justify-content:flex-end; gap:8px; padding-top:12px; border-top:1px solid #f2efe7;">
					<button type="button" onclick="closeAlamatModal()" class="{{ $btnClass }}" style="background:#fff; color:#4d4640; border:1px solid #e0dbcf;">Batal</button>
					<button type="submit" class="{{ $btnClass }}"><i class="fa fa-floppy-o m-r-6"></i> Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
(function () {
	window.openAlamatModal = function (addr) {
		var modal = document.getElementById('alamatModal');
		var form  = document.getElementById('alamatForm');
		var wil   = document.getElementById('alamatWilayah');
		var isEdit = !!(addr && addr.id);

		document.getElementById('alamatModalTitle').textContent = isEdit ? 'Edit Alamat' : 'Tambah Alamat';
		document.getElementById('alamatMethod').value = isEdit ? 'PUT' : 'POST';
		form.action = isEdit
			? @json(url('/akun/alamat')) + '/' + addr.id
			: @json(route('akun.alamat.store'));

		document.getElementById('al_label').value         = isEdit ? (addr.label || '') : '';
		document.getElementById('al_full_address').value  = isEdit ? (addr.full_address || '') : '';
		document.getElementById('al_is_default').checked  = isEdit ? !!addr.is_default : false;

		wil.dataset.initProvince = isEdit ? (addr.province_id || '') : '';
		wil.dataset.initCity     = isEdit ? (addr.city_id || '')     : '';
		wil.dataset.initDistrict = isEdit ? (addr.district_id || '') : '';
		document.dispatchEvent(new CustomEvent('wilayah:reinit'));

		modal.style.display = 'block';
		modal.scrollTop = 0;
	};
	window.closeAlamatModal = function () {
		document.getElementById('alamatModal').style.display = 'none';
	};
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && document.getElementById('alamatModal').style.display === 'block') {
			closeAlamatModal();
		}
	});
})();
</script>
