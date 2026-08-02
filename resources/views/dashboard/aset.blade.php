@extends('layouts.app')
@section('title', 'Manajemen Barang / Aset')

@section('content')
<div style="display:flex; flex-direction:column; gap:25px; max-width:1300px; margin:0 auto;">

    {{-- TOOLBAR --}}
    <div class="card">
        <div class="toolbar" style="flex-wrap:wrap; gap:10px;">
            {{-- Search + Filter Kategori --}}
            <form method="GET" action="{{ route('aset.index') }}" id="filter-form-aset" style="display:flex; gap:8px; flex:1; min-width:0; flex-wrap:nowrap; align-items:center;">
                <div class="search-bar" style="width:250px; min-width:200px;">
                    <i data-lucide="search" class="search-icon" style="width:18px;height:18px;"></i>
                    <input type="text" name="search" id="searchInput" value="{{ $search }}"
                           placeholder="Cari nama / kode barang..."
                           oninput="filterTable()"
                           onkeydown="if(event.key === 'Enter') event.preventDefault();">
                </div>
                <select name="kode_kategori" class="form-select" style="max-width:220px; text-overflow:ellipsis; white-space:nowrap; overflow:hidden;" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->kode_kategori }}" {{ $filterKategori == $kat->kode_kategori ? 'selected' : '' }}>
                            {{ $kat->kode_kategori }} – {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- Info + Aksi --}}
            <div style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
                <div style="background:#eff6ff; color:#2563eb; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:bold; white-space:nowrap;">
                    {{ $barang->count() }} Barang
                </div>
                <a href="{{ route('aset.index') }}?download_template=1" class="btn btn-secondary">
                    <i data-lucide="download" style="width:16px;height:16px;"></i> Template
                </a>
                <button type="button" onclick="openModal('upload-modal')" class="btn btn-success">
                    <i data-lucide="upload" style="width:16px;height:16px;"></i> Upload Excel
                </button>
                
                <input type="file" id="mass-foto-input" multiple accept="image/*" style="display:none;" onchange="handleMassFotoUpload(event)">
                <button type="button" class="btn btn-warning" onclick="document.getElementById('mass-foto-input').click()" style="background:#f59e0b; color:white; border:none;">
                    <i data-lucide="image" style="width:16px;height:16px;"></i> Upload Gambar Barang
                </button>

                <button type="button" class="btn btn-primary" onclick="openAddModal()">
                    <i data-lucide="plus" style="width:16px;height:16px;"></i> Tambah Manual
                </button>
            </div>
        </div>
    </div>

    {{-- (Upload form dipindah ke dalam modal) --}}

    {{-- Tabel Manajemen Aset --}}
    <div class="card table-container" style="zoom: 90%; max-height:740px; overflow-y:auto; padding:0;">
        <table style="min-width:980px;">
            <thead>
                <tr>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:60px;">Foto</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:120px;">Kode Kategori</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:150px;">Kategori</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:100px;">Kode Barang</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:220px;">Nama Barang</th>

                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:120px;">Harga Satuan</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:80px;">Sisa Stok</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:80px;">Stok Min.</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:100px;">Status</th>
                    <th style="position:sticky; top:0; background:#f8fafc; z-index:10; border-bottom:1px solid #e2e8f0; background-clip:padding-box; width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barang as $item)
                    @php 
                        $isKosong = $item->stok_aktual == 0;
                        $isKritis = !$isKosong && $item->stok_minimum > 0 && $item->stok_aktual <= $item->stok_minimum; 
                    @endphp
                    <tr>
                        <td>
                            @if($item->foto_barang)
                                <img src="{{ asset('uploads/'.$item->foto_barang) }}" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
                            @else
                                <div class="photo-placeholder" style="margin:0 auto;">
                                    <i data-lucide="image" style="width:18px;height:18px;color:#cbd5e1;"></i>
                                </div>
                            @endif
                        </td>
                        <td style="font-family:monospace; font-size:12px; color:#64748b;">
                            {{ $item->kode_kategori ?? '-' }}
                        </td>
                        <td style="font-size:13px; color:black;">
                            {{ $item->nama_kategori ?? '-' }}
                        </td>
                        <td style="font-family:monospace; font-weight:bold; color:#1f4068;">
                            {{ $item->kode_barang ?? '-' }}
                        </td>
                        <td style="color:black; text-align:center;">{{ $item->nama_barang }}</td>

                        <td style="color:black; font-family:monospace;">
                            {{ $item->harga_satuan > 0 ? 'Rp '.number_format($item->harga_satuan,0,',','.') : '-' }}
                        </td>
                        <td style="color:black;">{{ $item->stok_aktual }}</td>
                        <td style="color:black;">{{ $item->stok_minimum }}</td>
                        <td>
                            @if($isKosong)
                                <span class="status-kosong">KOSONG</span>
                            @elseif($isKritis)
                                <span class="status-kritis">KRITIS</span>
                            @else
                                <span class="status-aman">AMAN</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; justify-content:center; flex-wrap:nowrap; min-width:110px;">
                                <button class="btn btn-secondary btn-sm" onclick='openEditModal(@json($item))' title="Edit">
                                    <i data-lucide="edit-3" style="width:14px;height:14px;"></i>
                                </button>
                                <button class="btn btn-warning btn-sm" onclick='openStockModal(@json($item))' title="Tambah Stok">
                                    <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick='openDeleteModal({{ $item->id_barang }}, "{{ $item->nama_barang }}")' title="Hapus">
                                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center; padding:40px; color:#94a3b8; font-size:13px;">
                            Tidak ada barang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL TAMBAH / EDIT BARANG --}}
