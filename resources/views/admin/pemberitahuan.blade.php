@extends('layouts.app')
@section('title', 'Pemberitahuan Barang Baru')

@section('content')
<div class="card-lg" style="max-width: 1100px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 25px; flex-wrap:wrap; gap:15px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <div style="background:#e0f2fe; padding:10px; border-radius:10px; color:#0284c7;">
                <i data-lucide="message-square" style="width:24px;height:24px;"></i>
            </div>
            <div>
                <h3 style="margin:0; color:#1f4068; font-size:20px;">Daftar Pemberitahuan (Request Barang Baru)</h3>
                <p style="margin:5px 0 0; color:#64748b; font-size:14px;">Pesan dari pegawai mengenai barang yang belum tersedia di katalog.</p>
            </div>
        </div>
        <div>
            <button type="button" onclick="openDeleteModal()" class="btn" style="background: #ef4444; border: none; color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; display:flex; align-items:center; gap:6px; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                <i data-lucide="trash-2" style="width:16px;height:16px;"></i> Hapus Pesan Terbaca
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
            {{ session('success') }}
        </div>
    @endif

    @if($pemberitahuan->isEmpty())
        <div style="text-align:center; padding: 50px 20px; background:#f8fafc; border-radius:12px; border:2px dashed #cbd5e1;">
            <i data-lucide="check-circle-2" style="width:48px;height:48px;color:#94a3b8;margin-bottom:15px;"></i >
            <h3 style="margin:0 0 10px; color:#475569;">Belum Ada Pemberitahuan</h3>
            <p style="margin:0; color:#94a3b8;">Saat ini tidak ada request barang baru dari pegawai.</p>
        </div>
    @else
        <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: center;">
                <thead style="background: #f8fafc; color: #475569; font-size: 13px; font-weight: 700; text-transform: uppercase;">
                    <tr>
                        <th style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">Waktu</th>
                        <th style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">Pemohon</th>
                        <th style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">Nama Barang</th>
                        <th style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">Pesan</th>
                        <th style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pemberitahuan as $item)
                        @php
                            $isUnread = $item->status === 'unread';
                            $bgRow = $isUnread ? '#f0f9ff' : 'white';
                            $fontWeight = $isUnread ? '600' : '400';
                            $textColor = $isUnread ? '#0f172a' : '#475569';
                            $readUrl = route('admin.pemberitahuan.read', $item->id_pemberitahuan);
                        @endphp
                        
                        <tr style="background: {{ $bgRow }}; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: {{ $textColor }}; font-weight: {{ $fontWeight }}; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='{{ $bgRow }}'">
                            <td style="padding: 15px 20px;">
                                {{ $item->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td style="padding: 15px 20px;">
                                {{ $item->user->nama_lengkap ?? 'Unknown' }}
                            </td>
                            <td style="padding: 15px 20px;">
                                {{ $item->nama_barang }}
                            </td>
                            <td style="padding: 15px 20px;">
                                <a href="javascript:void(0)" 
                                   class="open-modal-btn"
                                   data-id="{{ $item->id_pemberitahuan }}"
                                   data-nama="{{ $item->nama_barang }}"
                                   data-deskripsi="{{ $item->deskripsi }}"
                                   data-user="{{ $item->user->nama_lengkap ?? 'Unknown' }}"
                                   data-time="{{ $item->created_at->format('d M Y, H:i') }}"
                                   data-status="{{ $item->status }}"
                                   data-readurl="{{ $readUrl }}"
                                   style="color: #0ea5e9; text-decoration: underline; font-weight: 500;">Baca Pesan</a>
                            </td>
                            <td style="padding: 15px 20px;">
                                @if($isUnread)
                                    <span style="background: #fef9c3; color: #854d0e; padding: 5px 12px; border-radius: 999px; font-size: 13px; font-weight: 500; border: 1px solid #fef08a;">
                                        ~ Belum Dibaca
                                    </span>
                                @else
                                    <span style="background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 999px; font-size: 13px; font-weight: 500; border: 1px solid #bbf7d0;">
                                        &#10003; Sudah Dibaca
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div style="margin-top: 25px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size: 14px; color: #64748b;">
                Menampilkan {{ $pemberitahuan->firstItem() ?? 0 }} - {{ $pemberitahuan->lastItem() ?? 0 }} dari {{ $pemberitahuan->total() }} entri laporan
            </div>
            <div>
                {{ $pemberitahuan->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Modal Popup --}}
<div id="messageModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15, 23, 42, 0.6); z-index:9999; justify-content:center; align-items:center; padding: 20px;">
    <div style="background:white; width:100%; max-width:550px; border-radius:16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display:flex; flex-direction:column; max-height: 90vh; overflow:hidden;">
        
        {{-- Modal Header --}}
        <div style="background: #f8fafc; padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size: 18px; color: #0f172a; font-weight: 600; display:flex; align-items:center; gap:10px;">
                <div style="background: white; padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0; color: #0ea5e9; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="mail-open" style="width:18px;height:18px;"></i>
                </div>
                Detail Pesan Pegawai
            </h3>
            <button onclick="closeModal()" style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:8px; border-radius:8px; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='none'">
                <i data-lucide="x" style="width:20px;height:20px;"></i>
            </button>
        </div>
        
        {{-- Modal Body --}}
        <div style="padding: 25px 30px; overflow-y:auto;">
            <div style="text-align: center; margin-bottom: 25px;">
                <h2 id="modalNama" style="margin:0 0 10px; font-size: 24px; color: #0f172a; font-weight: 700;"></h2>
                <div style="display:inline-flex; align-items:center; gap: 8px; background: #f1f5f9; padding: 6px 12px; border-radius: 999px; color: #475569; font-size: 13px;">
                    <i data-lucide="user" style="width:14px;height:14px;"></i>
                    <span id="modalUser" style="font-weight: 500;"></span>
                    <span style="color: #cbd5e1;">|</span>
                    <i data-lucide="calendar" style="width:14px;height:14px;"></i>
                    <span id="modalTime"></span>
                </div>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label style="display:block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Deskripsi / Keterangan</label>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; min-height: 120px; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);">
                    <p id="modalDeskripsi" style="margin: 0; color: #334155; line-height: 1.7; font-size: 15px; white-space: pre-wrap;"></p>
                </div>
            </div>
        </div>
        
        {{-- Modal Footer --}}
        <div style="padding: 20px 30px; border-top: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background: #f8fafc;">
            <div id="modalStatusInfo" style="font-size: 14px; color: #64748b; font-weight: 500;">
                <!-- Status text will be injected here -->
            </div>
            
            <div style="display:flex; gap: 10px;">
                <button onclick="closeModal()" class="btn" style="background: white; border: 1px solid #cbd5e1; color: #475569; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">Tutup</button>
                
                <form id="modalReadForm" action="" method="POST" style="display:none; margin:0;">
                    @csrf
                    <button type="submit" class="btn" style="background: #0ea5e9; border: none; color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display:flex; align-items:center; gap:8px; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                        <i data-lucide="check-circle" style="width:18px;height:18px;"></i> Tandai Dibaca
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15, 23, 42, 0.6); z-index:9999; justify-content:center; align-items:center; padding: 20px;">
    <div style="background:white; width:100%; max-width:400px; border-radius:16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display:flex; flex-direction:column; overflow:hidden;">
        <div style="padding: 25px; text-align: center;">
            <div style="background: #fee2e2; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i data-lucide="alert-triangle" style="width:30px;height:30px;color:#ef4444;"></i>
            </div>
            <h3 style="margin:0 0 10px; font-size: 18px; color: #0f172a; font-weight: 600;">Konfirmasi Hapus</h3>
            <p style="margin:0; color: #64748b; font-size: 14px; line-height: 1.5;">Apakah Anda yakin ingin menghapus <strong>SEMUA</strong> pesan yang sudah dibaca? Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div style="padding: 15px 25px; background: #f8fafc; border-top: 1px solid #e2e8f0; display:flex; justify-content:center; gap: 10px;">
            <button type="button" onclick="closeDeleteModal()" class="btn" style="flex:1; background: white; border: 1px solid #cbd5e1; color: #475569; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: 600;">Batal</button>
            <form action="{{ route('admin.pemberitahuan.clearRead') }}" method="POST" style="flex:1; margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="width:100%; background: #ef4444; border: none; color: white; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: 600;">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.open-modal-btn').forEach(button => {
        button.addEventListener('click', function() {
            const nama = this.getAttribute('data-nama');
            const deskripsi = this.getAttribute('data-deskripsi');
            const user = this.getAttribute('data-user');
            const time = this.getAttribute('data-time');
            const status = this.getAttribute('data-status');
            const readUrl = this.getAttribute('data-readurl');

            document.getElementById('modalNama').innerText = nama;
            document.getElementById('modalDeskripsi').innerText = deskripsi || 'Tidak ada keterangan tambahan yang diberikan.';
            document.getElementById('modalUser').innerText = user;
            document.getElementById('modalTime').innerText = time;
            
            const readForm = document.getElementById('modalReadForm');
            const statusInfo = document.getElementById('modalStatusInfo');
            
            if (status === 'unread') {
                readForm.style.display = 'block';
                readForm.action = readUrl;
                statusInfo.innerHTML = '<span style="color:#eab308;">Belum Dibaca</span>';
            } else {
                readForm.style.display = 'none';
                statusInfo.innerHTML = '<span style="color:#166534;"><i data-lucide="check" style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:4px;"></i>Sudah Dibaca</span>';
            }
            
            document.body.style.overflow = 'hidden';
            document.getElementById('messageModal').style.display = 'flex';
            
            // Re-initialize lucide icons inside modal for dynamically added ones
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });
    
    function closeModal() {
        document.getElementById('messageModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    document.getElementById('messageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    function openDeleteModal() {
        document.body.style.overflow = 'hidden';
        document.getElementById('deleteModal').style.display = 'flex';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
</script>
@endpush
