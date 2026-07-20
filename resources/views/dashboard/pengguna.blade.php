@extends('layouts.app')
@section('title', 'Manajemen Pengguna')

@section('content')
<div style="display:flex; flex-direction:column; gap:25px;">

    <h2 class="page-title">
        <i data-lucide="users" style="width:28px;height:28px;color:#2563eb;"></i>
        Manajemen Pengguna
    </h2>

    {{-- KARTU STATISTIK --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:15px;">
        <div class="metric-card" style="padding:15px 20px;">
            <div class="metric-icon" style="background:#eaf4fb; width:40px; height:40px; margin-bottom:8px;">
                <i data-lucide="users" style="width:20px;height:20px;color:#3498db;"></i>
            </div>
            <span class="metric-label" style="font-size:10px;">Total Pengguna</span>
            <h2 class="metric-value" style="color:#3498db; font-size:22px;">{{ $stats['total'] }}</h2>
        </div>
        <div class="metric-card" style="padding:15px 20px;">
            <div class="metric-icon" style="background:#e9f7ef; width:40px; height:40px; margin-bottom:8px;">
                <i data-lucide="shield" style="width:20px;height:20px;color:#2ecc71;"></i>
            </div>
            <span class="metric-label" style="font-size:10px;">Admin</span>
            <h2 class="metric-value" style="color:#2ecc71; font-size:22px;">{{ $stats['admin'] }}</h2>
        </div>
        <div class="metric-card" style="padding:15px 20px;">
            <div class="metric-icon" style="background:#fef9e7; width:40px; height:40px; margin-bottom:8px;">
                <i data-lucide="building" style="width:20px;height:20px;color:#f1c40f;"></i>
            </div>
            <span class="metric-label" style="font-size:10px;">Pegawai</span>
            <h2 class="metric-value" style="color:#f1c40f; font-size:22px;">{{ $stats['pegawai'] }}</h2>
        </div>
        <div class="metric-card" style="padding:15px 20px;">
            <div class="metric-icon" style="background:#fdedec; width:40px; height:40px; margin-bottom:8px;">
                <i data-lucide="user-minus" style="width:20px;height:20px;color:#e74c3c;"></i>
            </div>
            <span class="metric-label" style="font-size:10px;">Nonaktif</span>
            <h2 class="metric-value" style="color:#e74c3c; font-size:22px;">{{ $stats['nonaktif'] }}</h2>
        </div>
    </div>

    {{-- TOOLBAR (sesuai desain: search | Semua Divisi | Semua Role | Semua Status | Template | Upload CSV | + Manual) --}}
    <div class="card">
        <div class="toolbar" style="flex-wrap:wrap; gap:10px;">
            {{-- Filter form (auto-submit) --}}
            <form method="GET" action="{{ route('pengguna.index') }}" id="pengguna-filter-form" style="display:contents;">
                {{-- Search --}}
                <div class="search-bar" style="min-width:220px; flex:1;">
                    <i data-lucide="search" class="search-icon" style="width:18px;height:18px;"></i>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari nama, email, atau jabatan..."
                           onkeyup="debounceSubmitPengguna(this.form)">
                </div>

                {{-- Semua Divisi (urutan pertama sesuai gambar) --}}
                <select name="divisi" class="form-select" style="width:auto; min-width:150px;"
                        onchange="this.form.submit()">
                    <option value="Semua" {{ $divisiFilter==='Semua' ? 'selected' : '' }}>Semua Divisi</option>
                    @foreach($divisiList as $div)
                        <option value="{{ $div }}" {{ $divisiFilter===$div ? 'selected' : '' }}>{{ $div }}</option>
                    @endforeach
                </select>

                {{-- Semua Role --}}
                <select name="role" class="form-select" style="width:auto; min-width:130px;"
                        onchange="this.form.submit()">
                    <option value="Semua" {{ $roleFilter==='Semua' ? 'selected' : '' }}>Semua Role</option>
                    <option value="Admin" {{ $roleFilter==='Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Pegawai" {{ $roleFilter==='Pegawai' ? 'selected' : '' }}>Pegawai</option>
                    <option value="Pemimpin" {{ $roleFilter==='Pemimpin' ? 'selected' : '' }}>Pemimpin</option>
                </select>

                {{-- Semua Status --}}
                <select name="status" class="form-select" style="width:auto; min-width:140px;"
                        onchange="this.form.submit()">
                    <option value="Semua" {{ $statusFilter==='Semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Aktif" {{ $statusFilter==='Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Nonaktif" {{ $statusFilter==='Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </form>

            {{-- Action buttons (di luar form GET) --}}
            <div style="display:flex; gap:8px; align-items:center;">
                <a href="{{ route('pengguna.index') }}?download_template=1" style="display:flex; align-items:center; gap:8px; padding:10px 15px; background:white; color:#475569; border:1px solid #cbd5e1; border-radius:8px; font-weight:bold; font-size:13px; cursor:pointer; text-decoration:none; transition:0.2s;" title="Unduh Format Excel (CSV)">
                    <i data-lucide="download" style="width:16px;height:16px;"></i> Template
                </a>
                <button type="button" onclick="document.getElementById('csv-input-pengguna').click()" style="display:flex; align-items:center; gap:8px; padding:10px 15px; background:#10b981; color:white; border:none; border-radius:8px; font-weight:bold; font-size:13px; cursor:pointer; transition:0.2s;">
                    <i data-lucide="upload" style="width:16px;height:16px;"></i> Upload CSV
                </button>
                <button type="button" onclick="openModal('add-modal')" style="display:flex; align-items:center; gap:8px; padding:10px 15px; background:#2563eb; color:white; border:none; border-radius:8px; font-weight:bold; font-size:13px; cursor:pointer;">
                    <i data-lucide="plus" style="width:16px;height:16px;"></i> Manual
                </button>
            </div>
        </div>
    </div>

    {{-- FORM UPLOAD CSV PENGGUNA (tersembunyi) --}}
    <form method="POST" action="{{ route('pengguna.uploadExcel') }}" enctype="multipart/form-data" id="csv-form-pengguna" style="display:none;">
        @csrf
        <input type="file" name="file_excel" accept=".csv,.xlsx,.xls" id="csv-input-pengguna" onchange="submitPenggunaCsvForm()">
    </form>

    {{-- TABEL PENGGUNA --}}
    <div class="card table-container">
        <table style="min-width:900px;">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Jabatan</th>
                    <th>Divisi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengguna as $p)
                <tr>
                    <td>
                        <div style="display:flex; justify-content:center;">
                            @if($p->foto_profil)
                                <img src="{{ asset('uploads/'.$p->foto_profil) }}" alt="" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                            @else
                                <div style="width:38px;height:38px;background:#e2e8f0;border-radius:50%;display:flex;justify-content:center;align-items:center;">
                                    <i data-lucide="user" style="width:18px;height:18px;color:#94a3b8;"></i>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td style="color:black;">{{ $p->nama_lengkap }}</td>
                    <td style="color:black;">{{ $p->email }}</td>
                    <td style="color:black;">{{ ucfirst($p->role) }}</td>
                    <td style="color:black;">{{ $p->jabatan ?? '-' }}</td>
                    <td style="color:black;">{{ $p->divisi ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $p->is_verified ? 'badge-success' : 'badge-gray' }}">
                            {{ $p->is_verified ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                            <button class="btn btn-secondary btn-sm" onclick='openEditUserModal(@json($p))'>
                                <i data-lucide="edit" style="width:14px;height:14px;"></i>
                            </button>
                            <form method="POST" action="{{ route('pengguna.toggle', $p->id_user) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $p->is_verified ? 'btn-warning' : 'btn-success' }}" title="{{ $p->is_verified ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i data-lucide="{{ $p->is_verified ? 'user-minus' : 'user-check' }}" style="width:14px;height:14px;"></i>
                                </button>
                            </form>
                            @if($p->id_user !== auth()->id())
                            <button class="btn btn-danger btn-sm" onclick='openDeleteUserModal({{ $p->id_user }}, "{{ $p->nama_lengkap }}")'>
                                <i data-lucide="trash" style="width:14px;height:14px;"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8; font-size:13px;">Tidak ada pengguna ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL TAMBAH PENGGUNA --}}
<div class="modal-overlay" id="add-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Pengguna Baru</h3>
            <button class="modal-close" onclick="closeModal('add-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form method="POST" action="{{ route('pengguna.store') }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Kata Sandi</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="add-password" class="form-control" required minlength="6" style="padding-right:45px;">
                        <button type="button" onclick="togglePassword('add-password', 'add-eye-icon')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; color:#94a3b8;" title="Tampilkan/Sembunyikan Kata Sandi">
                            <span id="add-eye-icon" style="display:flex;">
                                <i data-lucide="eye" style="width:18px;height:18px;"></i>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control form-select">
                        <option value="pegawai">Pegawai</option>
                        <option value="admin">Admin</option>
                        <option value="pemimpin">Pemimpin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control" placeholder="Contoh: 199001012020121001">
                </div>
                <div class="form-group">
                    <label class="form-label">NIP BPS</label>
                    <input type="text" name="nip_bps" class="form-control" placeholder="Contoh: 340012345">
                </div>

                <div class="form-group">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Staff IT">
                </div>
                <div class="form-group">
                    <label class="form-label">Divisi</label>
                    <select name="divisi" class="form-control form-select">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisiList as $div)
                            <option value="{{ $div }}">{{ $div }}</option>
                        @endforeach
                        <option value="_custom">+ Divisi Lain (isi manual)</option>
                    </select>
                    <input type="text" id="add-divisi-custom" name="divisi_custom"
                           class="form-control" style="display:none; margin-top:6px;"
                           placeholder="Tulis nama divisi baru">
                </div>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('add-modal')">Batal</button>
                <button type="submit" class="btn btn-primary" onclick="handleAddDivisiSubmit(event, this.form)">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT PENGGUNA --}}
<div class="modal-overlay" id="edit-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Pengguna</h3>
            <button class="modal-close" onclick="closeModal('edit-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form method="POST" id="edit-user-form">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="edit-nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kata Sandi Baru <small style="color:#94a3b8;">(opsional)</small></label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="edit-password" class="form-control" minlength="6" style="padding-right:45px;">
                        <button type="button" onclick="togglePassword('edit-password', 'edit-eye-icon')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; color:#94a3b8;" title="Tampilkan/Sembunyikan Kata Sandi">
                            <span id="edit-eye-icon" style="display:flex;">
                                <i data-lucide="eye" style="width:18px;height:18px;"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" id="edit-role" class="form-control form-select">
                        <option value="pegawai">Pegawai</option>
                        <option value="admin">Admin</option>
                        <option value="pemimpin">Pemimpin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit-status" class="form-control form-select">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" id="edit-nip" class="form-control" placeholder="Contoh: 199001012020121001">
                </div>
                <div class="form-group">
                    <label class="form-label">NIP BPS</label>
                    <input type="text" name="nip_bps" id="edit-nip-bps" class="form-control" placeholder="Contoh: 340012345">
                </div>

                <div class="form-group">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" id="edit-jabatan" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Divisi</label>
                    <select name="divisi" id="edit-divisi-select" class="form-control form-select"
                            onchange="handleEditDivisiChange(this)">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisiList as $div)
                            <option value="{{ $div }}">{{ $div }}</option>
                        @endforeach
                        <option value="_custom">+ Divisi Lain (isi manual)</option>
                    </select>
                    <input type="text" id="edit-divisi-custom" name="divisi_custom"
                           class="form-control" style="display:none; margin-top:6px;"
                           placeholder="Tulis nama divisi baru">
                </div>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('edit-modal')">Batal</button>
                <button type="submit" class="btn btn-primary" onclick="handleEditDivisiSubmit(event, this.form)">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="modal-overlay" id="delete-user-modal">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <h3 style="color:#e74c3c;">Hapus Pengguna</h3>
            <button class="modal-close" onclick="closeModal('delete-user-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <p style="color:#475569; font-size:14px; margin-bottom:20px;">
            Apakah Anda yakin ingin menghapus <strong id="delete-user-name"></strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form method="POST" id="delete-user-form">
            @csrf
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('delete-user-modal')">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="trash" style="width:16px;height:16px;"></i> Hapus
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ===== MODAL =====
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('open'); });
});

