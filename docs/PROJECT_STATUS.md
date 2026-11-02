# Project Status

**Status:** Fase 3 (Modul Inti & Offline PWA: 3A, 3B, 3C) SELESAI — Pengujian in-memory 38 tes (100% lolos)  
**Tanggal:** 2 November 2026

## Yang tersedia

- `PROMPT.md`: prompt utama proyek.
- `AGENTS.md`: aturan kerja agent dan gerbang persetujuan.
- Skill spesialis lengkap pada `.agents/skills/`.
- Paket dokumen perencanaan lengkap di `docs/`.
- Kerangka fondasi Laravel 13, Tailwind CSS v4 (asset build terverifikasi), Dockerfile + .dockerignore Render.
- File migration fondasi dan seeder data sintetis di `database/`.

## Dokumen yang disusun dalam paket ini

- `PROJECT_BRIEF.md`
- `SRS.md` (diperbarui sesuai keputusan fokus guru, offline 1 minggu, final submitted)
- `WORKFLOWS.md` (diperbarui alur final submitted dan penanganan konflik server-wins)
- `DATABASE_DESIGN.md` (diperbarui status sesi/jurnal, metadata offline, Supabase IPv4 Pooler)
- `ROLE_PERMISSION_MATRIX.md` (diperbarui fokus peran Guru dan Admin)
- `WHATSAPP_REPORT_SPEC.md`
- `IMPORT_EXPORT_SPEC.md`
- `UI_DESIGN_SYSTEM.md`
- `DESIGN_REFERENCE_SKILLS.md`
- `SECURITY_THREAT_MODEL.md`
- `TEST_PLAN.md`
- `DEPLOYMENT_OPTIONS.md`
- `RENDER_DEPLOYMENT.md`
- `OFFLINE_MODE_SPEC.md` (diperbarui keputusan retensi 1 minggu, isolasi logout, notifikasi konflik)
- `DECISIONS.md` (mencatat D-001 s/d D-027)

## Batasan pekerjaan saat ini

- Tidak ada source code aplikasi dibuat.
- Tidak ada dependency dipasang.
- Tidak ada database/schema Supabase dibuat atau diubah.
- Tidak ada data sekolah yang diimpor.
- Tidak ada deployment dilakukan.

## Keputusan Pengguna Terkini (Fase 1)

1. **Pengguna/Akun:** Fokus saat ini khusus untuk Guru dan Admin sekolah.
2. **Alur Laporan Guru:** Bersifat langsung *final submitted* (tanpa verifikasi/approval bertingkat admin).
3. **Status Siswa Training & DW:** Ditandai secara manual oleh guru saat sesi kelas.
4. **Penyimpanan Offline:** Data jadwal & roster lokal disimpan untuk rentang 1 minggu ke depan. Saat logout, data antrean pending tetap disimpan dan diisolasi per akun agar tidak bocor.
5. **Konflik Sinkronisasi:** Data server dipertahankan (*server-wins*), dan guru mendapat notifikasi bahwa data telah diubah/dibuat oleh admin.
6. **Konektivitas & Waktu:** Zona waktu WITA (`Asia/Makassar`), koneksi Supabase via Session Pooler (IPv4).
7. **Perangkat Guru:** Absensi dan jurnal dilakukan sepenuhnya di ponsel pribadi. Mayoritas Android, sebagian iPhone (iOS Safari). Tidak ada laptop dalam alur guru.

## Verifikasi Versi Resmi dan Keamanan (Fase 2)

Catatan verifikasi versi per **29 Oktober 2026**:

