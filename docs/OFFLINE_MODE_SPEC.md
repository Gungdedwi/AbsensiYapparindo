# Spesifikasi Mode Offline — PWA dan Sinkronisasi Aman

**Status:** DRAFT — BELUM DISETUJUI  
**Tujuan:** membuat alur kerja utama tetap dapat digunakan saat sinyal internet lemah/putus-putus tanpa mengorbankan integritas absensi dan keamanan data.

## 1. Rekomendasi

Buat aplikasi sebagai **Progressive Web App (PWA) dengan dukungan offline terbatas dan sinkronisasi tertunda**. PWA tidak otomatis menjadi offline-first hanya karena ditambahkan manifest/service worker; perilaku offline harus dirancang dan diuji.

Prioritas offline awal:
1. Guru dapat membuka sesi/jadwal yang telah diunduh ketika online.
2. Guru dapat membuka roster siswa yang telah disiapkan untuk sesi tersebut.
3. Guru dapat mengisi absensi individual dan materi/jurnal tanpa koneksi.
4. Guru dapat menyimpan perubahan lokal dan melihat status `Menunggu Sinkronisasi`.
5. Saat koneksi kembali, data disinkronkan ke Laravel/Supabase dengan aman.
6. Jika server menolak atau menemukan konflik, catatan tetap ada secara lokal dengan status `Perlu Tindakan`; jangan menghapus atau menimpa diam-diam.

Usulan fase pertama adalah offline untuk absensi siswa serta jurnal/materi. Dukungan offline untuk agenda harian dan kehadiran guru/pegawai dapat dibangun memakai mekanisme sinkronisasi yang sama, setelah pola data, konflik, dan keamanan pada fase pertama lolos tes. Jika kebutuhan sekolah mengharuskan semuanya tersedia offline sejak rilis pertama, scope dan estimasi kompleksitas harus disetujui sebelum implementasi.

## 2. Batas yang wajib dipahami

- Perangkat perlu membuka aplikasi saat online setidaknya satu kali agar app shell/service worker dan data kerja dapat disiapkan. Perangkat baru yang belum pernah memuat aplikasi tidak dapat diasumsikan langsung bekerja offline.
- Data offline tidak langsung tersedia di dashboard/admin/server sampai berhasil tersinkron.
- Dashboard agregat seluruh sekolah dan laporan WhatsApp/PDF/Excel yang mengandalkan data semua pengguna memerlukan koneksi dan sinkronisasi yang memadai.
- Jangan membuat laporan berstatus lengkap jika ada perangkat/guru dengan data belum tersinkron atau sumber data yang diharapkan belum lengkap.
- Background Sync API tidak didukung secara konsisten di semua browser. Implementasikan tombol sinkronisasi manual dan retry saat aplikasi dibuka kembali/menjadi aktif; Background Sync hanya peningkatan opsional. Referensi: https://developer.mozilla.org/en-US/docs/Web/API/Background_Synchronization_API
- Cache browser, IndexedDB, dan PWA tidak menjamin data persisten selamanya. Pengguna dapat menghapus data browser, browser dapat membersihkan storage, perangkat bisa hilang, dan aplikasi dapat di-uninstall. Jangan menjanjikan ketahanan seperti database server.
- Jangan menyebut record berhasil disimpan di server saat hanya tersimpan lokal. Gunakan label yang berbeda dan jelas.

## 3. Arsitektur offline yang diusulkan

Karena stack yang diusulkan memakai Laravel Blade + Livewire, ingat bahwa interaksi Livewire normal mengirim request ke server dan tidak akan bekerja ketika koneksi benar-benar putus. Form absensi/jurnal yang wajib berjalan offline harus mempunyai jalur client-side khusus (misalnya komponen JavaScript/Alpine kecil) yang menyimpan perubahan ke IndexedDB; jangan menganggap form Livewire biasa otomatis berfungsi offline. Tetap gunakan Laravel untuk otorisasi dan validasi final saat sync. Hindari menambah SPA/framework frontend baru kecuali ada alasan yang disetujui.

