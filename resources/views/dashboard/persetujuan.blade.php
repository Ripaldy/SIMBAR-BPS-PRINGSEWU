@extends('layouts.app')
@section('title', 'Persetujuan Barang')

@section('content')
<div style="max-width:1200px; margin:0 auto; display:flex; flex-direction:column; gap:25px; position:relative; font-family:system-ui,-apple-system,sans-serif;">

    {{-- KARTU UTAMA --}}
    <div style="background:white; border-radius:24px; padding:35px; box-shadow:0 10px 30px rgba(0,0,0,0.02); border:1px solid #f1f5f9;">
        
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
            <div style="display:flex; align-items:center; gap:20px;">
                <div style="width:50px; height:50px; background:#eaf4fb; border-radius:50%; display:flex; justify-content:center; align-items:center;">
                    <i data-lucide="bell" style="width:24px;height:24px;color:#3498db;"></i>
                </div>
                <div>
                    <h2 style="margin:0; color:#1f4068; font-size:22px; font-weight:bold;">Antrean Persetujuan Barang</h2>
                </div>
            </div>
            <div style="background:#fdedec; color:#e74c3c; padding:8px 18px; border-radius:20px; font-size:13px; font-weight:bold;">
                <span id="pendingCount">0</span> Menunggu
            </div>
        </div>

        @if(session('success'))
        <div style="padding:12px 20px; margin-top:15px; border-radius:10px; display:flex; align-items:center; gap:10px; background:#e8f8f5; color:#27ae60; font-size:13px; font-weight:500;">
            <i data-lucide="alert-circle" style="width:16px;height:16px;"></i>
            {{ session('success') }}
        </div>
        @endif

        {{-- TABEL ANTREAN --}}
        <div style="overflow-x:auto; margin-top:35px; border-radius:10px; border:1px solid #f1f5f9;">
            <table style="width:100%; border-collapse:collapse; min-width:950px;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0; background:#f8fafc;">
                        <th style="padding:15px 10px; text-align:center; font-size:12px; color:#64748b;">Waktu</th>
                        <th style="padding:15px 10px; text-align:center; font-size:12px; color:#64748b;">Pemohon</th>
                        <th style="padding:15px 10px; text-align:center; font-size:12px; color:#64748b;">Barang Diajukan</th>
                        <th style="padding:15px 10px; text-align:center; font-size:12px; color:#64748b;">Jumlah Item</th>
                        <th style="padding:15px 10px; text-align:center; font-size:12px; color:#64748b;">Aksi Validasi</th>
                    </tr>
                </thead>
                <tbody id="antreanBody">
                    <tr><td colspan="5" style="text-align:center; padding:50px; color:#94a3b8;">Memuat...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL PROSES PERSETUJUAN --}}
    <div id="processModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); justify-content:center; align-items:center; z-index:1000; padding:20px;">
        <div style="background:white; border-radius:16px; width:100%; max-width:600px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
            
            <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-radius:16px 16px 0 0;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <i data-lucide="settings" style="width:20px;height:20px;color:#3498db;"></i>
                    <h3 style="margin:0; color:#1f4068; font-size:16px;">Proses Persetujuan Barang</h3>
                </div>
                <button onclick="closeProcessModal()" style="background:none; border:none; cursor:pointer;"><i data-lucide="x" style="width:20px;height:20px;color:#94a3b8;"></i></button>
            </div>

            <form method="POST" action="{{ route('persetujuan.proses') }}" id="processForm" style="display:flex; flex-direction:column; flex:1; min-height:0;">
                @csrf
                <div style="padding:25px; overflow-y:auto; flex:1;">
                    <div style="margin-bottom:20px; text-align:center;">
                        <strong id="modalPemohon" style="font-size:18px; color:#1f4068;"></strong>
                    </div>

                    <table style="width:100%; border-collapse:collapse; text-align:center;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th style="padding:12px; font-size:12px; color:#475569;">Nama Barang</th>
                                <th style="padding:12px; font-size:12px; color:#475569;">Diminta</th>
                                <th style="padding:12px; font-size:12px; color:#475569; width:180px;">Keputusan</th>
                                <th style="padding:12px; font-size:12px; color:#475569; width:100px;">Jml Disetujui</th>
                            </tr>
                        </thead>
                        <tbody id="modalFormItems">
                            {{-- Diisi JS --}}
                        </tbody>
                    </table>

                    <div style="margin-top:25px; padding:15px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; text-align:left;">
                        <span style="font-size:11px; color:#64748b; display:block; margin-bottom:5px; font-weight:bold;">ALASAN / TUJUAN PENGAJUAN:</span>
                        <span id="modalAlasan" style="font-size:13px; color:#475569; font-style:italic;"></span>
                    </div>
                </div>

                <div style="padding:15px 25px; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc; border-radius:0 0 16px 16px;">
                    <button type="button" onclick="closeProcessModal()" style="padding:10px 20px; background:white; border:1px solid #cbd5e1; border-radius:8px; cursor:pointer; font-weight:bold; font-family:inherit;">Batal</button>
                    <button type="submit" style="padding:10px 20px; background:#3498db; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-family:inherit;">Simpan Persetujuan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const antreanMentah = @json($antreanData);
