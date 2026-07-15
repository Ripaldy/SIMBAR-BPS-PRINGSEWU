@extends('layouts.app')
@section('title', 'Katalog Barang')

@section('content')
<div style="max-width:1200px; margin:0 auto; padding-bottom:100px; position:relative; font-family:system-ui,-apple-system,sans-serif;">

    {{-- HEADER & PENCARIAN --}}
    <div style="background:white; padding:20px 25px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <div style="width:45px; height:45px; background:#eaf4fb; border-radius:12px; display:flex; justify-content:center; align-items:center;">
                <i data-lucide="shopping-cart" style="width:22px;height:22px;color:#3498db;"></i>
            </div>
            <div>
                <h2 style="margin:0; color:#1f4068; font-size:18px; font-weight:bold; text-transform:uppercase;">Katalog Barang</h2>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:11px;">Pilih barang dan ajukan permintaan.</p>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            {{-- Search --}}
            <div style="position:relative; min-width:250px;">
                <input type="text" id="katalog-search" placeholder="Cari nama barang..."
                    style="width:100%; padding:10px 15px 10px 38px; border-radius:25px; border:1px solid #e2e8f0; outline:none; font-size:12px; box-sizing:border-box; font-family:inherit;"
                    oninput="filterKatalog()" value="">
                <i data-lucide="search" style="width:16px;height:16px;color:#94a3b8;position:absolute;left:14px;top:50%;transform:translateY(-50%);"></i>
            </div>

            {{-- Toggle View --}}
            <div style="display:flex; background:#f1f5f9; border-radius:8px; padding:4px;">
                <button id="btn-grid" onclick="setView('grid')"
                    style="padding:6px 12px; border-radius:6px; border:none; background:white; color:#2563eb; cursor:pointer; box-shadow:0 2px 5px rgba(0,0,0,0.05); display:flex; align-items:center; transition:0.2s;">
                    <i data-lucide="layout-grid" style="width:16px;height:16px;"></i>
                </button>
                <button id="btn-list" onclick="setView('list')"
                    style="padding:6px 12px; border-radius:6px; border:none; background:transparent; color:#64748b; cursor:pointer; display:flex; align-items:center; transition:0.2s;">
                    <i data-lucide="list" style="width:16px;height:16px;"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI ADD TO CART --}}
    <div id="add-notif" style="display:none; padding:10px 18px; margin-bottom:20px; border-radius:8px; background:#ecfdf5; color:#059669; font-size:12px; display:none; align-items:center; gap:8px; font-weight:500; box-shadow:0 2px 10px rgba(5,150,105,0.1);">
        <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i>
        <span id="notif-text"></span>
    </div>

    {{-- AREA KONTEN --}}
    <div id="katalog-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:15px;">
        @forelse($barang as $item)
        <div class="katalog-card"
             data-nama="{{ strtolower($item->nama_barang) }}"
             style="background:white; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.02); display:flex; flex-direction:column;">

            {{-- GAMBAR --}}
            <div style="height:130px; background:#f8fafc; display:flex; justify-content:center; align-items:center; position:relative; overflow:hidden;">
                <span style="position:absolute; top:8px; right:8px; background:white; color:#3498db; padding:3px 6px; border-radius:10px; font-size:9px; font-weight:bold; box-shadow:0 2px 4px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
                    {{ $item->kode }}
                </span>
                @if($item->foto_barang)
                    <img src="{{ asset('uploads/'.$item->foto_barang) }}" alt="{{ $item->nama_barang }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i data-lucide="image" style="width:30px;height:30px;color:#cbd5e1;"></i>
                @endif
            </div>

            {{-- NAMA BARANG --}}
            <div style="padding:10px 8px; flex:1; background:white;">
                <h4 style="margin:0; color:#1f4068; font-size:12px; line-height:1.4; text-align:center; font-weight:bold;">{{ $item->nama_barang }}</h4>
            </div>

            {{-- STOK & TOMBOL PILIH --}}
            <div style="display:flex; height:40px; background:white; border-top:1px solid #f1f5f9;">
                <div style="flex:1; display:flex; justify-content:center; align-items:center; border-right:1px solid #f1f5f9;">
                    <strong style="color:#1f4068; font-size:12px;">{{ $item->stok_aktual }} <span style="font-weight:normal; color:#1f4068; font-size:11px;">{{ $item->satuan }}</span></strong>
                </div>
                <div style="flex:1; display:flex; justify-content:center; align-items:center; padding:4px;">
                    @php $itemData = ['id_barang' => $item->id_barang, 'nama_barang' => $item->nama_barang, 'stok_aktual' => $item->stok_aktual, 'satuan' => $item->satuan, 'foto_barang' => $item->foto_barang, 'kode' => $item->kode]; @endphp
                    <button onclick='addToCart(@json($itemData))'
                        style="width:100%;height:100%;display:flex;justify-content:center;align-items:center;gap:4px;background:#154c79;color:white;border:none;border-radius:4px;cursor:pointer;font-size:11px;font-weight:bold;font-family:inherit;">
                        <i data-lucide="plus" style="width:12px;height:12px;"></i> Pilih
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div id="katalog-empty-state" style="grid-column:1/-1; text-align:center; padding:60px; color:#94a3b8; font-size:13px;">
            Tidak ada barang yang tersedia saat ini.
        </div>
        @endforelse
        <div id="no-result" style="display:none; grid-column:1/-1; text-align:center; padding:40px; color:#94a3b8; font-size:13px;">
            Tidak ada barang yang sesuai pencarian.
        </div>
    </div>

    {{-- TAMPILAN LIST --}}
    <div id="katalog-list" style="display:none; background:white; border-radius:12px; border:1px solid #f1f5f9; overflow-x:auto; box-shadow:0 4px 10px rgba(0,0,0,0.02);">
        <table style="width:100%; border-collapse:collapse; text-align:center; min-width:700px;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:15px 20px; font-size:12px; color:#64748b; font-weight:bold; text-transform:uppercase;">Foto</th>
                    <th style="padding:15px 20px; font-size:12px; color:#64748b; font-weight:bold; text-transform:uppercase;">Kode</th>
                    <th style="padding:15px 20px; font-size:12px; color:#64748b; font-weight:bold; text-transform:uppercase;">Nama Barang</th>
                    <th style="padding:15px 20px; font-size:12px; color:#64748b; font-weight:bold; text-transform:uppercase;">Sisa Stok</th>
                    <th style="padding:15px 20px; font-size:12px; color:#64748b; font-weight:bold; text-transform:uppercase;">Satuan</th>
                    <th style="padding:15px 20px; font-size:12px; color:#64748b; font-weight:bold; text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barang as $item)
                <tr class="katalog-list-row"
                    data-nama="{{ strtolower($item->nama_barang) }}"
                    style="border-bottom:1px solid #f1f5f9; transition:background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding:12px 20px; vertical-align:middle;">
                        <div style="width:45px;height:45px;background:#f1f5f9;border-radius:8px;overflow:hidden;display:flex;justify-content:center;align-items:center;margin:0 auto;border:1px solid #e2e8f0;">
                            @if($item->foto_barang)
                                <img src="{{ asset('uploads/'.$item->foto_barang) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <i data-lucide="image" style="width:20px;height:20px;color:#cbd5e1;"></i>
                            @endif
                        </div>
                    </td>
                    <td style="padding:12px 20px; vertical-align:middle;"><strong style="font-size:12px; color:#1f4068; font-weight:500;">{{ $item->kode }}</strong></td>
                    <td style="padding:12px 20px; vertical-align:middle;"><strong style="color:#1f4068; font-size:12px;">{{ $item->nama_barang }}</strong></td>
                    <td style="padding:12px 20px; vertical-align:middle;"><strong style="color:#1f4068; font-size:12px;">{{ $item->stok_aktual }}</strong></td>
                    <td style="padding:12px 20px; vertical-align:middle;"><strong style="font-size:12px; color:#1f4068; font-weight:500;">{{ $item->satuan }}</strong></td>
                    <td style="padding:12px 20px; vertical-align:middle;">
                        @php $itemDataList = ['id_barang' => $item->id_barang, 'nama_barang' => $item->nama_barang, 'stok_aktual' => $item->stok_aktual, 'satuan' => $item->satuan, 'foto_barang' => $item->foto_barang, 'kode' => $item->kode]; @endphp
                        <button onclick='addToCart(@json($itemDataList))'
                            style="display:inline-flex;align-items:center;gap:6px;padding:8px 15px;background:#154c79;color:white;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:bold;font-family:inherit;">
                            <i data-lucide="plus" style="width:14px;height:14px;"></i> Pilih
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:40px; color:#94a3b8; font-size:13px;">Tidak ada barang tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FLOATING CART BUTTON --}}
    <button id="floating-cart" onclick="openCart()"
        style="display:none; position:fixed; bottom:30px; right:30px; background:#e74c3c; color:white; border:none; border-radius:50px; padding:12px 20px; align-items:center; gap:8px; cursor:pointer; box-shadow:0 8px 20px rgba(231,76,60,0.4); z-index:100; font-family:inherit;">
        <div style="position:relative;">
            <i data-lucide="shopping-cart" style="width:20px;height:20px;"></i>
            <span id="cart-badge" style="position:absolute; top:-8px; right:-10px; background:white; color:#e74c3c; width:18px; height:18px; border-radius:50%; display:flex; justify-content:center; align-items:center; font-size:10px; font-weight:bold;">0</span>
        </div>
        <span style="font-weight:bold; font-size:13px;">Keranjang</span>
    </button>
