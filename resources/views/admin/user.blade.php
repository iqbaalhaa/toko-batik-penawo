@extends('layouts.admin')

@section('title', 'Kelola User')
@section('page_title', 'Kelola User')
@section('page_subtitle', 'Kelola akun admin dan pelanggan toko')

@section('content')
	@php
		$roleBadge = ['admin' => 'badge-danger', 'staff' => 'badge-info', 'pelanggan' => 'badge-brand'];
		$roleLabel = ['admin' => 'Admin', 'staff' => 'Staff', 'pelanggan' => 'Pelanggan'];
		$registeredThisMonth = $users->filter(fn($u) => $u->created_at && $u->created_at->isSameMonth(now()))->count();
		$authUser = session('auth_user');
	@endphp

	@if($errors->any())
		<div class="admin-card" style="background:#fbe4df; border-color:#f2c6be; color:#a5432f;">
			<strong>Mohon periksa kembali:</strong>
			<ul style="margin:6px 0 0 18px;">
				@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
			</ul>
		</div>
	@endif

	<div class="stat-grid" style="margin-bottom:22px;">
		<div class="stat-card">
			<div class="stat-card-icon bg-brand"><i class="fa fa-users"></i></div>
			<div>
				<div class="stat-card-label">Total User</div>
				<div class="stat-card-value">{{ $users->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-red"><i class="fa fa-shield"></i></div>
			<div>
				<div class="stat-card-label">Admin &amp; Staff</div>
				<div class="stat-card-value">{{ $users->whereIn('role', ['admin','staff'])->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-green"><i class="fa fa-user-circle"></i></div>
			<div>
				<div class="stat-card-label">Pelanggan</div>
				<div class="stat-card-value">{{ $users->where('role','pelanggan')->count() }}</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-card-icon bg-blue"><i class="fa fa-user-plus"></i></div>
			<div>
				<div class="stat-card-label">Registrasi Bulan Ini</div>
				<div class="stat-card-value">{{ $registeredThisMonth }}</div>
				<div class="stat-card-trend"><i class="fa fa-calendar"></i> {{ now()->translatedFormat('F Y') }}</div>
			</div>
		</div>
	</div>

	<div class="admin-card">
		<div class="admin-card-header">
			<div>
				<h3 class="admin-card-title">Daftar User</h3>
				<div class="admin-card-sub">Kelola akses akun pengguna</div>
			</div>
			<button type="button" class="btn-admin" onclick="openUserModal()"><i class="fa fa-user-plus"></i> Tambah User</button>
		</div>

		<div class="toolbar">
			<div class="toolbar-search">
				<i class="fa fa-search"></i>
				<input type="text" class="form-control-admin" id="userSearch" placeholder="Cari nama atau email...">
			</div>
			<select class="form-control-admin" id="userRoleFilter" style="max-width:160px;">
				<option value="">Semua Peran</option>
				<option value="admin">Admin</option>
				<option value="staff">Staff</option>
				<option value="pelanggan">Pelanggan</option>
			</select>
			<select class="form-control-admin" id="userStatusFilter" style="max-width:150px;">
				<option value="">Semua Status</option>
				<option value="aktif">Aktif</option>
				<option value="nonaktif">Nonaktif</option>
			</select>
		</div>

		<div style="overflow-x:auto;">
			<table class="admin-table" id="userTable">
				<thead>
					<tr>
						<th>User</th>
						<th>Peran</th>
						<th>Bergabung</th>
						<th>Total Pesanan</th>
						<th>Status</th>
						<th style="width:140px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@forelse($users as $u)
					<tr data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}" data-role="{{ $u->role }}" data-status="{{ $u->status }}">
						<td>
							<div style="display:flex; align-items:center; gap:12px;">
								<div style="width:38px; height:38px; border-radius:50%; background:#c29e5c; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:13px;">
									{{ strtoupper(substr($u->name,0,2)) }}
								</div>
								<div>
									<div style="font-weight:500; color:#2d2a26;">{{ $u->name }}</div>
									<div style="font-size:12px; color:#9a9288;">{{ $u->email }}</div>
								</div>
							</div>
						</td>
						<td><span class="badge-pill {{ $roleBadge[$u->role] ?? 'badge-muted' }}">{{ $roleLabel[$u->role] ?? $u->role }}</span></td>
						<td>{{ $u->created_at?->format('d M Y') ?? '—' }}</td>
						<td>{{ $u->orders_count ?? 0 }}</td>
						<td>
							@if($u->status === 'aktif')
								<span class="badge-pill badge-success">Aktif</span>
							@else
								<span class="badge-pill badge-muted">Nonaktif</span>
							@endif
						</td>
						<td>
							@php
								// Pra-encode payload aksi — hindari @json multi-baris yang
								// membingungkan parser Blade ketika array berisi nullsafe `?->`.
								$detailPayload = json_encode([
									'id'           => $u->id,
									'name'         => $u->name,
									'email'        => $u->email,
									'phone'        => $u->phone,
									'role'         => $u->role,
									'role_label'   => $roleLabel[$u->role] ?? $u->role,
									'status'       => $u->status,
									'orders_count' => $u->orders_count ?? 0,
									'created_at'   => $u->created_at?->translatedFormat('d F Y, H:i'),
								], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
								$editPayload = json_encode([
									'id'     => $u->id,
									'name'   => $u->name,
									'email'  => $u->email,
									'phone'  => $u->phone,
									'role'   => $u->role,
									'status' => $u->status,
								], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
							@endphp
							<button type="button" class="btn-admin-icon" title="Detail"
								onclick='openUserDetail({!! $detailPayload !!})'><i class="fa fa-eye"></i></button>
							<button type="button" class="btn-admin-icon" title="Edit"
								onclick='openUserModal({!! $editPayload !!})'><i class="fa fa-pencil-square-o"></i></button>
							@if($authUser && (int) $authUser['id'] === (int) $u->id)
								<button type="button" class="btn-admin-icon" title="Tidak dapat menghapus akun sendiri" disabled style="opacity:.4; cursor:not-allowed;"><i class="fa fa-trash-o"></i></button>
							@else
								<form action="{{ route('admin.user.destroy', $u) }}" method="POST" style="display:inline;"
									data-confirm-title="Hapus user?"
									data-confirm-message='User "{{ $u->name }}" ({{ $u->email }}) akan dihapus permanen beserta akses login dan riwayat alamatnya.{{ $u->orders_count ? " Pesanan akan tetap tersimpan tetapi tidak lagi terhubung ke akun." : "" }}'
									data-confirm-ok="Hapus User">
									@csrf @method('DELETE')
									<button type="submit" class="btn-admin-icon danger" title="Hapus"><i class="fa fa-trash-o"></i></button>
								</form>
							@endif
						</td>
					</tr>
					@empty
					<tr><td colspan="6" style="text-align:center; padding:24px; color:#9a9288;">Belum ada user</td></tr>
					@endforelse
				</tbody>
			</table>
			<div id="userEmptyFilter" style="display:none; text-align:center; padding:24px; color:#9a9288;">
				Tidak ada user yang cocok dengan filter.
			</div>
		</div>

		<div style="display:flex; justify-content:space-between; align-items:center; padding-top:18px; border-top:1px solid #f2efe7; margin-top:8px;">
			<div style="font-size:13px; color:#9a9288;" id="userPageInfo">Menampilkan {{ $users->count() }} user</div>
		</div>
	</div>

	<!-- ====================== User Add/Edit Modal ====================== -->
	<div class="cms-modal-overlay" id="userModal" style="display:none;">
		<div class="cms-modal" style="max-width:540px;">
			<div class="cms-modal-head">
				<h4 id="userModalTitle">Tambah User</h4>
				<button type="button" class="cms-modal-close" onclick="closeUserModal()"><i class="fa fa-times"></i></button>
			</div>
			<form id="userForm" action="{{ route('admin.user.store') }}" method="POST">
				@csrf
				<input type="hidden" name="_method" id="userFormMethod" value="POST">

				<div style="margin-bottom:12px;">
					<label class="form-label-admin">Nama Lengkap <span style="color:#a5432f;">*</span></label>
					<input type="text" name="name" id="userName" class="form-control-admin" required maxlength="60">
				</div>
				<div class="row">
					<div class="col-md-7" style="margin-bottom:12px;">
						<label class="form-label-admin">Email <span style="color:#a5432f;">*</span></label>
						<input type="email" name="email" id="userEmail" class="form-control-admin" required maxlength="120">
					</div>
					<div class="col-md-5" style="margin-bottom:12px;">
						<label class="form-label-admin">Telepon</label>
						<input type="text" name="phone" id="userPhone" class="form-control-admin" maxlength="30" placeholder="08xx xxxx xxxx">
					</div>
				</div>
				<div style="margin-bottom:12px;">
					<label class="form-label-admin">
						Password
						<span id="userPwdReq" style="color:#a5432f;">*</span>
						<small id="userPwdHint" style="color:#9a9288; font-weight:400; display:none;">— kosongkan jika tidak ingin diubah</small>
					</label>
					<input type="password" name="password" id="userPassword" class="form-control-admin" minlength="6" autocomplete="new-password">
				</div>
				<div class="row">
					<div class="col-md-6" style="margin-bottom:12px;">
						<label class="form-label-admin">Peran <span style="color:#a5432f;">*</span></label>
						<select name="role" id="userRole" class="form-control-admin" required>
							<option value="pelanggan">Pelanggan</option>
							<option value="staff">Staff</option>
							<option value="admin">Admin</option>
						</select>
					</div>
					<div class="col-md-6" style="margin-bottom:12px;">
						<label class="form-label-admin">Status <span style="color:#a5432f;">*</span></label>
						<select name="status" id="userStatus" class="form-control-admin" required>
							<option value="aktif">Aktif</option>
							<option value="nonaktif">Nonaktif</option>
						</select>
					</div>
				</div>

				<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:14px; text-align:right;">
					<button type="button" class="btn-admin btn-admin-outline" onclick="closeUserModal()">Batal</button>
					<button type="submit" class="btn-admin"><i class="fa fa-floppy-o"></i> Simpan</button>
				</div>
			</form>
		</div>
	</div>

	<!-- ====================== User Detail Modal ====================== -->
	<div class="cms-modal-overlay" id="userDetailModal" style="display:none;">
		<div class="cms-modal" style="max-width:480px;">
			<div class="cms-modal-head">
				<h4>Detail User</h4>
				<button type="button" class="cms-modal-close" onclick="closeUserDetail()"><i class="fa fa-times"></i></button>
			</div>
			<div id="userDetailBody"></div>
			<div style="padding-top:14px; border-top:1px solid #f2efe7; margin-top:14px; text-align:right;">
				<button type="button" class="btn-admin btn-admin-outline" onclick="closeUserDetail()">Tutup</button>
			</div>
		</div>
	</div>

@endsection

@push('styles')
<style>
	/* Modal — definisinya identik dengan tab CMS supaya tampilan seragam.
	   (Idealnya dipindah ke layout admin agar bisa dipakai semua halaman.) */
	.cms-modal-overlay {
		position: fixed; inset: 0; background: rgba(0,0,0,.45);
		z-index: 1200; display: flex; align-items: flex-start; justify-content: center;
		padding: 60px 20px; overflow-y: auto;
	}
	.cms-modal {
		background: #fff; border-radius: 8px; width: 100%; max-width: 720px;
		padding: 22px 24px; box-shadow: 0 20px 60px rgba(0,0,0,.25);
	}
	.cms-modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f2efe7; }
	.cms-modal-head h4 { margin: 0; font-size: 16px; color: #2d2a26; font-weight: 600; }
	.cms-modal-close { background: none; border: 0; font-size: 18px; color: #9a9288; cursor: pointer; }
	.cms-modal-close:hover { color: #2d2a26; }
</style>
@endpush

@push('scripts')
<script>
	(function() {
		var formEl = document.getElementById('userForm');
		var titleEl = document.getElementById('userModalTitle');
		var methodEl = document.getElementById('userFormMethod');
		var pwdEl = document.getElementById('userPassword');
		var pwdReq = document.getElementById('userPwdReq');
		var pwdHint = document.getElementById('userPwdHint');

		window.openUserModal = function(u) {
			formEl.reset();
			if (u) {
				titleEl.textContent = 'Edit User';
				formEl.action = '{{ url('admin/user') }}/' + u.id;
				methodEl.value = 'PUT';
				document.getElementById('userName').value = u.name || '';
				document.getElementById('userEmail').value = u.email || '';
				document.getElementById('userPhone').value = u.phone || '';
				document.getElementById('userRole').value = u.role || 'pelanggan';
				document.getElementById('userStatus').value = u.status || 'aktif';
				// Edit: password optional
				pwdEl.required = false;
				pwdReq.style.display = 'none';
				pwdHint.style.display = 'inline';
			} else {
				titleEl.textContent = 'Tambah User';
				formEl.action = '{{ route('admin.user.store') }}';
				methodEl.value = 'POST';
				document.getElementById('userRole').value = 'pelanggan';
				document.getElementById('userStatus').value = 'aktif';
				// Tambah: password required
				pwdEl.required = true;
				pwdReq.style.display = 'inline';
				pwdHint.style.display = 'none';
			}
			document.getElementById('userModal').style.display = 'flex';
		};
		window.closeUserModal = function() {
			document.getElementById('userModal').style.display = 'none';
		};

		// Detail modal
		window.openUserDetail = function(u) {
			var statusBadge = u.status === 'aktif'
				? '<span class="badge-pill badge-success">Aktif</span>'
				: '<span class="badge-pill badge-muted">Nonaktif</span>';
			var rows = [
				['Nama',          escapeHtml(u.name)],
				['Email',         escapeHtml(u.email)],
				['Telepon',       escapeHtml(u.phone || '—')],
				['Peran',         escapeHtml(u.role_label || u.role)],
				['Status',        statusBadge],
				['Total Pesanan', u.orders_count + ' pesanan'],
				['Bergabung',     escapeHtml(u.created_at || '—')],
			];
			var html = rows.map(function(r){
				return '<div style="display:flex; gap:14px; padding:8px 0; font-size:13.5px; border-bottom:1px solid #f2efe7;">'
					+   '<span style="width:120px; color:#9a9288;">' + r[0] + '</span>'
					+   '<span style="flex:1; color:#4d4640;">' + r[1] + '</span>'
					+ '</div>';
			}).join('');
			document.getElementById('userDetailBody').innerHTML = html;
			document.getElementById('userDetailModal').style.display = 'flex';
		};
		window.closeUserDetail = function() {
			document.getElementById('userDetailModal').style.display = 'none';
		};

		function escapeHtml(s) {
			return String(s == null ? '' : s)
				.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;').replace(/'/g, '&#039;');
		}

		// ----- Toolbar: search + role + status filters -----
		var search   = document.getElementById('userSearch');
		var roleSel  = document.getElementById('userRoleFilter');
		var statSel  = document.getElementById('userStatusFilter');
		var emptyMsg = document.getElementById('userEmptyFilter');
		var pageInfo = document.getElementById('userPageInfo');
		var rows     = document.querySelectorAll('#userTable tbody tr[data-name]');

		function applyFilters() {
			var q     = (search.value || '').trim().toLowerCase();
			var role  = roleSel.value;
			var stat  = statSel.value;
			var shown = 0;
			rows.forEach(function(row) {
				var matchQ    = ! q || row.dataset.name.indexOf(q) >= 0 || row.dataset.email.indexOf(q) >= 0;
				var matchRole = ! role || row.dataset.role === role;
				var matchStat = ! stat || row.dataset.status === stat;
				var visible   = matchQ && matchRole && matchStat;
				row.style.display = visible ? '' : 'none';
				if (visible) shown++;
			});
			emptyMsg.style.display = (shown === 0 && rows.length > 0) ? 'block' : 'none';
			pageInfo.textContent = 'Menampilkan ' + shown + ' dari ' + rows.length + ' user';
		}
		[search, roleSel, statSel].forEach(function(el){
			if (! el) return;
			el.addEventListener('input', applyFilters);
			el.addEventListener('change', applyFilters);
		});
	})();
</script>
@endpush
