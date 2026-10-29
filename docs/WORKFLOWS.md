# Alur Kerja Pengguna

**Status:** DRAFT — BELUM DISETUJUI

## 1. Siklus awal admin

1. Admin login.
2. Admin mengatur tahun ajaran/semester yang berlaku.
3. Admin memasukkan atau mengimpor data guru/pegawai, siswa, kelas, mata pelajaran, keanggotaan kelas, jadwal, dan penugasan.
4. Sistem memvalidasi format dan relasi, menampilkan pratinjau, serta menandai baris duplikat/error.
5. Admin meninjau hasil dan mengonfirmasi impor.
6. Sistem menyediakan ringkasan data yang berhasil dan gagal.

## 2. Alur absensi siswa dan jurnal

1. Guru login.
2. Guru memilih sesi/jadwal pada tanggal tertentu yang termasuk penugasannya.
3. Sistem menampilkan kelas, mata pelajaran, jam, dan roster siswa yang sesuai tanggal.
4. Guru memilih status per siswa: Hadir, Sakit, Izin, Alpa, Training, atau DW.
5. Sistem membedakan siswa yang belum dicatat dari siswa dengan status Alpa.
6. Guru mencatat nomor pertemuan dan materi pembelajaran.
7. Sistem menghitung rekap dari detail status dan memvalidasi sesi.
8. Guru meninjau rekap sebelum menyimpan/mengirim.
9. Guru mengirim laporan: status sesi dan jurnal langsung menjadi **Final Submitted** (tidak memerlukan persetujuan bertingkat).
10. Sistem mencatat waktu dan pelaku pengiriman (audit log). Catatan yang telah difinalkan dapat dikoreksi oleh admin jika terjadi kekeliruan data.

## 3. Alur agenda harian guru/pegawai

1. Data kehadiran guru/pegawai tersedia dari mekanisme yang disepakati.
2. Sistem menentukan kelompok/kategori kepegawaian dan apakah agenda wajib untuk orang tersebut pada tanggal itu.
3. Pengguna yang berwenang mengisi agenda harian.
4. Dashboard membedakan: sudah mengisi, belum mengisi padahal wajib, tidak wajib mengisi, tidak hadir, dan data belum lengkap.
5. Sistem tidak menyimpulkan semua orang sudah membuat agenda bila data kehadiran atau kewajiban belum lengkap.

## 4. Alur laporan jam mengajar

1. Sistem membentuk daftar sesi yang diharapkan dari jadwal dan pengecualian tanggal.
2. Sistem menghubungkan sesi dengan jurnal dan status pelaksanaan.
3. Sistem mengidentifikasi sesi yang belum memiliki jurnal atau memiliki kondisi khusus yang harus dilaporkan.
4. Admin meninjau daftar berdasarkan tingkat X, XI, XII, kelas, dan urutan jam.
5. Kondisi khusus dibedakan: guru tidak hadir, tugas luar, memberi tugas, guru hadir tetapi siswa tidak ada, sesi dibatalkan/diganti, jurnal belum diisi, dan status belum dikonfirmasi.
6. Admin mengoreksi/menandai data tidak benar sesuai izin.
7. Sistem menggunakan daftar yang ditinjau saat menghasilkan laporan.

## 5. Alur Bagian III — tidak hadir

1. Sistem mengambil data kehadiran guru/pegawai yang terkonfirmasi tidak hadir untuk tanggal terpilih.
2. Sistem mengelompokkan menurut kategori kepegawaian.
3. Jika kategori kosong dan semua data lengkap, tampilkan “Tidak ada” atau format yang disepakati.
4. Jika catatan belum lengkap, tampilkan peringatan “Data belum lengkap”, bukan menyatakan tidak ada ketidakhadiran.
5. Alasan pribadi/medis hanya dimasukkan jika diperlukan dan diizinkan.

## 6. Alur pembuatan pesan WhatsApp

