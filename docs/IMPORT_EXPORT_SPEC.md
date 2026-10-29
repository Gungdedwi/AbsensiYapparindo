# Spesifikasi Impor dan Ekspor

**Status:** DRAFT — BELUM DISETUJUI

## 1. Impor Excel/CSV

Input data manual dan impor Excel/CSV termasuk cakupan. Tabel/jenis data yang akan didukung pertama-tama perlu disepakati; calon awal adalah:
- guru dan pegawai;
- siswa;
- kelas dan keanggotaan kelas;
- mata pelajaran;
- jadwal;
- penugasan guru.

### Alur yang disarankan
1. Admin memilih jenis data dan template yang sesuai.
2. Sistem menampilkan kolom yang diharapkan dan pemetaan kolom file.
3. Sistem memvalidasi tipe, kolom wajib, duplikat, foreign key, nilai enum, dan relasi.
4. Sistem menampilkan preview dan jumlah baris valid/invalid/duplikat.
5. Admin mengonfirmasi.
6. Sistem menyimpan dalam transaksi/batch yang aman sesuai ukuran.
7. Sistem menyajikan hasil akhir dan daftar error per baris.
8. Sistem mencatat siapa yang menjalankan impor dan kapan, sesuai kebijakan audit.

### Keamanan
- Batasi ukuran file, ekstensi, MIME/type dan isi sesuai kemampuan library.
- Perlakukan nama file dan seluruh isi sebagai data tidak tepercaya.
- Jangan mengeksekusi macro/formula atau formula spreadsheet.
- Jangan menaruh file impor sensitif di lokasi publik.
- Hindari partial import tanpa ringkasan dan mekanisme pemulihan yang jelas.
- Gunakan data sintetis untuk tes.
- Perlu keputusan apakah import menggunakan mode “tambah saja”, “update jika ada”, atau kedua mode dengan preview.

## 2. Ekspor PDF dan Excel

- Filter berdasarkan tanggal/rentang, tahun ajaran, semester, kelas, guru, mata pelajaran, kategori pegawai dan status sesuai izin.
- PDF menampilkan judul, periode, filter, waktu pembuatan, tabel yang terbaca, header berulang bila panjang, dan total yang dapat direkonsiliasi.
- Excel mempunyai header, tipe tanggal/angka, filter/freeze header jika sesuai, dan menangani formula injection.
- Ekspor tidak boleh membocorkan kolom pribadi atau secret.
- Otorisasi dilakukan pada endpoint ekspor, bukan hanya pada halaman yang mengawali ekspor.
- File yang dihasilkan harus diuji dengan membuka dan memeriksa isinya, bukan hanya mengecek HTTP response.

## 3. Rencana template

Setiap template perlu menjelaskan:
- nama kolom;
- wajib/opsional;
- format tanggal, waktu, kode, dan kategori;
- kunci identitas yang dipakai untuk mencocokkan baris;
- kebijakan duplikat;
- bagaimana baris invalid diperbaiki;
- apakah template mendukung update data yang ada;
- bagaimana riwayat historis dipertahankan.

Template final belum dibuat sebelum contoh file dan aturan sekolah diketahui.
