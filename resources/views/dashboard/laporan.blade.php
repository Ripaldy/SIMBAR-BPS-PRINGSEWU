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
            <option value="grouped">Tabel Gabungan (Per Waktu)</option>
            <option value="itemized">Tabel Lengkap (Per Barang)</option>
            <option value="divisi">Tabel Pengeluaran Tim / Divisi</option>
            <option value="aggregate">Tabel Agregat Barang Keluar</option>
            <option value="aggregate_masuk">Tabel Agregat Barang Masuk</option>
        </select>

        {{-- Filter Divisi (muncul jika mode divisi) --}}
        <select id="divisiFilter" onchange="renderTable()"
            style="display:none; padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1f4068; outline:none; cursor:pointer; font-family:inherit;">
            <option value="Semua Divisi">Semua Divisi</option>
            @foreach($divisiList as $div)
                <option value="{{ $div }}">{{ $div }}</option>
            @endforeach
        </select>

        {{-- Filter Tahun & Bulan (muncul jika mode aggregate) --}}
        <select id="aggTahun" onchange="renderTable()"
            style="display:none; padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1f4068; outline:none; cursor:pointer; font-family:inherit;">
            <option value="Semua">Semua Tahun</option>
            @foreach($availableYears as $y)
                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>Tahun {{ $y }}</option>
            @endforeach
        </select>
        <select id="aggBulan" onchange="renderTable()"
            style="display:none; padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1f4068; outline:none; cursor:pointer; font-family:inherit;">
            <option value="Semua">Semua Bulan</option>
            <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
            <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
            <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
            <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
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
                        <button id="btn-csv" onclick="setExportFormat('csv')"
                            style="flex:1; padding:10px; border-radius:8px; border:2px solid #27ae60; background:#ecfdf5; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; color:#1f4068; font-weight:bold; font-family:inherit;">
                            <i data-lucide="file-spreadsheet" style="width:18px;height:18px;color:#27ae60;"></i> CSV
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
                        <option value="itemized">Tabel Lengkap (Per Barang)</option>
                        <option value="divisi">Tabel Pengeluaran Tim / Divisi</option>
                        <option value="aggregate">Tabel Agregat Barang Keluar</option>
                        <option value="aggregate_masuk">Tabel Agregat Barang Masuk</option>
                        <option value="grouped">Tabel Gabungan (Per Waktu)</option>
                    </select>
                </div>
            </div>

            {{-- Judul PDF (muncul jika format pdf) --}}
            <div id="pdf-title-wrap" style="display:none;">
                <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Judul Dokumen PDF</label>
                <input type="text" id="export-judul" value="Laporan Riwayat Pengajuan Inventaris BPS"
                    style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #cbd5e1; box-sizing:border-box; outline:none; color:#1f4068; font-family:inherit;">
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
                        <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
                        <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                        <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                        <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
                    </select>
                </div>
            </div>

            {{-- Divisi filter (jika tabel=divisi) --}}
            <div id="export-divisi-wrap" style="display:none;">
                <label style="font-size:12px; color:#64748b; font-weight:bold; display:block; margin-bottom:8px;">Pilih Tim / Divisi</label>
                <select id="export-divisi" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; outline:none; color:#1f4068; font-family:inherit;">
                    <option value="Semua Divisi">Semua Divisi</option>
                    @foreach($divisiList as $div)
                        <option value="{{ $div }}">{{ $div }}</option>
                    @endforeach
                </select>
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

@endsection

@push('scripts')
<script>
// ============================================================
//  DATA dari PHP (semua pengajuan yang sudah diproses)
// ============================================================
const riwayatMentah = @json($riwayatData);
const barangMasukMentah = @json($barangMasukData);

// ============================================================
//  STATE
// ============================================================
let currentPage = 1;
let itemsPerPage = 10;
let exportFormat = 'csv';
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

