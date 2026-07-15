@extends('layouts.app')
@section('title', 'Konfigurasi Otomatisasi')

@section('content')
<div style="display:flex; flex-direction:column; gap:25px; max-width:1100px; margin:0 auto; padding-bottom:50px;">

    {{-- KARTU UTAMA --}}
    <div style="background:white; border-radius:24px; padding:35px; box-shadow:0 10px 30px rgba(0,0,0,0.02); border:1px solid #f1f5f9;">

        {{-- HEADER --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:25px; flex-wrap:wrap; gap:15px;">
            <div style="display:flex; align-items:center; gap:20px;">
                <div style="width:50px; height:50px; background:#eaf4fb; border-radius:15px; display:flex; justify-content:center; align-items:center;">
                    <i data-lucide="settings" style="width:24px;height:24px;color:#3498db;"></i>
                </div>
                <div>
                    <h2 style="margin:0; color:#1f4068; font-size:22px; font-weight:bold; letter-spacing:-0.3px;">Konfigurasi Otomatisasi (Auto-Approve)</h2>
                    <p style="margin:6px 0 0 0; color:#64748b; font-size:13px;">Barang yang dicentang akan otomatis disetujui oleh sistem saat pegawai mengajukannya.</p>
                </div>
            </div>

            {{-- TOMBOL SIMPAN --}}
            <form method="POST" action="{{ route('otomatisasi.update') }}" id="otomatisasi-form">
                @csrf
                <input type="hidden" name="items_json" id="items_json_field">
                <button type="submit" style="display:flex; align-items:center; gap:8px; padding:12px 28px; background:#154c79; color:white; border:none; border-radius:25px; cursor:pointer; font-weight:bold; font-size:13px; box-shadow:0 4px 10px rgba(21,76,121,0.15); transition:0.2s; font-family:inherit;"
                    onmouseover="this.style.background='#10395b'" onmouseout="this.style.background='#154c79'"
                    onclick="prepareSubmit()">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Simpan
                </button>
            </form>
        </div>

        {{-- TABEL BARANG --}}
        <div style="background:white; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.02); overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:center; min-width:700px;">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        <th style="padding:15px 10px; width:60px;">
                            <input type="checkbox" id="select-all"
                                   style="transform:scale(1.3); cursor:pointer; accent-color:#27ae60;"
                                   title="Pilih Semua" onchange="toggleAll(this)">
                        </th>
                        <th style="padding:15px 10px; font-size:12px; color:#1f4068; font-weight:bold; text-transform:uppercase;">Foto</th>
                        <th style="padding:15px 10px; font-size:12px; color:#1f4068; font-weight:bold; text-transform:uppercase;">Kode</th>
                        <th style="padding:15px 10px; font-size:12px; color:#1f4068; font-weight:bold; text-transform:uppercase;">Nama Barang</th>
                        <th style="padding:15px 10px; font-size:12px; color:#1f4068; font-weight:bold; text-transform:uppercase;">Sisa Stok</th>
                        <th style="padding:15px 10px; font-size:12px; color:#1f4068; font-weight:bold; text-transform:uppercase;">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barang as $item)
                    <tr class="barang-row {{ $item->is_auto_approve ? 'row-checked' : '' }}"
                        style="border-bottom:1px solid #f1f5f9; background:{{ $item->is_auto_approve ? '#f0fdf4' : 'white' }}; transition:background-color 0.2s; cursor:pointer;"
                        onclick="toggleRow({{ $item->id_barang }}, this)">

                        {{-- CHECKBOX --}}
                        <td style="padding:12px 10px; text-align:center; vertical-align:middle;" onclick="event.stopPropagation();">
                            <input type="checkbox"
                                   id="cb-{{ $item->id_barang }}"
                                   class="barang-checkbox"
                                   data-id="{{ $item->id_barang }}"
                                   {{ $item->is_auto_approve ? 'checked' : '' }}
                                   style="transform:scale(1.3); cursor:pointer; accent-color:#27ae60;"
                                   onchange="onCheckboxChange(this)">
                        </td>

                        {{-- FOTO --}}
                        <td style="padding:12px 10px; text-align:center; vertical-align:middle;">
                            <div style="width:45px; height:45px; background:white; border-radius:8px; display:flex; justify-content:center; align-items:center; border:1px solid #e2e8f0; overflow:hidden; margin:0 auto;">
                                @if($item->foto_barang)
                                    <img src="{{ asset('uploads/'.$item->foto_barang) }}" alt="{{ $item->nama_barang }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <i data-lucide="image" style="width:20px;height:20px;color:#cbd5e1;"></i>
                                @endif
                            </div>
                        </td>

                        {{-- KODE --}}
                        <td style="padding:12px 10px; text-align:center; vertical-align:middle;">
                            <span style="font-size:12px; color:#1f4068; font-weight:500;">{{ $item->kode }}</span>
                        </td>

                        {{-- NAMA BARANG --}}
                        <td style="padding:12px 10px; text-align:center; vertical-align:middle;">
                            <span style="font-size:12px; color:#1f4068; font-weight:500;">{{ $item->nama_barang }}</span>
                        </td>

                        {{-- SISA STOK --}}
                        <td style="padding:12px 10px; text-align:center; vertical-align:middle;">
                            <span id="stok-text-{{ $item->id_barang }}" style="font-size:12px; font-weight:500; color:{{ $item->is_auto_approve ? '#15803d' : '#1f4068' }};">
                                {{ $item->stok_aktual }}
                            </span>
                        </td>

                        {{-- SATUAN --}}
                        <td style="padding:12px 10px; text-align:center; vertical-align:middle;">
                            <span style="font-size:12px; color:#1f4068; font-weight:500;">{{ $item->satuan }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:50px; color:#94a3b8; font-size:13px;">
                            Belum ada data barang di inventaris.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// State: simpan status tiap barang
const barangState = {};
@foreach($barang as $item)
    barangState[{{ $item->id_barang }}] = {{ $item->is_auto_approve ? 'true' : 'false' }};
@endforeach

function toggleAll(master) {
    const isChecked = master.checked;
    document.querySelectorAll('.barang-checkbox').forEach(cb => {
        cb.checked = isChecked;
        const id = parseInt(cb.dataset.id);
        barangState[id] = isChecked;
        updateRowStyle(id, isChecked);
    });
}

function onCheckboxChange(cb) {
    const id = parseInt(cb.dataset.id);
    barangState[id] = cb.checked;
    updateRowStyle(id, cb.checked);
    syncMasterCheckbox();
}

function toggleRow(id, row) {
    const cb = document.getElementById('cb-' + id);
    cb.checked = !cb.checked;
    barangState[id] = cb.checked;
    updateRowStyle(id, cb.checked);
    syncMasterCheckbox();
}

function updateRowStyle(id, isChecked) {
    const row = document.querySelector(`#cb-${id}`)?.closest('tr');
    const stokSpan = document.getElementById(`stok-text-${id}`);
    if (row) row.style.backgroundColor = isChecked ? '#f0fdf4' : 'white';
    if (stokSpan) stokSpan.style.color = isChecked ? '#15803d' : '#1f4068';
}

function syncMasterCheckbox() {
    const all = document.querySelectorAll('.barang-checkbox');
    const checked = document.querySelectorAll('.barang-checkbox:checked');
    const master = document.getElementById('select-all');
    master.checked = all.length > 0 && all.length === checked.length;
    master.indeterminate = checked.length > 0 && checked.length < all.length;
}

function prepareSubmit() {
    const items = Object.keys(barangState).map(id => ({
        id_barang: parseInt(id),
        is_auto_approve: barangState[id]
    }));
    document.getElementById('items_json_field').value = JSON.stringify(items);
}

// Init master checkbox state
syncMasterCheckbox();
</script>
@endpush
