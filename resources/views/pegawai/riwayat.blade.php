@extends('layouts.app')
@section('title', 'Riwayat Pengajuan')

@section('content')
<div style="max-width:1200px; margin:0 auto; padding-bottom:50px; font-family:system-ui,-apple-system,sans-serif;">

    {{-- HEADER & PENCARIAN & FILTER --}}
    <div style="background:white; padding:20px 25px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:30px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <div style="width:45px; height:45px; background:#eff6ff; border-radius:12px; display:flex; justify-content:center; align-items:center;">
                <i data-lucide="history" style="width:22px;height:22px;color:#3498db;"></i>
            </div>
            <div>
                <h2 style="margin:0; color:#1f4068; font-size:18px; font-weight:bold;">RIWAYAT PENGAJUAN</h2>
            </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <select id="statusFilter" onchange="applyFilter()" style="padding:10px 15px; border-radius:8px; border:1px solid #e2e8f0; color:#475569; font-size:12px; outline:none; cursor:pointer; background:white; font-family:inherit;">
                <option value="Semua">Semua Status</option>
                <option value="pending">Diproses</option>
                <option value="selesai">Selesai Diproses</option>
            </select>

            <div style="position:relative; min-width:250px;">
                <input type="text" id="searchQuery" oninput="applyFilter()" placeholder="Cari barang atau alasan..."
                    style="width:100%; padding:10px 15px 10px 38px; border-radius:8px; border:1px solid #e2e8f0; outline:none; font-size:12px; box-sizing:border-box; font-family:inherit;">
                <i data-lucide="search" style="width:16px;height:16px;color:#94a3b8;position:absolute;left:14px;top:50%;transform:translateY(-50%);"></i>
            </div>
        </div>
    </div>

    {{-- TABEL RIWAYAT DENGAN PAGINASI --}}
    <div style="background:white; border-radius:12px; border:1px solid #f1f5f9; box-shadow:0 4px 10px rgba(0,0,0,0.02);">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:center; min-width:850px;">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        <th style="padding:15px 20px; font-size:11px; color:#64748b; font-weight:bold; text-transform:uppercase; width:200px; text-align:center;">Tanggal Pengajuan</th>
                        <th style="padding:15px 20px; font-size:11px; color:#64748b; font-weight:bold; text-transform:uppercase; text-align:center; width:100px;">Jumlah Item</th>
                        <th style="padding:15px 20px; font-size:11px; color:#64748b; font-weight:bold; text-transform:uppercase; text-align:center; width:40%;">Alasan / Tujuan</th>
                        <th style="padding:15px 20px; font-size:11px; color:#64748b; font-weight:bold; text-transform:uppercase; text-align:center; width:100px;">Detail</th>
                        <th style="padding:15px 20px; font-size:11px; color:#64748b; font-weight:bold; text-transform:uppercase; text-align:center; width:150px;">Status</th>
                    </tr>
                </thead>
                <tbody id="riwayatBody">
                    <tr><td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-size:13px;">Memuat...</td></tr>
                </tbody>
            </table>
        </div>

        {{-- KONTROL PAGINASI (SLIDE) --}}
        <div id="paginationControls" style="display:none; justify-content:space-between; align-items:center; padding:15px 25px; border-top:1px solid #f1f5f9; background:#fafaf9; border-radius:0 0 12px 12px; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:10px; font-size:12px; color:#64748b;">
                Tampilkan:
                <select id="itemsPerPage" onchange="changeItemsPerPage()" style="padding:6px 10px; border-radius:6px; border:1px solid #e2e8f0; outline:none; cursor:pointer; font-family:inherit;">
                    <option value="5">5 Baris</option>
                    <option value="10">10 Baris</option>
                    <option value="20">20 Baris</option>
                    <option value="50">50 Baris</option>
                </select>
                <span>dari <span id="totalItemsDisplay">0</span> entri riwayat</span>
            </div>

            <div style="display:flex; align-items:center; gap:10px;">
                <button id="btnPrev" onclick="changePage(-1)" style="display:flex; align-items:center; justify-content:center; padding:6px; background:white; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; color:#1f4068;">
                    <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
                </button>
                <span style="font-size:13px; font-weight:bold; color:#1f4068; padding:0 10px;">
                    Halaman <span id="currentPageDisplay">1</span> / <span id="totalPagesDisplay">1</span>
                </span>
                <button id="btnNext" onclick="changePage(1)" style="display:flex; align-items:center; justify-content:center; padding:6px; background:white; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; color:#1f4068;">
                    <i data-lucide="chevron-right" style="width:16px;height:16px;"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL POP-UP DETAIL --}}
    <div id="detailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); justify-content:center; align-items:center; z-index:1000; padding:20px;">
        <div style="background:white; border-radius:16px; width:100%; max-width:700px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 10px 25px rgba(0,0,0,0.1); animation:fadeIn 0.2s ease-out;">
            <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-radius:16px 16px 0 0;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <i data-lucide="eye" style="width:20px;height:20px;color:#3498db;"></i>
                    <h3 style="margin:0; color:#1f4068; font-size:16px;">Detail Pengajuan Barang</h3>
                </div>
                <button onclick="closeDetail()" style="background:none; border:none; cursor:pointer;"><i data-lucide="x" style="width:20px;height:20px;color:#94a3b8;"></i></button>
            </div>

            <div style="padding:25px; overflow-y:auto;" id="detailContent">
                {{-- Diisi oleh JS --}}
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
@keyframes fadeIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }
</style>
<script>
const riwayatMentah = @json($riwayatData);
let riwayatGrouped = [];
let filteredRiwayat = [];
let currentPage = 1;
let itemsPerPage = 5;