1. Admin memilih tanggal dan memeriksa kelengkapan data.
2. Sistem membentuk Bagian I, II, III berdasarkan spesifikasi laporan.
3. Sistem menampilkan preview lengkap dan peringatan yang relevan.
4. Admin dapat menyunting teks bila diberi izin; penyuntingan harus dipahami sebagai perubahan pada draft pesan, bukan perubahan data sumber.
5. Tombol “Salin” menyalin teks.
6. Tombol “Buka WhatsApp” membuka WhatsApp dengan teks terisi.
7. Admin memilih percakapan dan menekan “Kirim” secara manual.
8. Aplikasi hanya mencatat bahwa draft dibuat/dibuka jika fitur audit ini disepakati. Jangan mengklaim bahwa pesan terkirim.

## 7. Alur export

1. Pengguna berwenang memilih periode/filter.
2. Backend memvalidasi hak akses dan parameter filter.
3. Sistem menyajikan pratinjau laporan.
4. Pengguna memilih PDF atau Excel.
5. Sistem menghasilkan file dengan filter yang sama.
6. QA merekonsiliasi isi file terhadap data sumber.

## 8. Keputusan operasional yang telah disahkan

- **Pengguna Akun:** Difokuskan pada Guru dan Admin; akun pegawai non-guru ditunda.
- **Status Laporan:** Langsung *final submitted* saat dikirim oleh guru.
- **Status Training/DW:** Ditandai manual oleh guru saat sesi kelas.
- **Retensi Offline:** Disimpan untuk rentang 1 minggu ke depan.
- **Konflik:** *Server-wins*; akun guru mendapatkan notifikasi jika data sudah diubah/dibuat oleh admin.
- **Koreksi:** Admin berwenang mengoreksi catatan yang telah final bila ada kekeliruan, dengan jejak audit tercatat.

## 9. Alur offline saat sinyal tidak stabil

1. Ketika tersedia koneksi, guru login dan menyiapkan sesi/jadwal serta roster yang diperlukan untuk rentang 1 minggu ke depan.
2. UI menunjukkan waktu terakhir sinkronisasi dan data yang tersedia secara offline.
3. Ketika koneksi hilang, app shell yang telah di-cache tetap membuka antarmuka offline; guru dapat mengisi absensi dan jurnal yang memang sudah disiapkan lokal.
4. Sistem menyimpan perubahan di IndexedDB dengan status “Tersimpan di perangkat — belum ke server”.
5. Guru melihat antrean perubahan yang menunggu sinkronisasi. Tidak boleh muncul konfirmasi “berhasil terkirim” pada server.
6. Ketika koneksi kembali, aplikasi mencoba sync dari halaman aktif/online event atau tombol “Sinkronkan sekarang”. Background Sync hanya opsional karena tidak didukung merata.
7. Server memvalidasi autentikasi, hak akses, sesi, roster, dan status; kemudian memberi acknowledgement per record secara idempotent.
8. **Resolusi Konflik:** Jika admin telah mengedit/membuat catatan di server saat guru offline, data server dipertahankan (*server-wins*). Server mengirim notifikasi ke akun guru bahwa data telah dibuat/diubah oleh admin.
9. Admin hanya menggunakan data yang telah tersinkron untuk laporan final. Jika ada data tertunda yang seharusnya masuk laporan, tampilkan “Data belum lengkap”.
10. **Prosedur Logout:** Pengguna diizinkan logout meskipun ada antrean pending. Data antrean lokal diisolasi dan diikat ke user ID di IndexedDB sehingga tidak bocor ke akun lain.

## 10. Batas operasi offline

- Absensi siswa dan jurnal/materi merupakan prioritas fase pertama.
- Agenda harian guru dapat diisi saat online/tersinkron; fitur pegawai non-guru ditunda.
- Dashboard sekolah agregat, import/export, dan finalisasi laporan WhatsApp memerlukan koneksi online.
- Perangkat harus pernah memuat aplikasi secara online dan menyiapkan data sebelum bekerja offline.