</div>

{{-- DRAWER KERANJANG --}}
<div id="cart-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:1000; justify-content:flex-end;">
    <div id="cart-drawer" style="width:100%; max-width:450px; background:white; height:100%; display:flex; flex-direction:column; box-shadow:-5px 0 30px rgba(0,0,0,0.1); transform:translateX(100%); transition:transform 0.3s ease-out;">

        <div style="padding:20px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
            <h3 style="margin:0; color:#1f4068; display:flex; align-items:center; gap:10px; font-size:15px;">
                <i data-lucide="shopping-cart" style="width:18px;height:18px;color:#3498db;"></i> Keranjang Pengajuan
            </h3>
            <button onclick="closeCart()" style="background:none; border:none; cursor:pointer;">
                <i data-lucide="x" style="width:20px;height:20px;color:#94a3b8;"></i>
            </button>
        </div>

        <div id="cart-items-list" style="flex:1; overflow-y:auto; padding:20px;">
            <div id="cart-empty-msg" style="text-align:center; color:#94a3b8; margin-top:50px; font-size:12px;">Keranjang kosong.</div>
        </div>

        <div id="cart-footer" style="display:none; padding:20px; border-top:1px solid #e2e8f0; background:#f8fafc;">
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:12px; color:#475569; font-weight:bold; margin-bottom:8px;">Alasan / Tujuan (Opsional)</label>
                <textarea id="cart-alasan" rows="2" placeholder="Tulis tujuan penggunaan barang..."
                    style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1; font-size:12px; box-sizing:border-box; outline:none; resize:vertical; font-family:inherit;"></textarea>
            </div>
            <form method="POST" action="{{ route('katalog.submit') }}" id="submit-form">
                @csrf
                <div id="form-items-hidden"></div>
                <input type="hidden" name="alasan" id="form-alasan">
                <button type="button" onclick="submitPengajuan()"
                    style="width:100%; padding:12px; background:#27ae60; color:white; border:none; border-radius:8px; font-size:14px; font-weight:bold; cursor:pointer; display:flex; justify-content:center; align-items:center; gap:8px; font-family:inherit;">
                    <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i> Ajukan Sekarang
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ===== CART STATE =====
let cart = {};
let viewMode = 'grid';

