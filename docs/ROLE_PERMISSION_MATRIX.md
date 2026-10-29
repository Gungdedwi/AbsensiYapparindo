# Matriks Peran dan Hak Akses

**Status:** DRAFT — BELUM DISETUJUI

## 1. Peran yang diusulkan

- **Administrator sistem:** mengelola konfigurasi dan akun teknis.
- **Admin/operator sekolah:** mengelola data master sesuai kewenangan, memantau data, mengoreksi/memeriksa laporan, ekspor, dan membuat pesan laporan.
- **Guru:** membaca penugasan sendiri, mengisi jurnal serta absensi siswa pada sesi yang menjadi kewenangannya, dan agenda bila wajib.
- **Pegawai non-guru:** subjek pencatatan kehadiran/agenda; apakah memiliki akun adalah keputusan terbuka.
- **Siswa:** subjek absensi; tidak ada akun siswa pada cakupan awal.

## 2. Matriks awal

| Objek/tindakan | Administrator sistem | Admin sekolah | Guru | Pegawai tanpa akun |
|---|---|---|---|---|
| Mengelola akun/peran | Ya, minimum yang diperlukan | Terbatas sesuai kebijakan | Tidak | Tidak |
| Membaca seluruh data master | Berdasarkan kebutuhan admin | Ya sesuai ruang lingkup sekolah | Hanya data terkait penugasan | Tidak |
| Mengubah siswa/kelas/periode | Tidak otomatis; sesuai tugas | Ya, jika diizinkan | Tidak | Tidak |
| Membaca roster kelas | Sesuai kebutuhan | Ya | Hanya kelas yang ditugaskan | Tidak |
| Mencatat absensi siswa | Tidak otomatis | Koreksi/operasional sesuai kebijakan | Hanya sesi yang ditugaskan | Tidak |
| Mengisi jurnal/materi | Tidak otomatis | Koreksi jika diberi wewenang | Sesi yang ditugaskan | Tidak |
| Mengisi agenda harian | Jika relevan dan diizinkan | Dapat mencatat/membantu jika ditentukan | Agenda sendiri jika diwajibkan | Metode masih terbuka |
| Mencatat kehadiran pegawai | Konfigurasi/akses terbatas | Usulan: operator berwenang | Hanya diri sendiri bila alur itu dipilih | Tidak ada login |
| Verifikasi laporan | Sesuai role yang disetujui | Jika workflow verifikasi dipilih | Tidak boleh memverifikasi laporan sendiri kecuali kebijakan khusus | Tidak |
| Membuka dashboard laporan | Untuk administrasi teknis sesuai izin | Ya | Ringkasan milik sendiri/penugasan jika disetujui | Tidak |
| Ekspor PDF/Excel/WhatsApp | Sesuai izin | Ya | Terbatas pada data yang diizinkan | Tidak |
| Audit log | Terbatas | Hanya yang relevan | Tidak, kecuali data tindakannya sendiri bila disetujui | Tidak |

## 3. Aturan otorisasi

- Semua operasi terproteksi harus memeriksa izin di server.
- Jangan mempercayai `user_id`, `teacher_id`, role, kelas, status verifikasi, atau filter yang dikirim frontend.
- Periksa bahwa sesi benar-benar termasuk penugasan guru.
- Terapkan otorisasi untuk membaca data, bukan hanya menulis.
- Pembatasan juga berlaku untuk ekspor PDF/Excel dan pesan WhatsApp.
- Perubahan penting harus mencatat pelaku, waktu, objek dan tindakan bila audit disetujui.
- Jangan menampilkan data medis/izin pribadi kepada semua pengguna.
- Admin teknis tidak otomatis membutuhkan akses isi data pribadi siswa.

## 4. Keputusan yang masih diperlukan

1. Siapa membuat dan menonaktifkan akun guru/pegawai?
2. Apakah seorang pengguna boleh memiliki lebih dari satu peran?
3. Apakah admin dapat mengedit data absensi yang telah difinalkan?
4. Apakah guru dapat mengisi agenda sendiri atau admin yang mengisi?
5. Apakah guru boleh melihat rekap seluruh sekolah atau hanya data penugasannya?
6. Apakah admin memverifikasi setiap laporan?
7. Siapa dapat melihat alasan medis/cuti dan detail tertentu?

Jangan implementasikan matriks sebagai final sebelum keputusan ini disetujui.