function processData() {
    const groupsObj = {};
    riwayatMentah.forEach(curr => {
        const key = curr.waktu_pengajuan;
        if (!groupsObj[key]) groupsObj[key] = { waktu_pengajuan: key, items: [] };
        groupsObj[key].items.push(curr);
    });

    riwayatGrouped = Object.values(groupsObj).map(group => {
        const items = group.items;
        
        let groupStatus = 'approved';
        if (items.some(i => i.status_pengajuan === 'pending')) groupStatus = 'pending';
        else if (items.some(i => i.status_pengajuan === 'rejected') && items.some(i => i.status_pengajuan === 'approved')) groupStatus = 'sebagian';
        else if (items.every(i => i.status_pengajuan === 'rejected')) groupStatus = 'rejected';

        const nama_barang_gabungan = items.map(i => i.nama_barang).join(', ');
        const jumlah_item = items.reduce((sum, i) => sum + parseInt(i.jumlah_diminta), 0);
        
        const uniqueReasons = [...new Set(items.map(i => i.alasan).filter(a => a && a.trim() !== ''))];
        const alasan_gabungan = uniqueReasons.length > 0 ? uniqueReasons.join(' | ') : 'Tidak ada alasan.';

        return { ...group, status_pengajuan: groupStatus, nama_barang_gabungan, jumlah_item, alasan_gabungan };
    });

    riwayatGrouped.sort((a, b) => new Date(b.waktu_pengajuan) - new Date(a.waktu_pengajuan));
    applyFilter();
}

function applyFilter() {
    const search = document.getElementById('searchQuery').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;

    filteredRiwayat = riwayatGrouped.filter(group => {
        const matchSearch = group.nama_barang_gabungan.toLowerCase().includes(search) || group.alasan_gabungan.toLowerCase().includes(search);
        const matchStatus = status === 'Semua' || 
                            (status === 'pending' && group.status_pengajuan === 'pending') ||
                            (status === 'selesai' && group.status_pengajuan !== 'pending');
        return matchSearch && matchStatus;
    });

    currentPage = 1;
    renderTable();
}