// ===== VIEW TOGGLE =====
function setView(mode) {
    viewMode = mode;
    document.getElementById('katalog-grid').style.display = mode === 'grid' ? 'grid' : 'none';
    document.getElementById('katalog-list').style.display = mode === 'list' ? 'block' : 'none';
    document.getElementById('btn-grid').style.background = mode === 'grid' ? 'white' : 'transparent';
    document.getElementById('btn-grid').style.color = mode === 'grid' ? '#2563eb' : '#64748b';
    document.getElementById('btn-grid').style.boxShadow = mode === 'grid' ? '0 2px 5px rgba(0,0,0,0.05)' : 'none';
    document.getElementById('btn-list').style.background = mode === 'list' ? 'white' : 'transparent';
    document.getElementById('btn-list').style.color = mode === 'list' ? '#2563eb' : '#64748b';
    document.getElementById('btn-list').style.boxShadow = mode === 'list' ? '0 2px 5px rgba(0,0,0,0.05)' : 'none';
}

// ===== SEARCH FILTER =====
function filterKatalog() {
    const q = document.getElementById('katalog-search').value.toLowerCase();
    const cards = document.querySelectorAll('.katalog-card');
    const rows  = document.querySelectorAll('.katalog-list-row');
    let visibleCount = 0;

    cards.forEach(card => {
        const match = card.dataset.nama.includes(q);
        card.style.display = match ? 'flex' : 'none';
        if (match) visibleCount++;
    });
    rows.forEach(row => {
        const match = row.dataset.nama.includes(q);
        row.style.display = match ? '' : 'none';
    });

    const noResult = document.getElementById('no-result');
    if (noResult) noResult.style.display = visibleCount === 0 && q ? 'block' : 'none';
}

// ===== CART LOGIC =====
function addToCart(barang) {
    if (cart[barang.id_barang]) {
        if (cart[barang.id_barang].qty >= parseInt(barang.stok_aktual)) {
            alert('Maksimal stok tercapai!'); return;
        }
        cart[barang.id_barang].qty++;
    } else {
        cart[barang.id_barang] = { ...barang, qty: 1 };
    }
    updateCartUI();
    showNotif(barang.nama_barang + ' ditambahkan ke keranjang.');
    lucide.createIcons();
}

