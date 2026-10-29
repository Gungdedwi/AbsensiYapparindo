# Decision Log

**Status keseluruhan:** semua keputusan tetap bersifat usulan/draf hingga ditandai disetujui pengguna.

## Ditetapkan oleh pengguna

| ID | Keputusan | Status |
|---|---|---|
| D-001 | Aplikasi dimulai dari proyek kosong | Diputuskan |
| D-002 | Absensi siswa individual dan rekap jumlah otomatis | Diputuskan |
| D-003 | Login memakai email atau username dan kata sandi | Diputuskan sebagai kebutuhan |
| D-004 | Database pilihan awal Supabase PostgreSQL karena biaya | Preferensi diputuskan; validasi teknis/operasional masih diperlukan |
| D-005 | Data master dapat input manual dan impor Excel/CSV | Diputuskan |
| D-006 | Laporan tersedia di dashboard dan dapat diekspor PDF/Excel | Diputuskan |
| D-007 | Training dan DW adalah status khusus siswa, terpisah dari Hadir dan Alpa | Diputuskan |
| D-008 | Cakupan mencakup absensi siswa, jurnal/materi, agenda/jadwal mengajar, dan kehadiran guru/pegawai terintegrasi | Diputuskan |
| D-009 | WhatsApp: bentuk dan pratinjau pesan, buka WhatsApp dengan teks terisi, pengguna memilih percakapan dan menekan Kirim | Diputuskan |
| D-010 | Bagian I menampilkan orang yang hadir tetapi belum mengisi agenda; bila data lengkap dan semuanya mengisi, tampilkan “Semua Membuat Agenda” | Diputuskan |
| D-011 | Render Free Web Service + Docker dipilih sebagai target prototipe/testing; hosting produksi belum diputuskan | Diputuskan untuk prototipe, belum ada izin membuat service/deploy |
| D-012 | Stack usulan: Laravel 13/PHP 8.3+, Blade/Livewire/Tailwind, Supabase PostgreSQL | Usulan, belum disetujui sebagai arsitektur final |
| D-020 | Fokus akun saat ini hanya untuk Guru dan Admin; akun pegawai non-guru ditunda untuk kebutuhan masa depan | Diputuskan |
| D-021 | Laporan absensi & jurnal yang dikirim guru langsung berstatus final submitted (tidak memerlukan verifikasi/approval bertingkat oleh admin) | Diputuskan |
| D-022 | Status siswa Training dan DW ditandai secara manual oleh guru pengajar pada saat sesi kelas | Diputuskan |
| D-023 | Rentang penyimpanan data jadwal & roster lokal di IndexedDB adalah 1 minggu ke depan | Diputuskan |
| D-024 | Kebijakan logout saat antrean offline pending: Logout diizinkan, data antrean lokal dikunci dan diisolasi per akun agar tidak bocor ke akun lain | Diputuskan |
| D-025 | Resolusi konflik sinkronisasi: Data server dipertahankan (*server-wins*), akun guru mendapatkan notifikasi bahwa data telah diubah/dibuat oleh admin | Diputuskan |
| D-026 | Zona waktu resmi sekolah: WITA (`Asia/Makassar`) | Diputuskan |
| D-027 | Mode koneksi ke Supabase: Session Pooler (IPv4) untuk kompatibilitas penuh dengan Render dan jaringan lokal | Diputuskan |

## Rekomendasi desain/arsitektur

- Autentikasi Laravel pada versi awal; tidak perlu Supabase Auth kecuali dianalisis dan disetujui.
- Pertimbangkan schema PostgreSQL khusus Laravel agar tabel internal tidak sengaja diekspos melalui Supabase Data API.
- Zona waktu resmi dikonfirmasi `Asia/Makassar` (WITA).
- Pisahkan agenda harian dari jurnal sesi mengajar.
- Laporan absensi & jurnal bersifat langsung final submitted oleh guru (tidak perlu verifikasi bertingkat).
- Jangan anggap data kosong berarti Alpa atau tidak hadir.
- Gunakan UI/UX skill sekolah dan evaluasi `arham777/ui-ux-kit` untuk desain khusus.

## Keputusan terbuka

- Struktur jadwal dan aturan sesi khusus/pengganti/libur.
- Format resmi cetak PDF/Excel sekolah (kop surat, tanda tangan).
- Template format impor data master (kolom Excel/CSV siswa & guru).
- Hosting produksi dan strategi backup jangka panjang setelah masa prototipe Render.

## Tambahan keputusan deployment

| ID | Keputusan | Status |
|---|---|---|
| D-013 | Repository kode: `Gungdedwi/AbsensiYapparindo`, branch `main` | Diputuskan sebagai target repo; tetap verifikasi status saat bekerja |
| D-014 | Render Free memakai Docker untuk Laravel PHP dan hanya untuk prototipe | Diputuskan sebagai target awal |
| D-015 | Deployment, koneksi akun GitHub/Render, pengisian secret dan migrasi memerlukan persetujuan eksplisit | Wajib |


## Tambahan — konektivitas

| ID | Keputusan | Status |
|---|---|---|
| D-016 | Area sekolah memiliki sinyal internet lemah/intermiten; aplikasi perlu tetap dapat mencatat alur utama offline | Kebutuhan pengguna diputuskan |
| D-017 | Pendekatan PWA + IndexedDB + sync idempotent | Diputuskan |
| D-018 | Fase pertama: fokus alur inti sekolah untuk Guru & Admin, offline untuk absensi siswa dan jurnal/materi | Diputuskan |
| D-019 | Dashboard agregat dan laporan WhatsApp final online saja sampai data terkait tersinkron | Diputuskan |
