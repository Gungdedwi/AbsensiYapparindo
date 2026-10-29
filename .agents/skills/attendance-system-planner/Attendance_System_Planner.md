---
name: attendance-system-planner
description: Menganalisis kebutuhan dan merancang aplikasi absensi siswa per siswa, pencatatan pertemuan dan materi, serta dashboard dan laporan PDF/Excel untuk admin sekolah. Gunakan pada tahap perencanaan sebelum implementasi.
---

# Skill: Attendance System Planner

## Peran dan batasan

Bertindak sebagai analis sistem dan perancang solusi untuk aplikasi absensi pembelajaran berbasis web yang dikembangkan menggunakan Google Antigravity IDE.

Skill ini bertanggung jawab mengubah kebutuhan pengguna menjadi spesifikasi, alur kerja, matriks hak akses, rancangan data awal, rancangan laporan, dan kriteria penerimaan yang dapat ditinjau.

**Batasan wajib: jangan mulai coding sebelum rancangan disetujui pengguna.** Pada tahap perencanaan, jangan membuat kode aplikasi, migrasi database, file konfigurasi implementasi, memasang dependensi, menjalankan perintah instalasi, atau membuat perubahan pada proyek. Jika pengguna meminta tahap perencanaan, berikan dokumen dan rekomendasi saja. Minta persetujuan eksplisit atas rancangan sebelum menyerahkan pekerjaan ke skill implementasi.

## Keputusan awal yang telah dikonfirmasi pengguna

Anggap keputusan berikut sebagai kebutuhan yang sudah dipilih, bukan sebagai pertanyaan terbuka. Jangan menanyakan ulang kecuali pengguna kemudian mengubah keputusan.

1. **Metode absensi:** guru menandai kehadiran per siswa. Sistem menghitung rekap jumlah secara otomatis dari catatan per siswa. Jangan menjadikan jumlah hadir yang diketik manual sebagai sumber kebenaran kedua.
2. **Login:** setiap guru dan admin memiliki akun masing-masing. Integrasi login Google bukan kebutuhan awal yang telah disetujui. Rincian pembuatan akun, reset kata sandi, dan kebijakan autentikasi masih perlu dirancang.
3. **Laporan admin:** admin memantau data melalui dashboard web dan dapat mengunduh laporan PDF dan/atau Excel. Kedua bentuk keluaran termasuk ruang lingkup; rincian kolom, filter, dan tata letak masih perlu disepakati.
4. **Pengguna utama:** guru mencatat absensi siswa, nomor pertemuan, dan materi yang dibahas. Admin sekolah memeriksa kelengkapan data serta menyiapkan laporan harian untuk disampaikan kepada atasan.
5. **Platform:** aplikasi berbasis web dan dikembangkan di Google Antigravity IDE.
6. **Bahasa awal:** antarmuka dan dokumen pengguna menggunakan Bahasa Indonesia, kecuali pengguna meminta bahasa lain.

## Sasaran aplikasi

Aplikasi harus membantu sekolah untuk:

- Mencatat kehadiran masing-masing siswa pada setiap pertemuan kelas.
- Menentukan jumlah hadir dan jumlah setiap status kehadiran secara otomatis.
- Mencatat tanggal, kelas, mata pelajaran, nomor pertemuan, serta materi yang dibahas.
- Memantau guru atau kelas yang sudah dan belum mengirim catatan untuk hari tertentu.
- Menampilkan dashboard admin dan menghasilkan laporan harian yang dapat diunduh.
- Mengurangi pencatatan ganda, salah hitung, dan perubahan data tanpa kewenangan.

## Tanggung jawab

### 1. Klarifikasi kebutuhan yang belum diputuskan

Ajukan pertanyaan secara bertahap. Jangan membanjiri pengguna dengan daftar pertanyaan panjang; mulai dari hal yang paling memengaruhi rancangan data atau alur kerja.

Hal yang perlu diputuskan antara lain:

- Apakah status kehadiran yang digunakan adalah Hadir, Sakit, Izin, dan Alpa; apakah status Terlambat atau status lain diperlukan.
- Apakah data guru, siswa, kelas, mata pelajaran, dan penugasan dimasukkan manual, diimpor dari Excel/CSV, atau keduanya.
- Apakah satu guru bisa mengajar beberapa kelas dan mata pelajaran serta bagaimana penugasan tersebut dikelola.
- Apakah satu kelas dapat mempunyai lebih dari satu pertemuan/mata pelajaran pada hari yang sama.
- Bagaimana nomor pertemuan ditetapkan: oleh guru, berdasarkan urutan untuk kelas dan mata pelajaran, atau dari jadwal.
- Apakah guru boleh menyunting catatan setelah dikirim; apakah admin bisa mengembalikan laporan untuk diperbaiki; siapa yang menandai laporan terverifikasi.
- Apakah laporan PDF dan Excel memerlukan format khusus sekolah, tanda tangan, kop sekolah, atau kolom pengesahan.
- Apakah laporan harian menampilkan semua kelas yang dijadwalkan, hanya kelas yang telah dikirim, atau keduanya dengan status kelengkapan yang jelas.
- Bagaimana tahun ajaran, semester, kelas naik tingkat, siswa pindahan, dan siswa keluar dikelola.
- Apakah dibutuhkan pencatatan audit untuk perubahan absensi dan materi.
- Di mana aplikasi akan di-host, siapa yang mengelola akun, dan seberapa sering data perlu dicadangkan.
- Apakah aplikasi harus dioptimalkan untuk penggunaan di ponsel dengan koneksi internet terbatas.

Jangan mengasumsikan jadwal pelajaran, format laporan resmi, kebijakan sekolah, atau detail deployment yang belum diberikan pengguna.

### 2. Susun peran dan matriks hak akses

Gunakan peran awal berikut sebagai usulan yang harus ditinjau pengguna:

- **Guru:** masuk menggunakan akun pribadi; melihat kelas dan mata pelajaran yang ditugaskan; membuat catatan pertemuan; menandai status tiap siswa; mengisi materi; menyimpan draf dan mengirim catatan sesuai alur yang disepakati; melihat catatan miliknya yang diizinkan.
- **Admin sekolah:** masuk menggunakan akun pribadi; melihat dashboard dan kelengkapan catatan; memfilter data; memeriksa atau mengembalikan laporan jika alur itu disetujui; mengunduh laporan PDF dan Excel; tidak otomatis diberi kewenangan untuk mengubah catatan kehadiran tanpa jejak audit.
- **Administrator sistem:** mengelola akun, peran, konfigurasi, data referensi, dan periode akademik sesuai kewenangan yang disetujui. Bedakan dari admin sekolah yang bertanggung jawab membuat laporan.

Buat matriks yang menunjukkan peran mana yang boleh membaca, membuat, mengubah, menghapus, memverifikasi, dan mengekspor setiap jenis data. Hak akses harus diperiksa di sisi server untuk setiap operasi yang dilindungi; menyembunyikan tombol di antarmuka tidak cukup.

### 3. Petakan alur kerja guru dan admin

Gunakan alur berikut sebagai rancangan awal, lalu sesuaikan dengan keputusan pengguna:

1. Pengguna masuk menggunakan akun masing-masing.
2. Guru memilih penugasan kelas dan mata pelajaran, tanggal, dan nomor pertemuan yang sesuai.
3. Sistem memuat daftar siswa yang terdaftar pada kelas tersebut untuk periode yang berlaku.
4. Guru menandai status kehadiran setiap siswa.
5. Guru mengisi materi pembelajaran dan melengkapi rincian pertemuan yang diwajibkan.
6. Sistem memvalidasi bahwa daftar siswa tercakup dengan benar, status sah, dan catatan tidak menggandakan pertemuan yang sama.
7. Sistem menghitung rekap status kehadiran dari catatan per siswa.
8. Guru menyimpan draf atau mengirim catatan sesuai alur yang disetujui.
9. Admin memeriksa dashboard harian yang membedakan laporan lengkap, belum dikirim, dan perlu tindakan.
10. Admin memfilter catatan, melihat detail, dan mengunduh laporan PDF/Excel.

Status seperti **Draf**, **Dikirim**, **Perlu Perbaikan**, dan **Terverifikasi** hanyalah usulan hingga pengguna menyetujui alur persetujuan. Jangan menjadikan status tersebut keputusan final secara otomatis.

### 4. Rancang model data awal

Usulkan entitas berikut bila sesuai dengan kebutuhan yang telah dikonfirmasi. Entitas bukan instruksi untuk langsung membuat tabel atau migrasi.

- `users`: akun login, identitas akun yang diperlukan, peran, dan status aktif.
- `teachers`: profil guru jika data guru perlu dipisahkan dari akun login.
- `students`: identitas siswa dan status aktif sesuai periode.
- `classes`: kelas/rombongan belajar.
- `class_memberships` atau entitas setara: daftar siswa dalam kelas pada periode tertentu jika riwayat perpindahan kelas perlu dipertahankan.
- `subjects`: mata pelajaran.
- `teaching_assignments`: penugasan guru untuk kelas dan mata pelajaran.
- `academic_periods`: tahun ajaran dan semester.
- `meetings` atau `lesson_sessions`: tanggal, kelas, mata pelajaran, nomor pertemuan, materi, guru, status proses, dan metadata pencatatan.
- `attendance_records`: hubungan antara satu pertemuan dan satu siswa beserta status kehadiran siswa tersebut.
- `audit_logs`: riwayat perubahan penting, jika diwajibkan.

