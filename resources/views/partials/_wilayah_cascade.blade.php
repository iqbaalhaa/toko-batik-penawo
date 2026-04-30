{{--
    Cascading dropdown wilayah — provinsi → kota/kab → kecamatan.

    Cara pakai: bungkus 3 <select> + 3 hidden name dengan elemen
    `[data-wilayah-form]` dan beri atribut `data-role` ke masing-masing:
      - data-role="province" / "regency" / "district" untuk select
      - data-role="province_name" / "city_name" / "district_name" untuk hidden
    Atribut `data-init-province/city/district` di root mengisi nilai awal saat edit.

    Untuk modal yang nilai awalnya berubah-ubah: dispatch
    `document.dispatchEvent(new CustomEvent('wilayah:reinit'))` setelah
    mengubah `data-init-*`.
--}}
<script>
(function () {
	var URL_PROV = @json(route('api.wilayah.provinces'));
	var URL_REG  = @json(route('api.wilayah.regencies'));
	var URL_DIST = @json(route('api.wilayah.districts'));

	function setupRoot(root) {
		// Hindari pasang listener dua kali untuk root yang sama.
		if (root.__wilayahWired) {
			loadProvinces(root);
			return;
		}
		root.__wilayahWired = true;

		var provSel = root.querySelector('[data-role="province"]');
		var citySel = root.querySelector('[data-role="regency"]');
		var distSel = root.querySelector('[data-role="district"]');
		if (!provSel || !citySel || !distSel) return;

		provSel.addEventListener('change', function () {
			updateName(root, 'province');
			clearName(root, 'city'); clearName(root, 'district');
			if (provSel.value) loadRegencies(root, provSel.value);
			else { disableSelect(citySel, '— Pilih kota/kabupaten —'); disableSelect(distSel, '— Pilih kecamatan —'); }
		});
		citySel.addEventListener('change', function () {
			updateName(root, 'city'); clearName(root, 'district');
			if (citySel.value) loadDistricts(root, citySel.value);
			else { disableSelect(distSel, '— Pilih kecamatan —'); }
		});
		distSel.addEventListener('change', function () {
			updateName(root, 'district');
		});

		loadProvinces(root);
	}

	function fill(sel, items, selectedId, placeholder) {
		sel.innerHTML = '';
		var opt0 = document.createElement('option');
		opt0.value = ''; opt0.textContent = placeholder;
		sel.appendChild(opt0);
		items.forEach(function (it) {
			var o = document.createElement('option');
			o.value = it.id;
			o.textContent = it.name;
			o.dataset.name = it.name;
			if (selectedId && String(it.id) === String(selectedId)) o.selected = true;
			sel.appendChild(o);
		});
	}

	function disableSelect(sel, placeholder) {
		sel.disabled = true;
		sel.innerHTML = '<option value="">' + placeholder + '</option>';
	}

	function selFor(root, role) { return root.querySelector('[data-role="' + role + '"]'); }
	function nameFor(root, role) { return root.querySelector('[data-role="' + role + '_name"]'); }

	function updateName(root, role) {
		var sel = selFor(root, role === 'city' ? 'regency' : role);
		var hidden = nameFor(root, role);
		if (!hidden) return;
		var opt = sel.options[sel.selectedIndex];
		hidden.value = opt ? (opt.dataset.name || '') : '';
	}
	function clearName(root, role) {
		var hidden = nameFor(root, role);
		if (hidden) hidden.value = '';
	}

	function loadJson(url) {
		return fetch(url, { headers: {'Accept':'application/json'} }).then(function (r) { return r.json(); });
	}

	function loadProvinces(root) {
		var provSel = selFor(root, 'province');
		var citySel = selFor(root, 'regency');
		var distSel = selFor(root, 'district');
		var initProv = root.dataset.initProvince || '';
		var initCity = root.dataset.initCity     || '';
		var initDist = root.dataset.initDistrict || '';

		// Reset cascade saat re-init dipanggil dari modal.
		disableSelect(citySel, '— Pilih kota/kabupaten —');
		disableSelect(distSel, '— Pilih kecamatan —');
		clearName(root, 'city'); clearName(root, 'district');

		return loadJson(URL_PROV).then(function (items) {
			fill(provSel, items, initProv, '— Pilih provinsi —');
			updateName(root, 'province');
			if (provSel.value) return loadRegencies(root, provSel.value, initCity, initDist);
		});
	}

	function loadRegencies(root, provinceId, preselectCityId, preselectDistrictId) {
		var citySel = selFor(root, 'regency');
		var distSel = selFor(root, 'district');
		citySel.disabled = false;
		disableSelect(distSel, '— Pilih kecamatan —');
		clearName(root, 'city'); clearName(root, 'district');

		return loadJson(URL_REG + '?province_id=' + encodeURIComponent(provinceId)).then(function (items) {
			fill(citySel, items, preselectCityId || '', '— Pilih kota/kabupaten —');
			updateName(root, 'city');
			if (citySel.value) return loadDistricts(root, citySel.value, preselectDistrictId);
		});
	}

	function loadDistricts(root, regencyId, preselectDistrictId) {
		var distSel = selFor(root, 'district');
		distSel.disabled = false;
		clearName(root, 'district');

		return loadJson(URL_DIST + '?regency_id=' + encodeURIComponent(regencyId)).then(function (items) {
			fill(distSel, items, preselectDistrictId || '', '— Pilih kecamatan —');
			updateName(root, 'district');
		});
	}

	function setupAll() {
		document.querySelectorAll('[data-wilayah-form]').forEach(setupRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupAll);
	} else {
		setupAll();
	}
	// Hook reload untuk modal yang ganti data-init-* lalu memicu event ini.
	document.addEventListener('wilayah:reinit', setupAll);
})();
</script>
