@extends('layouts.app')
@section('title', 'Request Barang Baru')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 180px);">
    <div class="card-lg" style="width: 100%; max-width: 600px; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
    <div style="display:flex; align-items:center; gap:15px; margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
        <i data-lucide="help-circle" style="width:32px;height:32px;color:#3498db;"></i>
        <h2 style="margin:0; color:#1f4068; font-size:22px;">Request Barang Baru</h2>
    </div>

    <p style="color:#64748b; margin-bottom: 25px; line-height: 1.6;">
        Jika barang yang Anda cari tidak tersedia di katalog, Anda dapat memberitahu Admin dengan mengisi form di bawah ini. Admin akan mempertimbangkan untuk menambahkan barang tersebut ke dalam sistem.
    </p>

    <form action="{{ route('request-barang.store') }}" method="POST">
        @csrf
        <div style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom: 8px; font-weight: 600; color: #334155;">Nama Barang <span style="color:#ef4444;">*</span></label>
            <input type="text" name="nama_barang" class="form-input" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px;" placeholder="Contoh: Kertas HVS A3" required>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display:block; margin-bottom: 8px; font-weight: 600; color: #334155;">Deskripsi / Keterangan <span style="color:#ef4444;">*</span></label>
            <textarea name="deskripsi" rows="7" class="form-input red-placeholder" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px;" placeholder="Tuliskan spesifikasi, jumlah yang biasanya dibutuhkan, atau alasan mengapa barang ini diperlukan..." required></textarea>
            <style>
                .red-placeholder::placeholder { color: #ef4444; opacity: 1; }
                .red-placeholder:-ms-input-placeholder { color: #ef4444; }
                .red-placeholder::-ms-input-placeholder { color: #ef4444; }
            </style>
        </div>

        <div style="display:flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('katalog.index') }}" class="btn" style="background:#f1f5f9; color:#475569; padding: 12px 24px; border-radius: 8px; text-decoration:none;">Batal</a>
            <button type="submit" class="btn" style="background:#3498db; color:white; padding: 12px 24px; border-radius: 8px; border:none; cursor:pointer; font-weight:600;">Kirim Request</button>
        </div>
    </form>
    </div>
</div>
@endsection
