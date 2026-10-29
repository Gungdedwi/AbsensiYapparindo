# Software Requirements Specification (SRS)

**Status:** DRAFT — BELUM DISETUJUI  
**Versi:** 0.1  
**Bahasa:** Bahasa Indonesia

## 1. Tujuan

Menjelaskan kebutuhan fungsional dan nonfungsional untuk aplikasi web absensi dan jurnal pembelajaran SMK. Ini merupakan draf perencanaan; belum menjadi izin implementasi.

## 2. Ruang lingkup

Sistem meliputi data master, autentikasi, hak akses, absensi siswa, jurnal/materi, jadwal pelajaran, agenda harian, kehadiran guru/pegawai, dashboard, impor, ekspor, dan penyusun laporan WhatsApp.

## 3. Kebutuhan fungsional

| ID | Kebutuhan | Prioritas awal | Catatan |
|---|---|---|---|
| FR-001 | Pengguna login dengan email atau username dan kata sandi | Must | Autentikasi lokal Laravel usulan |
| FR-002 | Admin mengelola akun dan peran sesuai izin | Must | Alur pembuatan akun belum diputuskan |
| FR-003 | Sistem mengelola guru/pegawai, siswa, kelas, mata pelajaran, tahun ajaran, semester, jadwal, dan penugasan | Must | Field wajib menunggu desain data |
| FR-004 | Admin dapat memasukkan data secara manual | Must | Validasi duplikasi |
| FR-005 | Admin dapat mengimpor Excel/CSV dengan pratinjau dan laporan error per baris | Must | Template sumber perlu disepakati |
| FR-006 | Guru melihat hanya penugasan yang berhak diakses | Must | Otorisasi server-side |
| FR-007 | Guru mencatat absensi setiap siswa untuk satu sesi pembelajaran | Must | Tidak boleh ada duplikasi siswa-sesi |
| FR-008 | Sistem menghitung rekap absensi per status secara otomatis | Must | Detail absensi adalah sumber kebenaran |
| FR-009 | Status Training dan DW dibedakan dari Hadir dan Alpa | Must | Aturan tanggal/penempatan harus diklarifikasi |
| FR-010 | Sistem membedakan belum dicatat dari tidak hadir | Must | Tidak ada inferensi dari data kosong |
| FR-011 | Guru mencatat nomor pertemuan dan materi yang dibahas | Must | Terhubung dengan sesi/jadwal |
| FR-012 | Sistem memantau sesi yang dijadwalkan tetapi jurnalnya belum lengkap | Must | Memerlukan jadwal harian dan pengecualian |
| FR-013 | Guru/pegawai dapat memiliki agenda harian terpisah dari jurnal mengajar | Must | Kewajiban agenda harus didefinisikan |
| FR-014 | Sistem mencatat kehadiran guru/pegawai dan kategori/status | Must | Metode input masih terbuka |
| FR-015 | Admin melihat dashboard dan filter laporan | Must | Hak akses diterapkan di server |
| FR-016 | Admin mengekspor PDF dan Excel | Must | Isi sesuai filter dan sumber yang sama |
| FR-017 | Sistem menghasilkan pesan WhatsApp bagian I/II/III | Must | Mengikuti dokumen `WHATSAPP_REPORT_SPEC.md` |
| FR-018 | Pesan WhatsApp dapat dipratinjau dan diedit sebelum membuka WhatsApp | Must | Human-in-the-loop; aplikasi tidak mengirim otomatis |
| FR-019 | Sistem menampilkan peringatan bila data yang diperlukan untuk laporan belum lengkap | Must | Tidak membuat klaim final otomatis |
| FR-020 | Perubahan penting dapat diaudit | Should | Retensi dan field audit perlu diputuskan |
| FR-021 | Sistem menyediakan filter tanggal, tahun ajaran, semester, kelas, guru, mata pelajaran, kategori pegawai, dan status | Must | Filter sesuai akses |
| FR-022 | Sistem dapat membedakan draf, dikirim, perlu perbaikan, diverifikasi bila workflow persetujuan dipilih | Should | Workflow belum final |
| FR-023 | Guru dapat menyiapkan jadwal/roster yang diizinkan saat online dan mencatat absensi/jurnal tertentu saat offline | Must for offline phase 1 | Cakupan final menunggu persetujuan; lihat `OFFLINE_MODE_SPEC.md` |
| FR-024 | Perubahan offline disimpan lokal dengan status yang jelas dan antrean sinkronisasi idempotent | Must for offline phase 1 | Tidak boleh diklaim sebagai data server sebelum acknowledgement |
| FR-025 | Saat online kembali, sistem menyinkronkan data, melaporkan konflik/error per item, dan tidak membuat duplikat | Must for offline phase 1 | Otorisasi divalidasi ulang di server |
| FR-026 | Dashboard/laporan memberi peringatan jika data yang diharapkan belum tersinkron | Must | Jangan klaim laporan lengkap saat ada sync tertunda yang relevan |