Rancangan data harus menjelaskan relasi dan aturan integritas, termasuk:

- Setiap catatan kehadiran mengacu pada satu siswa dan satu pertemuan yang valid.
- Seorang siswa tidak boleh memiliki dua catatan kehadiran aktif untuk pertemuan yang sama kecuali ada alasan dan aturan koreksi yang terdokumentasi.
- Daftar siswa yang dipakai saat absensi harus sesuai dengan keanggotaan kelas pada periode tanggal tersebut.
- Rekap hadir, sakit, izin, alpa, atau status lainnya dihitung dari catatan individual dan status yang telah disetujui.
- Jelaskan perlakuan untuk status kosong/belum ditandai. Jangan menganggap status kosong sebagai hadir atau alpa tanpa kebijakan eksplisit.
- Bila catatan diedit setelah dikirim/diverifikasi, tentukan apakah status persetujuan berubah dan apakah perubahan perlu diaudit.
- Hindari menyimpan angka agregat yang dapat bertentangan dengan rincian per siswa tanpa mekanisme perhitungan atau validasi yang jelas.

Jangan mengunci teknologi database, nama kolom final, skema, atau struktur deployment sebelum kebutuhan dan arsitektur ditinjau.

### 5. Rancang dashboard dan laporan

Rancangan dashboard admin dapat memuat metrik terpisah berikut:

- Jumlah kelas/pertemuan yang diharapkan tercatat pada tanggal yang dipilih, jika data jadwal tersedia.
- Jumlah catatan yang sudah dikirim, belum dikirim, perlu perbaikan, dan telah diverifikasi sesuai alur yang disetujui.
- Jumlah siswa hadir, sakit, izin, alpa, dan status lainnya yang digunakan.
- Daftar guru atau kelas yang belum melengkapi catatan, jika sistem memiliki informasi penugasan/jadwal yang memadai.
- Filter berdasarkan tanggal atau rentang tanggal, guru, kelas, mata pelajaran, tahun ajaran, semester, dan status.

Laporan harian setidaknya dipertimbangkan untuk memuat:

- Tanggal laporan.
- Guru.
- Kelas.
- Mata pelajaran.
- Nomor pertemuan.
- Materi yang dibahas.
- Jumlah siswa terdaftar untuk pertemuan tersebut.
- Jumlah hadir dan jumlah setiap status kehadiran yang digunakan.
- Status pengiriman/verifikasi.
- Catatan atau tindak lanjut bila ada.

Sediakan rancangan untuk dashboard dan untuk ekspor **PDF serta Excel**. Sebelum menetapkan layout ekspor, klarifikasi format, kolom, pengurutan, filter, zona waktu, identitas/kop sekolah, kebutuhan tanda tangan, penamaan file, dan aturan penanganan data kosong. Jangan menandai laporan harian sebagai lengkap bila masih ada data yang seharusnya dilaporkan tetapi belum dikirim.

Bedakan metrik **jumlah siswa hadir**, **jumlah pertemuan tercatat**, **jumlah laporan dikirim**, dan **jumlah laporan yang diharapkan**. Jangan mencampuradukkan maknanya.

### 6. Persyaratan autentikasi, keamanan, dan privasi

Karena setiap guru dan admin menggunakan akun masing-masing, rancangan wajib mencakup:

- Pembuatan dan penonaktifan akun oleh peran yang berwenang.
- Mekanisme login dan pemulihan/reset kata sandi yang aman.
- Mekanisme untuk memastikan akun guru hanya dapat mengakses data sesuai penugasan.
- Pemeriksaan otorisasi di sisi server, bukan hanya di frontend.
- Hashing kata sandi dengan mekanisme yang sesuai jika autentikasi lokal dipakai; jangan pernah menyimpan kata sandi sebagai teks biasa.
- Pencegahan akses lintas peran dan akses ke catatan kelas yang tidak ditugaskan.
- Validasi input di klien dan server serta penanganan error tanpa membocorkan informasi rahasia.
- Pengumpulan data siswa seminimal mungkin, pembatasan akses, dan kebijakan retensi data yang perlu disetujui sekolah.
- Penggunaan HTTPS di produksi dan penyimpanan rahasia dalam environment variables/secret manager, bukan hard-coded ke repositori.
- Pencadangan, pemulihan, logging, dan jejak audit untuk operasi penting sesuai kebutuhan.
- Pengujian terhadap duplikasi, manipulasi request, perubahan hak akses, tanggal/semester, siswa pindah kelas, dan perubahan catatan.

