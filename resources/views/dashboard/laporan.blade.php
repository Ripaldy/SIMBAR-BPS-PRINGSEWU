@extends('layouts.app')
@section('title', 'Riwayat & Laporan')

@section('content')
<div style="display:flex; flex-direction:column; gap:25px; padding-bottom:50px; font-family:system-ui,-apple-system,sans-serif; max-width:1200px; margin:0 auto;">

    {{-- ===== 1. HEADER ===== --}}
    <div style="background:white; border-radius:16px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <div style="width:50px;height:50px;background:#eaf4fb;border-radius:12px;display:flex;justify-content:center;align-items:center;">
                <i data-lucide="clock" style="width:24px;height:24px;color:#3498db;"></i>
            </div>
            <div>
                <h2 style="margin:0; color:#1f4068; font-size:20px;">Riwayat &amp; Laporan</h2>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:12px;">Pusat data seluruh transaksi inventaris yang telah diproses.</p>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="openModal('hapus-modal')"
                style="display:flex;align-items:center;gap:8px;padding:10px 18px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;font-size:12px;font-weight:bold;cursor:pointer;font-family:inherit;">
                <i data-lucide="trash-2" style="width:16px;height:16px;"></i> Hapus Data
            </button>
            <button onclick="openModal('export-modal')"
                style="display:flex;align-items:center;gap:8px;padding:10px 18px;background:#27ae60;color:white;border:none;border-radius:8px;font-size:12px;font-weight:bold;cursor:pointer;font-family:inherit;">
                <i data-lucide="download" style="width:16px;height:16px;"></i> Ekspor Data
            </button>
        </div>
    </div>

    {{-- ===== 2. TOOLBAR FILTER ===== --}}
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        {{-- Search --}}
        <div style="position:relative; min-width:250px; flex:1;">
            <i data-lucide="search" style="width:16px;height:16px;color:#94a3b8;position:absolute;left:12px;top:50%;transform:translateY(-50%);"></i>
            <input type="text" id="kataKunci" placeholder="Cari pemohon, divisi atau barang..."
                style="width:100%; padding:10px 15px 10px 35px; border-radius:8px; border:1px solid #e2e8f0; outline:none; font-size:12px; box-sizing:border-box; font-family:inherit;"
                oninput="debounceRender()" value="">
        </div>

        {{-- Mode Tampilan --}}
        <select id="viewMode" onchange="onViewModeChange()"
            style="padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1f4068; outline:none; cursor:pointer; font-family:inherit;">
            <option value="grouped">History Pengajuan (Gabungan)</option>
            <option value="itemized">History Pengajuan (Per Barang)</option>
            <option value="divisi">Tabel Pengeluaran Tim Kerja</option>
            <option value="aggregate_gabungan">Tabel Agregat Barang</option>
            <option value="rincian">Laporan Rincian Barang Persediaan</option>
            <option value="harga_barang">Tabel Harga Per Barang</option>
        </select>

        {{-- Filter Divisi (muncul jika mode divisi) --}}
        <select id="divisiFilter" onchange="renderTable()"
            style="display:none; padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1f4068; outline:none; cursor:pointer; font-family:inherit;">
            <option value="Semua Divisi">Semua Tim Kerja</option>
            @foreach($divisiList as $div)
                <option value="{{ $div }}">{{ $div }}</option>
            @endforeach
        </select>

        {{-- Filter Tahun & Bulan (berlaku untuk semua mode) --}}
        <select id="aggTahun" onchange="renderTable()"
            style="padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1f4068; outline:none; cursor:pointer; font-family:inherit;">
            <option value="Semua">Semua Tahun</option>
            @foreach($availableYears as $y)
                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>Tahun {{ $y }}</option>
            @endforeach
        </select>
        <select id="aggBulan" onchange="renderTable()"
            style="padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1f4068; outline:none; cursor:pointer; font-family:inherit;">
            <option value="Semua">Semua Bulan</option>
            <option value="1" {{ now()->month == 1 ? 'selected' : '' }}>Januari</option>
            <option value="2" {{ now()->month == 2 ? 'selected' : '' }}>Februari</option>
            <option value="3" {{ now()->month == 3 ? 'selected' : '' }}>Maret</option>
            <option value="4" {{ now()->month == 4 ? 'selected' : '' }}>April</option>
            <option value="5" {{ now()->month == 5 ? 'selected' : '' }}>Mei</option>
            <option value="6" {{ now()->month == 6 ? 'selected' : '' }}>Juni</option>
            <option value="7" {{ now()->month == 7 ? 'selected' : '' }}>Juli</option>
            <option value="8" {{ now()->month == 8 ? 'selected' : '' }}>Agustus</option>
            <option value="9" {{ now()->month == 9 ? 'selected' : '' }}>September</option>
            <option value="10" {{ now()->month == 10 ? 'selected' : '' }}>Oktober</option>
            <option value="11" {{ now()->month == 11 ? 'selected' : '' }}>November</option>
            <option value="12" {{ now()->month == 12 ? 'selected' : '' }}>Desember</option>
        </select>
    </div>

    {{-- ===== 3. TABEL ===== --}}
    <div style="background:white; border-radius:16px; border:1px solid #f1f5f9; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
        <div id="table-container" style="overflow-x:auto;"></div>

        {{-- PAGINASI --}}
        <div id="pagination-bar" style="display:none; justify-content:space-between; align-items:center; padding:15px 25px; border-top:1px solid #f1f5f9; background:#fafaf9; border-radius:0 0 16px 16px; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:10px; font-size:12px; color:#64748b;">
                Tampilkan:
                <select id="itemsPerPage" onchange="onPerPageChange()"
                    style="padding:6px 10px; border-radius:6px; border:1px solid #e2e8f0; outline:none; cursor:pointer; font-family:inherit;">
                    <option value="5">5 Baris</option>
                    <option value="10" selected>10 Baris</option>
                    <option value="20">20 Baris</option>
                    <option value="50">50 Baris</option>
                </select>
                <span id="total-label">dari 0 entri</span>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <button id="btn-prev" onclick="prevPage()"
                    style="display:flex;align-items:center;justify-content:center;padding:6px;background:white;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;color:#1f4068;">
                    <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
                </button>
                <span id="page-label" style="font-size:13px; font-weight:bold; color:#1f4068; padding:0 10px;">Halaman 1 / 1</span>
                <button id="btn-next" onclick="nextPage()"
                    style="display:flex;align-items:center;justify-content:center;padding:6px;background:white;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;color:#1f4068;">
                    <i data-lucide="chevron-right" style="width:16px;height:16px;"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL DETAIL (Tabel 1: Grouped) ===== --}}
<div class="modal-overlay" id="detail-modal">
    <div class="modal" style="max-width:700px; padding:0; overflow:hidden;">
        <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-radius:16px 16px 0 0;">
            <h3 style="margin:0; color:#1f4068; font-size:16px;">Rincian Pengajuan</h3>
            <button class="modal-close" onclick="closeModal('detail-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div style="padding:25px; overflow-y:auto; max-height:75vh;">
            <div id="detail-header" style="margin-bottom:20px; display:flex; justify-content:space-between; background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;"></div>
            <div style="border-radius:8px; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:20px;">
                <table style="width:100%; border-collapse:collapse; text-align:center;">
                    <thead>
                        <tr style="background:#f1f5f9; border-bottom:1px solid #e2e8f0;">
                            <th style="padding:10px; font-size:12px; color:#475569; text-transform:uppercase;">Gambar</th>
                            <th style="padding:10px; font-size:12px; color:#475569; text-align:center; text-transform:uppercase;">Nama Barang</th>
                            <th style="padding:10px; font-size:12px; color:#475569; text-transform:uppercase;">Kode</th>
                            <th style="padding:10px; font-size:12px; color:#475569; text-transform:uppercase;">Diminta</th>
                            <th style="padding:10px; font-size:12px; color:#475569; text-transform:uppercase;">Disetujui</th>
                            <th style="padding:10px; font-size:12px; color:#475569; text-transform:uppercase;">Ditolak</th>
                        </tr>
                    </thead>
                    <tbody id="detail-tbody"></tbody>
                </table>
            </div>
            <div id="detail-alasan" style="padding:15px; background:#fafaf9; border-radius:8px; border:1px solid #e2e8f0;"></div>
        </div>
    </div>
</div>

