# Project Brief — Aplikasi Absensi dan Jurnal SMK

**Status:** DRAFT — BELUM DISETUJUI  
**Tanggal dibuat:** 29 Oktober 2026  
**Bahasa aplikasi:** Bahasa Indonesia

## Tujuan

Menyediakan aplikasi web untuk mengelola absensi individual siswa, jurnal/materi pembelajaran, jadwal dan jam mengajar, agenda harian guru/pegawai, kehadiran guru/pegawai, serta rekap operasional harian yang dapat ditinjau admin dan disiapkan untuk WhatsApp.

## Cakupan yang diputuskan

1. Absensi siswa per siswa, dengan rekap jumlah otomatis.
2. Status siswa: Hadir, Sakit, Izin, Alpa, serta Training dan DW sebagai status khusus terpisah. Training/DW bukan Hadir di kelas dan bukan Alpa.
3. Jurnal mengajar berisi tanggal/sesi, nomor pertemuan, mata pelajaran, kelas, dan materi yang dibahas.
4. Jadwal/jam mengajar untuk menentukan sesi yang diharapkan.
5. Agenda harian guru/pegawai, terpisah dari jurnal mengajar per sesi.
6. Pencatatan kehadiran guru/pegawai.
7. Dashboard admin untuk memantau kelengkapan data dan rekap.
8. Input manual serta impor Excel/CSV.
9. Ekspor laporan PDF/Excel.
10. Laporan WhatsApp yang dibangkitkan dari data aplikasi, dapat dipratinjau dan diedit, kemudian membuka WhatsApp dengan teks terisi. Pengguna sendiri memilih percakapan dan menekan Kirim.

## Teknologi usulan — belum final sampai disetujui

- PHP 8.3+ dan Laravel 13.
- Blade, Livewire, Tailwind CSS.
- Supabase PostgreSQL.
- Autentikasi dikelola Laravel dengan login email atau username.
- Hosting aplikasi belum dipilih.
- Zona waktu default usulan: `Asia/Makassar`, harus dikonfirmasi.

## Pengguna utama

- Administrator sistem.
- Admin/operator sekolah.
- Guru.
- Pegawai sebagai subjek pencatatan kehadiran/agenda; akun login bagi semua pegawai belum dipastikan.
- Siswa sebagai subjek absensi; akun siswa tidak termasuk cakupan awal.

## Kebutuhan konektivitas

Aplikasi perlu tetap dapat digunakan saat sinyal terputus-putus. Rekomendasi awal: PWA/offline-first per perangkat, dengan data kerja yang telah dipersiapkan saat online dan antrean sinkronisasi. Dashboard agregat dan laporan final memerlukan data yang telah tersinkron. Ini bukan sistem multi-perangkat realtime tanpa internet; jika sekolah membutuhkan backend lokal yang dibagikan lewat LAN ketika internet total putus, itu menjadi keputusan arsitektur terpisah.

## Hasil yang diharapkan

- Catatan tidak ganda.
- Data kosong tidak dikira Alpa/tidak hadir.
- Riwayat tahun ajaran dan perpindahan siswa tidak merusak laporan lama.
- Dashboard dan semua format ekspor konsisten.
- Laporan WhatsApp hanya menyertakan data yang valid dan tidak mengklaim terkirim otomatis.
- Akses data dibatasi menurut peran dan penugasan.

## Hal terbuka yang bisa memengaruhi desain

- Cara pencatatan kehadiran guru/pegawai.
- Aturan kewajiban agenda per kategori pegawai.
- Semantik dan administrasi status Training/DW untuk siswa.
- Apakah laporan guru harus diverifikasi admin.
- Struktur jadwal nyata, termasuk jam, sesi pengganti, hari libur, dan pembatalan.
- Template impor yang digunakan sekolah.
- Format resmi PDF/Excel dan audiens laporan WhatsApp.
- Hosting dan kebutuhan backup operasional.
