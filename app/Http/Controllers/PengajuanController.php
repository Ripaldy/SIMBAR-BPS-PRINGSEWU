<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanController extends Controller
{
    // ===================== ADMIN: PERSETUJUAN =====================
    public function persetujuan(Request $request)
    {
        $antrean = Pengajuan::with(['user', 'barang'])
            ->where('status_pengajuan', 'pending')
            ->orderBy('waktu_pengajuan')
            ->get();

        // Transformasi ke array plain agar bisa di-@json di blade tanpa closure
        $antreanData = $antrean->map(function ($p) {
            return [
                'id_pengajuan'     => $p->id_pengajuan,
                'id_barang'        => $p->id_barang,
                'id_user'          => $p->id_user,
                'waktu_pengajuan'  => $p->waktu_pengajuan ? $p->waktu_pengajuan->toIso8601String() : null,
                'nama_lengkap'     => $p->user->nama_lengkap ?? '-',
                'nama_barang'      => $p->barang->nama_barang ?? '-',
                'foto_barang'      => $p->barang->foto_barang ?? null,
                'jumlah_diminta'   => $p->jumlah_diminta,
                'alasan'           => $p->alasan,
            ];
        })->values()->toArray();

        return view('dashboard.persetujuan', compact('antreanData'));
    }

    public function prosesPersetujuan(Request $request)
    {
        $items = $request->input('items', []);
        $adminId = auth()->id();

        DB::transaction(function () use ($items, $adminId) {
            foreach ($items as $item) {
                $pengajuan = Pengajuan::findOrFail($item['id_pengajuan']);
                $status    = $item['status'];
                $jumlahDisetujui = (int) ($item['jumlah_disetujui'] ?? 0);

                $pengajuan->update([
                    'status_pengajuan' => $status,
                    'jumlah_disetujui' => $status === 'approved' ? $jumlahDisetujui : 0,
                    'waktu_diproses'   => Carbon::now(),
                    'diproses_oleh'    => $adminId,
                ]);

                if ($status === 'approved' && $jumlahDisetujui > 0) {
                    Barang::where('id_barang', $pengajuan->id_barang)
                        ->decrement('stok_aktual', $jumlahDisetujui);
                }
            }
        });

        return redirect()->route('persetujuan.index')->with('success', 'Pengajuan berhasil diproses.');
    }

    // ===================== PEGAWAI: KATALOG =====================
    public function katalog(Request $request)
    {
        $search = $request->get('search', '');
        $filterKategori = $request->get('kode_kategori', '');

        $barang = Barang::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nama_barang', 'ilike', "%{$search}%");
            })
            ->when($filterKategori, function ($q) use ($filterKategori) {
                $q->where('kode_kategori', $filterKategori);
            })
            ->orderByRaw('CASE WHEN stok_aktual > 0 THEN 1 ELSE 0 END DESC')
            ->orderBy('nama_barang')
            ->get();

        $kategoriList = Barang::whereNotNull('kode_kategori')
            ->select('kode_kategori', 'nama_kategori')
            ->distinct()
            ->orderBy('nama_kategori')
            ->get();
        $divisiList = [
            'Tim Subbagian Umum', 'Tim Statistik Sosial', 'Tim Statistik Produksi',
            'Tim Statistik Distribusi', 'Tim Neraca Wilayah dan Analisis Statistik',
            'Tim Pengolahan dan IT', 'Tim Diseminasi Statistik', 'Tim Reformasi Birokrasi',
            'Tim Perencanaan dan Administrasi Keuangan', 'Tim Pembinaan dan Pelaksanaan Statistik Sektoral',
            'Umum Kantor', 'Tim Humas', 'Tim Sensus Ekonomi 2026'
        ];

        return view('pegawai.katalog', compact('barang', 'search', 'kategoriList', 'filterKategori', 'divisiList'));
    }

    public function submitPengajuan(Request $request)
    {
        $request->validate([
            'items'     => 'required|array|min:1',
            'tim_kerja' => 'required|string|max:100',
            'alasan'    => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $waktu = Carbon::now();

        try {
            DB::transaction(function () use ($request, $user, $waktu) {
                // Validasi stok sebelum insert
                foreach ($request->items as $item) {
                    $barang = Barang::findOrFail($item['id_barang']);
                    $jumlah = (int) $item['qty'];

                    if ($jumlah <= 0) {
                        throw new \Exception("Jumlah untuk barang {$barang->nama_barang} harus lebih dari 0.");
                    }
                    if ($jumlah > $barang->stok_aktual) {
                        throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi (Sisa: {$barang->stok_aktual}).");
                    }
                }

                foreach ($request->items as $item) {
                    $barang = Barang::findOrFail($item['id_barang']);
                    $jumlah = (int) $item['qty'];

                    $status = $barang->is_auto_approve ? 'approved' : 'pending';
                    $jumlahDisetujui = $barang->is_auto_approve ? $jumlah : 0;

                    Pengajuan::create([
                        'id_user'         => $user->id_user,
                        'id_barang'       => $barang->id_barang,
                        'jumlah_diminta'  => $jumlah,
                        'jumlah_disetujui' => $jumlahDisetujui,
                        'status_pengajuan' => $status,
                        'tim_kerja'       => $request->tim_kerja,
                        'alasan'          => $request->alasan,
                        'waktu_pengajuan' => $waktu,
                        'waktu_diproses'  => $barang->is_auto_approve ? $waktu : null,
                        'diproses_oleh'   => $barang->is_auto_approve ? null : null,
                    ]);

                    if ($barang->is_auto_approve) {
                        $barang->decrement('stok_aktual', $jumlah);
                    }
                }
            });

            return redirect()->route('katalog.index')->with('success', 'Pengajuan berhasil dikirim.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    // ===================== PEGAWAI: RIWAYAT =====================
    public function riwayat(Request $request)
    {
        $user = auth()->user();

        // Ambil SEMUA riwayat pengajuan milik user ini
        $pengajuan = Pengajuan::with(['barang'])
            ->where('id_user', $user->id_user)
            ->orderByDesc('waktu_pengajuan')
            ->get();

        // Transformasi ke array plain agar bisa di-@json di blade tanpa closure
        $riwayatData = $pengajuan->map(function ($p) {
            return [
                'id_pengajuan'     => $p->id_pengajuan,
                'id_barang'        => $p->id_barang,
                'waktu_pengajuan'  => $p->waktu_pengajuan ? $p->waktu_pengajuan->toIso8601String() : null,
                'nama_barang'      => $p->barang->nama_barang ?? '-',
                'foto_barang'      => $p->barang->foto_barang ?? null,
                'jumlah_diminta'   => $p->jumlah_diminta,
                'jumlah_disetujui' => $p->jumlah_disetujui,
                'status_pengajuan' => $p->status_pengajuan,
                'alasan'           => $p->alasan,
            ];
        })->values()->toArray();

        return view('pegawai.riwayat', compact('riwayatData'));
    }

    // ===================== ADMIN: LAPORAN =====================
    public function laporan(Request $request)
    {
        // Ambil SEMUA riwayat yang sudah diproses (bukan pending)
        $semua = Pengajuan::with(['user', 'barang'])
            ->where('status_pengajuan', '!=', 'pending')
            ->orderByDesc('waktu_pengajuan')
            ->get();

        // Transformasi ke array plain agar bisa di-@json di blade tanpa closure
        $riwayatData = $semua->map(function ($p) {
            return [
                'id_pengajuan'     => $p->id_pengajuan,
                'id_barang'        => $p->id_barang,
                'id_user'          => $p->id_user,
                'waktu_pengajuan'  => $p->waktu_pengajuan ? $p->waktu_pengajuan->toIso8601String() : null,
                'nama_lengkap'     => $p->user->nama_lengkap ?? '-',
                'tim_kerja'        => $p->tim_kerja ?? '-',
                'nama_barang'      => $p->barang->nama_barang ?? '-',
                'kode_barang'      => $p->barang->kode_barang ?? null,
                'kode_kategori'    => $p->barang->kode_kategori ?? null,
                'nama_kategori'    => $p->barang->nama_kategori ?? null,
                'foto_barang'      => $p->barang->foto_barang ?? null,
                'satuan'           => $p->barang->satuan ?? '-',
                'jumlah_diminta'   => $p->jumlah_diminta,
                'jumlah_disetujui' => $p->jumlah_disetujui,
                'status_pengajuan' => $p->status_pengajuan,
                'alasan'           => $p->alasan,
            ];
        })->values()->toArray();

        // Ambil data Barang Masuk
        $barangMasuk = \App\Models\BarangMasuk::with('barang')->orderByDesc('waktu_masuk')->get();
        $barangMasukData = $barangMasuk->map(function ($bm) {
            return [
                'id_barang_masuk' => $bm->id_barang_masuk,
                'id_barang'       => $bm->id_barang,
                'nama_barang'     => $bm->barang->nama_barang ?? '-',
                'kode_barang'     => $bm->barang->kode_barang ?? null,
                'foto_barang'     => $bm->barang->foto_barang ?? null,
                'satuan'          => $bm->barang->satuan ?? '-',
                'jumlah_masuk'    => $bm->jumlah_masuk,
                'waktu_masuk'     => $bm->waktu_masuk ? $bm->waktu_masuk->toIso8601String() : null,
            ];
        })->values()->toArray();

        // ===== DATA LAPORAN RINCIAN BARANG PERSEDIAAN =====
        // Ambil semua barang beserta relasi barang_masuk dan pengajuan
        $semuaBarang = Barang::with(['pengajuan' => function($q) {
                $q->whereIn('status_pengajuan', ['approved', 'sebagian']);
            }, 'barangMasuk'])
            ->orderBy('nama_kategori')
            ->orderBy('kode_barang')
            ->get();

        $rincianData = $semuaBarang->map(function ($b) {
            return [
                'id_barang'       => $b->id_barang,
                'kode_barang'     => $b->kode_barang ?? null,
                'nama_barang'     => $b->nama_barang,
                'kode_kategori'   => $b->kode_kategori ?? null,
                'nama_kategori'   => $b->nama_kategori ?? null,
                'foto_barang'     => $b->foto_barang ?? null,
                'satuan'          => $b->satuan ?? '-',
                'harga_satuan'    => (int) ($b->harga_satuan ?? 0),
                'stok_aktual'     => (int) $b->stok_aktual,
                // pengajuan per item: [{ id_barang, jumlah_disetujui, waktu_pengajuan }]
                'pengajuan'       => $b->pengajuan->map(fn($p) => [
                    'jumlah_disetujui' => (int) ($p->jumlah_disetujui ?? 0),
                    'waktu'            => $p->waktu_pengajuan ? $p->waktu_pengajuan->toIso8601String() : null,
                ])->values()->toArray(),
                // barang_masuk per item: [{ jumlah_masuk, waktu_masuk }]
                'barang_masuk'    => $b->barangMasuk->map(fn($bm) => [
                    'jumlah_masuk' => (int) $bm->jumlah_masuk,
                    'waktu'        => $bm->waktu_masuk ? $bm->waktu_masuk->toIso8601String() : null,
                ])->values()->toArray(),
            ];
        })->values()->toArray();

        // Daftar divisi
        $divisiList = [
            'Tim Subbagian Umum',
            'Tim Statistik Sosial',
            'Tim Statistik Produksi',
            'Tim Statistik Distribusi',
            'Tim Neraca Wilayah dan Analisis Statistik',
            'Tim Pengolahan dan IT',
            'Tim Diseminasi Statistik',
            'Tim Reformasi Birokrasi',
            'Tim Perencanaan dan Administrasi Keuangan',
            'Tim Pembinaan dan Pelaksanaan Statistik Sektoral',
            'Umum Kantor',
            'Tim Humas',
            'Tim Sensus Ekonomi 2026'
        ];

        $availableYears = range(now()->year, 2024);

        return view('dashboard.laporan', compact('riwayatData', 'divisiList', 'availableYears', 'barangMasukData', 'rincianData'));
    }

    public function hapusLaporan(Request $request)
    {
        $year  = $request->input('tahun');
        $month = $request->input('bulan');

        $query = Pengajuan::where('status_pengajuan', '!=', 'pending')
            ->whereYear('waktu_pengajuan', $year);

        if ($month !== 'Semua') {
            $query->whereMonth('waktu_pengajuan', $month);
        }

        $query->delete();
        return redirect()->route('laporan.index')->with('success', 'Data laporan berhasil dihapus.');
    }
}
