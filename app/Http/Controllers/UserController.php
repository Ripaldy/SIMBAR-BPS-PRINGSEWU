<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Handle download template CSV
        if ($request->has('download_template')) {
            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="template_pengguna.csv"',
            ];
            $content  = "\xEF\xBB\xBF"; // BOM untuk Excel UTF-8
            $content .= "Nama Lengkap,Email,Password,Role,Jabatan,NIP,NIP BPS\n";
            $content .= "Ahmad Fauzi,ahmad@example.com,password123,pegawai,Staff IT,199001012020121001,340012345\n";
            $content .= "Siti Rahma,siti@example.com,password123,pegawai,Sekretaris,199505052021122002,340054321\n";
            return response()->make($content, 200, $headers);
        }

        $search       = $request->get('search', '');
        $roleFilter   = $request->get('role', 'Semua');
        $statusFilter = $request->get('status', 'Semua');
        $divisiFilter = $request->get('divisi', 'Semua');

        $pengguna = User::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('nama_lengkap', 'ilike', "%{$search}%")
                       ->orWhere('email', 'ilike', "%{$search}%")
                       ->orWhere('jabatan', 'ilike', "%{$search}%")
                       ->orWhere('divisi', 'ilike', "%{$search}%");
                });
            })
            ->when($roleFilter !== 'Semua', function ($q) use ($roleFilter) {
                $q->where('role', strtolower($roleFilter));
            })
            ->when($statusFilter === 'Aktif', function ($q) {
                $q->where('is_verified', true);
            })
            ->when($statusFilter === 'Nonaktif', function ($q) {
                $q->where('is_verified', false);
            })
            ->when($divisiFilter !== 'Semua', function ($q) use ($divisiFilter) {
                $q->where('divisi', $divisiFilter);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $stats = [
            'total'    => User::count(),
            'admin'    => User::where('role', 'admin')->count(),
            'pegawai'  => User::where('role', 'pegawai')->count(),
            'nonaktif' => User::where('is_verified', false)->count(),
        ];

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

        return view('dashboard.pengguna', compact('pengguna', 'stats', 'divisiList', 'search', 'roleFilter', 'statusFilter', 'divisiFilter'));
    }

    public function store(Request $request)
    {
        // Jika pilihan divisi adalah '_custom', ambil dari input teks divisi_custom
        $divisi = $request->divisi;
        if ($divisi === '_custom') {
            $divisi = $request->divisi_custom;
        }
        if (empty($divisi)) $divisi = null;

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'role'         => 'required|in:admin,pegawai,pemimpin',
            'jabatan'      => 'nullable|string|max:100',
            'nip'          => 'nullable|string|max:50',
            'nip_bps'      => 'nullable|string|max:50',
        ]);

        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => strtolower($request->role),
            'jabatan'      => $request->jabatan ?: null,
            'nip'          => $request->nip ?: null,
            'nip_bps'      => $request->nip_bps ?: null,
            'divisi'       => $divisi,
            'is_verified'  => true,
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Jika pilihan divisi adalah '_custom', ambil dari input teks divisi_custom
        $divisi = $request->divisi;
        if ($divisi === '_custom') {
            $divisi = $request->divisi_custom;
        }
        if (empty($divisi)) $divisi = null;

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'role'         => 'required|in:admin,pegawai,pemimpin',
            'jabatan'      => 'nullable|string|max:100',
            'nip'          => 'nullable|string|max:50',
            'nip_bps'      => 'nullable|string|max:50',
        ]);

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'role'         => strtolower($request->role),
            'jabatan'      => $request->jabatan ?: null,
            'nip'          => $request->nip ?: null,
            'nip_bps'      => $request->nip_bps ?: null,
            'divisi'       => $divisi,
            'is_verified'  => $request->input('status') === 'Aktif',
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => !$user->is_verified]);
        $status = $user->is_verified ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('pengguna.index')->with('success', "Pengguna berhasil {$status}.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id_user === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $user->delete();
        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Download template CSV untuk import pengguna
     */
    public function downloadTemplate(Request $request)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_pengguna.csv"',
        ];
        $content  = "\xEF\xBB\xBF"; // BOM untuk Excel UTF-8
        $content .= "Nama Lengkap,Email,Password,Role,Jabatan,NIP,NIP BPS\n";
        $content .= "Ahmad Fauzi,ahmad@example.com,password123,pegawai,Staff IT,199001012020121001,340012345\n";
        $content .= "Siti Rahma,siti@example.com,password123,pegawai,Sekretaris,199505052021122002,340054321\n";

        return response()->make($content, 200, $headers);
    }

    /**
     * Upload & import pengguna dari CSV/Excel
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file',
        ], [
            'file_excel.required' => 'Pilih file terlebih dahulu.',
            'file_excel.file'     => 'File tidak valid.',
        ]);

        $file = $request->file('file_excel');
        $ext  = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            return redirect()->back()->withErrors(['file_excel' => 'File harus berformat CSV atau Excel (.csv/.xlsx/.xls).']);
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
        $errors = [];

        foreach ($lines as $lineNo => $row) {
            if (count($row) < 3) continue;

            $nama  = trim(str_replace('"', '', $row[0]));
            $email = trim(str_replace('"', '', $row[1]));
            $pass  = trim(str_replace('"', '', $row[2]));
            $role  = strtolower(trim(str_replace('"', '', $row[3] ?? 'pegawai')));
            $jabatan = trim(str_replace('"', '', $row[4] ?? ''));
            $nip     = trim(str_replace('"', '', $row[5] ?? ''));
            $nip_bps = trim(str_replace('"', '', $row[6] ?? ''));

            if (empty($nama) || empty($email) || empty($pass)) continue;
            if (!in_array($role, ['admin', 'pegawai', 'pemimpin'])) $role = 'pegawai';

            // Cek email sudah ada atau tidak
            if (User::where('email', $email)->exists()) {
                $errors[] = "Baris " . ($lineNo + 2) . ": Email '{$email}' sudah terdaftar, dilewati.";
                continue;
            }

            User::create([
                'nama_lengkap' => $nama,
                'email'        => $email,
                'password'     => Hash::make($pass),
                'role'         => $role,
                'jabatan'      => $jabatan ?: null,
                'nip'          => $nip ?: null,
                'nip_bps'      => $nip_bps ?: null,
                'is_verified'  => true,
            ]);
            $count++;
        }

        $message = "{$count} pengguna berhasil diimpor.";
        if (!empty($errors)) {
            $message .= ' Catatan: ' . implode(' | ', array_slice($errors, 0, 5));
        }

        return redirect()->route('pengguna.index')->with('success', $message);
    }
}
