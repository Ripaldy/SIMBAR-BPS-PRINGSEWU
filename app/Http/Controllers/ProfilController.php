<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('dashboard.profil', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip'          => 'nullable|string|max:30',
            'no_telepon'   => 'nullable|string|max:20',
            'jabatan'      => 'nullable|string|max:100',
            'divisi'       => 'nullable|string|max:100',
            'foto_profil'  => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama_lengkap', 'nip', 'no_telepon', 'jabatan', 'divisi']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                @unlink(public_path('uploads/' . $user->foto_profil));
            }
            $file = $request->file('foto_profil');
            // FIX: Gunakan hashName() agar nama file menjadi acak & aman
            $filename = $file->hashName();
            $file->move(public_path('uploads'), $filename);
            $data['foto_profil'] = $filename;
        }

        $user->update($data);
        return redirect()->route('profil.index')->with('success', 'Profil berhasil diperbarui.');
    }
}
