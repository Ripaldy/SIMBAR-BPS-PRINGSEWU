<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Shared chart data for pemimpin and admin
        $year     = request('year', Carbon::now()->year);
        $month    = request('month', 'Semua');
        $currentYear = Carbon::now()->year;
        $availableYears = range($currentYear, 2024);
        $dataTren = $this->getTrendData($year);
        $dataKategori = $this->getKategoriData($year, $month);
        $dataBarang = $this->getBarangTerlaris($year, $month);

        if ($user->isPemimpin()) {
            // Stats for pemimpin
            $totalBarang        = Barang::count();
            $permintaanTertunda = Pengajuan::where('status_pengajuan', 'pending')->count();
            $barangKritis       = Barang::whereRaw('stok_aktual <= stok_minimum')->get();
            $totalDisetujui     = Pengajuan::where('status_pengajuan', 'approved')->count();

            return view('pemimpin.index', compact(
                'user',
                'totalBarang',
                'permintaanTertunda',
                'barangKritis',
                'totalDisetujui',
                'dataTren',
                'dataKategori',
                'dataBarang',
                'year',
                'month',
                'availableYears'
            ));
        }

        // Stats for admin dashboard
        $totalBarang        = Barang::count();
        $permintaanTertunda = Pengajuan::where('status_pengajuan', 'pending')->count();
        $totalPengguna      = User::count();
        $barangKritis       = Barang::whereRaw('stok_aktual <= stok_minimum')->get();

        return view('dashboard.index', compact(
            'user',
            'totalBarang',
            'permintaanTertunda',
            'totalPengguna',
            'barangKritis',
            'dataTren',
            'dataKategori',
            'dataBarang',
            'year',
            'month',
            'availableYears'
        ));
    }

    private function getTrendData(int $year): array
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $data = Pengajuan::where('status_pengajuan', 'approved')
            ->whereYear('waktu_pengajuan', $year)
            ->selectRaw('EXTRACT(MONTH FROM waktu_pengajuan) as bulan, SUM(jumlah_disetujui) as jumlah')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $result = [];
        foreach ($months as $num => $name) {
            $result[] = [
                'name'   => $name,
                'jumlah' => $data->has($num) ? (int) $data[$num]->jumlah : 0,
            ];
        }
        return $result;
    }

    private function getKategoriData(int $year, string $month): array
    {
        $query = Pengajuan::where('status_pengajuan', 'approved')
            ->whereYear('pengajuan.waktu_pengajuan', $year)
            ->join('barang', 'pengajuan.id_barang', '=', 'barang.id_barang')
            ->selectRaw('barang.kategori as name, SUM(pengajuan.jumlah_disetujui) as value')
            ->groupBy('barang.kategori');

        if ($month !== 'Semua') {
            $query->whereMonth('pengajuan.waktu_pengajuan', $month);
        }

        return $query->get()->toArray();
    }

    private function getBarangTerlaris(int $year, string $month): array
    {
        $query = Pengajuan::where('status_pengajuan', 'approved')
            ->whereYear('pengajuan.waktu_pengajuan', $year)
            ->join('barang', 'pengajuan.id_barang', '=', 'barang.id_barang')
            ->selectRaw('barang.nama_barang as name, SUM(pengajuan.jumlah_disetujui) as jumlah')
            ->groupBy('barang.nama_barang')
            ->orderByDesc('jumlah')
            ->limit(10);

        if ($month !== 'Semua') {
            $query->whereMonth('pengajuan.waktu_pengajuan', $month);
        }

        return $query->get()->toArray();
    }
}