{{-- ===== MODAL HAPUS DATA ===== --}}
<div class="modal-overlay" id="hapus-modal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header">
            <h3 style="color:#1f4068; display:flex; align-items:center; gap:8px;">
                <i data-lucide="trash-2" style="width:20px;height:20px;color:#dc2626;"></i> Hapus Riwayat Pengajuan
            </h3>
            <button class="modal-close" onclick="closeModal('hapus-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div style="background:#fef2f2; padding:15px; border-radius:8px; border:1px solid #fecaca; margin-bottom:20px;">
            <p style="margin:0; font-size:13px; color:#991b1b; line-height:1.5; display:flex; align-items:flex-start; gap:8px;">
                <i data-lucide="alert-triangle" style="width:18px;height:18px; flex-shrink:0; margin-top:1px;"></i>
                <span><strong>Perhatian!</strong> Aksi ini akan menghapus data riwayat secara permanen. Pastikan Anda telah <strong>mengekspor data</strong> terlebih dahulu.</span>
            </p>
        </div>
        <form method="POST" action="{{ route('laporan.hapus') }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                <div class="form-group">
                    <label class="form-label">Pilih Tahun</label>
                    <select name="tahun" class="form-control form-select">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}">Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pilih Bulan</label>
                    <select name="bulan" class="form-control form-select">
                        <option value="Semua">Semua Bulan</option>
                        <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
                        <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                        <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                        <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:15px; border-top:1px solid #f1f5f9;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('hapus-modal')">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="trash-2" style="width:16px;height:16px;"></i> Ya, Hapus Permanen
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EKSPOR DATA ===== --}}
<div class="modal-overlay" id="export-modal">
    <div class="modal" style="max-width:600px; padding:0; overflow:hidden;">
        <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; color:#1f4068; font-size:16px; display:flex; align-items:center; gap:8px;">
                <i data-lucide="download" style="width:20px;height:20px;color:#27ae60;"></i> Pengaturan Ekspor Laporan
            </h3>
            <button class="modal-close" onclick="closeModal('export-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div style="padding:25px; display:flex; flex-direction:column; gap:20px; max-height:60vh; overflow-y:auto;">
            {{-- Format & Tabel --}}
            <div style="display:flex; gap:15px;">
                <div style="flex:1;">
                    <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Format Dokumen</label>
                    <div style="display:flex; gap:10px;">
                        <button id="btn-csv" onclick="setExportFormat('xlsx')"
                            style="flex:1; padding:10px; border-radius:8px; border:2px solid #27ae60; background:#ecfdf5; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; color:#1f4068; font-weight:bold; font-family:inherit;">
                            <i data-lucide="file-spreadsheet" style="width:18px;height:18px;color:#27ae60;"></i> Excel
                        </button>
                        <button id="btn-pdf" onclick="setExportFormat('pdf')"
                            style="flex:1; padding:10px; border-radius:8px; border:1px solid #cbd5e1; background:white; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; color:#1f4068; font-weight:bold; font-family:inherit;">
                            <i data-lucide="file-text" style="width:18px;height:18px;color:#64748b;"></i> PDF
                        </button>
                    </div>
                </div>
                <div style="flex:1;">
                    <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Saring Tabel</label>
                    <select id="export-tabel" onchange="onExportTabelChange()"
                        style="width:100%; padding:11px; border-radius:8px; border:1px solid #cbd5e1; outline:none; color:#1f4068; font-family:inherit;">
                        <option value="grouped">History Pengajuan (Gabungan)</option>
                        <option value="itemized">History Pengajuan (Per Barang)</option>
                        <option value="divisi">Tabel Pengeluaran Tim Kerja</option>
                        <option value="aggregate_gabungan">Tabel Agregat Barang</option>
                        <option value="rincian">Laporan Rincian Barang Persediaan</option>
                        <option value="harga_barang">Tabel Harga Per Barang</option>
                    </select>
                </div>
            </div>


            {{-- Tahun & Bulan --}}
            <div style="display:flex; gap:15px;">
                <div style="flex:1;">
                    <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Pilih Tahun</label>
                    <select id="export-tahun" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; outline:none; color:#1f4068; font-family:inherit;">
                        <option value="Semua">Semua Tahun</option>
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Pilih Bulan</label>
                    <select id="export-bulan" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; outline:none; color:#1f4068; font-family:inherit;">
                        <option value="Semua">Semua Bulan</option>
                        <option value="1" {{ now()->month == 1 ? 'selected' : '' }}>Januari</option>
                        <option value="2" {{ now()->month == 2 ? 'selected' : '' }}>Februari</option>
                        <option value="3" {{ now()->month == 3 ? 'selected' : '' }}>Maret</option>
                        <option value="4" {{ now()->month == 4 ? 'selected' : '' }}>April</option>
                        <option value="5" {{ now()->month == 5 ? 'selected' : '' }}>Mei</option>
                        <option value="6" {{ now()->month == 6 ? 'selected' : '' }}>Juni</option>
                        <option value="7" {{ now()->month == 7 ? 'selected' : '' }}>Juli</option>
                        <option value="8" {{ now()->month == 8 ? 'selected' : '' }}>Agustus</option>
                        <option value="9" {{ now()->month == 9 ? 'selected' : '' }}>September</option>
                        <option value="10" {{ now()->month == 10 ? 'selected' : '' }}>Oktober</option>
                        <option value="11" {{ now()->month == 11 ? 'selected' : '' }}>November</option>
                        <option value="12" {{ now()->month == 12 ? 'selected' : '' }}>Desember</option>
                    </select>
                </div>
            </div>

            {{-- Divisi filter (jika tabel=divisi) --}}
            <div id="export-divisi-wrap" style="display:none;">
                <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Pilih Tim Kerja</label>
                <select id="export-divisi" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; outline:none; color:#1f4068; font-family:inherit;">
                    <option value="Semua Divisi">Semua Tim Kerja</option>
                    @foreach($divisiList as $div)
                        <option value="{{ $div }}">{{ $div }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Form Judul Khusus (jika tabel=divisi) --}}
            <div id="export-judul-divisi-wrap" style="display:none; margin-top:15px;">
                <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Ubah Judul Dokumen</label>
                <input type="text" id="export-judul-divisi" value="TABEL PENGELUARAN TIM KERJA" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; box-sizing:border-box; outline:none; color:#1f4068; font-family:inherit;">
            </div>

            {{-- Kolom Ekspor --}}
            <div id="export-kolom-wrap" style="display:none; margin-top:15px;">
                <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Pilih Kolom Ekspor</label>
                <div id="export-kolom-container" style="display:flex; flex-wrap:wrap; gap:10px;">
                    <!-- Checkboxes injected via JS -->
                </div>
            </div>
        </div>
        <div style="padding:15px 25px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc; border-radius:0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeModal('export-modal')">Batal</button>
            <button class="btn btn-primary" onclick="executeExport()">
                <i data-lucide="download" style="width:16px;height:16px;"></i> Buat Ekspor
            </button>
        </div>
    </div>
</div>

