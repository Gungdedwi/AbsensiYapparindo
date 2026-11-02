<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard masing-masing atau ke halaman login
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('teacher.dashboard');
    }

    return redirect()->route('login');
});

// Autentikasi Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Autentikasi Logout (Shared)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Ruang Kerja Guru (Dilindungi autentikasi dan peran teacher)
Route::middleware(['auth', 'role:teacher'])->prefix('guru')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
});

// Ruang Kerja Admin Sekolah (Dilindungi autentikasi dan peran admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});
