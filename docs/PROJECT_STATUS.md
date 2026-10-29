# Project Status

**Status:** Fase 1 (Perencanaan & Penyelarasan Desain) selesai — menunggu konfirmasi sebelum melangkah ke Fase 2  
**Tanggal:** 29 Oktober 2026

## Yang tersedia

- `PROMPT.md`: prompt utama proyek.
- `AGENTS.md`: aturan kerja agent dan gerbang persetujuan.
- Skill spesialis lengkap pada `.agents/skills/`.
- Paket dokumen perencanaan dalam `docs/` yang telah diselaraskan dengan keputusan pengguna.

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
| **Tahap 4 — Implementasi Sub 1–3 & 13** | Kerangka Laravel, auth foundation, migration Supabase, Dockerfile. | **Fase 2 — Setup Fondasi & Arsitektur** | **BELUM DIMULAI** (Target berikutnya) |
| **Tahap 4 — Implementasi Sub 4–9** | Data master, jadwal, absensi siswa, jurnal, PWA offline Fase 1. | **Fase 3 — Modul Inti & Offline PWA** | Masa Datang |
| **Tahap 4 — Implementasi Sub 10–11** | Dashboard admin, ekspor PDF/Excel, laporan WhatsApp. | **Fase 4 — Pelaporan & WhatsApp** | Masa Datang |
| **Tahap 4 — Sub 12, 14 & Tahap 5** | QA/Testing menyeluruh, rilis/deployment prototipe Render. | **Fase 5 — Testing & Deployment Prototipe** | Masa Datang (Butuh izin deploy khusus) |

### Batas Ketat Pekerjaan Fase 2 (Tahap 4 Sub 1–3 & 13):
- **Termasuk di Fase 2:** Inisialisasi kerangka Laravel 13, konfigurasi Tailwind CSS, file `Dockerfile` + `.dockerignore`, koneksi Supabase Session Pooler (IPv4), file migration tabel fondasi, eksekusi migration terkontrol, dan seeder data sintetis (dummy).
- **TIDAK Termasuk di Fase 2:** Form absensi/jurnal (Fase 3), Service Worker PWA/IndexedDB offline (Fase 3), ekspor PDF/Excel/WhatsApp (Fase 4), deployment live ke cloud Render (Fase 5), serta data riil sekolah.

## Langkah berikutnya

1. Pengguna meninjau pemetaan di atas dan memastikan batas kerja Fase 2 telah disepakati.
2. Pengguna memberikan persetujuan eksplisit untuk melangkah ke Fase 2 (Setup fondasi teknis).
