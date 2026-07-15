# SIMBAR (Sistem Informasi Manajemen Barang)

SIMBAR adalah sebuah sistem berbasis web terintegrasi yang dirancang untuk mengelola inventaris, aset, dan siklus permohonan barang di dalam organisasi atau instansi. Sistem ini memberikan kemudahan mulai dari pencatatan barang masuk, permohonan pengambilan barang oleh pegawai, persetujuan oleh admin, hingga pencetakan laporan analitik.

---

## Arsitektur Sistem
Sistem ini dibangun menggunakan arsitektur perangkat lunak **MVC (Model-View-Controller)**:
- **Model**: Mengelola dan merepresentasikan data beserta logika relasional *database* (Barang, User, Pengajuan, BarangMasuk).
- **View**: Merender *user interface* secara dinamis menggunakan mesin templat **Blade** milik Laravel.
- **Controller**: Menjadi jembatan logika *business process* antara *View* dan *Model* (misalnya: `DashboardController`, `AsetController`, `PengajuanController`).

Aplikasi beroperasi sebagai sistem ***Server-Side Rendered (SSR)*** secara penuh di mana semua data dan *routing* difasilitasi di sisi *back-end*.

---

## Stack Teknologi (Tools)
- **Framework Utama**: Laravel (PHP)
- **Database**: Relational Database Management System (mendukung MySQL / MariaDB / PostgreSQL).
- **Styling**: Vanilla CSS kustom untuk desain UI yang ringan, bersih, dan premium.
- **Iconography**: Lucide Icons (didistribusikan via CDN).
- **Charting**: Chart.js (untuk visualisasi Dasbor).
- **Format Pertukaran Data**: Ekspor file `.csv` (Comma-Separated Values).

---

## Manajemen Hak Akses (Role)
Sistem ini menggunakan struktur Multi-Role dengan 3 tingkatan hak akses:

1. **Administrator (Admin)**
   Memiliki kendali penuh terhadap sistem:
   - Menambahkan dan memanajemen barang/aset.
   - Menambahkan dan memanajemen *user* lain.
   - Menyetujui atau menolak permohonan barang dari pegawai/pemimpin.
   - Melihat Dasbor analitik utama dan memantau stok kritis.
   - Mengakses fitur Riwayat dan Laporan (semua transaksi).

2. **Pegawai**
   Pengguna standar yang bertindak sebagai pemohon aset:
   - Melihat dan meramban (*browsing*) Katalog Barang.
   - Melakukan pengajuan barang (*checkout* barang).
   - Melihat status persetujuan pada menu Riwayat Pengajuan khusus untuk permohonan pribadinya.

3. **Pemimpin**
   *Role* gabungan (kombinasi fungsi pengawasan dan pemohon):
   - Dapat mengajukan barang (sama seperti peran Pegawai).
   - Memiliki akses khusus untuk melihat Dasbor Analitik (Grafik tren, peringkat barang, peringatan stok kritis) yang setara dengan tampilan dasbor Admin.
   - Memiliki riwayat pengajuannya sendiri.

---

## Fitur-Fitur Utama

### 1. Dasbor & Analitik Berjalan
Menyediakan visualisasi data inventaris (Chart.js), mencakup:
- **Grafik Tren Barang Keluar (Line Chart):** Laju fluktuasi permohonan barang antar-waktu.
- **Peringkat Barang Terlaris (Bar Chart):** Top aset yang sering diminta.
- **Kartu Metrik & Peringatan Dini:** Peringatan "Stok Kritis" jika stok aktual di bawah limit stok minimum.

### 2. Manajemen Aset Otomatis & Manual
- Dukungan *Import* masal via file CSV.
- Fitur **Auto-Approve**: Mem-Bypass siklus "Persetujuan Admin" untuk barang-barang kecil tak bernilai tinggi (misalnya Alat Tulis Kantor).
- Fitur *Add Stock*: Rekam jejak otomatis ketika admin menyuntikkan stok tambahan.

### 3. Manajemen Pengguna & Tim
Penambahan akun secara internal oleh admin dengan pembagian berdasarkan Role dan Divisi/Tim kerja.

### 4. Sistem Persetujuan Terintegrasi (Checkout)
Keranjang multi-item *real-time*:
- Pegawai memilih sejumlah aset dari katalog dan melampirkan alasan.
- Admin menyetujui, menyetujui sebagian, atau menolak permohonan dalam antrean (*Pending*).
- Kalkulasi pemotongan stok aktual berjalan seketika saat permohonan disetujui.

### 5. Laporan & Agregasi Lanjutan
Filter berjenjang lintas-tabel untuk keperluan *auditing*, mencakup 5 model data laporan:
1. **Tabel Gabungan (Per Waktu)**
2. **Tabel Lengkap (Per Barang / Itemized)**
3. **Tabel Pengeluaran Tim / Divisi**
4. **Tabel Agregat Barang Keluar**
5. **Tabel Agregat Barang Masuk**
*Mendukung ekspor tabel langsung menjadi dokumen CSV dan pencetakan ke format PDF.*

---

## Cara Konfigurasi & Instalasi

### Prasyarat:
- PHP >= 8.1
- Composer
- MySQL/MariaDB (atau software server lokal seperti XAMPP / Laragon)

### Langkah Pemasangan:
1. **Klon / Ekstrak Proyek**
   Simpan kode sumber (*source code*) pada direktori *server* lokal Anda (contoh: `laragon/www/simbar`).
   
2. **Instalasi Dependensi**
   Buka terminal di dalam direktori `simbar` dan jalankan:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   Salin *file* environment dan atur koneksi *database* Anda:
   ```bash
   cp .env.example .env
   ```
   Buka `.env` dan atur detail *database*, misalnya:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=simbar_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Kunci Aplikasi & Migrasi Database**
   Bangkitkan kunci aplikasi dan jalankan perintah pembangunan (migrasi) struktur tabel ke dalam database:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```

5. **Jalankan Aplikasi**
   Setelah semua langkah selesai, luncurkan *development server*:
   ```bash
   php artisan serve
   ```
   Buka URL `http://127.0.0.1:8000` pada peramban web (*browser*).

---

*(Dokumentasi dibuat secara sistematis untuk proyek internal. Pastikan selalu mem-backup database secara berkala sebelum melakukan perubahan struktur di level Production).*
