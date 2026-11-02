@extends('layouts.app')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="min-h-full flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-md space-y-6">
        <!-- Identitas Sekolah & Logo -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-black text-2xl shadow-md shadow-emerald-600/20">
                AY
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">SMK Yapparindo</h1>
            <p class="text-sm text-slate-500">Sistem Absensi & Jurnal Pembelajaran</p>
        </div>

        <!-- Kartu Formulir Login -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-semibold text-slate-900">Masuk ke Akun Anda</h2>
                <p class="text-xs text-slate-500 mt-0.5">Gunakan email atau nama pengguna yang telah terdaftar</p>
            </div>

            <!-- Pesan Status Flash -->
            @if (session('status'))
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Pesan Error Global -->
            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
                    <div class="flex items-center gap-2 font-medium">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Gagal Masuk</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-rose-700 pl-7">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4" novalidate>
                @csrf

                <!-- Input Identifier (Email atau Username) -->
                <div class="space-y-1.5">
                    <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Email atau Nama Pengguna
                    </label>
                    <input
                        type="text"
                        name="login"
                        id="login"
                        value="{{ old('login') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@example.test atau budi.santoso"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                    >
                </div>

                <!-- Input Kata Sandi -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition pr-10"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                        >
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Opsi Ingat Saya -->
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        >
                        <span class="text-xs text-slate-600 select-none">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center py-3 px-4 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-sm transition min-h-[44px] cursor-pointer"
                    >
                        Masuk ke Sistem
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Informasi -->
        <div class="text-center text-xs text-slate-400 space-y-1">
            <p>SMK Yapparindo &copy; 2026. Hak Cipta Dilindungi.</p>
            <p class="text-[11px] text-slate-400/80">Zona Waktu: WITA (Asia/Makassar)</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.innerHTML = isPassword
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            });
        }
    });
</script>
@endsection

