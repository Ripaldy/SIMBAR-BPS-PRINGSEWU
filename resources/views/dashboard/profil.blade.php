@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div style="max-width:800px; margin:0 auto; font-family:system-ui,-apple-system,sans-serif;">

    <div style="background:white; border-radius:12px; padding:30px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border:1px solid #f1f5f9;">

        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:20px; margin-bottom:30px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i data-lucide="user" style="width:24px;height:24px;color:#3498db;"></i>
                <h3 style="margin:0; color:#1f4068; font-size:18px; font-weight:bold;">PENGATURAN PROFIL</h3>
            </div>

            <button id="btnEditData" onclick="toggleEditMode()" style="display:flex; align-items:center; gap:8px; padding:10px 20px; background:#3498db; color:white; border:none; border-radius:8px; font-weight:bold; font-size:13px; cursor:pointer; transition:0.2s; font-family:inherit;">
                <i data-lucide="edit" style="width:16px;height:16px;"></i> Edit Biodata
            </button>
        </div>

        @if(session('success'))
            <div style="padding:12px 15px; margin-bottom:20px; border-radius:8px; display:flex; align-items:center; gap:10px; background:#e8f8f5; color:#27ae60; font-size:13px;">
                <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div style="padding:12px 15px; margin-bottom:20px; border-radius:8px; display:flex; align-items:center; gap:10px; background:#fdedec; color:#e74c3c; font-size:13px;">
                <i data-lucide="alert-circle" style="width:18px;height:18px;"></i>
                Terjadi kesalahan. Periksa kembali isian Anda.
            </div>
        @endif

        <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" id="profilForm">
            @csrf
            <div style="display:flex; gap:40px; flex-wrap:wrap;">

                {{-- KOLOM KIRI: FOTO PROFIL & ROLE --}}
                <div style="display:flex; flex-direction:column; align-items:center; width:220px;">
                    <div style="width:160px; height:160px; border-radius:50%; background:#f1f5f9; border:4px solid white; box-shadow:0 4px 15px rgba(0,0,0,0.1); overflow:hidden; margin-bottom:20px; display:flex; justify-content:center; align-items:center; position:relative;">
                        @if($user->foto_profil)
                            <img id="previewFoto" src="{{ asset('uploads/'.$user->foto_profil) }}" alt="Profil" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <i data-lucide="user" id="placeholderFoto" style="width:60px;height:60px;color:#cbd5e1;"></i>
                            <img id="previewFoto" src="" alt="Profil" style="width:100%;height:100%;object-fit:cover;display:none;">
                        @endif
                    </div>

                    <input type="file" name="foto_profil" id="inputFoto" accept="image/png, image/jpeg" style="display:none;" onchange="handleFileChange(this)">

                    <button type="button" id="btnUbahFoto" onclick="document.getElementById('inputFoto').click()" disabled style="display:flex; align-items:center; gap:8px; padding:8px 20px; background:transparent; color:#cbd5e1; border:1px solid #e2e8f0; border-radius:20px; font-size:13px; font-weight:bold; cursor:not-allowed; margin-bottom:10px; transition:0.2s; font-family:inherit;">
                        <i data-lucide="camera" style="width:16px;height:16px;"></i> Ubah Foto
                    </button>
                    <span style="font-size:11px; color:#94a3b8; margin-bottom:25px;">Maksimal 2MB. (JPG/PNG)</span>

                    <div style="background:#eaf4fb; color:#2980b9; padding:6px 20px; border-radius:20px; font-size:11px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">
                        {{ $user->role === 'admin' ? 'ADMINISTRATOR' : ($user->role === 'pemimpin' ? 'PEMIMPIN' : 'PEGAWAI') }}
                    </div>
                </div>

                {{-- KOLOM KANAN: FORM BIODATA --}}
                <div style="flex:1; display:flex; flex-direction:column; gap:20px;">

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:8px;">
                            <i data-lucide="user" style="width:14px;height:14px;"></i> Nama Lengkap
                        </label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" disabled class="form-input" required placeholder="Belum melengkapi biodata" style="width:100%; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#1f4068; background:#f8fafc; outline:none; box-sizing:border-box; font-family:inherit;">
                    </div>

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:8px;">
                            <i data-lucide="mail" style="width:14px;height:14px;"></i> Email Utama <span style="font-size:10px; color:#e74c3c;">(Sifatnya Permanen)</span>
                        </label>
                        <input type="email" value="{{ $user->email }}" disabled style="width:100%; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#94a3b8; background:#f1f5f9; cursor:not-allowed; box-sizing:border-box; font-family:inherit;">
                    </div>

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:8px;">
                            <i data-lucide="lock" style="width:14px;height:14px;"></i> Kata Sandi <span style="font-size:10px; color:#94a3b8;">(Kosongkan jika tidak ingin diubah)</span>
                        </label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="inputPassword" disabled class="form-input" placeholder="********" style="width:100%; padding:12px 15px; padding-right:45px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#1f4068; background:#f8fafc; outline:none; box-sizing:border-box; font-family:inherit;">
                            <button type="button" onclick="togglePassword()" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center; color:#94a3b8;" title="Tampilkan/Sembunyikan Kata Sandi">
                                <span id="eyeIconContainer" style="display:flex;">
                                    <i data-lucide="eye" style="width:18px;height:18px;"></i>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div style="display:flex; gap:20px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:200px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:8px;">
                                <i data-lucide="hash" style="width:14px;height:14px;"></i> NIP Pegawai
                            </label>
                            <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" disabled class="form-input" placeholder="-" style="width:100%; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#1f4068; background:#f8fafc; outline:none; box-sizing:border-box; font-family:inherit;">
                        </div>
                        <div style="flex:1; min-width:200px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:8px;">
                                <i data-lucide="phone" style="width:14px;height:14px;"></i> No. Telepon
                            </label>
                            <input type="text" name="no_telepon" value="{{ old('no_telepon', $user->no_telepon) }}" disabled class="form-input" placeholder="-" style="width:100%; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#1f4068; background:#f8fafc; outline:none; box-sizing:border-box; font-family:inherit;">
                        </div>
                    </div>

                    <div style="display:flex; gap:20px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:200px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:8px;">
                                <i data-lucide="briefcase" style="width:14px;height:14px;"></i> Jabatan
                            </label>
                            <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" disabled class="form-input" placeholder="Contoh: Staf Lapangan" style="width:100%; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#1f4068; background:#f8fafc; outline:none; box-sizing:border-box; font-family:inherit;">
                        </div>

                        <div style="flex:1; min-width:200px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#475569; margin-bottom:8px;">
                                <i data-lucide="users" style="width:14px;height:14px;"></i> Tim / Divisi
                            </label>
                            <div id="divisi-view" style="width:100%; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#1f4068; background:#f8fafc; box-sizing:border-box;">
                                {{ $user->divisi ?: 'Belum diatur' }}
                            </div>
                            <select name="divisi" id="divisi-edit" class="form-input" style="display:none; width:100%; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; color:#1f4068; background:white; outline:none; box-sizing:border-box; font-family:inherit;">
                                <option value="">Pilih Tim / Divisi</option>
                                @php
                                    $divisiList = [
                                        'Tim Subbagian Umum', 'Tim Statistik Sosial', 'Tim Statistik Produksi',
                                        'Tim Statistik Distribusi', 'Tim Neraca Wilayah dan Analisis Statistik',
                                        'Tim Pengolahan dan IT', 'Tim Diseminasi Statistik', 'Tim Reformasi Birokrasi',
                                        'Tim Perencanaan dan Administrasi Keuangan', 'Tim Pembinaan dan Pelaksanaan Statistik Sektoral',
                                        'Umum Kantor', 'Tim Humas', 'Tim Sensus Ekonomi 2026'
                                    ];
                                @endphp
                                @foreach($divisiList as $div)
                                    <option value="{{ $div }}" {{ $user->divisi == $div ? 'selected' : '' }}>{{ $div }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
            </div>
            
            {{-- Tombol submit tersembunyi yang akan dipanggil saat tombol atas diklik dalam mode edit --}}
            <button type="submit" id="btnSubmitForm" style="display:none;"></button>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
let isEditing = false;
@if($errors->any())
    isEditing = true;
@endif

function toggleEditMode() {
    isEditing = !isEditing;
    const btnEdit = document.getElementById('btnEditData');
    const inputs = document.querySelectorAll('.form-input');
    const btnFoto = document.getElementById('btnUbahFoto');
    const divView = document.getElementById('divisi-view');
    const divEdit = document.getElementById('divisi-edit');

    if (isEditing) {
        // Berubah ke mode simpan
        btnEdit.innerHTML = `<i data-lucide="save" style="width:16px;height:16px;"></i> Simpan Perubahan`;
        btnEdit.style.backgroundColor = '#2ecc71';
        btnEdit.setAttribute('onclick', 'document.getElementById("btnSubmitForm").click()');
        
        // Aktifkan input
        inputs.forEach(input => {
            input.disabled = false;
            if(input.name === 'password') {
                input.placeholder = "Ketik kata sandi baru...";
            }
            input.style.backgroundColor = 'white';
        });

        // Aktifkan upload foto
        btnFoto.disabled = false;
        btnFoto.style.color = '#3498db';
        btnFoto.style.borderColor = '#3498db';
        btnFoto.style.cursor = 'pointer';

        // Tampilkan select divisi
        divView.style.display = 'none';
        divEdit.style.display = 'block';

    } else {
        // (Logika batal tidak diperlukan di sini karena tombol berubah menjadi submit form)
    }

    lucide.createIcons();
}

function handleFileChange(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2MB!');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('previewFoto');
            preview.src = e.target.result;
            preview.style.display = 'block';
            const placeholder = document.getElementById('placeholderFoto');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

function togglePassword() {
    const input = document.getElementById('inputPassword');
    const container = document.getElementById('eyeIconContainer');
    if (input.type === 'password') {
        input.type = 'text';
        container.innerHTML = '<i data-lucide="eye-off" style="width:18px;height:18px;"></i>';
    } else {
        input.type = 'password';
        container.innerHTML = '<i data-lucide="eye" style="width:18px;height:18px;"></i>';
    }
    lucide.createIcons();
}

if (isEditing) {
    // Jalankan satu siklus agar tampilan menyesuaikan flag if ada error
    isEditing = false; 
    toggleEditMode();
}
</script>
@endpush