### A. Service worker dan app shell
Cache hanya aset minimum yang diperlukan untuk membuka antarmuka offline, misalnya CSS/JS/font/icon dan halaman offline yang netral.

- Gunakan HTTPS pada hosting; service worker memerlukan secure context kecuali lingkungan localhost.
- Beri versi cache dan strategi pembaruan yang jelas.
- Jangan cache semua URL secara otomatis.
- Jangan cache HTML autentikasi atau response API pribadi menggunakan strategi cache-first.
- Endpoint autentikasi, API absensi, halaman privat, file ekspor, dan respons yang memuat data siswa harus mengikuti kebijakan network/no-store yang ditinjau. Jangan menyimpan response sensitif ke Cache Storage secara generik.
- Service worker tidak boleh memuat script pihak ketiga atau kode remote yang tidak ditinjau.

Referensi umum: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Offline_and_background_operation

### B. Penyimpanan lokal
Gunakan IndexedDB untuk structured data dan antrean perubahan offline; jangan gunakan `localStorage` sebagai database antrean besar.

Simpan data minimum yang diperlukan untuk tugas offline:
- identitas sesi lokal dan user/account binding non-secret;
- sesi/jadwal terpilih untuk rentang yang disetujui;
- roster siswa yang diperlukan pada sesi tersebut;
- status absensi lokal;
- draft jurnal/materi;
- client-generated UUID/idempotency key dan metadata sinkronisasi.

Jangan simpan data pribadi berlebihan seperti alamat, nomor telepon, tanggal lahir, atau informasi kesehatan siswa hanya demi kenyamanan offline. Jangan menyimpan password, access token, refresh token, database credential, Supabase secret/service-role key, atau cookie sebagai payload lokal buatan aplikasi.

IndexedDB bukan jaminan enkripsi at-rest. Minimalkan data dan durasi penyimpanan. Minta pengguna menggunakan perangkat yang memiliki screen lock dan akun OS terlindungi.

**Kebijakan yang telah diputuskan pengguna:**
- **Rentang data offline:** jadwal dan roster kelas disimpan untuk **1 minggu ke depan**.
- **Prosedur logout pada antrean pending:** Pengguna diizinkan logout meskipun masih ada antrean offline yang belum tersinkron. Data antrean lokal **diisolasi dan diikat ke user ID/account binding masing-masing** di IndexedDB sehingga tidak dapat dilihat atau diakses oleh akun lain yang masuk di perangkat yang sama. Antrean tersebut akan dilanjutkan sinkronisasinya saat akun bersangkutan login kembali dalam kondisi online.
- Jika sesi Laravel kedaluwarsa saat offline, guru tetap dapat mengisi ruang kerja lokal yang terikat akunnya, dan sinkronisasi akan diproses setelah guru mengautentikasi ulang saat online. Jangan menyimpan token sesi secara eksplisit di IndexedDB.

### C. Antrean perubahan lokal
Setiap operasi lokal memiliki:
- local record ID/UUID unik;
- idempotency key unik untuk operasi tulis;
- tipe operasi dan versi schema;
- target session/lesson ID yang telah ditetapkan dari data yang diunduh;
- data absensi/jurnal minimum;
- waktu perangkat untuk referensi (server tetap menentukan waktu penerimaan);
- status lokal: `pending`, `syncing`, `synced`, `conflict`, atau `failed`;
- pesan error aman dan jumlah percobaan tanpa menyimpan token.

Jangan menjadikan jam perangkat sebagai bukti otoritatif waktu pengiriman. Simpan waktu lokal hanya untuk konteks dan gunakan waktu server saat sinkronisasi. Jangan membuang antrean setelah request timeout karena server mungkin sudah menyimpan; gunakan idempotency key untuk menangani retry.

