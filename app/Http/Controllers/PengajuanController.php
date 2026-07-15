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
        $barang = Barang::where('stok_aktual', '>', 0)
            ->when($search, function ($q) use ($search) {
                $q->where('nama_barang', 'ilike', "%{$search}%");
            })
            ->orderBy('nama_barang')
            ->get();

        return view('pegawai.katalog', compact('barang', 'search'));
    }

    public function submitPengajuan(Request $request)
    {
        $request->validate([
            'items'  => 'required|array|min:1',
            'alasan' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $waktu = Carbon::now();

        DB::transaction(function () use ($request, $user, $waktu) {
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['id_barang']);
                $jumlah = (int) $item['qty'];

                if ($jumlah > $barang->stok_aktual) continue;

                $status = $barang->is_auto_approve ? 'approved' : 'pending';
                $jumlahDisetujui = $barang->is_auto_approve ? $jumlah : 0;

                Pengajuan::create([
                    'id_user'         => $user->id_user,
                    'id_barang'       => $barang->id_barang,
                    'jumlah_diminta'  => $jumlah,
                    'jumlah_disetujui' => $jumlahDisetujui,
                    'status_pengajuan' => $status,
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
                'divisi'           => $p->user->divisi ?? '-',
                'nama_barang'      => $p->barang->nama_barang ?? '-',
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
                'foto_barang'     => $bm->barang->foto_barang ?? null,
                'satuan'          => $bm->barang->satuan ?? '-',
                'jumlah_masuk'    => $bm->jumlah_masuk,
                'waktu_masuk'     => $bm->waktu_masuk ? $bm->waktu_masuk->toIso8601String() : null,
            ];
        })->values()->toArray();

        // Daftar divisi unik dari seluruh pengguna di sistem (seperti di Manajemen Pengguna)
        $divisiList = \App\Models\User::whereNotNull('divisi')
            ->distinct()
            ->pluck('divisi')
            ->sort()
            ->values();

        $availableYears = range(now()->year, 2024);

        return view('dashboard.laporan', compact('riwayatData', 'divisiList', 'availableYears', 'barangMasukData'));
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