function removeFromCart(id) {
    delete cart[id];
    updateCartUI();
    lucide.createIcons();
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    const newQty = cart[id].qty + delta;
    if (newQty < 1) { removeFromCart(id); return; }
    if (newQty > parseInt(cart[id].stok_aktual)) { alert('Melebihi stok!'); return; }
    cart[id].qty = newQty;
    updateCartUI();
}

function updateCartUI() {
    const items = Object.values(cart);
    const totalQty = items.length;
    const floatBtn = document.getElementById('floating-cart');
    const badge    = document.getElementById('cart-badge');
    const footer   = document.getElementById('cart-footer');
    const listEl   = document.getElementById('cart-items-list');

    // Floating button
    floatBtn.style.display = totalQty > 0 ? 'flex' : 'none';
    badge.textContent = totalQty;

    // Cart items
    if (items.length === 0) {
        footer.style.display   = 'none';
        listEl.innerHTML = '<div style="text-align:center; color:#94a3b8; margin-top:50px; font-size:12px;">Keranjang kosong.</div>';
        return;
    }

    footer.style.display   = 'block';

    listEl.innerHTML = items.map(item => `
        <div style="border:1px solid #e2e8f0; border-radius:10px; padding:15px; position:relative; margin-bottom:15px;">
            <button onclick="removeFromCart('${item.id_barang}')" style="position:absolute; top:12px; right:12px; background:none; border:none; cursor:pointer; color:#e74c3c;" title="Hapus">
                <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            </button>
            <div style="display:flex; gap:15px; align-items:center;">
                <div style="width:50px; height:50px; background:#f1f5f9; border-radius:8px; overflow:hidden; flex-shrink:0;">
                    ${item.foto_barang ? `<img src="/uploads/${item.foto_barang}" alt="" style="width:100%;height:100%;object-fit:cover;">` : '<div style="width:100%;height:100%;display:flex;justify-content:center;align-items:center;"><i data-lucide="image" style="width:20px;height:20px;color:#cbd5e1;"></i></div>'}
                </div>
                <div style="flex:1;">
                    <strong style="display:block; color:#1f4068; font-size:13px; margin-bottom:4px; padding-right:20px;">${item.nama_barang}</strong>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px;">
                        <span style="font-size:11px; color:#64748b;">Sisa: ${item.stok_aktual} ${item.satuan}</span>
                        <div style="display:flex; align-items:center; border:1px solid #cbd5e1; border-radius:6px; overflow:hidden;">
                            <button type="button" onclick="changeQty('${item.id_barang}', -1)" style="padding:4px 8px; background:#f8fafc; border:none; cursor:pointer; color:#475569;">−</button>
                            <span style="width:35px; text-align:center; font-size:12px; font-weight:bold; color:#1f4068;">${item.qty}</span>
                            <button type="button" onclick="changeQty('${item.id_barang}', 1)" style="padding:4px 8px; background:#f8fafc; border:none; cursor:pointer; color:#475569;">+</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    lucide.createIcons();
}

function openCart() {
    const overlay = document.getElementById('cart-overlay');
    const drawer  = document.getElementById('cart-drawer');
    overlay.style.display = 'flex';
    setTimeout(() => drawer.style.transform = 'translateX(0)', 10);
    lucide.createIcons();
}

function closeCart() {
    const drawer = document.getElementById('cart-drawer');
    drawer.style.transform = 'translateX(100%)';
    setTimeout(() => document.getElementById('cart-overlay').style.display = 'none', 300);
}

document.getElementById('cart-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeCart();
});

function showNotif(msg) {
    const el = document.getElementById('add-notif');
    document.getElementById('notif-text').textContent = msg;
    el.style.display = 'flex';
    setTimeout(() => el.style.display = 'none', 2000);
}

function submitPengajuan() {
    const alasan = document.getElementById('cart-alasan').value.trim();
    if (Object.keys(cart).length === 0) { alert('Keranjang kosong.'); return; }

    const hidden = document.getElementById('form-items-hidden');
    hidden.innerHTML = '';
    Object.values(cart).forEach((item, idx) => {
        hidden.innerHTML += `<input type="hidden" name="items[${idx}][id_barang]" value="${item.id_barang}">
                             <input type="hidden" name="items[${idx}][qty]" value="${item.qty}">`;
    });
    document.getElementById('form-alasan').value = alasan;
    document.getElementById('submit-form').submit();
}
</script>
@endpush
