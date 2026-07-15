@extends('layouts.app')
@section('title', 'Manajemen Barang / Aset')

@section('content')
<div style="display:flex; flex-direction:column; gap:25px; max-width:1200px; margin:0 auto;">

    <h2 class="page-title">
        <i data-lucide="boxes" style="width:28px;height:28px;color:#2563eb;"></i>
        Manajemen Barang / Aset
    </h2>

    {{-- TOOLBAR --}}
    <div class="card">
        <div class="toolbar" style="flex-wrap:wrap; gap:10px;">
            {{-- Search --}}
            <form method="GET" action="{{ route('aset.index') }}" id="filter-form-aset" style="display:contents;">
                <div class="search-bar" style="min-width:250px; flex:1;">
                    <i data-lucide="search" class="search-icon" style="width:18px;height:18px;"></i>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari nama barang..."
                           onkeyup="debounceSubmit(this.form)">
                </div>
            </form>

            {{-- Info + Aksi --}}
            <div style="display:flex; gap:8px; align-items:center; margin-left:auto;">
                <div style="background:#eff6ff; color:#2563eb; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:bold; white-space:nowrap;">
                    {{ $barang->count() }} Barang
                </div>
                <a href="{{ route('aset.index') }}?download_template=1" class="btn btn-secondary">
                    <i data-lucide="download" style="width:16px;height:16px;"></i> Template
                </a>
                <button type="button" onclick="document.getElementById('csv-input-aset').click()" class="btn btn-success">
                    <i data-lucide="upload" style="width:16px;height:16px;"></i> Upload CSV
                </button>
                <button type="button" class="btn btn-primary" onclick="openAddModal()">
                    <i data-lucide="plus" style="width:16px;height:16px;"></i> Tambah Manual
                </button>
            </div>
        </div>
    </div>

    {{-- FORM UPLOAD CSV (tersembunyi) --}}
    <form method="POST" action="{{ route('aset.uploadCsv') }}" enctype="multipart/form-data" id="csv-form-aset" style="display:none;">
        @csrf
        <input type="file" name="file_excel" accept=".csv,.xlsx,.xls" id="csv-input-aset" onchange="submitCsvForm()">
    </form>

    {{-- Tabel manajemen aset --}}
    <div class="card table-container">
        <table style="min-width:800px;">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Sisa Stok</th>
                    <th>Stok Min.</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barang as $item)
                    @php $isKritis = $item->stok_aktual <= $item->stok_minimum; @endphp
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
                        <td>
                            <span style="color:black;">{{ $item->kode }}</span>
                        </td>
                        <td style="color:black; text-align:center;">{{ $item->nama_barang }}</td>
                        <td style="color:black;">{{ $item->satuan }}</td>
                        <td style="color:black;">{{ $item->stok_aktual }}</td>
                        <td style="color:black;">{{ $item->stok_minimum }}</td>
                        <td>
                            @if($isKritis)
                                <span class="status-kritis">⚠ KRITIS</span>
                            @else
                                <span class="status-aman">✓ AMAN</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
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
                        <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8; font-size:13px;">
                            Tidak ada barang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Form popup untuk tambah manual barang --}}
<div class="modal-overlay" id="form-modal">
    <div class="modal" style="max-width:460px; padding:20px;">
        <div class="modal-header">
            <h3 id="modal-title">Tambah Barang</h3>
            <button class="modal-close" onclick="closeFormModal()"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form method="POST" id="barang-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="id_barang" id="field-id">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group" style="margin-bottom:5px;">
                    <label class="form-label" style="margin-bottom:4px;">Nama Barang</label>
                    <input type="text" name="nama_barang" id="field-nama" class="form-control" required oninput="this.value=this.value.toUpperCase()">
                </div>

                <div class="form-group" style="margin-bottom:5px;">
                    <label class="form-label" style="margin-bottom:4px;">Satuan</label>
                    <select name="satuan" id="field-satuan" class="form-control form-select" required>
                        <option value="">-- Pilih Satuan --</option>
                        <option value="PCS">PCS (Buah)</option>
                        <option value="RIM">RIM</option>
                        <option value="BOX">BOX (Dus)</option>
                        <option value="LUSIN">LUSIN</option>
                        <option value="PACK">PACK</option>
                        <option value="UNIT">UNIT</option>
                        <option value="BOTOL">BOTOL</option>
                        <option value="LITER">LITER</option>
                        <option value="KG">KG</option>
                        <option value="METER">METER</option>
                        <option value="ROLL">ROLL</option>
                        <option value="SET">SET</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:5px;">
                    <label class="form-label" style="margin-bottom:4px;">Stok Awal</label>
                    <input type="number" name="stok_aktual" id="field-stok" class="form-control" required min="0">
                </div>
                <div class="form-group" style="margin-bottom:5px;">
                    <label class="form-label" style="margin-bottom:4px;">Stok Minimum</label>
                    <input type="number" name="stok_minimum" id="field-min" class="form-control" required min="0" value="5">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" style="margin-bottom:4px;">Foto Barang <small style="color:#94a3b8;font-weight:normal;">(Opsional, 2MB)</small></label>
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
                <label class="form-label">Jumlah Tambah</label>
                <input type="number" name="jumlah_tambah" class="form-control" required min="1" placeholder="Masukkan jumlah">
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
    let _debounceTimer;
    function debounceSubmit(form) {
        clearTimeout(_debounceTimer);
        _debounceTimer = setTimeout(() => form.submit(), 500);
    }

    // Logika menampilkan modal untuk fungsi tambah manual barang
    function openAddModal() {
        document.getElementById('modal-title').textContent = 'Tambah Barang Baru';
        document.getElementById('barang-form').action = '{{ route('aset.store') }}';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('field-id').value = '';
        document.getElementById('field-nama').value = '';
        document.getElementById('field-satuan').value = '';
        document.getElementById('field-stok').value = '';
        document.getElementById('field-min').value = '5';
        document.getElementById('field-auto').checked = false;
        openModal('form-modal');
    }

    function openEditModal(item) {
        document.getElementById('modal-title').textContent = 'Edit Barang';
        document.getElementById('barang-form').action = '/dashboard/aset/' + item.id_barang + '/update';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('field-id').value = item.id_barang;
        document.getElementById('field-nama').value = item.nama_barang;
        document.getElementById('field-satuan').value = item.satuan || '';
        document.getElementById('field-stok').value = item.stok_aktual;
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
</script>
@endpush
