@extends('layouts.app')

@section('title', 'Mode Luar Jaringan (Offline)')

@section('content')
<div class="min-h-full flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 text-center space-y-5">
        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto shadow-inner">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-12.728 0m0 0l2.829-2.829m-2.829 2.829L3 21m2.829-12.728a5 5 0 017.072 0m0 0l-2.829 2.829" />
            </svg>
        </div>

        <div class="space-y-1">
            <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[11px] font-bold tracking-wide uppercase">
                Sinyal Terputus
            </span>
            <h1 class="text-xl font-bold text-slate-900 mt-1">Anda Sedang Offline</h1>
            <p class="text-xs text-slate-500 leading-relaxed">
                Koneksi internet tidak terdeteksi. Namun, Anda tetap dapat membuka aplikasi dan mencatat absensi siswa yang tersimpan di perangkat lokal.
            </p>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs text-slate-600 space-y-1 text-left">
            <p class="font-bold text-slate-800">Petunjuk Mode Offline:</p>
            <p>&bull; Catatan absensi akan disimpan di memori perangkat (IndexedDB).</p>
            <p>&bull; Data akan otomatis disinkronkan saat sinyal internet kembali.</p>
        </div>

        <div class="pt-2 flex flex-col gap-2">
            <a
                href="{{ route('teacher.dashboard') }}"
                class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition min-h-[42px]"
            >
                Buka Ruang Kerja Guru
            </a>
            <button
                type="button"
                onclick="window.location.reload()"
                class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition min-h-[42px] cursor-pointer"
            >
                Coba Muat Ulang Halaman
            </button>
        </div>
    </div>
</div>
@endsection