### D. Endpoint sinkronisasi server-side
Laravel tetap menjadi sumber otoritatif. Server harus:
- mengautentikasi pengguna saat sinkronisasi;
- memverifikasi peran dan penugasan yang berlaku;
- memvalidasi session ID, membership roster, enum status, panjang teks, dan aturan bisnis;
- menerima batch terbatas dengan ukuran/limit terdokumentasi;
- menerapkan idempotency key unik atau mekanisme setara;
- mengembalikan hasil per item: diterima, sudah diterima sebelumnya, perlu konflik, atau ditolak;
- tidak mempercayai user ID/role yang dikirim dari client;
- tidak mengizinkan guru menulis ke kelas/penugasan lain;
- menggunakan transaksi dan constraint database yang sesuai;
- mencatat audit yang diperlukan tanpa merekam secret atau data berlebihan.

Retry request tidak boleh membuat absensi ganda. Unique constraint per siswa-pertemuan tetap wajib; idempotency harus melindungi seluruh operasi sync, bukan hanya tombol di UI.

## 4. Status UI yang wajib jelas

Tampilkan indikator global dan per item:
- **Online** / **Offline**
- **Terakhir sinkron: [waktu]**
- **Tersimpan di perangkat — belum ke server**
- **Menunggu sinkronisasi (N)**
- **Sedang menyinkronkan**
- **Tersinkron ke server**
- **Konflik — perlu diperiksa**
- **Gagal — coba lagi**
- **Data jadwal/roster sudah lama — perbarui saat online**

Jangan menggunakan ikon/warna saja; sediakan label teks. Tombol “Sinkronkan sekarang” harus selalu tersedia saat antrean pending dan koneksi terdeteksi. Gunakan retry/backoff untuk kegagalan jaringan sementara, tetapi minta tindakan pengguna pada validation error/permission denial.

## 5. Penanganan konflik dan data stale

Skenario konflik meliputi:
- dua perangkat mengisi siswa/sesi yang sama;
- guru mencoba sinkronisasi setelah hak penugasan berubah;
- siswa pindah kelas setelah roster diunduh;
- jadwal dibatalkan/diganti saat perangkat offline;
- server menerima perubahan lalu respons hilang;
- format lokal berubah setelah update aplikasi.

Aturan resolusi konflik (telah diputuskan pengguna):
- **Server-Wins:** jika terjadi perbedaan data (misal admin sudah mengedit/membuat absensi di server sementara guru mengirim data offline sesi/siswa yang sama), data server dipertahankan.
- **Notifikasi ke Guru:** akun guru menerima notifikasi jelas bahwa data telah dibuat/diubah oleh admin di server, sehingga guru mengetahui alasan catatan lokalnya tidak menimpa server.
- Request yang sudah diterima server tetapi responsnya hilang harus aman dikirim ulang via idempotency key.
- Jika schema lokal sudah tidak kompatibel, jangan menghapus antrean. Sediakan pesan tindakan yang jelas.

## 6. Kebijakan laporan

- Admin dashboard, laporan harian, PDF/Excel agregat, dan draf WhatsApp harus menunjukkan waktu data terakhir diperbarui bila relevan.
- Jika perangkat atau laporan yang diharapkan belum tersinkron, tampilkan `Data belum lengkap/ada sinkronisasi tertunda`.
- Jangan tampilkan “Semua Membuat Agenda” jika data agenda/kehadiran dari pengguna yang wajib mengisi belum lengkap atau belum tersinkron.
- Jangan masukkan catatan offline ke laporan final seolah-olah telah berada di server.
- Setelah sync, lakukan rekonsiliasi agar dashboard, PDF, Excel, dan pesan WhatsApp memakai data yang sama.
- Penyusunan dan pengiriman WhatsApp tetap online; admin harus memeriksa pratinjau dan menekan Kirim sendiri.

## 7. Urutan implementasi

