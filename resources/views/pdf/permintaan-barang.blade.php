<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Permintaan Barang Persediaan</title>
    <style>
        body {
            font-family: 'Calibri', Helvetica, Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }
        .header-table td {
            border: none;
            padding: 0;
        }
        .logo {
            width: 80px; /* Resize the logo so it's not huge */
        }
        .bps-text {
            color: #1e4b8f; /* Blue color similar to BPS logo text */
            font-style: italic;
            font-weight: bold;
            font-family: 'Calibri', Helvetica, Arial, sans-serif;
        }
        .title-container {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 25px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 2px 0;
        }
        .tim-kerja {
            font-weight: normal;
            font-size: 11px;
            margin-bottom: 15px;
            text-align: left;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            table-layout: fixed;
        }
        .main-table th, .main-table td {
            border: 1px solid black;
            padding: 6px;
            text-align: center;
            word-wrap: break-word;
        }
        .main-table th {
            font-weight: bold;
        }
        .text-left {
            text-align: left !important;
            padding-left: 10px !important;
        }
        .signatures {
            width: 100%;
            border: none;
            margin-top: 20px;
        }
        .signatures td {
            border: none;
            padding: 0;
            text-align: left;
            width: 50%;
            vertical-align: top;
        }
        .sign-block {
            padding-left: 10px;
        }
        .sign-block p {
            margin: 3px 0;
        }
        .sign-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 90px; text-align: left; vertical-align: middle;">
                <img src="{{ public_path('Logo_BPS.PNG') }}" class="logo" alt="Logo BPS">
            </td>
            <td style="text-align: left; vertical-align: middle; padding-left: 5px;">
                <div class="bps-text" style="font-size: 14px;">BADAN PUSAT STATISTIK</div>
                <div class="bps-text" style="font-size: 14px;">KABUPATEN PRINGSEWU</div>
            </td>
        </tr>
    </table>

    <div class="title-container">
        <div class="title">Permintaan</div>
        <div class="title">Barang Persediaan</div>
    </div>

    <div class="tim-kerja">
        {{ $timKerja }}
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Nama Barang</th>
                <th style="width: 10%;">Jumlah<br>Barang</th>
                <th style="width: 25%;">Kode Barang</th>
                <th style="width: 20%;">Keperluan</th>
                <th style="width: 10%;">Ket</th>
            </tr>
        </thead>
        <tbody>
            @php
                $itemCount = count($pengajuan);
                $rows = max(16, $itemCount);
            @endphp

            @for($i = 0; $i < $rows; $i++)
                <tr>
                    @if($i < $itemCount)
                        <td>{{ $i + 1 }}</td>
                        @php
                            $p = $pengajuan[$i];
                            // Jumlah disetujui / diminta
                            $jumlah = ($p->status_pengajuan === 'approved' || $p->status_pengajuan === 'sebagian') 
                                      ? $p->jumlah_disetujui 
                                      : $p->jumlah_diminta;
                            
                            // Gabungkan kode kategori dan kode barang
                            $kodeKat = $p->barang->kode_kategori ?? '';
                            $kodeBrg = $p->barang->kode_barang ?? '';
                            
                            // Jika dua-duanya ada, gabungkan dengan titik. Jika tidak ada, pakai salah satu, atau fallback
                            if ($kodeKat && $kodeBrg) {
                                $kode = $kodeKat . '.' . $kodeBrg;
                            } else if ($kodeKat) {
                                $kode = $kodeKat;
                            } else if ($kodeBrg) {
                                $kode = $kodeBrg;
                            } else {
                                $kode = 'BRG-' . str_pad($p->id_barang, 3, '0', STR_PAD_LEFT);
                            }
                        @endphp
                        <td class="text-left">{{ $p->barang->nama_barang ?? '-' }}</td>
                        <td>{{ $jumlah }}</td>
                        <td>{{ $kode }}</td>
                        <td>{{ $p->alasan }}</td>
                        <td></td>
                    @else
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    @endif
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-block">
                    <p>Di bukukan : {{ \Carbon\Carbon::parse($waktu)->locale('id')->translatedFormat('j F Y') }}</p>
                    <p>Kasubbag Umum</p>
                    <div class="sign-space"></div>
                    <p>{{ $kasubbag }}</p>
                </div>
            </td>
            <td>
                <div class="sign-block">
                    <p>Pringsewu, {{ \Carbon\Carbon::parse($waktu)->locale('id')->translatedFormat('j F Y') }}</p>
                    <p>Penerima,</p>
                    <div class="sign-space"></div>
                    <p>{{ $penerima }}</p>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