1. **PHP Runtime di Dockerfile:**
   - **Tag spesifik yang dipakai:** `php:8.3.36-fpm-alpine3.24` (bukan tag mengambang `php:8.3-fpm-alpine`).
   - **Pemeriksaan Docker Hub:** Diverifikasi langsung pada Docker Hub Official Library PHP (`hub.docker.com/_/php`) bahwa tag `8.3.36-fpm-alpine3.24` dan `8.3.36-fpm-alpine` benar-benar ada dan tersedia.
   - **Verifikasi Rilis Keamanan:** Diverifikasi ke [php.net/archive/2026.php](https://www.php.net/archive/2026.php) dan [php.net/supported-versions.php](https://www.php.net/supported-versions.php) bahwa patch PHP 8.3.36 dirilis resmi pada 21 Oktober 2026 dan memuat perbaikan untuk dua kerentanan kritis Remote Code Execution (RCE): CVE-2026-109618 (RCE via OOM corruption) dan CVE-2026-109619 (RCE via stale global state).
   - **Tanggal verifikasi:** 29 Oktober 2026.
   - **Status dukungan upstream:** PHP 8.3 aktif menerima security fixes hingga 31 Desember 2027.

2. **Laravel Framework:**
   - **Versi yang dipakai:** `^13.0` di `composer.json`.
   - **Sumber resmi:** Dokumentasi resmi Laravel Release Support Policy ([laravel.com/docs/releases](https://laravel.com/docs/releases)).
   - **Tanggal verifikasi:** 29 Oktober 2026.
   - **Status dukungan:** Siklus rilis tahunan Laravel dengan dukungan 18 bulan bug fixes dan 2 tahun security fixes.

3. **Tailwind CSS:**
   - **Versi dipatok:** `tailwindcss: 4.3.3` dan `@tailwindcss/vite: 4.3.3` di `package.json` (dipatok persis sesuai versi yang terpasang di `package-lock.json`, bukan rentang `^4.0.0`).
   - **Sumber resmi:** [tailwindcss.com/blog](https://tailwindcss.com/blog) & NPM registry (`npmjs.com/package/tailwindcss`).
   - **Tanggal verifikasi:** 29 Oktober 2026.
   - **Status build:** Berhasil di-compile menggunakan Vite v6 tanpa error (`public/build/`).

## Pertanyaan terbuka

- Proporsi guru Android dan iPhone, untuk menentukan prioritas pengujian.
- Apakah guru bersedia memasang aplikasi ke layar utama ponsel (PWA).
- Verifikasi perilaku PWA, penyimpanan offline, dan sinkronisasi di iOS Safari sebelum keputusan offline difinalkan (lihat `UI_DESIGN_SYSTEM.md` bagian 8).

## Pemetaan Penomoran PROMPT.md (Tahap 0–5) vs Milestone Kerja (Fase 1–5)

Agar tidak terjadi salah paham terkait batas kerja teknis:

| Tahap di PROMPT.md | Lingkup Kerja di PROMPT.md | Ekuivalen Milestone Kerja | Batasan & Status |
|---|---|---|---|
| **Tahap 0 — Inspeksi** | Cek Git, struktur repo, 11 skill. | **Fase 1 (Bagian A)** | **SELESAI** |
| **Tahap 1 — Analisis & Klarifikasi** | Identifikasi kebutuhan, 6 pertanyaan terbuka. | **Fase 1 (Bagian B)** | **SELESAI** |
| **Tahap 2 — Dokumen Perencanaan Saja** | Menyusun & memperbarui 16 dokumen draf di `docs/`. | **Fase 1 (Bagian C)** | **SELESAI** |
| **Tahap 3 — Review & Persetujuan Eksplisit** | Presentasi keputusan, arsitektur, approval gate. | **Fase 1 (Bagian D / Akhir)** | **SELESAI** (Menunggu persetujuan Fase 2) |
| **Tahap 4 — Implementasi Sub 1–3 & 13** | Kerangka Laravel, auth foundation, migration Supabase, Dockerfile. | **Fase 2 — Setup Fondasi & Arsitektur** | **SELESAI** |
| **Tahap 4 — Implementasi Sub 2 (Auth)** | Autentikasi email/username, role middleware (teacher vs admin), rate limiting, mobile/desktop UI. | **Fase 3A — Autentikasi & Ruang Kerja** | **SELESAI** (24 tes in-memory lolos) |
| **Tahap 4 — Implementasi Sub 4, 5, 7, 8** | Data master, jadwal penugasan, absensi siswa individual (H/S/I/A/T/DW), jurnal materi, rekap otomatis. | **Fase 3B — Absensi Siswa & Jurnal Kelas** | **SELESAI** (32 tes in-memory lolos) |
| **Tahap 4 — Implementasi Sub 9** | Offline PWA, service worker, IndexedDB 1 minggu, idempotent sync API, resolusi konflik server-wins. | **Fase 3C — PWA & Sinkronisasi Offline** | **SELESAI** (38 tes in-memory lolos) |
| **Tahap 4 — Implementasi Sub 10–11** | Dashboard admin agregat, ekspor PDF/Excel, pembentuk pesan WhatsApp. | **Fase 4 — Pelaporan & WhatsApp** | Langkah Berikutnya |
| **Tahap 4 — Sub 12, 14 & Tahap 5** | QA/Testing menyeluruh, rilis/deployment prototipe Render. | **Fase 5 — Testing & Deployment Prototipe** | Masa Datang (Butuh izin deploy khusus) |

### Batas Ketat Pekerjaan Fase 3C:
- **Termasuk di Fase 3C:** Web App Manifest (`manifest.json`), Service Worker (`sw.js`) dengan cache app shell, IndexedDB storage adapter (`offline-sync.js`) untuk paket jadwal/roster 1 minggu ke depan, isolasi akun lokal saat logout (D-024), endpoint server `POST /guru/api/sync` dengan idempotensi mutlak dan resolusi konflik server-wins (D-025), serta notifikasi sistem otomatis saat terjadi konflik.
- **TIDAK Termasuk di Fase 3C:** Generator pesan WhatsApp & ekspor PDF/Excel (Fase 4), dan migrasi cloud ke Supabase (menunggu persetujuan terpisah).

## Langkah berikutnya

1. Pengguna meninjau hasil implementasi dan pengujian Fase 3C.
2. Pengguna memberikan arahan untuk melakukan komit/push atau melangkah ke **Fase 4: Pelaporan & WhatsApp (Dashboard Agregat, Ekspor PDF/Excel, Pratinjau WhatsApp)**.
