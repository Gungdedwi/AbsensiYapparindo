# Matriks Peran dan Hak Akses

**Status:** DRAFT — BELUM DISETUJUI

## 1. Peran yang diusulkan

- **Administrator sistem:** mengelola konfigurasi teknis dan akun.
- **Admin/operator sekolah:** mengelola data master, memantau dashboard sekolah, koreksi operasional data final (tercatat audit), ekspor laporan, dan menyusun pesan WhatsApp.
- **Guru:** masuk dengan akun pribadi, membaca penugasan mengajar sendiri, mencatat absensi siswa (termasuk status manual Training/DW) dan materi jurnal, mengirim laporan (*final submitted*), serta mengisi agenda harian sendiri.
- **Pegawai non-guru:** belum memiliki akun login pada fase ini (fokus guru dan admin).
- **Siswa:** subjek absensi; tidak ada akun siswa.

## 2. Matriks peran

| Objek / Tindakan | Administrator Sistem | Admin Sekolah | Guru |
|---|---|---|---|
| Mengelola akun Guru & Admin | Ya | Sesuai kebijakan sekolah | Tidak |
| Membaca seluruh data master | Sesuai kebutuhan sistem | Ya | Hanya kelas & mapel penugasan |
| Mengubah siswa/kelas/periode | Tidak otomatis | Ya | Tidak |
| Membaca roster kelas | Ya | Ya | Hanya kelas yang ditugaskan |
| Mencatat & finalisasi absensi siswa | Tidak | Koreksi jika diperlukan (diaudit) | Ya, sesi yang ditugaskan (*final submitted*) |
| Mengisi jurnal/materi pembelajaran | Tidak | Koreksi jika diperlukan (diaudit) | Ya, sesi yang ditugaskan (*final submitted*) |
| Mengisi agenda harian | Tidak | Bantuan operasional bila perlu | Ya, agenda pribadi |
| Membuka dashboard laporan | Administrasi teknis | Ya, seluruh sekolah | Ringkasan penugasan sendiri |
| Ekspor PDF / Excel / WhatsApp | Sesuai izin | Ya | Sesuai penugasan sendiri bila diizinkan |
| Audit log & Riwayat perubahan | Ya | Ya | Notifikasi jika datanya dikoreksi admin |

## 3. Aturan otorisasi

- Semua operasi terproteksi harus memeriksa izin di server.
- Jangan mempercayai `user_id`, `teacher_id`, role, kelas, status, atau filter yang dikirim frontend.
- Periksa bahwa sesi benar-benar termasuk penugasan guru yang sedang login.
- Laporan guru berstatus langsung *final submitted* saat disimpan/dikirim.
- Jika admin melakukan koreksi data terhadap laporan yang telah difinalkan, sistem mencatat audit log dan memberikan notifikasi ke akun guru bersangkutan.
- Terapkan otorisasi untuk membaca data, bukan hanya menulis.
- Pembatasan juga berlaku untuk ekspor PDF/Excel dan pesan WhatsApp.
- Jangan menampilkan data medis/izin pribadi kepada pengguna yang tidak berwenang.

## 4. Status keputusan hak akses (Fase 1)

- Akun difokuskan pada Guru dan Admin; non-guru ditunda.
- Guru mengelola dan mengirim absensi serta jurnal langsung berstatus *final submitted*.
- Guru mengisi agenda harian sendiri.
- Admin dapat mengoreksi data yang telah difinalkan, dan sistem otomatis memberi notifikasi ke akun guru terkait.
- Guru melihat rekapitulasi data penugasannya sendiri; admin melihat data agregat seluruh sekolah.
