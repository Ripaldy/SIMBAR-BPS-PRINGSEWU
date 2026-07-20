<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\OtomatisasiController;

// ======================== AUTH ========================
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ======================== PROTECTED (Semua Role) ========================
Route::middleware(['auth'])->group(function () {

    // Profil (semua role)
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::post('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // ===================== ADMIN & PEMIMPIN =====================
    Route::middleware(['role:admin,pemimpin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // ===================== ADMIN ONLY =====================
    Route::middleware(['role:admin'])->group(function () {

        // Manajemen Aset
        Route::get('/dashboard/aset', [AsetController::class, 'index'])->name('aset.index');
        Route::post('/dashboard/aset', [AsetController::class, 'store'])->name('aset.store');
        Route::post('/dashboard/aset/upload-csv', [AsetController::class, 'uploadCsv'])->name('aset.uploadCsv');
        Route::post('/dashboard/aset/{id}/update', [AsetController::class, 'update'])->name('aset.update');
        Route::post('/dashboard/aset/{id}/stock', [AsetController::class, 'addStock'])->name('aset.addStock');
        Route::post('/dashboard/aset/{id}/delete', [AsetController::class, 'destroy'])->name('aset.destroy');

        // Manajemen Pengguna
        Route::get('/dashboard/pengguna', [UserController::class, 'index'])->name('pengguna.index');
        Route::post('/dashboard/pengguna', [UserController::class, 'store'])->name('pengguna.store');
        Route::post('/dashboard/pengguna/upload-excel', [UserController::class, 'uploadExcel'])->name('pengguna.uploadExcel');
        Route::post('/dashboard/pengguna/{id}/update', [UserController::class, 'update'])->name('pengguna.update');
        Route::post('/dashboard/pengguna/{id}/toggle', [UserController::class, 'toggleStatus'])->name('pengguna.toggle');
        Route::post('/dashboard/pengguna/{id}/delete', [UserController::class, 'destroy'])->name('pengguna.destroy');

        // Persetujuan
        Route::get('/dashboard/persetujuan', [PengajuanController::class, 'persetujuan'])->name('persetujuan.index');
        Route::post('/dashboard/persetujuan/proses', [PengajuanController::class, 'prosesPersetujuan'])->name('persetujuan.proses');

        // Laporan
        Route::get('/dashboard/laporan', [PengajuanController::class, 'laporan'])->name('laporan.index');
        Route::post('/dashboard/laporan/hapus', [PengajuanController::class, 'hapusLaporan'])->name('laporan.hapus');

        // Otomatisasi
        Route::get('/dashboard/otomatisasi', [OtomatisasiController::class, 'index'])->name('otomatisasi.index');
        Route::post('/dashboard/otomatisasi', [OtomatisasiController::class, 'update'])->name('otomatisasi.update');
    });

    // ===================== PEGAWAI & PEMIMPIN =====================
    Route::middleware(['role:pegawai,pemimpin'])->group(function () {
        Route::get('/katalog', [PengajuanController::class, 'katalog'])->name('katalog.index');
        Route::post('/katalog/submit', [PengajuanController::class, 'submitPengajuan'])->name('katalog.submit');
        Route::get('/riwayat', [PengajuanController::class, 'riwayat'])->name('riwayat.index');
    });
});
