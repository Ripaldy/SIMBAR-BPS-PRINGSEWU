<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;

class OtomatisasiController extends Controller
{
    public function index(Request $request)
    {
        $barang = Barang::orderBy('nama_barang')->get();
        return view('dashboard.otomatisasi', compact('barang'));
    }

    public function update(Request $request)
    {
        // Handle items_json dari tampilan tabel baru
        $itemsJson = $request->input('items_json');
        if ($itemsJson) {
            $items = json_decode($itemsJson, true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    Barang::where('id_barang', $item['id_barang'])
                        ->update(['is_auto_approve' => (bool) $item['is_auto_approve']]);
                }
                return redirect()->route('otomatisasi.index')->with('success', 'Pengaturan auto-approve berhasil disimpan.');
            }
        }

        // Fallback: handle checkbox array lama (auto_approve_ids[])
        $autoApproveIds = $request->input('auto_approve_ids', []);
        Barang::query()->update(['is_auto_approve' => false]);
        if (!empty($autoApproveIds)) {
            Barang::whereIn('id_barang', $autoApproveIds)->update(['is_auto_approve' => true]);
        }

        return redirect()->route('otomatisasi.index')->with('success', 'Pengaturan auto-approve berhasil disimpan.');
    }
}
