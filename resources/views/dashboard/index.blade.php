@extends('layouts.app')
@section('title', 'Dasbor Admin')

@section('content')
<div style="display:flex; flex-direction:column; gap:30px;">

    {{-- 4 KARTU METRIK --}}
    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-icon" style="background:#eaf4fb;">
                <i data-lucide="package" style="width:24px;height:24px;color:#3498db;"></i>
            </div>
            <span class="metric-label">Total Barang</span>
            <h2 class="metric-value" style="color:#3498db;">{{ $totalBarang }}</h2>
        </div>
        <div class="metric-card">
            <div class="metric-icon" style="background:#fef9e7;">
                <i data-lucide="clipboard-list" style="width:24px;height:24px;color:#f1c40f;"></i>
            </div>
            <span class="metric-label">Permintaan Tertunda</span>
            <h2 class="metric-value" style="color:#f1c40f;">{{ $permintaanTertunda }}</h2>
        </div>
        <div class="metric-card">
            <div class="metric-icon" style="background:#fdedec;">
                <i data-lucide="alert-triangle" style="width:24px;height:24px;color:#e74c3c;"></i>
            </div>
            <span class="metric-label">Barang Kritis</span>
            <h2 class="metric-value" style="color:#e74c3c;">{{ $barangKritis->count() }}</h2>
        </div>
        <div class="metric-card">
            <div class="metric-icon" style="background:#e9f7ef;">
                <i data-lucide="users" style="width:24px;height:24px;color:#2ecc71;"></i>
            </div>
            <span class="metric-label">Total Pengguna</span>
            <h2 class="metric-value" style="color:#2ecc71;">{{ $totalPengguna }}</h2>
        </div>
    </div>

    {{-- GRAFIK ANALITIK --}}
    {{-- Tampilan grafik tren barang keluar dan peringkat barang terlaris --}}
    <div class="card-lg">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:15px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i data-lucide="bar-chart-3" style="width:20px;height:20px;color:#3498db;"></i>
                <h3 style="margin:0; color:#1f4068; font-size:18px;">Analitik Sirkulasi Inventaris</h3>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" style="display:flex; gap:10px; flex-wrap:wrap;" id="chart-form">
                <select name="chart_type" class="form-select" style="width:auto;" onchange="document.getElementById('chart-form').submit();">
                    <option value="tren" {{ request('chart_type','tren')==='tren' ? 'selected' : '' }}>Grafik Tren Barang</option>
                    <option value="terlaris" {{ request('chart_type')==='terlaris' ? 'selected' : '' }}>Peringkat Barang Terlaris</option>
                </select>
                @if(request('chart_type') === 'terlaris')
                <select name="month" class="form-select" style="width:auto;" onchange="document.getElementById('chart-form').submit();">
                    <option value="Semua" {{ $month==='Semua' ? 'selected' : '' }}>Semua Bulan</option>
                    @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $month==$val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @endif
                <select name="year" class="form-select" style="width:auto;" onchange="document.getElementById('chart-form').submit();">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $year==$y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endforeach
                </select>
                @if(request('chart_type', 'tren') === 'tren')
                <select id="trend-filter-select" class="form-select" style="width:auto;" onchange="filterChart(this.value)">
                    <option value="gabungan">Gabungan</option>
                    <option value="masuk">Barang Masuk</option>
                    <option value="keluar">Barang Keluar</option>
                </select>
                @endif
            </form>
        </div>

        {{-- Chart Canvas --}}
        <div style="height:400px; width:100%; border:1px solid #f1f5f9; border-radius:12px; padding:20px; box-sizing:border-box; position:relative; display:flex; flex-direction:column;">
            <div style="flex:1; position:relative; width:100%;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI STOK KRITIS --}}
    <div style="background:#fffcfc; padding:30px; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border:1px solid #fee2e2;">
        <h3 style="margin:0 0 20px; font-size:16px; color:#b91c1c; display:flex; align-items:center; gap:8px;">
            <i data-lucide="alert-triangle" style="width:20px;height:20px;color:#ef4444;"></i>
            Notifikasi Stok Kritis
        </h3>

        @if($barangKritis->isEmpty())
            <div style="padding:20px; background:#ecfdf5; border-radius:8px; color:#047857; font-size:14px; border:1px solid #a7f3d0;">
                Luar biasa! Saat ini tidak ada persediaan barang yang mencapai batas stok kritis.
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:15px;">
                @foreach($barangKritis as $item)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:15px; background:white; border-radius:10px; border:1px solid #fecaca; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h4 style="margin:0 0 4px; color:#7f1d1d; font-size:15px;">{{ $item->nama_barang }}</h4>
                        <span style="font-size:12px; color:#64748b;">
                            Kategori: {{ $item->kategori }} | ID: {{ $item->kode }}
                        </span>
                    </div>
                    <div style="display:flex; align-items:center; gap:15px;">
                        <span style="background:#fef2f2; color:#ef4444; padding:5px 12px; border-radius:6px; font-size:13px; font-weight:bold;">
                            Stok: {{ $item->stok_aktual }} {{ $item->satuan }}
                        </span>
                        <span style="color:#94a3b8; font-size:13px;">
                            Minimum: {{ $item->stok_minimum }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartType = '{{ request('chart_type', 'tren') }}';
const dataTren     = @json($dataTren);
const dataTrenMasuk = @json($dataTrenMasuk);
const dataKategori = @json($dataKategori);
const dataBarang   = @json($dataBarang);

const ctx = document.getElementById('mainChart').getContext('2d');
const COLORS = ['#3498db','#e74c3c','#f1c40f','#2ecc71','#9b59b6','#34495e'];

let chart;

// Logika tampilan grafik tren barang
if (chartType === 'tren') {
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dataTren.map(d => d.name),
            datasets: [
                {
                    label: 'Total Barang Keluar',
                    data: dataTren.map(d => d.jumlah),
                    borderColor: '#e74c3c', // Red for keluar
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    pointRadius: 2,
                    pointBackgroundColor: '#e74c3c',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Total Barang Masuk',
                    data: dataTrenMasuk.map(d => d.jumlah),
                    borderColor: '#2ecc71', // Green for masuk
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    borderWidth: 2,
                    pointRadius: 2,
                    pointBackgroundColor: '#2ecc71',
                    fill: true,
                    tension: 0.4,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { 
                legend: { display: false }, // Turn off legend as requested
                tooltip: { 
                    backgroundColor: 'white', 
                    titleColor: '#1f4068', 
                    bodyColor: '#3498db', 
                    borderColor: '#e2e8f0', 
                    borderWidth: 1, 
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ' : ' + context.raw;
                        }
                    }
                } 
            },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#e2e8f0' } } }
        }
    });

    // Fungsi untuk memfilter grafik
    window.filterChart = function(type) {
        // Tampilkan/Sembunyikan dataset
        if(type === 'masuk') {
            chart.setDatasetVisibility(0, false); // Sembunyikan keluar
            chart.setDatasetVisibility(1, true);  // Tampilkan masuk
        } else if(type === 'keluar') {
            chart.setDatasetVisibility(0, true);  // Tampilkan keluar
            chart.setDatasetVisibility(1, false); // Sembunyikan masuk
        } else {
            chart.setDatasetVisibility(0, true);  // Tampilkan keluar
            chart.setDatasetVisibility(1, true);  // Tampilkan masuk
        }
        chart.update();
    }
} else {
    // Logika tampilan grafik peringkat barang terlaris
    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataBarang.map(d => d.name),
            datasets: [{
                label: 'Total Item Keluar',
                data: dataBarang.map(d => d.jumlah),
                backgroundColor: '#3498db',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { minRotation: 45, maxRotation: 45 } },
                y: { grid: { color: '#e2e8f0' } }
            }
        }
    });
}
</script>
@endpush
