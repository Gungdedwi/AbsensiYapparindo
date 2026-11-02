<?php

use App\Http\Controllers\Admin\AttendanceMonitoringController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Teacher\AttendanceSessionController;
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
    Route::get('/penugasan/{assignment}', [AttendanceSessionController::class, 'assignment'])->name('assignments.show');
    Route::get('/penugasan/{assignment}/sesi/buat', [AttendanceSessionController::class, 'create'])->name('sessions.create');
    Route::post('/penugasan/{assignment}/sesi', [AttendanceSessionController::class, 'store'])->name('sessions.store');
    Route::get('/sesi/{session}', [AttendanceSessionController::class, 'show'])->name('sessions.show');
});

// Ruang Kerja Admin Sekolah (Dilindungi autentikasi dan peran admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/absensi', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/sesi/{session}', [AdminAttendanceController::class, 'show'])->name('sessions.show');
});