Tahap implementasi setelah persetujuan:
1. PWA manifest dan app shell cache dengan aset minimum.
2. Status online/offline dan timestamp sinkronisasi.
3. Fitur download/sync roster dan jadwal penugasan untuk tanggal/rentang yang disetujui.
4. IndexedDB schema dan storage adapter dengan versi schema.
5. Form absensi offline dan draft jurnal/materi.
6. Outbox antrean dengan idempotency key.
7. Endpoint sync Laravel dengan validasi, transaksi, otorisasi, dan per-item acknowledgement.
8. UI review konflik dan gagal sync.
9. Retry on app resume/online dan manual sync; Background Sync opsional setelah dukungan browser diperiksa.
10. Tambahkan agenda harian/kehadiran pegawai pada fase berikutnya bila hasil uji core workflow memadai atau bila disetujui menjadi scope fase pertama.
11. Review keamanan lokal dan server.
12. Uji offline nyata dan kondisi jaringan terputus-putus.

Jangan mengimplementasikan semua ini dalam satu file/commit besar. Gunakan skill `minimal-code-engineering`, `full-stack-developer`, `application-security-engineer`, `qa-testing-engineer`, dan `devops-deployment-engineer`.

## 8. Kriteria pengujian

Gunakan data sintetis dan perangkat/browser target yang disetujui.

- App shell dapat dibuka setelah aplikasi berhasil dimuat/dipasang saat online lalu koneksi diputus.
- Guru dapat membuka hanya sesi/roster yang sebelumnya berhasil disiapkan.
- Absensi dan jurnal dapat disimpan lokal offline dan tetap tampil setelah navigasi/reload yang didukung.
- Status “tersimpan lokal” tidak dikira sudah tersimpan ke server.
- Setelah jaringan kembali, sync dapat dilakukan manual meskipun Background Sync API tidak tersedia.
- Request retry tidak menggandakan record.
- Konflik karena sesi/absensi sudah berubah tidak menimpa data diam-diam.
- Hak akses diperiksa ulang saat sync.
- Logout dengan antrean pending memberi peringatan dan tidak menghapus data tanpa konfirmasi.
- Data lokal user sebelumnya tidak tersedia untuk user berbeda.
- Data jadwal/roster basi ditandai dan tidak disamarkan sebagai data terbaru.
- Gangguan koneksi di tengah batch tidak menyebabkan record hilang atau duplikasi.
- Admin tidak membuat laporan final dari data yang diketahui belum tersinkron.
- Clear site data/uninstall dan hilangnya browser storage ditangani sebagai risiko dokumentasi, bukan dijanjikan dapat dipulihkan otomatis.
- Uji Android/Chrome dan browser laptop yang benar-benar akan dipakai; dukungan install/PWA/background sync berbeda antar browser.

## 9. Trade-off

Kelebihan:
- absensi dapat dicatat saat sinyal putus;
- data tidak harus diketik ulang setelah jaringan pulih;
- lebih sesuai untuk kelas dengan konektivitas tidak stabil.

Biaya/risiko:
- implementasi lebih kompleks daripada form online saja;
- konflik dan antrean perlu didesain serta dites;
- data lokal tetap memiliki risiko kehilangan/perangkat bersama;
- semua laporan agregat tidak dapat dijamin lengkap sampai sinkronisasi;
- browser berbeda dalam dukungan Background Sync dan pengelolaan storage.

## 10. Status persetujuan pengguna (Fase 1)

Keputusan yang telah disetujui:
1. **Cakupan offline fase pertama:** Absensi siswa dan jurnal/materi pembelajaran. Agenda/kehadiran pegawai non-guru ditunda.
2. **Rentang data offline:** 1 minggu ke depan.
3. **Retensi & Prosedur logout:** Logout diizinkan; antrean pending diisolasi per akun di IndexedDB.
4. **Aturan konflik:** Data server dipertahankan (*server-wins*); sistem mengirimkan notifikasi ke akun guru.
5. **Indikator laporan:** Laporan harian/dashboard online wajib menandai status jika ada sync tertunda.

Tahap persiapan spesifikasi offline (Fase 1) telah selesai dan diselaraskan. Kode aplikasi/service worker akan diimplementasikan pada fase implementasi setelah mendapat persetujuan lanjutan.