let groupedAntrean = [];
let processGroup = null;

function processData() {
    const groupsObj = {};
    antreanMentah.forEach(curr => {
        const key = curr.waktu_pengajuan + '_' + curr.id_user;
        if (!groupsObj[key]) {
            groupsObj[key] = {
                id_group: key,
                waktu_pengajuan: curr.waktu_pengajuan,
                nama_lengkap: curr.nama_lengkap,
                items: []
            };
        }
        groupsObj[key].items.push(curr);
    });

    groupedAntrean = Object.values(groupsObj).sort((a, b) => new Date(a.waktu_pengajuan) - new Date(b.waktu_pengajuan));
    renderTable();
}

function formatWaktuSingkat(isoString) {
    if (!isoString) return '-';
    const d = new Date(isoString);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    const hh = String(d.getHours()).padStart(2, '0');
    const min = String(d.getMinutes()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd} ${hh}:${min}`;
}

function renderTable() {
    document.getElementById('pendingCount').innerText = groupedAntrean.length;
    const tbody = document.getElementById('antreanBody');

    if (groupedAntrean.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:50px; color:#94a3b8;">Tidak ada pengajuan menunggu.</td></tr>`;
        return;
    }

    tbody.innerHTML = groupedAntrean.map(group => {
        const namaBarangGabungan = group.items.map(i => i.nama_barang).join(', ');
        const totalItem = group.items.reduce((sum, i) => sum + parseInt(i.jumlah_diminta), 0);
        
        return `
        <tr style="border-bottom:1px solid #f1f5f9; background:transparent;">
            <td style="padding:15px 10px; text-align:center; font-size:12px; color:#64748b;">${formatWaktuSingkat(group.waktu_pengajuan)}</td>
            <td style="padding:15px 10px; text-align:center;">
                <div style="display:flex; align-items:center; justify-content:center; gap:6px; color:#1f4068;">
                    <i data-lucide="user" style="width:14px;height:14px;color:#64748b;"></i><strong style="font-size:13px;">${group.nama_lengkap}</strong>
                </div>
            </td>
            <td style="padding:15px 10px; text-align:center; font-size:13px; color:#1f4068;">${namaBarangGabungan}</td>
            <td style="padding:15px 10px; text-align:center;"><strong style="font-size:15px; color:#154c79;">${totalItem}</strong></td>
            <td style="padding:15px 10px; text-align:center;">
                <button onclick='openProcessModal(${JSON.stringify(group)})'
                    style="padding:8px 20px; background:#d6eaf8; color:#000000; border:none; border-radius:8px; font-size:12px; font-weight:bold; cursor:pointer; margin:0 auto; display:block; font-family:inherit;">
                    Proses
                </button>
            </td>
        </tr>`;
    }).join('');
    lucide.createIcons();
}

function openProcessModal(group) {
    processGroup = group;
    document.getElementById('modalPemohon').innerText = group.nama_lengkap;
    document.getElementById('modalAlasan').innerText = group.items[0]?.alasan || '-';

    const tbody = document.getElementById('modalFormItems');
    tbody.innerHTML = group.items.map((item, idx) => `
        <tr style="border-bottom:1px solid #e2e8f0;">
            <input type="hidden" name="items[${idx}][id_pengajuan]" value="${item.id_pengajuan}">
            <td style="padding:15px 10px; font-size:13px; color:#1f4068; font-weight:bold;">${item.nama_barang}</td>
            <td style="padding:15px 10px; font-size:14px;">${item.jumlah_diminta}</td>
            <td style="padding:15px 10px;">
                <div style="display:flex; gap:10px; justify-content:center;">
                    <label style="font-size:12px; display:flex; align-items:center; gap:5px; cursor:pointer; color:#059669;">
                        <input type="radio" name="items[${idx}][status]" value="approved" checked onchange="toggleDisetujui(${idx}, true, ${item.jumlah_diminta})"> Setuju
                    </label>
                    <label style="font-size:12px; display:flex; align-items:center; gap:5px; cursor:pointer; color:#dc2626;">
                        <input type="radio" name="items[${idx}][status]" value="rejected" onchange="toggleDisetujui(${idx}, false, ${item.jumlah_diminta})"> Tolak
                    </label>
                </div>
            </td>
            <td style="padding:15px 10px;">
                <input type="number" id="jml_${idx}" name="items[${idx}][jumlah_disetujui]" min="1" max="${item.jumlah_diminta}" value="${item.jumlah_diminta}"
                    style="width:60px; padding:8px; text-align:center; border-radius:6px; border:1px solid #cbd5e1; font-family:inherit;">
            </td>
        </tr>
    `).join('');

    document.getElementById('processModal').style.display = 'flex';
}

function toggleDisetujui(idx, isApproved, maxVal) {
    const input = document.getElementById(`jml_${idx}`);
    if (isApproved) {
        input.disabled = false;
        input.value = maxVal;
        input.style.backgroundColor = 'white';
    } else {
        input.disabled = true;
        input.value = 0;
        input.style.backgroundColor = '#f1f5f9';
    }
}

function closeProcessModal() {
    document.getElementById('processModal').style.display = 'none';
}

// init
processData();
</script>
@endpush