// ===== DEBOUNCE SEARCH =====
let _debounceTimer;
function debounceSubmitPengguna(form) {
    clearTimeout(_debounceTimer);
    _debounceTimer = setTimeout(() => form.submit(), 500);
}

// ===== EDIT USER MODAL =====
function openEditUserModal(user) {
    document.getElementById('edit-user-form').action = '/dashboard/pengguna/' + user.id_user + '/update';
    document.getElementById('edit-nama').value = user.nama_lengkap;
    document.getElementById('edit-role').value = user.role;
    document.getElementById('edit-status').value = user.is_verified ? 'Aktif' : 'Nonaktif';
    document.getElementById('edit-jabatan').value = user.jabatan || '';
    document.getElementById('edit-nip').value = user.nip || '';
    document.getElementById('edit-nip-bps').value = user.nip_bps || '';

    // Set divisi dropdown
    const divisiSelect = document.getElementById('edit-divisi-select');
    const divisiCustom = document.getElementById('edit-divisi-custom');
    const divisiVal    = user.divisi || '';
    const optExist     = Array.from(divisiSelect.options).find(o => o.value === divisiVal);

    if (!divisiVal) {
        divisiSelect.value = '';
        divisiCustom.style.display = 'none';
    } else if (optExist) {
        divisiSelect.value = divisiVal;
        divisiCustom.style.display = 'none';
    } else {
        divisiSelect.value = '_custom';
        divisiCustom.style.display = 'block';
        divisiCustom.value = divisiVal;
    }

    divisiSelect.name = 'divisi';
    divisiCustom.name = 'divisi_custom';
    openModal('edit-modal');
}