<div class="modal-overlay" id="form-modal">
    <div class="modal" style="max-width:540px; padding:20px;">
        <div class="modal-header">
            <h3 id="modal-title">Tambah Barang</h3>
            <button class="modal-close" onclick="closeFormModal()"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form method="POST" id="barang-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="id_barang" id="field-id">

            {{-- Baris 1: Kode Kategori + Nama Kategori --}}
            <p style="font-size:11px; color:#94a3b8; margin:0 0 6px 0; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Informasi Kategori BPS</p>
            <div style="display:grid; grid-template-columns:1fr 2fr; gap:10px; margin-bottom:10px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Kode Kategori</label>
                    <input type="text" name="kode_kategori" id="field-kode-kategori" class="form-control"
                           placeholder="1010301001" style="font-family:monospace;"
                           list="kategori-suggestions" oninput="autoFillKategori(this.value)">
                    <datalist id="kategori-suggestions">
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat->kode_kategori }}" label="{{ $kat->nama_kategori }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="field-nama-kategori" class="form-control"
                           placeholder="ALAT TULIS" oninput="this.value=this.value.toUpperCase()">
                </div>
            </div>

            {{-- Baris 2: Kode Barang + Nama Barang --}}
            <p style="font-size:11px; color:#94a3b8; margin:0 0 6px 0; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Informasi Barang</p>
            <div style="display:grid; grid-template-columns:1fr 2fr; gap:10px; margin-bottom:10px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Kode Barang</label>
                    <input type="text" name="kode_barang" id="field-kode" class="form-control"
                           placeholder="000001" style="font-family:monospace;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Nama Barang</label>
                    <input type="text" name="nama_barang" id="field-nama" class="form-control" required
                           oninput="this.value=this.value.toUpperCase()">
                </div>
            </div>

            {{-- Baris 3: Satuan + Harga Satuan + Stok --}}
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Satuan <small style="color:#94a3b8;font-weight:normal;">(Opsional)</small></label>
                    <input type="text" name="satuan" id="field-satuan" class="form-control" list="satuan-list" placeholder="-- Ketik atau Pilih --" oninput="this.value=this.value.toUpperCase()">
                    <datalist id="satuan-list">
                        @foreach($satuanList as $satuan)
                            <option value="{{ $satuan }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Harga Satuan <small style="color:#94a3b8;font-weight:normal;">(Rp)</small></label>
                    <input type="number" name="harga_satuan" id="field-harga" class="form-control" min="0" placeholder="0" value="0">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Sisa Stok</label>
                    <input type="number" name="stok_aktual" id="field-stok" class="form-control" required min="0">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Stok Minimum</label>
                    <input type="number" name="stok_minimum" id="field-min" class="form-control" required min="0" value="5">
                </div>
            </div>

            {{-- Baris 4: Foto + Auto Approve --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:0;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Foto <small style="color:#94a3b8;font-weight:normal;">(Opsional, maks 2MB)</small></label>
                    <input type="file" name="foto_barang" class="form-control" accept="image/*">
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:8px; margin-bottom:0; padding-top:25px;">
                    <input type="checkbox" name="is_auto_approve" id="field-auto" value="1" style="width:16px;height:16px;accent-color:#3498db;">
                    <label for="field-auto" class="form-label" style="margin:0; cursor:pointer; font-size:13px;">Auto-Approve Pengajuan</label>
                </div>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeFormModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL UPLOAD EXCEL --}}
<div class="modal-overlay" id="upload-modal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3>Upload Excel & Sinkronisasi</h3>
            <button class="modal-close" onclick="closeModal('upload-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form method="POST" action="{{ route('aset.uploadCsv') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:15px;">
                <p style="font-size:13px; color:#64748b; margin-bottom:10px;">Pilih metode upload:</p>
                <label style="display:flex; align-items:flex-start; gap:10px; margin-bottom:10px; cursor:pointer;">
                    <input type="radio" name="upload_mode" value="setup" checked style="margin-top:3px; accent-color:#3498db;">
                    <div>
                        <strong style="font-size:14px; color:#1e293b;">Mode 1: Setup Stok Awal (Migrasi)</strong>
                        <p style="font-size:12px; color:#64748b; margin:0;">Stok Aktual akan <b>ditimpa</b> dengan angka di Excel tanpa mencatat Laporan Barang Masuk. Hanya untuk setup awal.</p>
                    </div>
                </label>
                <label style="display:flex; align-items:flex-start; gap:10px; margin-bottom:10px; cursor:pointer;">
                    <input type="radio" name="upload_mode" value="update_harga" style="margin-top:3px; accent-color:#3498db;">
                    <div>
                        <strong style="font-size:14px; color:#1e293b;">Mode 2: Update Harga & Stok Min</strong>
                        <p style="font-size:12px; color:#64748b; margin:0;">Hanya meng-update <b>Harga Satuan & Stok Minimum</b>. Stok Aktual di Excel <b>diabaikan</b>. Tanpa catat Barang Masuk.</p>
                    </div>
                </label>
                <label style="display:flex; align-items:flex-start; gap:10px; margin-bottom:10px; cursor:pointer;">
                    <input type="radio" name="upload_mode" value="tambah_baru" style="margin-top:3px; accent-color:#3498db;">
                    <div>
                        <strong style="font-size:14px; color:#1e293b;">Mode 3: Import Barang Baru</strong>
                        <p style="font-size:12px; color:#64748b; margin:0;">Hanya meng-import barang dengan kode yang <b>belum ada</b> di sistem. Barang yang kodenya sudah ada akan <b>dilewati/diabaikan</b>.</p>
                    </div>
                </label>
                <label style="display:flex; align-items:flex-start; gap:10px; margin-bottom:15px; cursor:pointer;">
                    <input type="radio" name="upload_mode" value="tambah_stok" style="margin-top:3px; accent-color:#3498db;">
                    <div>
                        <strong style="font-size:14px; color:#1e293b;">Mode 4: Tambah Stok (Barang Masuk bulanan)</strong>
                        <p style="font-size:12px; color:#64748b; margin:0;">Stok di Excel akan <b>ditambahkan</b> ke sisa stok saat ini dan <b>dicatat otomatis</b> di Laporan Barang Masuk.</p>
                    </div>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">Pilih File (.xlsx, .xls, .csv)</label>
                <input type="file" name="file_excel" class="form-control" accept=".csv,.xlsx,.xls" required>
            </div>
            
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('upload-modal')">Batal</button>
                <button type="submit" class="btn btn-success">
                    <i data-lucide="upload" style="width:16px;height:16px;"></i> Upload & Proses
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL MASS UPLOAD PROGRESS --}}
<div class="modal-overlay" id="mass-upload-progress-modal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3>Proses Upload Gambar Massal</h3>
            <button class="modal-close" onclick="closeModal('mass-upload-progress-modal')" id="mass-upload-close-btn" style="display:none;"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div style="padding: 20px;">
            <p id="mass-upload-status" style="margin-bottom:10px; font-weight:bold; color:#1e293b;">Menyiapkan...</p>
            <div style="width:100%; background:#e2e8f0; border-radius:8px; height:12px; margin-bottom:15px; overflow:hidden;">
                <div id="mass-upload-progress-bar" style="width:0%; background:#10b981; height:100%; transition:width 0.2s;"></div>
            </div>
            <div id="mass-upload-log" style="height:150px; overflow-y:auto; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px; font-size:12px; color:#475569; font-family:monospace;">
            </div>
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="btn btn-primary" onclick="window.location.reload()" id="mass-upload-done-btn" style="display:none;">Selesai & Muat Ulang</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH STOK --}}
<div class="modal-overlay" id="stock-modal">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <h3>Tambah Stok</h3>
            <button class="modal-close" onclick="closeModal('stock-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <p id="stock-item-name" style="color:#475569; font-size:14px; margin-bottom:20px;"></p>
        <form method="POST" id="stock-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Jumlah Tambah (Gunakan angka minus untuk pembatalan / undo)</label>
                <input type="number" name="jumlah_tambah" class="form-control" required placeholder="Contoh: 10 atau -10">
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('stock-modal')">Batal</button>
                <button type="submit" class="btn btn-success">
                    <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Tambah Stok
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="modal-overlay" id="delete-modal">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <h3 style="color:#e74c3c;">Konfirmasi Hapus</h3>
            <button class="modal-close" onclick="closeModal('delete-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <p style="color:#475569; font-size:14px; margin-bottom:20px;">
            Apakah Anda yakin ingin menghapus barang <strong id="delete-item-name"></strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form method="POST" id="delete-form">
            @csrf
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('delete-modal')">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="trash-2" style="width:16px;height:16px;"></i> Hapus
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Data kategori yang sudah ada untuk auto-fill
    const kategoriMap = {
        @foreach($kategoriList as $kat)
        '{{ $kat->kode_kategori }}': '{{ $kat->nama_kategori }}',
        @endforeach
    };

    // Auto-isi nama kategori saat kode kategori diketik
    function autoFillKategori(kode) {
        const nama = kategoriMap[kode.trim()];
        if (nama) {
            document.getElementById('field-nama-kategori').value = nama;
        }
    }

    let _debounceTimer;
    function debounceSubmit(form) {
        clearTimeout(_debounceTimer);
        _debounceTimer = setTimeout(() => form.submit(), 500);
    }

    function openAddModal() {
        document.getElementById('modal-title').textContent = 'Tambah Barang Baru';
        document.getElementById('barang-form').action = '{{ route('aset.store') }}';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('field-id').value = '';
        document.getElementById('field-kode-kategori').value = '';
        document.getElementById('field-nama-kategori').value = '';
        document.getElementById('field-kode').value = '';
        document.getElementById('field-nama').value = '';
        document.getElementById('field-satuan').value = '';
        document.getElementById('field-harga').value = '0';
        document.getElementById('field-stok').value = '';
        document.getElementById('field-stok').readOnly = false;
        document.getElementById('field-stok').style.backgroundColor = '';
        document.getElementById('field-stok').title = '';
        document.getElementById('field-min').value = '5';
        document.getElementById('field-auto').checked = false;
        openModal('form-modal');
    }

    function openEditModal(item) {
        document.getElementById('modal-title').textContent = 'Edit Barang';
        document.getElementById('barang-form').action = '/dashboard/aset/' + item.id_barang + '/update';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('field-id').value = item.id_barang;
        document.getElementById('field-kode-kategori').value = item.kode_kategori || '';
        document.getElementById('field-nama-kategori').value = item.nama_kategori || '';
        document.getElementById('field-kode').value = item.kode_barang || '';
        document.getElementById('field-nama').value = item.nama_barang;
        document.getElementById('field-satuan').value = item.satuan || '';
        document.getElementById('field-harga').value = item.harga_satuan || 0;
        document.getElementById('field-stok').value = item.stok_aktual;
        document.getElementById('field-stok').readOnly = true;
        document.getElementById('field-stok').style.backgroundColor = '#f1f5f9';
        document.getElementById('field-stok').title = 'Gunakan fitur Tambah Stok (Ikon +) untuk mengubah stok';
        document.getElementById('field-min').value = item.stok_minimum || 5;
        document.getElementById('field-auto').checked = item.is_auto_approve == true || item.is_auto_approve === true;
        openModal('form-modal');
    }

    function openStockModal(item) {
        document.getElementById('stock-item-name').textContent = 'Barang: ' + item.nama_barang;
        document.getElementById('stock-form').action = '/dashboard/aset/' + item.id_barang + '/stock';
        openModal('stock-modal');
    }

    function openDeleteModal(id, name) {
        document.getElementById('delete-item-name').textContent = name;
        document.getElementById('delete-form').action = '/dashboard/aset/' + id + '/delete';
        openModal('delete-modal');
    }

    function closeFormModal() { closeModal('form-modal'); }
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('open');
        });
    });

    function submitCsvForm() {
        const fileInput = document.getElementById('csv-input-aset');
        if (fileInput.files && fileInput.files[0]) {
            document.getElementById('csv-form-aset').submit();
        }
    }

    function filterTable() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("tbody tr");
        
        rows.forEach(row => {
            // Abaikan baris "Kosong" agar tidak di-filter out jika ada
            if (row.querySelector('td').colSpan > 1) return;
            
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? "" : "none";
        });
    }

    async function handleMassFotoUpload(event) {
        const files = event.target.files;
        if (files.length === 0) return;

        openModal('mass-upload-progress-modal');
        document.getElementById('mass-upload-close-btn').style.display = 'none';
        document.getElementById('mass-upload-done-btn').style.display = 'none';
        
        const statusEl = document.getElementById('mass-upload-status');
        const progressEl = document.getElementById('mass-upload-progress-bar');
        const logEl = document.getElementById('mass-upload-log');
        
        logEl.innerHTML = '';
        
        const total = files.length;
        let successCount = 0;
        let failCount = 0;

        for (let i = 0; i < total; i++) {
            const file = files[i];
            statusEl.textContent = `Mengunggah ${i + 1} dari ${total}... (${file.name})`;
            progressEl.style.width = Math.round(((i + 1) / total) * 100) + '%';
            
            const formData = new FormData();
            formData.append('foto', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('aset.massUploadFoto') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    successCount++;
                    logEl.innerHTML += `<div style="color:#10b981; margin-bottom:4px;">[SUKSES] ${file.name} - ${data.message}</div>`;
                } else {
                    failCount++;
                    logEl.innerHTML += `<div style="color:#ef4444; margin-bottom:4px;">[GAGAL] ${file.name} - ${data.message || 'Tidak cocok'}</div>`;
                }
            } catch (error) {
                failCount++;
                logEl.innerHTML += `<div style="color:#ef4444; margin-bottom:4px;">[ERROR] ${file.name} - Gagal mengunggah</div>`;
            }

            // scroll log to bottom
            logEl.scrollTop = logEl.scrollHeight;
        }

        statusEl.textContent = `Selesai! Berhasil: ${successCount}, Gagal: ${failCount}`;
        document.getElementById('mass-upload-close-btn').style.display = 'block';
        document.getElementById('mass-upload-done-btn').style.display = 'inline-block';
        
        // Reset input so they can upload again if needed
        event.target.value = '';
    }
</script>
@endpush
