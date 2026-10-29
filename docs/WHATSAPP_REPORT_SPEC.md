# Spesifikasi Laporan WhatsApp Harian

**Status:** DRAFT — BELUM DISETUJUI

## 1. Mode pengiriman

Versi awal memakai **pratinjau + buka WhatsApp + kirim manual**.

1. Admin memilih tanggal.
2. Backend mengambil data sesuai hak akses dan membentuk draf.
3. UI menampilkan pratinjau lengkap dengan indikator data belum lengkap.
4. Admin dapat mengedit teks draf jika diizinkan.
5. “Salin pesan” menyalin teks.
6. “Buka WhatsApp” membuka WhatsApp/Web dengan pesan yang telah di-URL-encode.
7. Admin memilih grup/penerima dan menekan Kirim sendiri.
8. Aplikasi tidak menyatakan pesan terkirim karena tautan hanya membuka komposer pesan.

Pengiriman otomatis, WhatsApp Business API, OTP, password WhatsApp, atau otomasi browser tidak termasuk versi awal.

## 2. Kerangka pesan

Gunakan tanggal laporan dan salam berdasarkan waktu lokal yang telah disetujui. Jangan hard-code tanggal contoh atau nama individu dari contoh lama.

```text
Selamat [PAGI/SIANG/SORE/MALAM] Bapak/Ibu Guru dan Pegawai 🙏

I. LAPORAN AGENDA GURU DAN PEGAWAI YANG HADIR BEKERJA
HARI [NAMA HARI], [TANGGAL]

GURU KONTRAK
[nama yang hadir tetapi belum mengisi agenda; atau "Semua Membuat Agenda"]

GURU HONOR
[nama yang hadir tetapi belum mengisi agenda; atau "Semua Membuat Agenda"]

PEGAWAI
[nama yang hadir tetapi belum mengisi agenda; atau "Semua Membuat Agenda"]

PEGAWAI TRAINING
[nama yang hadir tetapi belum mengisi agenda; atau "Semua Membuat Agenda"]

II. GURU YANG TIDAK MENGISI JURNAL JAM MENGAJAR / JAM MENGAJAR DENGAN KONDISI KHUSUS
HARI [NAMA HARI], [TANGGAL]

KELAS X
[entri terverifikasi]

KELAS XI
[entri terverifikasi]

KELAS XII
[entri terverifikasi]

III. TIDAK HADIR
GURU DAN PEGAWAI YANG TIDAK MASUK HARI [NAMA HARI], [TANGGAL]

GURU KONTRAK
[nama dan keterangan terkonfirmasi atau "Tidak ada"]

GURU HONOR
[nama dan keterangan terkonfirmasi atau "Tidak ada"]

PEGAWAI
[nama dan keterangan terkonfirmasi atau "Tidak ada"]

PEGAWAI TRAINING
[nama dan keterangan terkonfirmasi atau "Tidak ada"]
```

## 3. Bagian I — hadir tetapi belum mengisi agenda

Logika:
- Pilih orang yang kehadirannya tercatat **Hadir**.
- Pastikan orang tersebut wajib mengisi agenda pada tanggal terpilih.
- Bandingkan record kehadiran dan agenda harian pada tanggal yang sama.
- Jika ada orang yang hadir, wajib mengisi, tetapi agendanya kosong, tampilkan namanya di kategori kepegawaian yang benar.
- Jika data kehadiran dan kewajiban agenda lengkap dan semua orang yang hadir sudah mengisi, tampilkan “Semua Membuat Agenda”.
- Jika kategori tidak memiliki anggota atau datanya belum lengkap, tampilkan “Tidak ada data” atau “Data belum lengkap”; jangan menganggap semua sudah membuat agenda.
- Orang yang tidak hadir tidak boleh masuk daftar orang yang hadir tetapi belum mengisi agenda.

## 4. Bagian II — jam mengajar/jurnal yang perlu dilaporkan

Sumber utama adalah sesi aktual yang dihasilkan dari jadwal dan pengecualian tanggal serta jurnal/status sesi. Setiap entri perlu mencakup bila relevan:
- tingkat dan kelas;
- rentang jam pelajaran;
- PAGI/SIANG jika sekolah memakai penanda itu;
- mata pelajaran;
- guru;
- alasan atau status khusus yang terverifikasi;
- catatan bahwa tugas diberikan jika memang benar.

Kelompokkan KELAS X, XI, XII; kemudian kelas dan rentang jam secara urut. Jangan menduplikasi sesi.

Kondisi harus dibedakan:
- jurnal belum diisi;
- guru tidak hadir;
- guru bertugas di luar sekolah;
- guru memberi tugas;
- guru hadir tetapi tidak ada siswa;
- sesi dibatalkan atau diganti;
- status belum dikonfirmasi.

Daftar kondisi hanyalah usulan. Istilah dan dampaknya perlu disetujui sekolah.

## 5. Bagian III — tidak hadir

- Ambil hanya status tidak hadir yang telah dicatat dan dikonfirmasi.
- Kelompokkan berdasarkan kategori kepegawaian.
- Data yang belum masuk bukan bukti tidak hadir.
- Jika data kategori lengkap dan tidak ada orang yang tidak hadir, tampilkan “Tidak ada”.
- Jika data belum lengkap, tampilkan peringatan pada pratinjau dan cegah admin mengira laporan telah lengkap.
- Alasan pribadi/medis harus diminimalkan dan hanya tampil bila relevan serta diizinkan.

## 6. Definisi kategori yang tidak boleh tercampur

- `employment_category`: Guru Kontrak, Guru Honor, Pegawai, Pegawai Training, dan kategori pegawai lain yang disetujui.
- Status absensi siswa `TRAINING` dan `DAILY_WORKER` adalah domain berbeda. Jangan menganggap pegawai training sama dengan siswa berstatus Training.
- Status hadir/tidak hadir pegawai berbeda dengan status absensi siswa.
- Agenda harian berbeda dari jurnal jam mengajar.

## 7. Validasi dan teknis

- Semua bagian harus memakai tanggal yang sama.
- Sumber data dan filter sama dengan dashboard.
- Gunakan zona waktu sekolah yang dikonfirmasi; usulan awal `Asia/Makassar`.
- Newline dan URL encoding harus diuji.
- Bila URL menjadi terlalu panjang, sediakan tombol salin teks dan unduh `.txt`; jangan memotong tanpa pemberitahuan.
- Tampilkan peringatan jika data belum lengkap.
- Simpan “draft dibuat” secara terpisah dari “pesan dikirim” bila penyimpanan histori laporan dipilih.
- Jangan klaim pesan terkirim otomatis.

## 8. Uji penerimaan minimum

- Bagian I hanya menampilkan orang hadir tetapi belum mengisi agenda.
- “Semua Membuat Agenda” tampil hanya saat data lengkap mendukungnya.
- Bagian II mengambil sesi yang benar dan tidak menduplikasi entri.
- Bagian III tidak mengubah data kosong menjadi tidak hadir.
- Teks sesuai filter dan tanggal pilihan.
- Teks panjang dan karakter khusus dapat disalin atau dibuka dengan aman.
- Ekspor PDF/Excel dan WhatsApp mempunyai angka yang sama untuk filter yang sama.