{{-- ===== MODAL PENGATURAN PDF ===== --}}
<div class="modal-overlay" id="pdf-modal">
    <div class="modal" style="max-width:500px; padding:0; overflow:hidden;">
        <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; color:#1f4068; font-size:16px; display:flex; align-items:center; gap:8px;">
                <i data-lucide="file-text" style="width:20px;height:20px;color:#27ae60;"></i> Pengaturan Dokumen PDF
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('pdf-modal')"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <form action="{{ route('laporan.pengajuan.pdf') }}" method="POST" target="_blank">
            @csrf
            <input type="hidden" name="waktu_pengajuan" id="pdf-waktu">
            <input type="hidden" name="id_user" id="pdf-user">
            
            <div style="padding:25px; display:flex; flex-direction:column; gap:15px;">
                <div class="form-group">
                    <label style="font-size:12px; color:#64748b; font-weight:bold; margin-bottom:5px; display:block;">Nama Kasubbag Umum</label>
                    @php
                        $kasubbagList = \App\Models\User::whereRaw('LOWER(jabatan) LIKE ?', ['%kepala subbagian umum%'])
                                                          ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kasubbag umum%'])
                                                          ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%kasubag umum%'])
                                                          ->get();
                    @endphp
                    @if($kasubbagList->count() > 0)
                        <select name="kasubbag" id="pdf-kasubbag" class="form-control" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; box-sizing:border-box; font-family:inherit; cursor:pointer;">
                            @foreach($kasubbagList as $k)
                                <option value="{{ $k->nama_lengkap }}">{{ $k->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="kasubbag" id="pdf-kasubbag" class="form-control" placeholder="Ketik Nama Kasubbag Umum..." required style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; box-sizing:border-box;">
                    @endif
                </div>
                <div class="form-group">
                    <label style="font-size:12px; color:#64748b; font-weight:bold; margin-bottom:5px; display:block;">Nama Penerima (Pemohon)</label>
                    <input type="text" name="penerima" id="pdf-penerima" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; box-sizing:border-box;">
                </div>
                <div class="form-group">
                    <label style="font-size:12px; color:#64748b; font-weight:bold; margin-bottom:5px; display:block;">Tim Kerja</label>
                    <input type="text" name="tim_kerja" id="pdf-timkerja" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; box-sizing:border-box;">
                </div>
            </div>
            <div style="padding:15px 25px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc; border-radius:0 0 16px 16px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('pdf-modal')" style="padding:10px 18px; border-radius:8px; border:1px solid #cbd5e1; background:white; cursor:pointer;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 18px; border-radius:8px; background:#27ae60; color:white; border:none; cursor:pointer; display:flex; align-items:center; gap:8px;" onclick="setTimeout(()=>closeModal('pdf-modal'), 500)">
                    <i data-lucide="printer" style="width:16px;height:16px;"></i> Cetak PDF
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script>
// ============================================================
//  DATA dari PHP (semua pengajuan yang sudah diproses)
// ============================================================
const riwayatMentah   = @json($riwayatData);
const barangMasukMentah = @json($barangMasukData);
const rincianMentah   = @json($rincianData);

// ============================================================
//  STATE
// ============================================================
let currentPage = 1;
let itemsPerPage = 10;
let exportFormat = 'xlsx';
let _debounceTimer;

// ============================================================
//  HELPERS
// ============================================================
function fmtWaktu(str) {
    if (!str) return '-';
    const d = new Date(str);
    const pad = n => String(n).padStart(2,'0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function kode(item) { return item.kode_barang ? item.kode_barang : 'BRG-' + String(item.id_barang).padStart(3,'0'); }

function imgCell(foto, alt) {
    const src = foto ? `/uploads/${foto}` : null;
    const inner = src ? `<img src="${src}" alt="" style="width:100%;height:100%;object-fit:cover;">` : `<span style="font-size:10px;color:#000;">No Img</span>`;
    return `<div style="width:40px;height:40px;overflow:hidden;display:flex;justify-content:center;align-items:center;margin:0 auto;border:1px solid #ccc;border-radius:4px;">${inner}</div>`;
}

function tdS(content, extra='') {
    return `<td style="padding:12px;font-size:12px;color:#000;text-align:center;border-right:1px solid #e2e8f0;font-weight:normal;${extra}">${content}</td>`;
}
function tdL(content) { return `<td style="padding:12px;font-size:12px;color:#000;text-align:center;font-weight:normal;">${content}</td>`; }

// ============================================================
//  BUILD GROUPED DATA
// ============================================================
function buildGrouped(data) {
    const obj = {};
    data.forEach(item => {
        const key = item.waktu_pengajuan + '_' + item.id_user;
        if (!obj[key]) obj[key] = { id_group: key, id_user: item.id_user, waktu_pengajuan: item.waktu_pengajuan, nama_lengkap: item.nama_lengkap, tim_kerja: item.tim_kerja, items: [] };
        obj[key].items.push(item);
    });
    return Object.values(obj).map(g => {
        const items = g.items;
        const total = items.reduce((s, i) => s + parseInt(i.jumlah_diminta), 0);
        return { ...g, status_pengajuan: 'approved', totalItem: total };
    }).sort((a,b) => new Date(b.waktu_pengajuan) - new Date(a.waktu_pengajuan));
}

// ============================================================
//  FILTER & RENDER
// ============================================================
function getFilteredData() {
    const kata     = document.getElementById('kataKunci').value.toLowerCase();
    const mode     = document.getElementById('viewMode').value;
    const divisi   = document.getElementById('divisiFilter').value;
    const aggTahun = document.getElementById('aggTahun').value;
    const aggBulan = document.getElementById('aggBulan').value;

    let base = riwayatMentah;

    // Filter tanggal/bulan untuk data riwayat pengeluaran
    if (aggTahun !== 'Semua') {
        const periodStart = new Date(parseInt(aggTahun), aggBulan === 'Semua' ? 0 : parseInt(aggBulan) - 1, 1);
        const periodEnd   = new Date(parseInt(aggTahun), aggBulan === 'Semua' ? 12 : parseInt(aggBulan), 0, 23, 59, 59);
        
        base = base.filter(item => {
            const d = new Date(item.waktu_pengajuan);
            return d >= periodStart && d <= periodEnd;
        });
    }

    if (mode === 'aggregate_gabungan') {
        return buildAggregateGabungan(aggTahun, aggBulan, kata);
    }

    if (mode === 'divisi') {
        base = base.filter(item => {
            const matchD = divisi === 'Semua Divisi' || item.tim_kerja === divisi;
            const matchK = !kata || item.nama_lengkap.toLowerCase().includes(kata) || item.nama_barang.toLowerCase().includes(kata) || (item.tim_kerja && item.tim_kerja.toLowerCase().includes(kata));
            return matchD && matchK;
        });
        return buildAggregateDivisi(base);
    } else if (mode === 'grouped') {
        const groups = buildGrouped(base);
        return groups.filter(g => {
            return !kata || g.nama_lengkap.toLowerCase().includes(kata) || (g.tim_kerja && g.tim_kerja.toLowerCase().includes(kata)) || g.items.some(i => i.nama_barang.toLowerCase().includes(kata));
        });
    } else if (mode === 'aggregate') {
        base = base.filter(item => !kata || item.nama_barang.toLowerCase().includes(kata));
    } else {
        base = base.filter(item => {
            return !kata || item.nama_lengkap.toLowerCase().includes(kata) || item.nama_barang.toLowerCase().includes(kata) || (item.tim_kerja && item.tim_kerja.toLowerCase().includes(kata));
        });
    }

    return base;
}

function buildAggregateDivisi(data) {
    const map = {};
    data.forEach(curr => {
        let disetujui = curr.status_pengajuan !== 'rejected' && curr.jumlah_disetujui !== null ? parseInt(curr.jumlah_disetujui) : 0;
        if (disetujui > 0) {
            const key = (curr.tim_kerja || 'Tanpa Tim') + '_' + curr.id_barang;
            if (!map[key]) {
                map[key] = {
                    tim_kerja: curr.tim_kerja || '-',
                    id_barang: curr.id_barang,
                    kode_barang: curr.kode_barang,
                    kode_kategori: curr.kode_kategori || '-',
                    nama_kategori: curr.nama_kategori || '-',
                    foto_barang: curr.foto_barang,
                    nama_barang: curr.nama_barang,
                    satuan: curr.satuan || '-',
                    jumlah: 0
                };
            }
            map[key].jumlah += disetujui;
        }
    });
    return Object.values(map).sort((a,b) => {
        if (a.tim_kerja === b.tim_kerja) return b.jumlah - a.jumlah;
        return a.tim_kerja.localeCompare(b.tim_kerja);
    });
}

function buildAggregateGabungan(tahun, bulan, kata) {
    const periodStart = tahun !== 'Semua' ? new Date(parseInt(tahun), 0, 1) : null;
    const periodEnd   = (() => {
        if (tahun === 'Semua') return null;
        if (bulan === 'Semua') return new Date(parseInt(tahun), 11, 31, 23, 59, 59);
        return new Date(parseInt(tahun), parseInt(bulan), 0, 23, 59, 59);
    })();

    const result = [];
    rincianMentah.forEach(b => {
        if (kata && !b.nama_barang.toLowerCase().includes(kata)) return;

        const masuk = b.barang_masuk.filter(bm => {
            if (!periodStart) return true;
            const d = new Date(bm.waktu); return d >= periodStart && d <= periodEnd;
        }).reduce((s, bm) => s + bm.jumlah_masuk, 0);

        const keluar = b.pengajuan.filter(p => {
            if (!periodStart) return true;
            const d = new Date(p.waktu); return d >= periodStart && d <= periodEnd;
        }).reduce((s, p) => s + p.jumlah_disetujui, 0);

        if (masuk > 0 || keluar > 0) {
            result.push({
                ...b,
                masuk: masuk,
                keluar: keluar
            });
        }
    });

    // Sort by total movement (masuk + keluar) descending
    return result.sort((a,b) => (b.masuk + b.keluar) - (a.masuk + a.keluar));
}

// ============================================================
//  RENDER TABLE
// ============================================================
function renderTable() {
    lucide.createIcons();
    const mode = document.getElementById('viewMode').value;
    const filtered = getFilteredData();

    let displayData = filtered;

    // === RINCIAN: render khusus tanpa paginasi ===
    if (mode === 'rincian') {
        const tahun = document.getElementById('aggTahun').value;
        const bulan = document.getElementById('aggBulan').value;
        document.getElementById('table-container').innerHTML = buildRincianHTML(tahun, bulan);
        document.getElementById('pagination-bar').style.display = 'none';
        lucide.createIcons();
        return;
    }

    // === HARGA BARANG: render khusus tanpa paginasi ===
    if (mode === 'harga_barang') {
        const tahun = document.getElementById('aggTahun').value;
        const bulan = document.getElementById('aggBulan').value;
        document.getElementById('table-container').innerHTML = buildHargaBarangHTML(tahun, bulan);
        document.getElementById('pagination-bar').style.display = 'none';
        lucide.createIcons();
        return;
    }

    if (mode === 'aggregate') {
        displayData = buildAggregate(filtered);
    } else if (mode === 'aggregate_masuk') {
        displayData = buildAggregateMasuk(filtered);
    }

    const total = displayData.length;
    const totalPages = Math.ceil(total / itemsPerPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * itemsPerPage;
    const end   = start + itemsPerPage;
    const slice = displayData.slice(start, end);

    // Build HTML
    let html = '';
    const thStyle = 'padding:15px;font-size:12px;color:#333;font-weight:bold;text-transform:uppercase;text-align:center;border-right:1px solid #e2e8f0;';
    const thLast  = 'padding:15px;font-size:12px;color:#333;font-weight:bold;text-transform:uppercase;text-align:center;';
    const bgHead  = 'background:#f8fafc;border-bottom:1px solid #e2e8f0;';

    if (mode === 'grouped') {
        // --- TABEL GABUNGAN ---
        const thGS = 'padding:15px 10px;font-size:12px;color:#64748b;font-weight:bold;text-transform:uppercase;text-align:center;';
        html = `<table style="width:100%;border-collapse:collapse;text-align:center;min-width:950px;">
            <thead><tr style="${bgHead}">
                <th style="${thGS}width:15%;">Waktu</th>
                <th style="${thGS}width:20%;">Pemohon</th>
                <th style="${thGS}width:20%;">Tim Kerja</th>
                <th style="${thGS}width:10%;">Jumlah Item</th>
                <th style="${thGS}width:8%;">Detail</th>
                <th style="${thGS}width:7%;">Download</th>
                <th style="${thGS}width:20%;">Status</th>
            </tr></thead><tbody>`;
        if (slice.length === 0) {
            html += `<tr><td colspan="7" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada riwayat ditemukan.</td></tr>`;
        } else {
            slice.forEach(g => {
                const statusBadge = `<span style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#ecfdf5;color:#059669;border-radius:20px;font-size:12px;white-space:nowrap;">✓ Selesai Diproses</span>`;
                const groupJson = JSON.stringify(g).replace(/'/g,"&#39;");
                html += `<tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${fmtWaktu(g.waktu_pengajuan)}</td>
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${g.nama_lengkap}</td>
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${g.tim_kerja || '-'}</td>
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${g.totalItem}</td>
                    <td style="padding:15px 10px;text-align:center;vertical-align:middle;">
                        <span onclick='openDetailModal(${groupJson})' style="color:#3498db;font-size:12px;cursor:pointer;text-decoration:underline;display:inline-flex;align-items:center;gap:4px;">
                            <i data-lucide="eye" style="width:14px;height:14px;"></i> Detail
                        </span>
                    </td>
                    <td style="padding:15px 10px;text-align:center;vertical-align:middle;">
                        <span onclick='openPdfModal("${g.waktu_pengajuan}", "${g.id_user}", "${g.nama_lengkap}", "${g.tim_kerja || ''}")' style="color:#27ae60;font-size:12px;cursor:pointer;text-decoration:underline;display:inline-flex;align-items:center;gap:4px;">
                            <i data-lucide="file-text" style="width:14px;height:14px;"></i> PDF
                        </span>
                    </td>
                    <td style="padding:15px 10px;text-align:center;">${statusBadge}</td>
                </tr>`;
            });
        }
        html += `</tbody></table>`;

    } else if (mode === 'itemized') {
        // --- TABEL LENGKAP ---
        html = `<table style="width:100%;border-collapse:collapse;text-align:center;min-width:1000px;">
            <thead><tr style="${bgHead}">
                <th style="${thStyle}">Tanggal / Waktu</th>
                <th style="${thStyle}">Gambar</th>
                <th style="${thStyle}">Pemohon</th>
                <th style="${thStyle}">Tim Kerja</th>
                <th style="${thStyle}">Kode</th>
                <th style="${thStyle}">Jenis Barang</th>
                <th style="${thStyle}">Diminta</th>
                <th style="${thStyle}">Disetujui</th>
                <th style="${thLast}">Ditolak</th>
            </tr></thead><tbody>`;
        if (slice.length === 0) {
            html += `<tr><td colspan="9" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada riwayat per item.</td></tr>`;
        } else {
            slice.forEach(item => {
                const diminta = parseInt(item.jumlah_diminta);
                let disetujui = 0, ditolak = 0;
                if (item.status_pengajuan === 'rejected') { ditolak = diminta; }
                else { disetujui = item.jumlah_disetujui !== null ? parseInt(item.jumlah_disetujui) : 0; ditolak = diminta - disetujui; }
                html += `<tr style="border-bottom:1px solid #e2e8f0;">
                    ${tdS(fmtWaktu(item.waktu_pengajuan))}
                    <td style="padding:12px;border-right:1px solid #e2e8f0;">${imgCell(item.foto_barang, item.nama_barang)}</td>
                    ${tdS(item.nama_lengkap)}
                    ${tdS(item.tim_kerja || '-')}
                    ${tdS(kode(item))}
                    ${tdS(item.nama_barang)}
                    ${tdS(diminta)}
                    ${tdS(disetujui)}
                    ${tdL(ditolak)}
                </tr>`;
            });
        }
        html += `</tbody></table>`;

    } else if (mode === 'divisi') {
        // --- TABEL DIVISI ---
        html = `<table style="width:100%;border-collapse:collapse;text-align:center;min-width:1000px;">
            <thead><tr style="${bgHead}">
                <th style="${thStyle}">No</th>
                <th style="${thStyle}">Tim Kerja</th>
                <th style="${thStyle}">Gambar</th>
                <th style="${thStyle}">Kode</th>
                <th style="${thStyle}">Kode Kategori</th>
                <th style="${thStyle}">Kategori</th>
                <th style="${thStyle}">Nama Barang</th>
                <th style="${thLast}">Jumlah Barang</th>
            </tr></thead><tbody>`;
        if (slice.length === 0) {
            html += `<tr><td colspan="8" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada data pengeluaran tim kerja.</td></tr>`;
        } else {
            slice.forEach((item, index) => {
                html += `<tr style="border-bottom:1px solid #e2e8f0;">
                    ${tdS(start + index + 1)}
                    ${tdS(item.tim_kerja)}
                    <td style="padding:12px;border-right:1px solid #e2e8f0;vertical-align:middle;">${imgCell(item.foto_barang, item.nama_barang)}</td>
                    ${tdS(kode(item))}
                    ${tdS(item.kode_kategori || '-')}
                    ${tdS(item.nama_kategori || '-')}
                    ${tdS(item.nama_barang)}
                    ${tdS(item.jumlah)}
                </tr>`;
            });
        }
        html += `</tbody></table>`;

    } else if (mode === 'aggregate') {
        // --- TABEL AGREGAT KELUAR ---
        html = `<table style="width:100%;border-collapse:collapse;text-align:center;min-width:950px;">
            <thead><tr style="${bgHead}">
                <th style="${thStyle}">Gambar</th>
                <th style="${thStyle}">Kode</th>
                <th style="${thStyle}">Jenis Barang</th>
                <th style="${thStyle}">Jumlah Yang Keluar</th>
                <th style="${thLast}">Satuan</th>
            </tr></thead><tbody>`;
        if (displayData.length === 0) {
            html += `<tr><td colspan="5" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada data agregat barang keluar.</td></tr>`;
        } else {
            slice.forEach(item => {
                html += `<tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:12px;border-right:1px solid #e2e8f0;vertical-align:middle;">${imgCell(item.foto_barang, item.nama_barang)}</td>
                    ${tdS(kode(item))}
                    ${tdS(item.nama_barang)}
                    ${tdS(item.jumlah_keluar)}
                    ${tdL(item.satuan)}
                </tr>`;
            });
        }
        html += `</tbody></table>`;
    } else if (mode === 'aggregate_masuk') {
        // --- TABEL AGREGAT MASUK ---
        html = `<table style="width:100%;border-collapse:collapse;text-align:center;min-width:950px;">
            <thead><tr style="${bgHead}">
                <th style="${thStyle}">Gambar</th>
                <th style="${thStyle}">Kode</th>
                <th style="${thStyle}">Jenis Barang</th>
                <th style="${thStyle}">Jumlah Yang Masuk</th>
                <th style="${thLast}">Satuan</th>
            </tr></thead><tbody>`;
        if (displayData.length === 0) {
            html += `<tr><td colspan="5" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada data agregat barang masuk.</td></tr>`;
        } else {
            slice.forEach(item => {
                html += `<tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:12px;border-right:1px solid #e2e8f0;vertical-align:middle;">${imgCell(item.foto_barang, item.nama_barang)}</td>
                    ${tdS(kode(item))}
                    ${tdS(item.nama_barang)}
                    ${tdS(item.jumlah)}
                    ${tdL(item.satuan)}
                </tr>`;
            });
        }
        html += `</tbody></table>`;
    } else if (mode === 'aggregate_gabungan') {
        html = `<table style="width:100%;border-collapse:collapse;text-align:center;min-width:950px;">
            <thead><tr style="${bgHead}">
                <th style="${thStyle}">Gambar</th>
                <th style="${thStyle}">Kode</th>
                <th style="${thStyle}">Kode Kategori</th>
                <th style="${thStyle}">Kategori</th>
                <th style="${thStyle}">Nama Barang</th>
                <th style="${thStyle}">Jumlah Masuk</th>
                <th style="${thStyle}">Jumlah Keluar</th>
                <th style="${thLast}">Satuan</th>
            </tr></thead><tbody>`;
        if (displayData.length === 0) {
            html += `<tr><td colspan="8" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada data agregat pada periode ini.</td></tr>`;
        } else {
            slice.forEach(item => {
                html += `<tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:12px;border-right:1px solid #e2e8f0;vertical-align:middle;">${imgCell(item.foto_barang, item.nama_barang)}</td>
                    ${tdS(kode(item))}
                    ${tdS(item.kode_kategori || '-')}
                    ${tdS(item.nama_kategori || '-')}
                    ${tdS(item.nama_barang)}
                    ${tdS(item.masuk)}
                    ${tdS(item.keluar)}
                    ${tdL(item.satuan)}
                </tr>`;
            });
        }
        html += `</tbody></table>`;
    }

    document.getElementById('table-container').innerHTML = html;

    // Update pagination
    const paginationBar = document.getElementById('pagination-bar');
    if (total > 0) {
        paginationBar.style.display = 'flex';
        document.getElementById('total-label').textContent = `dari ${total} entri laporan`;
        document.getElementById('page-label').textContent  = `Halaman ${currentPage} / ${totalPages}`;
        document.getElementById('btn-prev').disabled = currentPage <= 1;
        document.getElementById('btn-next').disabled = currentPage >= totalPages;
        document.getElementById('btn-prev').style.opacity = currentPage <= 1 ? '0.4' : '1';
        document.getElementById('btn-next').style.opacity = currentPage >= totalPages ? '0.4' : '1';
    } else {
        paginationBar.style.display = 'none';
    }

    lucide.createIcons();
}

// ============================================================
//  BUILD RINCIAN BARANG PERSEDIAAN
// ============================================================
const BULAN_NAMES = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function fmtRupiah(n) {
    if (!n || n === 0) return '0';
    return new Intl.NumberFormat('id-ID').format(n);
}

function buildRincianHTML(tahun, bulan) {
    // Filter mutasi berdasarkan periode
    const periodStart = tahun !== 'Semua' ? new Date(parseInt(tahun), 0, 1) : null;   // 1 Jan tahun
    const periodEnd   = (() => {
        if (tahun === 'Semua') return null;
        if (bulan === 'Semua') return new Date(parseInt(tahun), 11, 31, 23, 59, 59);
        return new Date(parseInt(tahun), parseInt(bulan), 0, 23, 59, 59); // akhir bulan
    })();

    // Group by kode_kategori → { nama_kategori, kode_kategori, items[] }
    const kategoriMap = {};
    rincianMentah.forEach(b => {
        const katKey = b.kode_kategori || '000000';
        if (!kategoriMap[katKey]) {
            kategoriMap[katKey] = {
                kode_kategori: b.kode_kategori || '-',
                nama_kategori: b.nama_kategori || 'LAINNYA',
                items: []
            };
        }
        kategoriMap[katKey].items.push(b);
    });

    // Account code mapping — prefix digits of kode_kategori map to account group
    const akunMap = {};
    Object.values(kategoriMap).forEach(kat => {
        const akunKode = kat.kode_kategori.length >= 6 ? kat.kode_kategori.substring(0, 6) : '117111';
        if (!akunMap[akunKode]) akunMap[akunKode] = { nama: 'Barang Konsumsi', kategori: [] };
        akunMap[akunKode].kategori.push(kat);
    });

    const periodLabel = tahun === 'Semua' ? 'Semua Periode'
        : (bulan === 'Semua' ? `01-01-${tahun} s.d. 31-12-${tahun}`
            : `01-01-${tahun} s.d. ${String(new Date(parseInt(tahun), parseInt(bulan), 0).getDate()).padStart(2,'0')}-${String(bulan).padStart(2,'0')}-${tahun}`);

    const thS = 'padding:8px 6px;font-size:11px;border:1px solid #ccc;text-align:center;font-weight:bold;background:#f1f5f9;text-transform:uppercase;';
    const tdN = 'padding:6px;font-size:11px;border:1px solid #ddd;text-align:right;';
    const tdC = 'padding:6px;font-size:11px;border:1px solid #ddd;text-align:center;';
    const tdL = 'padding:6px;font-size:11px;border:1px solid #ddd;text-align:left;';

    let html = `
    <div style="padding:20px;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <td colspan="9" style="border:none !important; padding:30px 0 15px 0 !important; text-align:left;">
                        <div style="text-align:center;margin-bottom:18px;">
                            <h2 style="margin:0;font-size:15px;font-weight:bold;color:#000;">LAPORAN RINCIAN BARANG PERSEDIAAN</h2>
                            <p style="margin:4px 0;font-size:12px;color:#333;"><b>UNTUK PERIODE YANG BERAKHIR TANGGAL: ${periodLabel.split(' s.d. ')[1] || '-'}</b></p>
                            <p style="margin:0;font-size:12px;color:#333;"><b>TAHUN ANGGARAN: ${tahun === 'Semua' ? 'SEMUA' : tahun}</b></p>
                        </div>
                        <p style="font-size:11px;color:#333;margin:4px 0;">NAMA UAKPB: BADAN PUSAT STATISTIK KABUPATEN PRINGSEWU</p>
                        <p style="font-size:11px;color:#333;margin:4px 0 0 0;">PERIODE: ${periodLabel}</p>
                    </td>
                </tr>
                <tr>
                    <th rowspan="2" style="${thS}width:80px;">KODE</th>
                    <th rowspan="2" style="${thS}width:200px;">URAIAN</th>
                    <th colspan="2" style="${thS}">NILAI S/D AWAL PERIODE</th>
                    <th colspan="3" style="${thS}">MUTASI</th>
                    <th colspan="2" style="${thS}">NILAI S/D AKHIR PERIODE</th>
                </tr>
                <tr>
                    <th style="${thS}">JUMLAH</th>
                    <th style="${thS}">RUPIAH</th>
                    <th style="${thS}">MASUK</th>
                    <th style="${thS}">KELUAR</th>
                    <th style="${thS}">JUMLAH</th>
                    <th style="${thS}">JUMLAH</th>
                    <th style="${thS}">RUPIAH</th>
                </tr>
            </thead>
            <tbody>`;

    Object.entries(akunMap).forEach(([akunKode, akun]) => {
        akun.kategori.forEach(kat => {
            const items = kat.items;
            let katTotalAwalRp = 0, katTotalAkhirRp = 0;

        // Compute per-item
        const computed = items.map(b => {
            // Masuk dalam periode
            const masukPeriode = b.barang_masuk.filter(bm => {
                if (!periodStart) return true;
                const d = new Date(bm.waktu);
                return d >= periodStart && d <= periodEnd;
            }).reduce((s, bm) => s + bm.jumlah_masuk, 0);

            // Keluar dalam periode
            const keluarPeriode = b.pengajuan.filter(p => {
                if (!periodStart) return true;
                const d = new Date(p.waktu);
                return d >= periodStart && d <= periodEnd;
            }).reduce((s, p) => s + p.jumlah_disetujui, 0);

            // ===== LOGIKA STOK YANG BENAR =====
            // Rekonstruksi stokAkhirPeriode:
            //   Stok saat ini = stokAkhirPeriode + masukSetelah - keluarSetelah
            //   → stokAkhirPeriode = stok_aktual - masukSetelah + keluarSetelah
            let stokAkhirPeriode;
            if (!periodEnd) {
                // Semua periode → gunakan stok aktual sekarang
                stokAkhirPeriode = b.stok_aktual;
            } else {
                const masukSetelah = b.barang_masuk.filter(bm => new Date(bm.waktu) > periodEnd)
                    .reduce((s, bm) => s + bm.jumlah_masuk, 0);
                const keluarSetelah = b.pengajuan.filter(p => new Date(p.waktu) > periodEnd)
                    .reduce((s, p) => s + p.jumlah_disetujui, 0);
                stokAkhirPeriode = b.stok_aktual - masukSetelah + keluarSetelah;
            }

            // stokAwalPeriode = stokAkhirPeriode - masuk + keluar (dalam periode)
            const stokAwalPeriode = stokAkhirPeriode - masukPeriode + keluarPeriode;
            const net = masukPeriode - keluarPeriode;

            const harga    = b.harga_satuan || 0;
            const rupAwal  = stokAwalPeriode > 0 ? stokAwalPeriode * harga : 0;
            const rupAkhir = stokAkhirPeriode > 0 ? stokAkhirPeriode * harga : 0;

            return { ...b, stokAwal: stokAwalPeriode, masukPeriode, keluarPeriode, net, stokAkhir: stokAkhirPeriode, rupAwal, rupAkhir, harga };
        });

        computed.forEach(c => { katTotalAwalRp += c.rupAwal; katTotalAkhirRp += c.rupAkhir; });

        // Kategori header row
        html += `<tr style="background:#f0f4ff;">
            <td style="${tdC}"></td>
            <td style="${tdL}">
                <span style="font-weight:bold;color:#1a56a0;font-size:11px;">${kat.nama_kategori}</span>
            </td>
            <td colspan="2" style="${tdN}color:#1a56a0;font-weight:bold;">${katTotalAwalRp > 0 ? fmtRupiah(katTotalAwalRp) : ''}</td>
            <td colspan="3" style="${tdN}"></td>
            <td colspan="2" style="${tdN}color:#1a56a0;font-weight:bold;">${katTotalAkhirRp > 0 ? fmtRupiah(katTotalAkhirRp) : ''}</td>
        </tr>`;

        // Item rows
        computed.forEach(c => {
            html += `<tr>
                <td style="${tdC}font-family:monospace;">${c.kode_barang || '-'}</td>
                <td style="${tdL}">${c.nama_barang}</td>
                <td style="${tdN}">${c.stokAwal > 0 ? c.stokAwal : 0}</td>
                <td style="${tdN}">${c.rupAwal > 0 ? fmtRupiah(c.rupAwal) : 0}</td>
                <td style="${tdN}">${c.masukPeriode > 0 ? c.masukPeriode : 0}</td>
                <td style="${tdN}">${c.keluarPeriode > 0 ? c.keluarPeriode : 0}</td>
                <td style="${tdN}">${c.net}</td>
                <td style="${tdN}">${c.stokAkhir > 0 ? c.stokAkhir : 0}</td>
                <td style="${tdN}">${c.rupAkhir > 0 ? fmtRupiah(c.rupAkhir) : 0}</td>
            </tr>`;
        });
        });
    });

    html += `</tbody></table></div>`;
    return html;
}

// ============================================================
//  BUILD HARGA PER BARANG
// ============================================================
function buildHargaBarangHTML(tahun, bulan) {
    const periodEnd = (() => {
        if (tahun === 'Semua') return new Date();
        if (bulan === 'Semua') return new Date(parseInt(tahun), 11, 31, 23, 59, 59);
        return new Date(parseInt(tahun), parseInt(bulan), 0, 23, 59, 59);
    })();

    const pad2 = n => String(n).padStart(2, '0');
    const tglLabel = `${pad2(periodEnd.getDate())}-${pad2(periodEnd.getMonth()+1)}-${periodEnd.getFullYear()}`;
    const tahunLabel = tahun === 'Semua' ? new Date().getFullYear() : tahun;

    // Group by kode_kategori → { nama_kategori, kode_kategori, items[] }
    const kategoriMap = {};
    rincianMentah.forEach(b => {
        const katKey = b.kode_kategori || '000000';
        if (!kategoriMap[katKey]) {
            kategoriMap[katKey] = {
                kode_kategori: b.kode_kategori || '-',
                nama_kategori: b.nama_kategori || 'LAINNYA',
                items: []
            };
        }
        kategoriMap[katKey].items.push(b);
    });

    // Account code mapping — prefix digits of kode_kategori map to account group
    const akunMap = {};
    Object.values(kategoriMap).forEach(kat => {
        // Use first 6 chars of kode_kategori as akun group, or '117111' as default
        const akunKode = kat.kode_kategori.length >= 6 ? kat.kode_kategori.substring(0, 6) : '117111';
        if (!akunMap[akunKode]) akunMap[akunKode] = { nama: 'Barang Konsumsi', kategori: [] };
        akunMap[akunKode].kategori.push(kat);
    });

    const thS = 'padding:8px 6px;font-size:11px;border:1px solid #ccc;text-align:center;font-weight:bold;background:#f1f5f9;text-transform:uppercase;';
    const tdN = 'padding:6px 8px;font-size:11px;border:1px solid #ddd;text-align:right;';
    const tdC = 'padding:6px 8px;font-size:11px;border:1px solid #ddd;text-align:right;font-family:monospace;';
    const tdL = 'padding:6px 8px;font-size:11px;border:1px solid #ddd;text-align:left;';

    let html = `
    <div style="padding:20px;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <td colspan="3" style="border:none !important; padding:30px 0 15px 0 !important;">
                        <div style="text-align:center;margin-bottom:12px;">
                            <h2 style="margin:0;font-size:16px;font-weight:bold;color:#000;">LAPORAN PERSEDIAAN</h2>
                            <p style="margin:4px 0;font-size:12px;font-weight:bold;color:#000;">UNTUK PERIODE YANG BERAKHIR TANGGAL ${tglLabel}</p>
                            <p style="margin:0;font-size:12px;font-weight:bold;color:#000;">TAHUN ANGGARAN : ${tahunLabel}</p>
                        </div>
                        <p style="font-size:11px;color:#333;margin:4px 0;text-align:left;">NAMA UAKPB : BADAN PUSAT STATISTIK KABUPATEN PRINGSEWU</p>
                    </td>
                </tr>
                <tr>
                    <th style="${thS}width:120px;">KODE</th>
                    <th style="${thS}">U R A I A N</th>
                    <th style="${thS}width:140px;">NILAI PER<br>${tglLabel}</th>
                </tr>
            </thead>
            <tbody>`;

    Object.entries(akunMap).forEach(([akunKode, akun]) => {
        // Level 1: Account group row removed as per request

        akun.kategori.forEach(kat => {
            let katTotal = 0;
            kat.items.forEach(b => {
                const stok = b.stok_aktual || 0;
                const harga = b.harga_satuan || 0;
                katTotal += stok > 0 ? stok * harga : 0;
            });

            // Level 2: Category row (blue text)
            html += `<tr>
                <td style="${tdC}color:#1a56a0;">${kat.kode_kategori}</td>
                <td style="${tdL}color:#1a56a0;font-weight:normal;">${kat.nama_kategori}</td>
                <td style="${tdN}color:#1a56a0;">${katTotal > 0 ? fmtRupiah(katTotal) : ''}</td>
            </tr>`;

            // Level 3: Item rows
            kat.items.forEach(b => {
                const stok = b.stok_aktual || 0;
                const harga = b.harga_satuan || 0;
                const nilai = stok > 0 ? stok * harga : 0;
                html += `<tr>
                    <td style="${tdC}">${b.kode_barang || '-'}</td>
                    <td style="${tdL}">${b.nama_barang}</td>
                    <td style="${tdN}">${fmtRupiah(nilai)}</td>
                </tr>`;
            });
        });
    });

    html += `</tbody></table></div>`;
    return html;
}

function onViewModeChange() {
    const mode = document.getElementById('viewMode').value;
    document.getElementById('divisiFilter').style.display  = mode === 'divisi' ? 'block' : 'none';
    document.getElementById('aggTahun').style.display      = 'block';
    document.getElementById('aggBulan').style.display      = 'block';
    currentPage = 1;
    renderTable();
}

function onPerPageChange() {
    itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
    currentPage = 1;
    renderTable();
}

function prevPage() { if (currentPage > 1) { currentPage--; renderTable(); } }
function nextPage() { currentPage++; renderTable(); }

function debounceRender() {
    clearTimeout(_debounceTimer);
    _debounceTimer = setTimeout(() => { currentPage = 1; renderTable(); }, 400);
}

// ============================================================
//  MODAL DETAIL (Tabel 1 Grouped)
// ============================================================
function openDetailModal(group) {
    document.getElementById('detail-header').innerHTML = `
        <div><span style="font-size:11px;color:#64748b;display:block;">Waktu Pengajuan:</span><strong style="font-size:13px;color:#1f4068;">${fmtWaktu(group.waktu_pengajuan)}</strong></div>
        <div style="text-align:right;"><span style="font-size:11px;color:#64748b;display:block;">Nama Pemohon:</span><strong style="font-size:13px;color:#1f4068;">${group.nama_lengkap}</strong></div>`;

    let rows = '';
    group.items.forEach(item => {
        const diminta = parseInt(item.jumlah_diminta);
        let disetujui = 0, ditolak = 0;
        if (item.status_pengajuan === 'rejected') { ditolak = diminta; }
        else { disetujui = item.jumlah_disetujui !== null ? parseInt(item.jumlah_disetujui) : 0; ditolak = diminta - disetujui; }
        const src = item.foto_barang ? `/uploads/${item.foto_barang}` : null;
        const imgHtml = src ? `<img src="${src}" style="width:100%;height:100%;object-fit:cover;">` : '';
        rows += `<tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px;"><div style="width:35px;height:35px;margin:0 auto;overflow:hidden;border-radius:4px;border:1px solid #e2e8f0;">${imgHtml}</div></td>
            <td style="padding:10px;font-size:12px;color:#000;text-align:center;font-weight:normal;">${item.nama_barang}</td>
            <td style="padding:10px;font-size:12px;color:#000;font-weight:normal;">${kode(item)}</td>
            <td style="padding:10px;font-size:12px;color:#000;font-weight:normal;">${diminta}</td>
            <td style="padding:10px;font-size:12px;color:#000;font-weight:normal;">${disetujui}</td>
            <td style="padding:10px;font-size:12px;color:#000;font-weight:normal;">${ditolak}</td>
        </tr>`;
    });
    document.getElementById('detail-tbody').innerHTML = rows;

    const alasan = group.items[0]?.alasan || null;
    document.getElementById('detail-alasan').innerHTML = `
        <span style="font-size:11px;color:#64748b;display:block;margin-bottom:8px;font-weight:bold;">ALASAN / TUJUAN:</span>
        <span style="font-size:13px;color:#475569;font-style:${alasan ? 'normal' : 'italic'};">${alasan || 'Tidak ada alasan.'}</span>`;

    openModal('detail-modal');
    lucide.createIcons();
}

// ============================================================
//  MODAL EKSPOR
// ============================================================
function setExportFormat(fmt) {
    exportFormat = fmt;
    document.getElementById('btn-csv').style.border    = fmt === 'xlsx' ? '2px solid #27ae60' : '1px solid #cbd5e1';
    document.getElementById('btn-csv').style.background= fmt === 'xlsx' ? '#ecfdf5' : 'white';
    document.getElementById('btn-pdf').style.border    = fmt === 'pdf' ? '2px solid #e74c3c' : '1px solid #cbd5e1';
    document.getElementById('btn-pdf').style.background= fmt === 'pdf' ? '#fef2f2' : 'white';
}

const exportColumnsConfig = {
    'grouped': ['Waktu Pengajuan', 'Pemohon', 'Tim Kerja', 'Total Item', 'Status'],
    'itemized': ['Waktu', 'Pemohon', 'Tim Kerja', 'Kode', 'Nama Barang', 'Diminta', 'Disetujui', 'Ditolak'],
    'aggregate_gabungan': ['Kode', 'Kode Kategori', 'Kategori', 'Nama Barang', 'Mutasi Masuk', 'Mutasi Keluar', 'Satuan']
};

function onExportTabelChange() {
    const tabel = document.getElementById('export-tabel').value;
    document.getElementById('export-divisi-wrap').style.display = tabel === 'divisi' ? 'block' : 'none';
    document.getElementById('export-judul-divisi-wrap').style.display = tabel === 'divisi' ? 'block' : 'none';

    const kolomWrap = document.getElementById('export-kolom-wrap');
    const kolomContainer = document.getElementById('export-kolom-container');

    if (exportColumnsConfig[tabel]) {
        kolomWrap.style.display = 'block';
        kolomContainer.innerHTML = exportColumnsConfig[tabel].map((col, idx) => `
            <label style="font-size:12px; color:#1f4068; display:flex; align-items:center; gap:5px; background:#f1f5f9; padding:5px 10px; border-radius:4px; cursor:pointer;">
                <input type="checkbox" class="export-col-cb" value="${idx}" checked> ${col}
            </label>
        `).join('');
    } else {
        kolomWrap.style.display = 'none';
        kolomContainer.innerHTML = '';
    }
}

function executeExport() {
    const tahun  = document.getElementById('export-tahun').value;
    const bulan  = document.getElementById('export-bulan').value;
    const tabel  = document.getElementById('export-tabel').value;
    const divisi = document.getElementById('export-divisi').value;
    let judul = "LAPORAN RIWAYAT PENGAJUAN INVENTARIS BPS";
    
    if (tabel === 'aggregate_gabungan') judul = "LAPORAN AGREGAT BARANG";
    else if (tabel === 'divisi') {
        const customJudul = document.getElementById('export-judul-divisi').value.trim();
        judul = customJudul ? customJudul : "LAPORAN PENGELUARAN TIM KERJA";
    }
    else if (tabel === 'grouped') judul = "LAPORAN HISTORY PENGAJUAN (GABUNGAN)";
    else if (tabel === 'itemized') judul = "LAPORAN HISTORY PENGAJUAN (PER BARANG)";

    let selectedColIndices = null;
    if (exportColumnsConfig[tabel]) {
        const cbs = document.querySelectorAll('.export-col-cb');
        selectedColIndices = Array.from(cbs).filter(cb => cb.checked).map(cb => parseInt(cb.value));
        if (selectedColIndices.length === 0) {
            alert('Silakan pilih minimal satu kolom untuk diekspor.');
            return;
        }
    }

    // === RINCIAN: ekspor khusus ===
    if (tabel === 'rincian') {
        const periodStart = tahun !== 'Semua' ? new Date(parseInt(tahun), 0, 1) : null;
        const periodEnd   = (() => {
            if (tahun === 'Semua') return null;
            if (bulan === 'Semua') return new Date(parseInt(tahun), 11, 31, 23, 59, 59);
            return new Date(parseInt(tahun), parseInt(bulan), 0, 23, 59, 59);
        })();
        const periodLabel = tahun === 'Semua' ? 'Semua Periode'
            : (bulan === 'Semua' ? `01-01-${tahun} s.d. 31-12-${tahun}`
                : `01-01-${tahun} s.d. ${String(new Date(parseInt(tahun), parseInt(bulan), 0).getDate()).padStart(2,'0')}-${String(bulan).padStart(2,'0')}-${tahun}`);

        const kategoriMap = {};
        rincianMentah.forEach(b => {
            const katKey = b.kode_kategori || '000000';
            if (!kategoriMap[katKey]) {
                kategoriMap[katKey] = {
                    kode_kategori: b.kode_kategori || '-',
                    nama_kategori: b.nama_kategori || 'LAINNYA',
                    items: []
                };
            }
            kategoriMap[katKey].items.push(b);
        });

        const akunMap = {};
        Object.values(kategoriMap).forEach(kat => {
            const akunKode = kat.kode_kategori.length >= 6 ? kat.kode_kategori.substring(0, 6) : '117111';
            if (!akunMap[akunKode]) akunMap[akunKode] = { nama: 'Barang Konsumsi', kategori: [] };
            akunMap[akunKode].kategori.push(kat);
        });

        const aoa = [];
        aoa.push(['LAPORAN RINCIAN BARANG PERSEDIAAN']);
        aoa.push([`PERIODE: ${periodLabel}`]);
        aoa.push([]);
        const headerRow = ['KODE', 'URAIAN', 'NILAI AWAL - JUMLAH', 'NILAI AWAL - RUPIAH', 'MUTASI MASUK', 'MUTASI KELUAR', 'MUTASI JUMLAH', 'NILAI AKHIR - JUMLAH', 'NILAI AKHIR - RUPIAH'];
        aoa.push(headerRow);

        const merges = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 8 } },
            { s: { r: 1, c: 0 }, e: { r: 1, c: 8 } },
        ];

        Object.entries(akunMap).forEach(([akunKode, akun]) => {
            akun.kategori.forEach(kat => {
                const items = kat.items;
            let katTotalAwalRp = 0, katTotalAkhirRp = 0;

            const computed = items.map(b => {
                const masukPeriode = b.barang_masuk.filter(bm => {
                    if (!periodStart) return true;
                    const d = new Date(bm.waktu); return d >= periodStart && d <= periodEnd;
                }).reduce((s, bm) => s + bm.jumlah_masuk, 0);
                const keluarPeriode = b.pengajuan.filter(p => {
                    if (!periodStart) return true;
                    const d = new Date(p.waktu); return d >= periodStart && d <= periodEnd;
                }).reduce((s, p) => s + p.jumlah_disetujui, 0);

                let stokAkhirPeriode;
                if (!periodEnd) {
                    stokAkhirPeriode = b.stok_aktual;
                } else {
                    const masukSetelah  = b.barang_masuk.filter(bm => new Date(bm.waktu) > periodEnd)
                        .reduce((s, bm) => s + bm.jumlah_masuk, 0);
                    const keluarSetelah = b.pengajuan.filter(p => new Date(p.waktu) > periodEnd)
                        .reduce((s, p) => s + p.jumlah_disetujui, 0);
                    stokAkhirPeriode = b.stok_aktual - masukSetelah + keluarSetelah;
                }
                const stokAwalPeriode = stokAkhirPeriode - masukPeriode + keluarPeriode;

                const harga = b.harga_satuan || 0;
                const rupAwal = stokAwalPeriode > 0 ? stokAwalPeriode * harga : 0;
                const rupAkhir = stokAkhirPeriode > 0 ? stokAkhirPeriode * harga : 0;

                return { ...b, stokAwalPeriode, masukPeriode, keluarPeriode, net: masukPeriode - keluarPeriode, stokAkhirPeriode, rupAwal, rupAkhir, harga };
            });

            computed.forEach(c => { katTotalAwalRp += c.rupAwal; katTotalAkhirRp += c.rupAkhir; });

            // Kategori header row
            const rIdx = aoa.length;
            aoa.push(['', kat.nama_kategori, katTotalAwalRp > 0 ? katTotalAwalRp : '', '', '', '', '', katTotalAkhirRp > 0 ? katTotalAkhirRp : '', '']);
            merges.push({ s: { r: rIdx, c: 2 }, e: { r: rIdx, c: 3 } });
            merges.push({ s: { r: rIdx, c: 4 }, e: { r: rIdx, c: 6 } });
            merges.push({ s: { r: rIdx, c: 7 }, e: { r: rIdx, c: 8 } });

            computed.forEach(c => {
                aoa.push([
                    c.kode_barang || '-', c.nama_barang,
                    c.stokAwalPeriode > 0 ? c.stokAwalPeriode : 0,
                    c.rupAwal,
                    c.masukPeriode, c.keluarPeriode, c.net,
                    c.stokAkhirPeriode > 0 ? c.stokAkhirPeriode : 0,
                    c.rupAkhir
                ]);
            });
        });
        });

        if (exportFormat === 'xlsx') {
            const ws = XLSX.utils.aoa_to_sheet(aoa);
            ws['!merges'] = merges;
            ws['!cols'] = [{ wch: 14 }, { wch: 38 }, { wch: 16 }, { wch: 20 }, { wch: 14 }, { wch: 14 }, { wch: 14 }, { wch: 16 }, { wch: 20 }];

            const bAll_r = { top:{style:'thin',color:{rgb:'AAAAAA'}}, bottom:{style:'thin',color:{rgb:'AAAAAA'}}, left:{style:'thin',color:{rgb:'AAAAAA'}}, right:{style:'thin',color:{rgb:'AAAAAA'}} };
            const bMed_r = { top:{style:'medium',color:{rgb:'154C79'}}, bottom:{style:'medium',color:{rgb:'154C79'}}, left:{style:'medium',color:{rgb:'154C79'}}, right:{style:'medium',color:{rgb:'154C79'}} };

            // Title rows
            if (ws['A1']) ws['A1'].s = { font:{bold:true,sz:14,color:{rgb:'1A3558'}}, alignment:{horizontal:'center',vertical:'center'} };
            if (ws['A2']) ws['A2'].s = { font:{bold:true,sz:11,color:{rgb:'333333'}}, alignment:{horizontal:'center',vertical:'center'} };

            // Column header row (row index 3 = 0-based)
            headerRow.forEach((_, ci) => {
                const addr = XLSX.utils.encode_cell({ r: 3, c: ci });
                if (ws[addr]) ws[addr].s = {
                    font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 10 },
                    fill: { fgColor: { rgb: '154C79' } },
                    alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
                    border: bMed_r
                };
            });

            // Data rows (row index 4 onwards)
            for (let ri = 4; ri < aoa.length; ri++) {
                const rowArr = aoa[ri];
                if (!rowArr) continue;
                const isKatRow = rowArr[0] === ''; // kategori rows have empty first cell
                for (let ci = 0; ci < headerRow.length; ci++) {
                    const addr = XLSX.utils.encode_cell({ r: ri, c: ci });
                    if (!ws[addr]) ws[addr] = { t: 'z' };
                    const isNumCol = ci >= 2;
                    ws[addr].s = isKatRow ? {
                        font: { bold: true, color: { rgb: '1A56A0' }, sz: 10 },
                        fill: { fgColor: { rgb: 'EDF3FC' } },
                        alignment: { horizontal: isNumCol ? 'right' : 'left', vertical: 'center' },
                        border: bAll_r
                    } : {
                        font: { sz: 10 },
                        fill: { fgColor: { rgb: ri % 2 === 0 ? 'FFFFFF' : 'F9FAFB' } },
                        alignment: { horizontal: isNumCol ? 'right' : (ci === 0 ? 'right' : 'left'), vertical: 'center' },
                        border: bAll_r
                    };
                    if (isNumCol && ws[addr].t === 'n') ws[addr].z = '#,##0';
                }
            }

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Rincian');
            XLSX.writeFile(wb, `Laporan_Rincian_${tahun}_${bulan}.xlsx`);
        } else {
            const pWin = window.open('', '_blank');
            const contentHTML = buildRincianHTML(tahun, bulan);
            const html = `<html><head><title>${judul}</title><style>
                body{font-family:Helvetica,Arial,sans-serif;padding:20px;color:#000;margin:0;}
                table{width:100%;border-collapse:collapse;font-size:11px;}
                th,td{border:1px solid #000 !important;color:#000 !important;font-size:11px !important;}
                @media print {
                    @page { size: landscape; margin: 0; }
                    body { padding: 0 20px; }
                }
            </style></head>
            <body onload="window.print();">
                ${contentHTML}
            </body></html>`;
            pWin.document.write(html);
            pWin.document.close();
        }

        closeModal('export-modal');
        return;
    }

    // === HARGA BARANG: ekspor khusus ===
    if (tabel === 'harga_barang') {
        if (exportFormat === 'pdf') {
            const pWin = window.open('', '_blank');
            const contentHTML = buildHargaBarangHTML(tahun, bulan);
            const html = `<html><head><title>Tabel Harga Per Barang</title><style>
                body{font-family:Helvetica,Arial,sans-serif;padding:20px;color:#000;margin:0;}
                table{width:100%;border-collapse:collapse;font-size:11px;}
                th,td{border:1px solid #000 !important;font-size:11px !important;}
                @media print {
                    @page { margin: 0; }
                    body { padding: 0 20px; }
                }
            </style></head>
            <body onload="window.print();">
                ${contentHTML}
            </body></html>`;
            pWin.document.write(html);
            pWin.document.close();
        } else {
            // Excel Export
            const periodEnd_hb = (() => {
                if (tahun === 'Semua') return new Date();
                if (bulan === 'Semua') return new Date(parseInt(tahun), 11, 31, 23, 59, 59);
                return new Date(parseInt(tahun), parseInt(bulan), 0, 23, 59, 59);
            })();
            const pad2_hb = n => String(n).padStart(2, '0');
            const tglLabel_hb = `${pad2_hb(periodEnd_hb.getDate())}-${pad2_hb(periodEnd_hb.getMonth()+1)}-${periodEnd_hb.getFullYear()}`;
            const tahunLabel_hb = tahun === 'Semua' ? new Date().getFullYear() : tahun;

            const kategoriMap_hb = {};
            rincianMentah.forEach(b => {
                const katKey = b.kode_kategori || '000000';
                if (!kategoriMap_hb[katKey]) kategoriMap_hb[katKey] = { kode_kategori: b.kode_kategori || '-', nama_kategori: b.nama_kategori || 'LAINNYA', items: [] };
                kategoriMap_hb[katKey].items.push(b);
            });

            const aoa_hb = [];
            aoa_hb.push(['LAPORAN PERSEDIAAN']);
            aoa_hb.push([`UNTUK PERIODE YANG BERAKHIR TANGGAL ${tglLabel_hb}`]);
            aoa_hb.push([`TAHUN ANGGARAN : ${tahunLabel_hb}`]);
            aoa_hb.push([]);
            aoa_hb.push(['NAMA UAKPB : BADAN PUSAT STATISTIK KABUPATEN PRINGSEWU']);
            aoa_hb.push([]);
            const headerRow_hb = ['KODE', 'URAIAN', `NILAI PER ${tglLabel_hb}`];
            aoa_hb.push(headerRow_hb);

            const merges_hb = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: 2 } },
                { s: { r: 1, c: 0 }, e: { r: 1, c: 2 } },
                { s: { r: 2, c: 0 }, e: { r: 2, c: 2 } },
                { s: { r: 4, c: 0 }, e: { r: 4, c: 2 } }
            ];

            Object.values(kategoriMap_hb).forEach(kat => {
                let katTotal = 0;
                kat.items.forEach(b => {
                    const stok = b.stok_aktual || 0;
                    const harga = b.harga_satuan || 0;
                    katTotal += stok > 0 ? stok * harga : 0;
                });
                aoa_hb.push([kat.kode_kategori, kat.nama_kategori, katTotal > 0 ? katTotal : 0]);
                kat.items.forEach(b => {
                    const stok = b.stok_aktual || 0;
                    const harga = b.harga_satuan || 0;
                    const nilai = stok > 0 ? stok * harga : 0;
                    aoa_hb.push([b.kode_barang || '-', b.nama_barang, nilai]);
                });
            });

            const ws_hb = XLSX.utils.aoa_to_sheet(aoa_hb);
            ws_hb['!merges'] = merges_hb;
            ws_hb['!cols'] = [{ wch: 18 }, { wch: 48 }, { wch: 25 }];

            const bAll_hb = { top:{style:'thin',color:{rgb:'AAAAAA'}}, bottom:{style:'thin',color:{rgb:'AAAAAA'}}, left:{style:'thin',color:{rgb:'AAAAAA'}}, right:{style:'thin',color:{rgb:'AAAAAA'}} };
            const bMed_hb = { top:{style:'medium',color:{rgb:'154C79'}}, bottom:{style:'medium',color:{rgb:'154C79'}}, left:{style:'medium',color:{rgb:'154C79'}}, right:{style:'medium',color:{rgb:'154C79'}} };

            if (ws_hb['A1']) ws_hb['A1'].s = { font:{bold:true,sz:14,color:{rgb:'1A3558'}}, alignment:{horizontal:'center',vertical:'center'} };
            if (ws_hb['A2']) ws_hb['A2'].s = { font:{bold:true,sz:11,color:{rgb:'333333'}}, alignment:{horizontal:'center',vertical:'center'} };
            if (ws_hb['A3']) ws_hb['A3'].s = { font:{bold:true,sz:11,color:{rgb:'333333'}}, alignment:{horizontal:'center',vertical:'center'} };
            if (ws_hb['A5']) ws_hb['A5'].s = { font:{sz:10,italic:true,color:{rgb:'555555'}}, alignment:{horizontal:'left',vertical:'center'} };

            headerRow_hb.forEach((_, ci) => {
                const addr = XLSX.utils.encode_cell({ r: 6, c: ci });
                if (ws_hb[addr]) ws_hb[addr].s = {
                    font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 10 },
                    fill: { fgColor: { rgb: '154C79' } },
                    alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
                    border: bMed_hb
                };
            });

            for (let ri = 7; ri < aoa_hb.length; ri++) {
                const rowArr = aoa_hb[ri];
                if (!rowArr) continue;
                const isKatRow = rowArr && rowArr[1] && !rincianMentah.some(b => b.kode_barang === rowArr[0]);
                for (let ci = 0; ci < 3; ci++) {
                    const addr = XLSX.utils.encode_cell({ r: ri, c: ci });
                    if (!ws_hb[addr]) ws_hb[addr] = { t: 'z' };
                    ws_hb[addr].s = isKatRow ? {
                        font: { bold: true, color: { rgb: '1A56A0' }, sz: 10 },
                        fill: { fgColor: { rgb: 'EDF3FC' } },
                        alignment: { horizontal: ci === 2 ? 'right' : (ci === 0 ? 'right' : 'left'), vertical: 'center' },
                        border: bAll_hb
                    } : {
                        font: { sz: 10 },
                        fill: { fgColor: { rgb: ri % 2 === 0 ? 'FFFFFF' : 'F9FAFB' } },
                        alignment: { horizontal: ci === 2 ? 'right' : (ci === 0 ? 'right' : 'left'), vertical: 'center' },
                        border: bAll_hb
                    };
                    if (ci === 2 && ws_hb[addr].t === 'n') ws_hb[addr].z = '#,##0';
                }
            }

            const wb_hb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb_hb, ws_hb, 'Harga Barang');
            XLSX.writeFile(wb_hb, `Laporan_Harga_Barang_${tahun}_${bulan}.xlsx`);
        }

        closeModal('export-modal');
        return;
    }

    let sourceData = (tabel === 'aggregate_masuk') ? barangMasukMentah : riwayatMentah;

    let data = sourceData.filter(item => {
        if (!item.waktu_pengajuan && !item.waktu_masuk) return true; // Safety check
        const d  = new Date(item.waktu_pengajuan || item.waktu_masuk);
        const mY = tahun === 'Semua' || d.getFullYear().toString() === tahun;
        const mM = bulan === 'Semua' || (d.getMonth()+1).toString() === bulan;
        const mD = (tabel !== 'divisi' && tabel !== 'grouped' && tabel !== 'itemized') || divisi === 'Semua Divisi' || item.tim_kerja === divisi;
        return mY && mM && mD;
    });

    let exportData = data;

    if (tabel === 'aggregate_gabungan') {
        exportData = buildAggregateGabungan(tahun, bulan, '').map(i => ({
            kode: kode(i),
            kode_kategori: i.kode_kategori || '-',
            nama_kategori: i.nama_kategori || '-',
            nama_barang: i.nama_barang,
            masuk: i.masuk,
            keluar: i.keluar,
            satuan: i.satuan || '-'
        }));
    } else if (tabel === 'divisi') {
        exportData = buildAggregateDivisi(data).map(i => ({
            tim_kerja: i.divisi,
            kode: kode(i),
            kode_kategori: i.kode_kategori || '-',
            nama_kategori: i.nama_kategori || '-',
            nama_barang: i.nama_barang,
            jumlah: i.jumlah,
            satuan: i.satuan || '-'
        }));
    } else if (tabel === 'grouped') {
        const groups = buildGrouped(data);
        exportData = groups.map(g => {
            let st = 'Disetujui';
            if (g.status_pengajuan === 'rejected') st = 'Ditolak';
            else if (g.status_pengajuan === 'sebagian') st = 'Sebagian';
            return { waktu: fmtWaktu(g.waktu_pengajuan), pemohon: g.nama_lengkap, tim_kerja: g.tim_kerja || '-', jumlah_item: g.totalItem, status: st };
        });
    }

    if (exportData.length === 0) {
        alert('Tidak ada data yang cocok untuk diekspor pada periode tersebut.');
        return;
    }

    if (exportFormat === 'xlsx') {
        // ===== EXCEL EXPORT (SheetJS) =====
        let aoa = [];

        // Judul
        aoa.push([judul]);
        aoa.push([]);

        let header = [];
        if (tabel === 'divisi') { header = ['No', 'Tim Kerja', 'Kode', 'Kode Kategori', 'Kategori', 'Nama Barang', 'Jumlah Barang']; }
        else if (tabel === 'aggregate_gabungan') { header = exportColumnsConfig['aggregate_gabungan']; }
        else if (tabel === 'grouped') { header = exportColumnsConfig['grouped']; }
        else { header = exportColumnsConfig['itemized']; }

        if (selectedColIndices) {
            header = header.filter((_, i) => selectedColIndices.includes(i));
        }
        aoa.push(header);

        exportData.forEach((r, index) => {
            let rowData = [];
            if (tabel === 'divisi') {
                rowData = [index + 1, r.tim_kerja, r.kode, r.kode_kategori, r.nama_kategori, r.nama_barang, r.jumlah];
            } else if (tabel === 'aggregate_gabungan') {
                rowData = [r.kode, r.kode_kategori, r.nama_kategori, r.nama_barang, r.masuk, r.keluar, r.satuan];
            } else if (tabel === 'grouped') {
                rowData = [r.waktu, r.pemohon, r.tim_kerja, r.jumlah_item, r.status];
            } else {
                const dm = parseInt(r.jumlah_diminta);
                let ds = 0, dt = 0;
                if (r.status_pengajuan === 'rejected') { dt = dm; } else { ds = r.jumlah_disetujui !== null ? parseInt(r.jumlah_disetujui) : 0; dt = dm - ds; }
                rowData = [fmtWaktu(r.waktu_pengajuan), r.nama_lengkap, r.tim_kerja||'-', kode(r), r.nama_barang, dm, ds, dt];
            }

            if (selectedColIndices) {
                rowData = rowData.filter((_, i) => selectedColIndices.includes(i));
            }
            aoa.push(rowData);
        });

        const ws = XLSX.utils.aoa_to_sheet(aoa);

        // Border helpers
        const bThin = { top:{style:'thin',color:{rgb:'AAAAAA'}}, bottom:{style:'thin',color:{rgb:'AAAAAA'}}, left:{style:'thin',color:{rgb:'AAAAAA'}}, right:{style:'thin',color:{rgb:'AAAAAA'}} };
        const bMedH = { top:{style:'medium',color:{rgb:'154C79'}}, bottom:{style:'medium',color:{rgb:'154C79'}}, left:{style:'medium',color:{rgb:'154C79'}}, right:{style:'medium',color:{rgb:'154C79'}} };

        // Merge title row
        const colCount = aoa[2] ? aoa[2].length : 8;
        ws['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: colCount - 1 } }];

        // Style title cell
        if (ws['A1']) {
            ws['A1'].s = {
                font: { bold: true, sz: 14, color: { rgb: '1A3558' } },
                alignment: { horizontal: 'center', vertical: 'center' }
            };
        }

        // Style header row (row index 2 = 0-based)
        const hRowIdx = 2;
        aoa[hRowIdx] && aoa[hRowIdx].forEach((col, ci) => {
            const cellAddr = XLSX.utils.encode_cell({ r: hRowIdx, c: ci });
            if (!ws[cellAddr]) ws[cellAddr] = { t: 's', v: col };
            ws[cellAddr].s = {
                font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 10 },
                fill: { fgColor: { rgb: '154C79' } },
                alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
                border: bMedH
            };
        });

        // Style data rows (starting at row index 3)
        for (let ri = 3; ri < aoa.length; ri++) {
            const rowArr = aoa[ri];
            if (!rowArr) continue;
            rowArr.forEach((val, ci) => {
                const addr = XLSX.utils.encode_cell({ r: ri, c: ci });
                if (!ws[addr]) ws[addr] = { t: 'z' };
                const isNum = typeof val === 'number';
                ws[addr].s = {
                    font: { sz: 10 },
                    fill: { fgColor: { rgb: ri % 2 === 0 ? 'FFFFFF' : 'F4F8FF' } },
                    alignment: { horizontal: isNum ? 'right' : 'left', vertical: 'center' },
                    border: bThin
                };
                if (isNum) ws[addr].z = '#,##0';
            });
        }

        // Auto column width
        const colWidths = aoa[hRowIdx] ? aoa[hRowIdx].map((_, ci) => {
            let max = 10;
            aoa.forEach(row => {
                if (row && row[ci] != null) max = Math.max(max, String(row[ci]).length + 2);
            });
            return { wch: Math.min(max, 50) };
        }) : [];
        ws['!cols'] = colWidths;

        // Row heights
        ws['!rows'] = ws['!rows'] || [];
        ws['!rows'][0] = { hpt: 26 };
        ws['!rows'][hRowIdx] = { hpt: 22 };

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Laporan');
        XLSX.writeFile(wb, `Laporan_${tabel}_${tahun}.xlsx`);
    } else {
        // PDF
        const pWin = window.open('', '_blank');
        let colHeaders, bodyRows;
        
        if (tabel === 'aggregate_gabungan' || tabel === 'grouped' || tabel === 'itemized') {
            const hList = exportColumnsConfig[tabel];
            const sIdx = selectedColIndices || hList.map((_, i) => i);
            colHeaders = sIdx.map(i => `<th>${hList[i]}</th>`).join('');
            
            bodyRows = exportData.map(r => {
                let rowVals = [];
                if (tabel === 'aggregate_gabungan') {
                    rowVals = [r.kode, r.kode_kategori, `<td class="left">${r.nama_kategori}</td>`, `<td class="left">${r.nama_barang}</td>`, r.masuk, r.keluar, r.satuan];
                } else if (tabel === 'grouped') {
                    rowVals = [r.waktu, `<td class="left">${r.pemohon}</td>`, r.tim_kerja, r.jumlah_item, r.status];
                } else {
                    const dm = parseInt(r.jumlah_diminta);
                    let ds = 0, dt = 0;
                    if (r.status_pengajuan === 'rejected') { dt = dm; } else { ds = r.jumlah_disetujui !== null ? parseInt(r.jumlah_disetujui) : 0; dt = dm - ds; }
                    rowVals = [fmtWaktu(r.waktu_pengajuan), `<td class="left">${r.nama_lengkap}</td>`, r.tim_kerja||'-', kode(r), `<td class="left">${r.nama_barang}</td>`, dm, ds, dt];
                }
                
                return '<tr>' + sIdx.map(i => {
                    let val = rowVals[i];
                    return String(val).startsWith('<td') ? val : `<td>${val}</td>`;
                }).join('') + '</tr>';
            }).join('');
        } else if (tabel === 'divisi') {
            colHeaders = '<th>No</th><th>Tim Kerja</th><th>Kode</th><th>Kode Kategori</th><th>Kategori</th><th>Nama Barang</th><th>Jumlah Barang</th>';
            bodyRows = exportData.map((item, index) => `<tr><td>${index + 1}</td><td class="left">${item.tim_kerja}</td><td>${item.kode}</td><td>${item.kode_kategori}</td><td class="left">${item.nama_kategori}</td><td class="left">${item.nama_barang}</td><td>${item.jumlah}</td></tr>`).join('');
        }

        // Buat label periode
        const endDay = (() => {
            if (tahun === 'Semua') return null;
            if (bulan === 'Semua') return 31;
            return new Date(parseInt(tahun), parseInt(bulan), 0).getDate();
        })();
        const mNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        
        let periodLabel = 'Semua Periode';
        if (tahun !== 'Semua') {
            if (bulan === 'Semua') {
                periodLabel = `01 Januari ${tahun} s.d. 31 Desember ${tahun}`;
            } else {
                periodLabel = `01 ${mNames[parseInt(bulan)-1]} ${tahun} s.d. ${endDay} ${mNames[parseInt(bulan)-1]} ${tahun}`;
            }
        }

        const html = `<html><head><title>${judul}</title><style>
            body{font-family:Helvetica,Arial,sans-serif;padding:20px;color:#000;margin:0;}
            h2{text-align:center;color:#000;margin-bottom:8px;font-size:16px;text-transform:uppercase;}
            h3{text-align:center;color:#000;margin-bottom:8px;font-size:12px;font-weight:normal;text-transform:uppercase;}
            p.periode{text-align:center;color:#000;margin-bottom:20px;font-size:12px;font-weight:bold;text-transform:uppercase;}
            table{width:100%;border-collapse:collapse;font-size:11px;}
            th,td{border:1px solid #000;padding:8px 10px;text-align:center;color:#000;}
            th{background:#f1f5f9;font-weight:bold;text-transform:uppercase;}
            .left{text-align:left;}
            .footer{position:fixed;bottom:20px;left:20px;right:20px;font-size:10px;color:#000;border-top:1px solid #000;padding-top:10px;}
            @media print {
                @page { margin: 0; }
                body { padding: 0 20px; }
            }
        </style></head><body>
        <table>
            <thead>
                <tr>
                    <td colspan="100%" style="border:none !important; padding:30px 0 15px 0 !important; text-align:center;">
                        <h3>Sistem Manajemen Asset Barang Persediaan BPS Kabupaten Pringsewu</h3>
                        <h2>${judul}</h2>
                        <p class="periode">PERIODE: ${periodLabel}</p>
                    </td>
                </tr>
                <tr>${colHeaders}</tr>
            </thead>
            <tbody>${bodyRows}</tbody>
        </table>
        <div class="footer">Dicetak Oleh Sistem Simbar Pada: ${fmtWaktu(new Date())} | Menampilkan: ${exportData.length} baris.</div>
        </body></html>`;
        pWin.document.write(html);
        pWin.document.close();
        pWin.focus();
        setTimeout(() => pWin.print(), 500);
    }
    closeModal('export-modal');
}

// ============================================================
//  MODAL HELPERS
// ============================================================
function openModal(id)  { document.getElementById(id).classList.add('open'); lucide.createIcons(); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('open'); });
});

// Init
renderTable();
onExportTabelChange();
function openPdfModal(waktu, id_user, nama_lengkap, tim_kerja) {
    document.getElementById('pdf-waktu').value = waktu;
    document.getElementById('pdf-user').value = id_user;
    document.getElementById('pdf-penerima').value = nama_lengkap;
    document.getElementById('pdf-timkerja').value = tim_kerja;
    
    openModal('pdf-modal');
}
</script>
@endpush
