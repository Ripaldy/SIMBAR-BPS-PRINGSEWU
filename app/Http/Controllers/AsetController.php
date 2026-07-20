<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\Storage;

class AsetController extends Controller
{
    // Logika menampilkan halaman manajemen aset dan fungsi unduh template CSV
    public function index(Request $request)
    {
        if ($request->has('download_template')) {
            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="template_barang.csv"',
            ];
            $content  = "\xEF\xBB\xBF";
            $content .= "Nama Barang,Satuan,Stok Aktual,Stok Minimum\n";
            $content .= "KERTAS HVS A4,RIM,50,10\n";
            $content .= "SPIDOL HITAM,PCS,20,5\n";

            return response()->make($content, 200, $headers);
        }

        $search = $request->get('search', '');

        $barang = Barang::when($search, function ($q) use ($search) {
                $q->where('nama_barang', 'ilike', "%{$search}%");
            })
            ->orderBy('nama_barang')
            ->get();

        return view('dashboard.aset', compact('barang', 'search'));
    }

    // Logika fungsi tambah manual barang
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang'  => 'required|string|max:255',
            'satuan'       => 'required|string|max:50',
            'stok_aktual'  => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'foto_barang'  => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama_barang', 'satuan', 'stok_aktual', 'stok_minimum']);
        $data['is_auto_approve'] = $request->boolean('is_auto_approve');

        if ($request->hasFile('foto_barang')) {
            $path = $request->file('foto_barang')->store('', 'uploads');
            $data['foto_barang'] = basename($path);
        }

        $barang = Barang::create($data);

        // Record to barang_masuk history
        BarangMasuk::create([
            'id_barang' => $barang->id_barang,
            'jumlah_masuk' => $barang->stok_aktual,
            'id_user' => auth()->id()
        ]);

        return redirect()->route('aset.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    // Logika fungsi update atau edit barang
    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $request->validate([
            'nama_barang'  => 'required|string|max:255',
            'satuan'       => 'required|string|max:50',
            'stok_aktual'  => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'foto_barang'  => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama_barang', 'satuan', 'stok_aktual', 'stok_minimum']);
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

    // Logika fungsi tambah stok barang
    public function addStock(Request $request, $id)
    {
        $request->validate(['jumlah_tambah' => 'required|integer|min:1']);
        $barang = Barang::findOrFail($id);
        $barang->increment('stok_aktual', $request->jumlah_tambah);

        // Record to barang_masuk history
        BarangMasuk::create([
            'id_barang' => $barang->id_barang,
            'jumlah_masuk' => $request->jumlah_tambah,
            'id_user' => auth()->id()
        ]);

        return redirect()->route('aset.index')->with('success', 'Stok berhasil ditambahkan.');
    }

    // Logika fungsi hapus barang
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        if ($barang->foto_barang) {
            @unlink(public_path('uploads/' . $barang->foto_barang));
        }
        $barang->delete();
        return redirect()->route('aset.index')->with('success', 'Barang berhasil dihapus.');
    }

    // Logika upload dan import file CSV
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

        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            return redirect()->back()->withErrors(['file_excel' => 'File harus berformat CSV atau Excel.']);
        }

        $lines = [];
        if (in_array($ext, ['xlsx', 'xls'])) {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($file->getRealPath())) {
                $lines = $xlsx->rows();
                array_shift($lines); // skip header
            } else {
                return redirect()->back()->withErrors(['file_excel' => \Shuchkin\SimpleXLSX::parseError()]);
            }
        } else {
            $csvLines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            array_shift($csvLines); // skip header
            foreach ($csvLines as $line) {
                $delimiter = strpos($line, ';') !== false ? ';' : ',';
                $lines[] = str_getcsv($line, $delimiter);
            }
        }

        $count = 0;

        foreach ($lines as $row) {

            if (count($row) >= 4) {
                $nama = trim(str_replace('"', '', $row[0]));
                if (empty($nama)) continue;

                $barang = Barang::create([
                    'nama_barang'     => strtoupper($nama),
                    'satuan'          => strtoupper(trim(str_replace('"', '', $row[1]))),
                    'stok_aktual'     => (int) trim($row[2]),
                    'stok_minimum'    => (int) trim($row[3]),
                    'is_auto_approve' => false,
                ]);

                // Record to barang_masuk history
                BarangMasuk::create([
                    'id_barang' => $barang->id_barang,
                    'jumlah_masuk' => $barang->stok_aktual,
                    'id_user' => auth()->id()
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
