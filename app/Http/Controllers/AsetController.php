<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\Storage;

class AsetController extends Controller
{
    // Tampilkan halaman manajemen aset + fungsi unduh template CSV
    public function index(Request $request)
    {
        if ($request->has('download_template')) {
            $data = [
                ['Kode Kategori', 'Nama Kategori', 'Kode Barang', 'Nama Barang', 'Satuan', 'Stok Aktual', 'Stok Minimum', 'Harga Satuan'],
                ['1010301001', 'ALAT TULIS', '000001', 'BALLPOINT BOLLINER', 'PCS', 50, 10, 2500],
                ['1010301001', 'ALAT TULIS', '000004', 'SPIDOL', 'PCS', 20, 5, 8000],
                ['1010302001', 'KERTAS', '000010', 'KERTAS HVS A4', 'RIM', 30, 5, 55000],
            ];
            
            return response()->streamDownload(function() use ($data) {
                echo \Shuchkin\SimpleXLSXGen::fromArray($data);
            }, 'template_barang_bps.xlsx');
        }

        $search          = $request->get('search', '');
        $filterKategori  = $request->get('kode_kategori', '');

        $barang = Barang::when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('nama_barang',   'ilike', "%{$search}%")
                       ->orWhere('kode_barang', 'ilike', "%{$search}%");
                });
            })
            ->when($filterKategori, function ($q) use ($filterKategori) {
                $q->where('kode_kategori', $filterKategori);
            })
            ->orderByRaw("kode_kategori NULLS LAST")
            ->orderByRaw("kode_barang NULLS LAST")
            ->orderBy('nama_barang')
            ->get();

        // Daftar kategori unik dari tabel barang (untuk dropdown filter)
        $kategoriList = Barang::whereNotNull('kode_kategori')
            ->select('kode_kategori', 'nama_kategori')
            ->distinct()
            ->orderBy('nama_kategori')
            ->get();

        // Daftar satuan unik dari tabel barang (untuk datalist)
        $satuanList = Barang::whereNotNull('satuan')
            ->select('satuan')
            ->distinct()
            ->orderBy('satuan')
            ->pluck('satuan');

        return view('dashboard.aset', compact('barang', 'search', 'kategoriList', 'filterKategori', 'satuanList'));
    }

    // Tambah barang manual
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang'   => 'required|string|max:255',
            'satuan'        => 'nullable|string|max:50',
            'harga_satuan'  => 'nullable|integer|min:0',
            'stok_aktual'   => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'foto_barang'   => 'nullable|image|max:2048',
            'kode_barang'   => 'nullable|string|max:50',
            'kode_kategori' => 'nullable|string|max:50',
            'nama_kategori' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['nama_barang', 'satuan', 'harga_satuan', 'stok_aktual', 'stok_minimum', 'kode_barang', 'kode_kategori', 'nama_kategori']);
        $data['harga_satuan']    = (int) ($data['harga_satuan'] ?? 0);
        $data['nama_barang']     = strtoupper($data['nama_barang']);
        $data['nama_kategori']   = isset($data['nama_kategori']) ? strtoupper($data['nama_kategori']) : null;
        $data['is_auto_approve'] = $request->boolean('is_auto_approve');

        if ($request->hasFile('foto_barang')) {
            $path = $request->file('foto_barang')->store('', 'uploads');
            $data['foto_barang'] = basename($path);
        }

        $existingBarang = null;
        if (!empty($data['kode_barang'])) {
            $query = Barang::where('kode_barang', $data['kode_barang']);
            if (!empty($data['kode_kategori'])) {
                $query->where('kode_kategori', $data['kode_kategori']);
            }
            $existingBarang = $query->first();
        } else {
            $existingBarang = Barang::where('nama_barang', $data['nama_barang'])->first();
        }

        if ($existingBarang) {
            // Jika barang sudah ada, cukup tambahkan stoknya
            if ($data['stok_aktual'] > 0) {
                $existingBarang->increment('stok_aktual', $data['stok_aktual']);
                
                BarangMasuk::create([
                    'id_barang'    => $existingBarang->id_barang,
                    'jumlah_masuk' => $data['stok_aktual'],
                    'id_user'      => auth()->id()
                ]);
            }
        } else {
            // Buat barang baru
            $barang = Barang::create($data);

            BarangMasuk::create([
                'id_barang'    => $barang->id_barang,
                'jumlah_masuk' => $barang->stok_aktual,
                'id_user'      => auth()->id()
            ]);
        }

        return redirect()->route('aset.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    // Edit barang
    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $request->validate([
            'nama_barang'   => 'required|string|max:255',
            'satuan'        => 'nullable|string|max:50',
            'harga_satuan'  => 'nullable|integer|min:0',
            'stok_aktual'   => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'foto_barang'   => 'nullable|image|max:2048',
            'kode_barang'   => 'nullable|string|max:50',
            'kode_kategori' => 'nullable|string|max:50',
            'nama_kategori' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['nama_barang', 'satuan', 'harga_satuan', 'stok_aktual', 'stok_minimum', 'kode_barang', 'kode_kategori', 'nama_kategori']);
        $data['harga_satuan']    = (int) ($data['harga_satuan'] ?? 0);
        $data['nama_barang']     = strtoupper($data['nama_barang']);
        $data['nama_kategori']   = isset($data['nama_kategori']) ? strtoupper($data['nama_kategori']) : null;
        $data['is_auto_approve'] = $request->boolean('is_auto_approve');

        if ($request->hasFile('foto_barang')) {
            if ($barang->foto_barang) {
                @unlink(public_path('uploads/' . $barang->foto_barang));
            }
            $path = $request->file('foto_barang')->store('', 'uploads');
            $data['foto_barang'] = basename($path);
        }

        $barang->update($data);
        return redirect()->route('aset.index')->with('success', 'Barang berhasil diperbarui.');
    }

    // Tambah stok barang
    public function addStock(Request $request, $id)
    {
        $request->validate(['jumlah_tambah' => 'required|integer|not_in:0']);
        $barang = Barang::findOrFail($id);
        $barang->increment('stok_aktual', $request->jumlah_tambah);

        BarangMasuk::create([
            'id_barang'    => $barang->id_barang,
            'jumlah_masuk' => $request->jumlah_tambah,
            'id_user'      => auth()->id()
        ]);

        return redirect()->route('aset.index')->with('success', 'Stok berhasil ditambahkan.');
    }

    // Hapus barang
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        if ($barang->foto_barang) {
            @unlink(public_path('uploads/' . $barang->foto_barang));
        }
        $barang->delete();
        return redirect()->route('aset.index')->with('success', 'Barang berhasil dihapus.');
    }

    // Import CSV/Excel (format BPS baru)
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file'
        ], [
            'file_excel.required' => 'Pilih file terlebih dahulu.',
            'file_excel.file'     => 'File tidak valid.',
        ]);

        $file = $request->file('file_excel');
        $ext  = strtolower($file->getClientOriginalExtension());
        $uploadMode = $request->input('upload_mode', 'tambah');

        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            return redirect()->back()->withErrors(['file_excel' => 'File harus berformat CSV atau Excel.']);
        }

        $lines = [];
        if (in_array($ext, ['xlsx', 'xls'])) {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($file->getRealPath())) {
                $lines = $xlsx->rows();
                array_shift($lines);
            } else {
                return redirect()->back()->withErrors(['file_excel' => \Shuchkin\SimpleXLSX::parseError()]);
            }
        } else {
            $csvLines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            array_shift($csvLines);
            foreach ($csvLines as $line) {
                $delimiter = strpos($line, ';') !== false ? ';' : ',';
                $lines[] = str_getcsv($line, $delimiter);
            }
        }

        $count = 0;

        foreach ($lines as $row) {
            // Format BPS: Kode Kategori | Nama Kategori | Kode Barang | Nama Barang | Satuan | Stok Aktual | Stok Minimum | Harga Satuan
            if (count($row) >= 7) {
                $kodeKategori = strtoupper(trim(str_replace('"', '', $row[0])));
                $namaKategori = strtoupper(trim(str_replace('"', '', $row[1])));
                $kodeBarang   = trim(str_replace('"', '', $row[2]));
                $namaBarang   = strtoupper(trim(str_replace('"', '', $row[3])));
                if (empty($namaBarang)) continue;

                $stokAktual = (int) trim($row[5]);
                $hargaSatuan = isset($row[7]) ? (int) trim($row[7]) : 0;

                $existingBarang = null;
                if (!empty($kodeBarang)) {
                    $query = Barang::where('kode_barang', $kodeBarang);
                    if (!empty($kodeKategori)) {
                        $query->where('kode_kategori', $kodeKategori);
                    }
                    $existingBarang = $query->first();
                } else {
                    $existingBarang = Barang::where('nama_barang', $namaBarang)->first();
                }

                if ($existingBarang) {
                    if ($uploadMode === 'setup') {
                        // Mode 1: Setup Stok Awal (Timpa stok aktual, tanpa riwayat)
                        $existingBarang->update([
                            'stok_aktual'  => $stokAktual,
                        ]);
                    } elseif ($uploadMode === 'update_harga') {
                        // Mode 2: Update Harga & Stok Min (Abaikan stok aktual)
                        $existingBarang->update([
                            'harga_satuan' => $hargaSatuan > 0 ? $hargaSatuan : $existingBarang->harga_satuan,
                            'stok_minimum' => (int) trim($row[6]),
                        ]);
                    } elseif ($uploadMode === 'tambah_baru') {
                        // Mode 3: Import Barang Baru (Skip jika barang sudah ada)
                        continue;
                    } elseif ($uploadMode === 'tambah_stok') {
                        // Mode 4: Tambah Stok (Barang Masuk bulanan)
                        if ($stokAktual != 0) {
                            $existingBarang->increment('stok_aktual', $stokAktual);
                            BarangMasuk::create([
                                'id_barang'    => $existingBarang->id_barang,
                                'jumlah_masuk' => $stokAktual,
                                'id_user'      => auth()->id()
                            ]);
                        }
                        if ($hargaSatuan > 0) {
                            $existingBarang->update(['harga_satuan' => $hargaSatuan]);
                        }
                    }
                    $count++;
                    continue;
                }

                // Jika barang BELUM ADA di database
                if ($uploadMode === 'update_harga') {
                    // Jika mode ini, kita skip karena tujuannya hanya update harga barang existing
                    continue;
                }

                $barang = Barang::create([
                    'kode_kategori'   => $kodeKategori ?: null,
                    'nama_kategori'   => $namaKategori ?: null,
                    'kode_barang'     => $kodeBarang ?: null,
                    'nama_barang'     => $namaBarang,
                    'satuan'          => strtoupper(trim(str_replace('"', '', $row[4]))),
                    'stok_aktual'     => $stokAktual,
                    'stok_minimum'    => (int) trim($row[6]),
                    'harga_satuan'    => $hargaSatuan,
                    'is_auto_approve' => false,
                ]);

                // Hanya catat Barang Masuk jika modenya Tambah Stok bulanan atau Import Baru yang memang harus dicatat (berdasarkan requirement awal).
                // Tapi user minta: Setup Stok Awal TIDAK dicatat.
                if ($uploadMode === 'tambah_stok' || $uploadMode === 'tambah_baru') {
                    if ($barang->stok_aktual != 0) {
                        BarangMasuk::create([
                            'id_barang'    => $barang->id_barang,
                            'jumlah_masuk' => $barang->stok_aktual,
                            'id_user'      => auth()->id()
                        ]);
                    }
                }
                
                $count++;

            } elseif (count($row) >= 4) {
                // Format lama (backward compat): Nama Barang | Satuan | Stok Aktual | Stok Minimum
                $nama = strtoupper(trim(str_replace('"', '', $row[0])));
                if (empty($nama)) continue;

                $stokAktual = (int) trim($row[2]);

                $existingBarang = Barang::where('nama_barang', $nama)->first();

                if ($existingBarang) {
                    if ($uploadMode === 'update') {
                        $existingBarang->update([
                            'stok_aktual'  => $stokAktual,
                            'stok_minimum' => (int) trim($row[3] ?? 0),
                        ]);
                    } else {
                        if ($stokAktual != 0) {
                            $existingBarang->increment('stok_aktual', $stokAktual);
                            BarangMasuk::create([
                                'id_barang'    => $existingBarang->id_barang,
                                'jumlah_masuk' => $stokAktual,
                                'id_user'      => auth()->id()
                            ]);
                        }
                    }
                    $count++;
                    continue;
                }

                $barang = Barang::create([
                    'nama_barang'     => $nama,
                    'satuan'          => strtoupper(trim(str_replace('"', '', $row[1]))),
                    'stok_aktual'     => $stokAktual,
                    'stok_minimum'    => (int) trim($row[3]),
                    'is_auto_approve' => false,
                ]);

                BarangMasuk::create([
                    'id_barang'    => $barang->id_barang,
                    'jumlah_masuk' => $barang->stok_aktual,
                    'id_user'      => auth()->id()
                ]);
                $count++;
            }
        }

        return redirect()->route('aset.index')->with('success', "{$count} barang berhasil diimpor.");
    }

    public function updateAutoApprove(Request $request)
    {
        $items = $request->input('items', []);
        foreach ($items as $item) {
            Barang::where('id_barang', $item['id_barang'])
                ->update(['is_auto_approve' => (bool) $item['is_auto_approve']]);
        }
        return redirect()->route('otomatisasi.index')->with('success', 'Pengaturan auto-approve disimpan.');
    }
}