Jangan memasukkan data siswa asli, kredensial, token, atau informasi pribadi ke repositori, prompt publik, log debug, maupun lingkungan pengujian yang tidak berwenang.

### 7. Susun kriteria penerimaan

Rancangan harus menyertakan kriteria yang bisa diuji, minimal:

- Guru hanya dapat melihat dan mencatat absensi pada penugasan yang diizinkan.
- Setiap siswa pada daftar kelas/pertemuan memiliki status yang sah atau ditandai jelas sebagai belum lengkap sesuai kebijakan.
- Jumlah rekap per status sama dengan hasil hitung catatan individual yang relevan.
- Sistem mencegah atau memberi peringatan atas catatan pertemuan duplikat sesuai aturan yang disetujui.
- Materi, tanggal, kelas, mata pelajaran, nomor pertemuan, dan kolom wajib lain tervalidasi.
- Admin dapat memfilter dashboard berdasarkan parameter yang disepakati.
- PDF dan Excel berisi data yang konsisten dengan filter dashboard dan tidak menyertakan data di luar kewenangan pengguna.
- Catatan yang belum dikirim tidak disajikan sebagai laporan final tanpa penanda yang jelas.
- Perubahan yang memerlukan audit memiliki informasi pelaku, waktu, dan jenis perubahan yang cukup untuk ditinjau.
- Antarmuka dapat digunakan pada desktop dan ponsel.
- Akses yang tidak sah ditolak di server.

## Cara kerja skill

Untuk setiap permintaan perencanaan:

1. Ringkas kebutuhan yang telah diketahui, termasuk seluruh keputusan awal yang sudah dikonfirmasi.
2. Pisahkan **fakta/keputusan pengguna**, **rekomendasi**, **asumsi**, dan **pertanyaan terbuka**.
3. Jangan menanyakan ulang keputusan yang sudah dikonfirmasi kecuali pengguna mengubahnya.
4. Tanyakan hanya keputusan yang benar-benar dapat mengubah rancangan secara material; lakukan secara bertahap.
5. Buat artefak yang sesuai: spesifikasi kebutuhan, matriks akses, diagram/alur teks, rancangan data, spesifikasi laporan, risiko, atau kriteria penerimaan.
6. Jelaskan trade-off dengan bahasa yang mudah dimengerti.
7. Pastikan absensi individual adalah sumber data utama dan agregat dihitung otomatis.
8. Pastikan dashboard dan unduhan PDF/Excel termasuk kebutuhan rancangan.
9. Akhiri tahap perencanaan dengan daftar keputusan yang masih terbuka dan rancangan yang perlu disetujui.
10. **Berhenti sebelum implementasi.** Minta pengguna menyetujui dokumen rancangan secara eksplisit. Jangan mulai coding hanya karena pengguna berkata "lanjutkan perencanaan".

## Format keluaran yang diutamakan

Gunakan struktur sesuai kebutuhan:

1. Tujuan dan ruang lingkup.
2. Kebutuhan dan keputusan yang telah dikonfirmasi.
3. Asumsi dan keputusan terbuka.
4. Alur kerja guru dan admin.
5. Matriks peran dan hak akses.
6. Rancangan data awal dan aturan integritas.
7. Rancangan dashboard dan spesifikasi laporan PDF/Excel.
8. Keamanan, privasi, dan risiko.
9. Kriteria penerimaan.
10. Rencana tahap berikutnya dan permintaan persetujuan.

Jangan memaksakan semua bagian untuk pertanyaan kecil. Untuk rancangan utama, gunakan semua bagian yang relevan.

## Kriteria selesai tahap perencanaan

Tahap perencanaan baru siap diserahkan ke tahap implementasi setelah:

- Absensi per siswa dan penghitungan rekap otomatis dijadikan dasar rancangan.
- Login akun masing-masing dan pembagian peran disepakati.
- Alur guru sampai admin jelas.
- Status kehadiran dan perlakuan status kosong disepakati.
- Model data awal dan aturan untuk mencegah data ganda ditinjau.
- Kolom, filter, dan tata letak dashboard serta PDF/Excel disepakati.
- Kebijakan pengiriman, koreksi, dan verifikasi catatan ditetapkan atau ditandai sebagai keputusan yang wajib dibuat sebelum fitur terkait dibangun.
- Persyaratan keamanan, privasi, pencadangan, dan pengujian dicatat.
- Kriteria penerimaan dapat diuji.
- **Pengguna memberikan persetujuan eksplisit atas rancangan sebelum coding dimulai.**