## 4. Kebutuhan nonfungsional

- **NFR-001 Keamanan:** autentikasi dan otorisasi server-side pada semua operasi terproteksi.
- **NFR-002 Privasi:** hanya kumpulkan/tampilkan data pribadi yang diperlukan.
- **NFR-003 Integritas:** data absensi harus konsisten, tidak ganda, dan rekap dapat ditelusuri.
- **NFR-004 Responsif:** nyaman digunakan pada ponsel, tablet, dan laptop.
- **NFR-005 Aksesibilitas:** form, tabel, fokus keyboard, kontras, dan label harus dapat digunakan.
- **NFR-006 Keandalan:** proses penyimpanan multi-record menggunakan transaksi bila diperlukan.
- **NFR-007 Audit:** perubahan penting dapat dilacak bila disetujui.
- **NFR-008 Pemulihan:** dokumentasi backup/restore harus tersedia sebelum penggunaan operasional.
- **NFR-009 Portabilitas:** hosting dan konfigurasi tidak boleh bergantung pada layanan yang belum disetujui.
- **NFR-010 Operabilitas:** setup lokal, tes, konfigurasi, health checks, dan prosedur rilis didokumentasikan.

## 5. Aturan bisnis utama

1. Setiap record absensi siswa terkait satu siswa dan satu sesi terjadwal.
2. Seorang siswa tidak boleh memiliki dua record absensi untuk sesi yang sama.
3. Nilai yang tidak diisi bukan otomatis Alpa.
4. Training/DW status khusus di luar hadir di kelas dan bukan Alpa.
5. Agenda harian bukan hal yang sama dengan jurnal pada jam pelajaran.
6. Tidak adanya data kehadiran guru tidak otomatis berarti tidak hadir.
7. Data jadwal dipakai untuk mengetahui sesi yang diharapkan; tanpa jadwal/penugasan yang memadai, sistem tidak boleh menyimpulkan kelalaian.
8. Laporan WhatsApp dihasilkan dari data yang disaring dan diizinkan, dapat dipratinjau, dan tidak dianggap terkirim sampai pengguna mengirimnya sendiri.
9. Dashboard, PDF, Excel, dan WhatsApp harus menggunakan definisi dan filter metrik yang konsisten.
10. Informasi medis/alasan pribadi dibatasi pada yang diperlukan untuk audiens laporan.

## 6. Acceptance criteria tingkat sistem

- Guru tidak dapat mengakses kelas/penugasan guru lain melalui manipulasi URL/API.
- Rekap status sama dengan hitungan records yang sah.
- Training/DW tidak menaikkan jumlah Hadir atau Alpa.
- Agenda harian dan jurnal mengajar dapat diperiksa secara terpisah.
- Status tidak diketahui/record kosong tidak menyebabkan laporan keliru.
- Data yang sama menghasilkan rekap konsisten pada dashboard, PDF, Excel, dan pesan WhatsApp.
- Import melaporkan jumlah sukses, dilewati, duplikat, dan gagal.
- Tautan WhatsApp hanya mengisi teks; aplikasi tidak menyatakan pesan sudah terkirim.
- Tes kritis dan review keamanan dilaksanakan sebelum rilis.

## 7. Hal yang membutuhkan keputusan

- Apakah admin perlu memverifikasi laporan guru atau guru langsung menyelesaikan pengiriman?
- Bagaimana agenda wajib ditentukan per kategori pegawai?
- Siapa dan bagaimana mencatat kehadiran pegawai?
- Bagaimana menentukan siswa yang memenuhi syarat status Training/DW, dan untuk rentang tanggal apa?
- Bagaimana jadwal mingguan berubah menjadi sesi aktual pada tanggal tertentu?
- Data apa yang wajib ditampilkan pada laporan harian dan siapa penerimanya?
- Template impor serta format resmi laporan sekolah.
- Hosting, backup, dan retensi arsip.

Semua hal di atas harus dituntaskan atau diberi asumsi eksplisit yang disetujui sebelum implementasi bagian terkait.
