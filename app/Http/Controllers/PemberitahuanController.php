<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemberitahuan;
use Carbon\Carbon;

class PemberitahuanController extends Controller
{
    // ===================== PEGAWAI & PEMIMPIN =====================
    public function create(Request $request)
    {
        return view('pegawai.request-barang');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'deskripsi'   => 'nullable|string|max:1000',
        ]);

        Pemberitahuan::create([
            'id_user'     => auth()->user()->id_user,
            'nama_barang' => $request->nama_barang,
            'deskripsi'   => $request->deskripsi,
            'status'      => 'unread',
        ]);

        return redirect()->back()->with('success', 'Pemberitahuan/Request barang berhasil dikirim ke Admin.');
    }

    // ===================== ADMIN =====================
    public function indexAdmin()
    {
        $pemberitahuan = Pemberitahuan::with('user')
            ->orderBy('status', 'desc') // 'unread' first ('u' > 'r')
            ->orderByDesc('created_at')
            ->paginate(5);
            
        return view('admin.pemberitahuan', compact('pemberitahuan'));
    }

    public function clearRead()
    {
        Pemberitahuan::where('status', 'read')->delete();
        return redirect()->back()->with('success', 'Semua pemberitahuan yang sudah dibaca berhasil dihapus.');
    }

    public function markAsRead($id)
    {
        $p = Pemberitahuan::findOrFail($id);
        $p->update(['status' => 'read']);
        return redirect()->back()->with('success', 'Pemberitahuan ditandai sudah dibaca.');
    }
}