function handleEditDivisiChange(select) {
    const custom = document.getElementById('edit-divisi-custom');
    if (select.value === '_custom') {
        custom.style.display = 'block';
        custom.focus();
    } else {
        custom.style.display = 'none';
        custom.value = '';
    }
}

function handleEditDivisiSubmit(e, form) {
    const select = document.getElementById('edit-divisi-select');
    const custom = document.getElementById('edit-divisi-custom');
    if (select.value === '_custom') {
        if (!custom.value.trim()) { e.preventDefault(); custom.focus(); return; }
        select.removeAttribute('name');
        custom.name = 'divisi';
    } else {
        select.name = 'divisi';
        custom.name = 'divisi_custom';
    }
}

// ===== ADD USER - DIVISI CUSTOM =====
const addDivisiSelect = document.querySelector('#add-modal select[name="divisi"]');
if (addDivisiSelect) {
    addDivisiSelect.addEventListener('change', function() {
        const custom = document.getElementById('add-divisi-custom');
        if (this.value === '_custom') {
            custom.style.display = 'block';
            custom.focus();
        } else {
            custom.style.display = 'none';
            custom.value = '';
        }
    });
}

function handleAddDivisiSubmit(e, form) {
    const select = form.querySelector('select[name="divisi"]');
    const custom = document.getElementById('add-divisi-custom');
    if (select && select.value === '_custom') {
        if (!custom.value.trim()) { e.preventDefault(); custom.focus(); return; }
        select.removeAttribute('name');
        custom.name = 'divisi';
    }
}

// ===== DELETE USER =====
function openDeleteUserModal(id, name) {
    document.getElementById('delete-user-name').textContent = name;
    document.getElementById('delete-user-form').action = '/dashboard/pengguna/' + id + '/delete';
    openModal('delete-user-modal');
}

// ===== CSV UPLOAD =====
function submitPenggunaCsvForm() {
    const fileInput = document.getElementById('csv-input-pengguna');
    if (fileInput.files && fileInput.files[0]) {
        document.getElementById('csv-form-pengguna').submit();
    }
}

// ===== TOGGLE PASSWORD =====
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        container.innerHTML = '<i data-lucide="eye-off" style="width:18px;height:18px;"></i>';
    } else {
        input.type = 'password';
        container.innerHTML = '<i data-lucide="eye" style="width:18px;height:18px;"></i>';
    }
    lucide.createIcons();
}
</script>
@endpush