function formatTanggal(isoString) {
    if (!isoString) return '-';
    const d = new Date(isoString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function renderTable() {
    const totalPages = Math.ceil(filteredRiwayat.length / itemsPerPage) || 1;
    const start = (currentPage - 1) * itemsPerPage;
    const currentItems = filteredRiwayat.slice(start, start + itemsPerPage);
    const tbody = document.getElementById('riwayatBody');

    if (filteredRiwayat.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-size:13px;">Tidak ada riwayat yang sesuai.</td></tr>`;
        document.getElementById('paginationControls').style.display = 'none';
        return;
    }

    tbody.innerHTML = currentItems.map(group => {
        let badgeHtml = '';
        if (group.status_pengajuan === 'pending') {
            badgeHtml = `<span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; background:#fffbeb; color:#d97706; border-radius:20px; font-size:11px; font-weight:bold; white-space:nowrap;"><i data-lucide="loader" style="width:14px;height:14px;"></i> Diproses</span>`;
        } else {
            badgeHtml = `<span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; background:#ecfdf5; color:#059669; border-radius:20px; font-size:11px; font-weight:bold; white-space:nowrap;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Selesai Diproses</span>`;
        }

        return `
        <tr style="border-bottom:1px solid #f1f5f9; transition:0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
            <td style="padding:15px 20px; text-align:center; font-size:13px; color:#475569; font-weight:500; vertical-align:middle;">${formatTanggal(group.waktu_pengajuan)}</td>
            <td style="padding:15px 20px; text-align:center; vertical-align:middle;"><strong style="color:#475569; font-size:15px;">${group.jumlah_item}</strong></td>
            <td style="padding:15px 20px; text-align:center; font-size:12px; color:${group.alasan_gabungan==='Tidak ada alasan.'?'#94a3b8':'#475569'}; font-style:${group.alasan_gabungan==='Tidak ada alasan.'?'italic':'normal'}; line-height:1.5; vertical-align:middle;">${group.alasan_gabungan}</td>
            <td style="padding:15px 20px; text-align:center; vertical-align:middle;">
                <span onclick='openDetail(${JSON.stringify(group)})' style="color:#3498db; font-size:13px; font-weight:bold; cursor:pointer; text-decoration:underline;">Detail</span>
            </td>
            <td style="padding:15px 20px; text-align:center; vertical-align:middle;"><div style="display:flex; justify-content:center; align-items:center;">${badgeHtml}</div></td>
        </tr>`;
    }).join('');

    // Update Pagination UI
    document.getElementById('paginationControls').style.display = 'flex';
    document.getElementById('totalItemsDisplay').innerText = filteredRiwayat.length;
    document.getElementById('currentPageDisplay').innerText = currentPage;
    document.getElementById('totalPagesDisplay').innerText = totalPages;

    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    
    btnPrev.disabled = currentPage === 1;
    btnPrev.style.backgroundColor = currentPage === 1 ? '#f1f5f9' : 'white';
    btnPrev.style.cursor = currentPage === 1 ? 'not-allowed' : 'pointer';

    btnNext.disabled = currentPage === totalPages;
    btnNext.style.backgroundColor = currentPage === totalPages ? '#f1f5f9' : 'white';
    btnNext.style.cursor = currentPage === totalPages ? 'not-allowed' : 'pointer';

    lucide.createIcons();
}

function changePage(delta) {
    const totalPages = Math.ceil(filteredRiwayat.length / itemsPerPage) || 1;
    let newPage = currentPage + delta;
    if (newPage < 1) newPage = 1;
    if (newPage > totalPages) newPage = totalPages;
    currentPage = newPage;
    renderTable();
}

function changeItemsPerPage() {
    itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
    currentPage = 1;
    renderTable();
}

// Modal Detail Logic
function openDetail(group) {
    let badgeHtml = '';
    if (group.status_pengajuan === 'pending') {
        badgeHtml = `<span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; background:#fffbeb; color:#d97706; border-radius:20px; font-size:11px; font-weight:bold;"><i data-lucide="loader" style="width:14px;height:14px;"></i> Diproses</span>`;
    } else {
        badgeHtml = `<span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; background:#ecfdf5; color:#059669; border-radius:20px; font-size:11px; font-weight:bold;"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Selesai Diproses</span>`;
    }

    let tbodyHtml = group.items.map(item => {
        let disetujui = 0, ditolak = 0;
        const diminta = parseInt(item.jumlah_diminta);

        if (item.status_pengajuan === 'pending') {
            disetujui = '-'; ditolak = '-';
        } else if (item.status_pengajuan === 'rejected') {
            disetujui = 0; ditolak = diminta;
        } else {
            disetujui = parseInt(item.jumlah_disetujui) || 0;
            ditolak = diminta - disetujui;
        }

        const imgHtml = item.foto_barang ? `<img src="/uploads/${item.foto_barang}" style="width:100%;height:100%;object-fit:cover;">` : `<i data-lucide="image" style="width:20px;height:20px;color:#cbd5e1;"></i>`;

        return `
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px; vertical-align:middle;">
                <div style="width:45px; height:45px; background:white; border-radius:6px; overflow:hidden; border:1px solid #e2e8f0; margin:0 auto; display:flex; justify-content:center; align-items:center;">
                    ${imgHtml}
                </div>
            </td>
            <td style="padding:10px; font-size:13px; color:#1f4068; font-weight:600; vertical-align:middle;">${item.nama_barang}</td>
            <td style="padding:10px; vertical-align:middle;"><span style="font-size:14px; color:#64748b; font-weight:bold;">${diminta}</span></td>
            <td style="padding:10px; vertical-align:middle;"><strong style="font-size:14px; color:${disetujui>0?'#059669':'#94a3b8'};">${disetujui}</strong></td>
            <td style="padding:10px; vertical-align:middle;"><strong style="font-size:14px; color:${ditolak>0?'#dc2626':'#94a3b8'};">${ditolak}</strong></td>
        </tr>`;
    }).join('');

    const html = `
    <div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
        <div>
            <span style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Waktu Pengajuan:</span>
            <strong style="font-size:13px; color:#1f4068;">${formatTanggal(group.waktu_pengajuan)}</strong>
        </div>
        <div style="text-align:right;">
            <span style="font-size:11px; color:#64748b; display:block; margin-bottom:4px;">Status Keseluruhan:</span>
            ${badgeHtml}
        </div>
    </div>
    <div style="border-radius:8px; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:20px;">
        <table style="width:100%; border-collapse:collapse; text-align:center;">
            <thead>
                <tr style="background:#f1f5f9; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px 10px; font-size:12px; color:#475569; font-weight:bold;">Gambar</th>
                    <th style="padding:12px 10px; font-size:12px; color:#475569; font-weight:bold;">Nama Barang</th>
                    <th style="padding:12px 10px; font-size:12px; color:#475569; font-weight:bold;">Diminta</th>
                    <th style="padding:12px 10px; font-size:12px; color:#475569; font-weight:bold;">Disetujui</th>
                    <th style="padding:12px 10px; font-size:12px; color:#475569; font-weight:bold;">Ditolak</th>
                </tr>
            </thead>
            <tbody>${tbodyHtml}</tbody>
        </table>
    </div>
    <div style="padding:15px; background:#fafaf9; border-radius:8px; border:1px solid #e2e8f0;">
        <span style="font-size:11px; color:#64748b; display:block; margin-bottom:8px; font-weight:bold; text-transform:uppercase;">Alasan / Tujuan Pengajuan:</span>
        <span style="font-size:13px; color:#475569; font-style:${group.alasan_gabungan==='Tidak ada alasan.'?'italic':'normal'}; line-height:1.5;">${group.alasan_gabungan}</span>
    </div>
    `;

    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailModal').style.display = 'flex';
    lucide.createIcons();
}

function closeDetail() {
    document.getElementById('detailModal').style.display = 'none';
}

// init
processData();
</script>
@endpush