function kode(id) { return 'BRG-' + String(id).padStart(3,'0'); }

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
        if (!obj[key]) obj[key] = { id_group: key, waktu_pengajuan: item.waktu_pengajuan, nama_lengkap: item.nama_lengkap, divisi: item.divisi, items: [] };
        obj[key].items.push(item);
    });
    return Object.values(obj).map(g => {
        const items = g.items;
        let status = 'approved';
        if (items.some(i => i.status_pengajuan === 'rejected') && items.some(i => i.status_pengajuan === 'approved')) status = 'sebagian';
        else if (items.every(i => i.status_pengajuan === 'rejected')) status = 'rejected';
        const total = items.reduce((s, i) => s + parseInt(i.jumlah_diminta), 0);
        return { ...g, status_pengajuan: status, totalItem: total };
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

    if (mode === 'aggregate_masuk') {
        base = barangMasukMentah.filter(item => {
            const d = new Date(item.waktu_masuk);
            const matchY = aggTahun === 'Semua' || d.getFullYear().toString() === aggTahun;
            const matchM = aggBulan === 'Semua' || (d.getMonth()+1).toString() === aggBulan;
            const matchK = !kata || item.nama_barang.toLowerCase().includes(kata);
            return matchY && matchM && matchK;
        });
        return base;
    }

    if (mode === 'aggregate') {
        base = base.filter(item => {
            const d = new Date(item.waktu_pengajuan);
            const matchY = aggTahun === 'Semua' || d.getFullYear().toString() === aggTahun;
            const matchM = aggBulan === 'Semua' || (d.getMonth()+1).toString() === aggBulan;
            return matchY && matchM;
        });
    }

    if (mode === 'divisi') {
        base = base.filter(item => {
            const matchD = divisi === 'Semua Divisi' || item.divisi === divisi;
            const matchK = !kata || item.nama_lengkap.toLowerCase().includes(kata) || item.nama_barang.toLowerCase().includes(kata) || (item.divisi && item.divisi.toLowerCase().includes(kata));
            return matchD && matchK;
        });
    } else if (mode === 'grouped') {
        const groups = buildGrouped(base);
        return groups.filter(g => {
            return !kata || g.nama_lengkap.toLowerCase().includes(kata) || (g.divisi && g.divisi.toLowerCase().includes(kata)) || g.items.some(i => i.nama_barang.toLowerCase().includes(kata));
        });
    } else if (mode === 'aggregate') {
        base = base.filter(item => !kata || item.nama_barang.toLowerCase().includes(kata));
    } else {
        base = base.filter(item => {
            return !kata || item.nama_lengkap.toLowerCase().includes(kata) || item.nama_barang.toLowerCase().includes(kata) || (item.divisi && item.divisi.toLowerCase().includes(kata));
        });
    }

    return base;
}

function buildAggregateMasuk(data) {
    const map = {};
    data.forEach(curr => {
        let masuk = parseInt(curr.jumlah_masuk);
        if (masuk > 0) {
            if (!map[curr.id_barang]) map[curr.id_barang] = { id_barang: curr.id_barang, foto_barang: curr.foto_barang, nama_barang: curr.nama_barang, satuan: curr.satuan || '-', jumlah: 0 };
            map[curr.id_barang].jumlah += masuk;
        }
    });
    return Object.values(map).sort((a,b) => b.jumlah - a.jumlah);
}

function buildAggregate(data) {
    const map = {};
    data.forEach(curr => {
        let disetujui = curr.status_pengajuan !== 'rejected' && curr.jumlah_disetujui !== null ? parseInt(curr.jumlah_disetujui) : 0;
        if (disetujui > 0) {
            if (!map[curr.id_barang]) map[curr.id_barang] = { id_barang: curr.id_barang, foto_barang: curr.foto_barang, nama_barang: curr.nama_barang, satuan: curr.satuan || '-', jumlah_keluar: 0 };
            map[curr.id_barang].jumlah_keluar += disetujui;
        }
    });
    return Object.values(map).sort((a,b) => b.jumlah_keluar - a.jumlah_keluar);
}

// ============================================================
//  RENDER TABLE
// ============================================================
function renderTable() {
    lucide.createIcons();
    const mode = document.getElementById('viewMode').value;
    const filtered = getFilteredData();

    let displayData = filtered;
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
                <th style="${thGS}width:20%;">Tim / Divisi</th>
                <th style="${thGS}width:15%;">Jumlah Item</th>
                <th style="${thGS}width:10%;">Detail</th>
                <th style="${thGS}width:20%;">Status</th>
            </tr></thead><tbody>`;
        if (slice.length === 0) {
            html += `<tr><td colspan="6" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada riwayat ditemukan.</td></tr>`;
        } else {
            slice.forEach(g => {
                const statusBadge = g.status_pengajuan === 'approved'
                    ? `<span style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#ecfdf5;color:#059669;border-radius:20px;font-size:12px;white-space:nowrap;">✓ Selesai Diproses</span>`
                    : g.status_pengajuan === 'rejected'
                    ? `<span style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#fef2f2;color:#dc2626;border-radius:20px;font-size:12px;">✗ Ditolak</span>`
                    : `<span style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#fefce8;color:#ca8a04;border-radius:20px;font-size:12px;">~ Sebagian</span>`;
                const groupJson = JSON.stringify(g).replace(/'/g,"&#39;");
                html += `<tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${fmtWaktu(g.waktu_pengajuan)}</td>
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${g.nama_lengkap}</td>
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${g.divisi || '-'}</td>
                    <td style="padding:15px 10px;font-size:12px;color:#000;text-align:center;">${g.totalItem}</td>
                    <td style="padding:15px 10px;text-align:center;vertical-align:middle;">
                        <span onclick='openDetailModal(${groupJson})' style="color:#3498db;font-size:12px;cursor:pointer;text-decoration:underline;">Detail</span>
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
                <th style="${thStyle}">Tim / Divisi</th>
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
                    ${tdS(item.divisi || '-')}
                    ${tdS(kode(item.id_barang))}
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
                <th style="${thStyle}">Tanggal / Waktu</th>
                <th style="${thStyle}">Gambar</th>
                <th style="${thStyle}">Pemohon</th>
                <th style="${thStyle}">Tim / Divisi</th>
                <th style="${thStyle}">Kode</th>
                <th style="${thStyle}">Jenis Barang</th>
                <th style="${thLast}">Jumlah</th>
            </tr></thead><tbody>`;
        if (slice.length === 0) {
            html += `<tr><td colspan="7" style="padding:40px;color:#94a3b8;font-size:13px;text-align:center;">Tidak ada data pengeluaran divisi.</td></tr>`;
        } else {
            slice.forEach(item => {
                const jumlah = item.status_pengajuan === 'rejected' ? 0 : (item.jumlah_disetujui !== null ? parseInt(item.jumlah_disetujui) : 0);
                html += `<tr style="border-bottom:1px solid #e2e8f0;">
                    ${tdS(fmtWaktu(item.waktu_pengajuan))}
                    <td style="padding:12px;border-right:1px solid #e2e8f0;">${imgCell(item.foto_barang, item.nama_barang)}</td>
                    ${tdS(item.nama_lengkap)}
                    ${tdS(item.divisi || '-')}
                    ${tdS(kode(item.id_barang))}
                    ${tdS(item.nama_barang)}
                    ${tdL(jumlah)}
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
                    ${tdS(kode(item.id_barang))}
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
                    ${tdS(kode(item.id_barang))}
                    ${tdS(item.nama_barang)}
                    ${tdS(item.jumlah)}
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

function onViewModeChange() {
    const mode = document.getElementById('viewMode').value;
    document.getElementById('divisiFilter').style.display  = mode === 'divisi' ? 'block' : 'none';
    document.getElementById('aggTahun').style.display      = (mode === 'aggregate' || mode === 'aggregate_masuk') ? 'block' : 'none';
    document.getElementById('aggBulan').style.display      = (mode === 'aggregate' || mode === 'aggregate_masuk') ? 'block' : 'none';
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
            <td style="padding:10px;font-size:12px;color:#000;font-weight:normal;">${kode(item.id_barang)}</td>
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
    document.getElementById('btn-csv').style.border    = fmt === 'csv' ? '2px solid #27ae60' : '1px solid #cbd5e1';
    document.getElementById('btn-csv').style.background= fmt === 'csv' ? '#ecfdf5' : 'white';
    document.getElementById('btn-pdf').style.border    = fmt === 'pdf' ? '2px solid #e74c3c' : '1px solid #cbd5e1';
    document.getElementById('btn-pdf').style.background= fmt === 'pdf' ? '#fef2f2' : 'white';
    document.getElementById('pdf-title-wrap').style.display = fmt === 'pdf' ? 'block' : 'none';
}

function onExportTabelChange() {
    const tabel = document.getElementById('export-tabel').value;
    document.getElementById('export-divisi-wrap').style.display = tabel === 'divisi' ? 'block' : 'none';
}

function executeExport() {
    const tahun  = document.getElementById('export-tahun').value;
    const bulan  = document.getElementById('export-bulan').value;
    const tabel  = document.getElementById('export-tabel').value;
    const divisi = document.getElementById('export-divisi').value;
    const judul  = document.getElementById('export-judul').value;

    let sourceData = (tabel === 'aggregate_masuk') ? barangMasukMentah : riwayatMentah;

    let data = sourceData.filter(item => {
        const d  = new Date(item.waktu_pengajuan || item.waktu_masuk);
        const mY = tahun === 'Semua' || d.getFullYear().toString() === tahun;
        const mM = bulan === 'Semua' || (d.getMonth()+1).toString() === bulan;
        const mD = (tabel !== 'divisi' && tabel !== 'grouped' && tabel !== 'itemized') || divisi === 'Semua Divisi' || item.divisi === divisi;
        return mY && mM && mD;
    });

    let exportData = data;

    if (tabel === 'aggregate') {
        const map = {};
        data.forEach(curr => {
            let d = curr.status_pengajuan !== 'rejected' && curr.jumlah_disetujui !== null ? parseInt(curr.jumlah_disetujui) : 0;
            if (d > 0) {
                if (!map[curr.id_barang]) map[curr.id_barang] = { kode: kode(curr.id_barang), nama_barang: curr.nama_barang, satuan: curr.satuan || '-', jumlah: 0 };
                map[curr.id_barang].jumlah += d;
            }
        });
        exportData = Object.values(map).sort((a,b) => b.jumlah - a.jumlah);
    } else if (tabel === 'aggregate_masuk') {
        const map = {};
        data.forEach(curr => {
            let masuk = parseInt(curr.jumlah_masuk);
            if (masuk > 0) {
                if (!map[curr.id_barang]) map[curr.id_barang] = { kode: kode(curr.id_barang), nama_barang: curr.nama_barang, satuan: curr.satuan || '-', jumlah: 0 };
                map[curr.id_barang].jumlah += masuk;
            }
        });
        exportData = Object.values(map).sort((a,b) => b.jumlah - a.jumlah);
    } else if (tabel === 'grouped') {
        const groups = buildGrouped(data);
        exportData = groups.map(g => {
            let st = 'Disetujui';
            if (g.status_pengajuan === 'rejected') st = 'Ditolak';
            else if (g.status_pengajuan === 'sebagian') st = 'Sebagian';
            return { waktu: fmtWaktu(g.waktu_pengajuan), pemohon: g.nama_lengkap, divisi: g.divisi || '-', jumlah_item: g.totalItem, status: st };
        });
    }

    if (exportData.length === 0) {
        alert('Tidak ada data yang cocok untuk diekspor pada periode tersebut.');
        return;
    }

    if (exportFormat === 'csv') {
        let headers, rows;
        if (tabel === 'aggregate') {
            headers = '"KODE","NAMA BARANG","JUMLAH KELUAR","SATUAN"';
            rows = exportData.map(item => `"${item.kode}","${item.nama_barang}",${item.jumlah},"${item.satuan}"`);
        } else if (tabel === 'aggregate_masuk') {
            headers = '"KODE","NAMA BARANG","JUMLAH MASUK","SATUAN"';
            rows = exportData.map(item => `"${item.kode}","${item.nama_barang}",${item.jumlah},"${item.satuan}"`);
        } else if (tabel === 'grouped') {
            headers = '"WAKTU","PEMOHON","DIVISI","JUMLAH ITEM","STATUS"';
            rows = exportData.map(item => `"${item.waktu}","${item.pemohon}","${item.divisi}",${item.jumlah_item},"${item.status}"`);
        } else {
            headers = '"WAKTU","PEMOHON","DIVISI","KODE","NAMA BARANG","DIMINTA","DISETUJUI","DITOLAK"';
            rows = exportData.map(item => {
                const dm = parseInt(item.jumlah_diminta);
                let ds = 0, dt = 0;
                if (item.status_pengajuan === 'rejected') { dt = dm; } else { ds = item.jumlah_disetujui !== null ? parseInt(item.jumlah_disetujui) : 0; dt = dm - ds; }
                return `"${fmtWaktu(item.waktu_pengajuan)}","${item.nama_lengkap}","${item.divisi || '-'}","${kode(item.id_barang)}","${item.nama_barang}",${dm},${ds},${dt}`;
            });
        }
        const csv = '\uFEFF' + [headers, ...rows].join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Laporan_${tabel}_${tahun}.csv`;
        link.click();
    } else {
        // PDF
        const pWin = window.open('', '_blank');
        let colHeaders, bodyRows;
        if (tabel === 'aggregate') {
            colHeaders = '<th>Kode</th><th>Nama Barang</th><th>Jumlah Keluar</th><th>Satuan</th>';
            bodyRows = exportData.map(item => `<tr><td>${item.kode}</td><td class="left">${item.nama_barang}</td><td>${item.jumlah}</td><td>${item.satuan}</td></tr>`).join('');
        } else if (tabel === 'aggregate_masuk') {
            colHeaders = '<th>Kode</th><th>Nama Barang</th><th>Jumlah Masuk</th><th>Satuan</th>';
            bodyRows = exportData.map(item => `<tr><td>${item.kode}</td><td class="left">${item.nama_barang}</td><td>${item.jumlah}</td><td>${item.satuan}</td></tr>`).join('');
        } else if (tabel === 'grouped') {
            colHeaders = '<th>Waktu</th><th>Pemohon</th><th>Divisi</th><th>Jumlah Item</th><th>Status</th>';
            bodyRows = exportData.map(item => `<tr><td>${item.waktu}</td><td class="left">${item.pemohon}</td><td>${item.divisi}</td><td>${item.jumlah_item}</td><td>${item.status}</td></tr>`).join('');
        } else {
            colHeaders = '<th>Waktu</th><th>Pemohon</th><th>Divisi</th><th>Kode</th><th>Nama Barang</th><th>Diminta</th><th>Disetujui</th><th>Ditolak</th>';
            bodyRows = exportData.map(item => {
                const dm = parseInt(item.jumlah_diminta);
                let ds = 0, dt = 0;
                if (item.status_pengajuan === 'rejected') { dt = dm; } else { ds = item.jumlah_disetujui !== null ? parseInt(item.jumlah_disetujui) : 0; dt = dm - ds; }
                return `<tr><td>${fmtWaktu(item.waktu_pengajuan)}</td><td class="left">${item.nama_lengkap}</td><td>${item.divisi||'-'}</td><td>${kode(item.id_barang)}</td><td class="left">${item.nama_barang}</td><td>${dm}</td><td>${ds}</td><td>${dt}</td></tr>`;
            }).join('');
        }
        const html = `<html><head><title>${judul}</title><style>
            body{font-family:Helvetica,Arial,sans-serif;padding:20px;color:#000;margin:0;}
            h2{text-align:center;color:#000;margin-bottom:20px;text-transform:uppercase;}
            table{width:100%;border-collapse:collapse;font-size:11px;}
            th,td{border:1px solid #000;padding:8px 10px;text-align:center;color:#000;}
            th{background:#f1f5f9;font-weight:bold;text-transform:uppercase;}
            .left{text-align:left;}
            .footer{position:fixed;bottom:20px;left:20px;right:20px;font-size:10px;color:#000;border-top:1px solid #000;padding-top:10px;}
        </style></head><body>
        <h2>${judul}</h2>
        <table><thead><tr>${colHeaders}</tr></thead><tbody>${bodyRows}</tbody></table>
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
</script>
@endpush
