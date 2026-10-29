---
name: minimal-code-engineering
description: Mencegah overengineering dan kode AI yang membengkak. Gunakan saat merencanakan atau mengimplementasikan fitur agar perubahan tetap kecil, jelas, sesuai Laravel, mudah diuji, dan tidak menambah abstraksi atau file tanpa kebutuhan nyata.
---

# Skill: Minimal Code Engineering

## 1. Tujuan

Bangun fitur yang memenuhi kebutuhan dengan **implementasi paling sederhana yang benar, aman, dapat diuji, dan mudah dirawat**.

Tujuan skill ini bukan memaksa jumlah baris kode sekecil mungkin. Tujuannya menghilangkan kode yang tidak menambah nilai: fitur di luar permintaan, duplikasi, placeholder, abstraksi prematur, lapisan pass-through, helper yang tidak perlu, konfigurasi berlebihan, serta arsitektur yang lebih rumit daripada kebutuhan sekolah.

Gunakan skill ini bersama `PROMPT.md`, `AGENTS.md`, spesifikasi yang disetujui, dan skill spesialis lain. Skill ini tidak membatalkan persyaratan keamanan, aksesibilitas, integritas data, testing, atau persetujuan pengguna.

## 2. Aturan inti

1. **Satu permintaan, satu lingkup.** Implementasikan permintaan yang disetujui, bukan daftar fitur tambahan yang dibayangkan.
2. **Perubahan terkecil yang cukup.** Cari bagian kode pemilik perilaku tersebut dan ubah seperlunya.
3. **Pakai kemampuan framework dahulu.** Untuk Laravel, periksa fitur bawaan seperti validation, Eloquent relationships, policies/gates, middleware, form requests, transactions, pagination, queues, scheduler, serta auth sebelum membuat mekanisme sendiri.
4. **Jangan menambah lapisan tanpa alasan.** Repository/service/DTO/factory/interface tidak otomatis diperlukan. Tambahkan hanya bila ada kebutuhan nyata seperti kompleksitas aturan bisnis, reuse bermakna, batas sistem eksternal, atau pengujian yang tidak praktis.
5. **Jangan menduplikasi logika.** Definisi status absensi, otorisasi, perhitungan rekap, dan pembentukan laporan harus memiliki sumber kebenaran yang jelas.
6. **Hindari giant files.** Jangan menempatkan seluruh aplikasi, semua endpoint, semua aturan, atau semua komponen UI dalam satu file.
7. **Hindari one-file-per-triviality.** Jangan membuat file, class, komponen, trait, atau helper hanya untuk membungkus satu pemanggilan tanpa manfaat nyata.
8. **Jangan membuat kerangka masa depan tanpa kebutuhan.** Hindari plugin system, event bus, generic CRUD engine, multi-tenancy, microservices, state management kompleks, abstraksi konfigurasi, atau integrasi yang belum diminta.
9. **Jangan mengompres kode agar jumlah baris terlihat kecil.** Jangan minify source, menggabungkan banyak statement ke satu baris, menghapus nama yang jelas, atau menghapus komentar penting untuk memenuhi target SLOC.
10. **Jangan mengorbankan kualitas.** Validasi, otorisasi, penanganan error, accessibility, dan tes yang diperlukan tetap wajib.
11. **Jangan mengklaim tes lulus tanpa menjalankannya.**
12. **Berhenti saat kebutuhan terpenuhi.** Sampaikan kemungkinan peningkatan tambahan secara terpisah; jangan langsung menerapkannya.

## 3. Workflow sebelum coding

Sebelum membuat perubahan:

1. Baca instruksi repo dan dokumen persetujuan yang relevan.
2. Baca skill yang relevan dengan fitur.
3. Pastikan acceptance criteria dan batas lingkup dipahami.
4. Periksa file yang ada, pola implementasi, dependency, dan perubahan Git lokal.
5. Cari apakah fungsi atau komponen serupa sudah ada.
6. Tentukan solusi paling sederhana yang mengikuti pola proyek.
7. Nyatakan asumsi penting atau blocker sebelum perubahan berisiko.
8. Jika approval gate di `PROMPT.md` belum dilewati, **berhenti pada perencanaan**; jangan coding.

Jangan menganggap repository kosong sampai benar-benar diperiksa. Jangan menimpa perubahan pengguna.

## 4. Pola khusus Laravel

Ikuti konvensi Laravel dan gunakan mekanisme bawaan sebelum menulis sistem baru.

### Gunakan ketika memang sesuai

- Form Request untuk validasi request yang perlu dipakai ulang atau cukup kompleks.
- Policy/Gate untuk otorisasi berbasis model/peran.
- Eloquent relation dan query scope untuk hubungan/filter yang jelas.
- DB transaction untuk operasi multi-record yang wajib atomik.
- Queue hanya untuk pekerjaan yang perlu berjalan di background.
- Scheduler hanya jika ada tugas terjadwal yang benar-benar dibutuhkan.
- Blade/Livewire components untuk UI yang reusable dan memang memiliki perilaku/struktur berulang.
- Laravel authentication/session dan password hashing yang teruji; jangan membuat kriptografi sendiri.
- Pagination untuk daftar besar.
- Package yang terawat bila menghindari implementasi khusus yang besar, setelah license, versi, dan kebutuhan diperiksa.

### Hindari

- Controller yang menampung seluruh aturan bisnis aplikasi.
- Model dengan semua perilaku sistem tanpa batas yang jelas.
- Repositories untuk setiap model secara otomatis jika Eloquent sudah cukup.
- Service class yang hanya meneruskan parameter ke satu fungsi lain tanpa menambah aturan atau batas tanggung jawab.
- Interface dengan satu implementasi yang tidak membutuhkan polymorphism/test seam.
- Trait yang digunakan satu kali hanya untuk memindahkan kode ke file lain.
- Membuat custom auth, permission engine, router, ORM, CSV parser, atau PDF layout engine jika mekanisme tepercaya yang sesuai sudah tersedia.
- Menambahkan package hanya karena agent mengenalnya.
- Membuat API terpisah jika aplikasi terintegrasi Blade/Livewire tidak membutuhkannya.

Tidak ada pola yang dilarang mutlak. Jelaskan alasan bila pilihan yang lebih kompleks benar-benar memberi manfaat.

## 5. Batas file dan struktur

Gunakan ukuran file sebagai **sinyal untuk review, bukan aturan pemecahan otomatis**.

- File baru sebaiknya fokus pada satu tanggung jawab yang mudah dijelaskan.
- Jika class/controller/component mendekati atau melebihi sekitar 300 baris, tinjau apakah ada tanggung jawab berbeda yang memang perlu dipisah.
- Jika satu file melebihi sekitar 500 baris, lakukan review eksplisit terhadap cohesion, duplikasi, testability, dan navigasi.
- Jangan memecah file besar hanya untuk mencapai angka baris; pemecahan yang menghasilkan puluhan class kecil tanpa tujuan adalah bentuk overengineering.
- Jangan membuat satu komponen UI yang memuat seluruh halaman aplikasi dengan logika yang tidak terkait.
- Jangan menyalin source dari package/framework ke aplikasi untuk mengubah sedikit perilaku.

Sebelum menambah file, jawab:
1. Apakah file ini memiliki tanggung jawab yang jelas?
2. Apakah kode serupa sudah ada?
3. Apakah pemisahan ini membuat sistem lebih mudah dipahami atau diuji?
4. Apakah fungsi framework atau package yang sudah disetujui dapat menyelesaikan masalah lebih sederhana?

Jika tidak ada manfaat yang jelas, jangan menambah file/lapisan tersebut.

## 6. Duplikasi, abstraksi, dan reuse

- Reuse ketika terdapat perilaku berulang yang sama atau aturan bisnis yang harus konsisten.
- Jangan membuat abstraksi generik untuk dua potongan yang kebetulan mirip tetapi maknanya berbeda.
- Jangan membuat konfigurasi dinamis bagi nilai yang akan selalu statis tanpa kebutuhan administrator.
- Hindari duplikasi query atau rumus hitung antara dashboard, PDF, Excel, dan WhatsApp; definisikan layanan/query agregasi yang jelas sesuai arsitektur.
- Absensi siswa, kehadiran guru/pegawai, agenda harian, dan jurnal mengajar tetap domain terpisah meskipun memiliki elemen UI yang mirip.
- Status Training/DW harus ditangani sesuai aturan domain; jangan menyatukannya dengan Hadir/Alpa hanya demi menyederhanakan kode.

Sederhana tidak berarti menggabungkan konsep bisnis berbeda. Batas domain yang benar lebih penting daripada pemakaian ulang yang dipaksakan.

## 7. Ukuran perubahan

Untuk setiap tugas:
- Sentuh sesedikit mungkin file yang diperlukan, tetapi jangan mempertahankan struktur buruk hanya demi menghindari perubahan.
- Hindari format ulang massal atau refactor tidak berkaitan.
- Jangan menambahkan fitur “sekalian” tanpa persetujuan.
- Gunakan diff untuk memahami dampak nyata perubahan.
- Untuk fitur besar, pecah menjadi vertical slices kecil yang masing-masing mempunyai acceptance criteria dan tes.
- Setelah setiap slice, tinjau perubahan sebelum melanjutkan.
- Jangan melakukan refactor luas dan perubahan perilaku bisnis pada commit/tahap yang sama kecuali diperlukan serta disetujui.

## 8. Tes yang proporsional dan bermakna

- Pertahankan tes yang melindungi aturan bisnis, keamanan, dan regresi.
- Tambahkan tes untuk acceptance criteria serta kasus gagal penting.
- Jangan menghasilkan banyak tes duplikat yang menguji perilaku identik tanpa nilai tambahan.
- Jangan menghapus, melemahkan, atau melewati tes yang gagal hanya agar build terlihat hijau.
- Pilih tes unit untuk logika kecil, integration untuk interaksi database/auth/API, dan end-to-end untuk alur kritis pengguna.
- Lakukan validasi yang sesuai perubahan; jangan menjalankan seluruh pipeline mahal untuk perubahan teks kecil kecuali aturan proyek mewajibkannya.
- Bila tes tidak dapat dijalankan, nyatakan sebagai belum dijalankan/diblokir, bukan lulus.

## 9. Cara meninjau kode yang sudah dibuat

Saat diminta “simplify”, “clean up”, “reduce code bloat”, atau “review for overengineering”:

1. Tentukan scope yang tepat dari file/diff yang diminta.
2. Catat baseline kondisi kerja dan tes sebelum perubahan.
3. Identifikasi dead code, placeholder, duplikasi, state yang redundant, custom utility yang dapat diganti API tepercaya, serta tanggung jawab yang salah tempat.
4. Urutkan perbaikan berdasarkan pengurangan kompleksitas dan risiko terendah dahulu.
5. Ajukan rencana perubahan sebelum refactor besar atau penghapusan.
6. Terapkan refactor kecil, satu kelas masalah pada satu waktu.
7. Jalankan tes setelah setiap perubahan bermakna.
8. Bandingkan perilaku dan hasil, bukan sekadar jumlah baris.
9. Audit apakah pengurangan berasal dari perbaikan struktural atau hanya komentar/whitespace/minifikasi.
10. Berhenti jika pengurangan lebih lanjut memerlukan pemotongan fitur, mengurangi keamanan, mengurangi keterbacaan, atau menambah risiko.

Jangan melakukan pembersihan seluruh repo secara spontan di akhir tugas yang tidak berkaitan.

## 10. Penggunaan metrik SLOC

Source lines of code (SLOC) boleh dipakai sebagai indikator untuk menemukan bloat, bukan sebagai satu-satunya ukuran kualitas.

Jika menghitung SLOC:
- Tetapkan definisi metrik dan baseline terlebih dahulu.
- Pisahkan source aplikasi dari `vendor/`, hasil build, file generated, lockfile, dokumentasi, dan file lain yang tidak mewakili kode yang dipelihara.
- Catat metode/tool yang digunakan agar perbandingan konsisten.
- Jangan membuat target jumlah baris total yang dipaksakan sebelum ukuran fitur dan acceptance criteria diketahui.
- Laporkan perubahan SLOC bersamaan dengan hasil tes, perilaku yang dipertahankan, file yang dihapus/diubah, dan risiko.
- Jangan mengejar penurunan angka dengan memindahkan kode ke file lain, memadatkan baris, atau menghapus tes yang bernilai.

## 11. Format respons setelah tugas

Berikan laporan ringkas:
- Tujuan dan scope.
- Skill/dokumen yang dibaca.
- Perubahan utama dan alasan.
- File baru yang ditambahkan dan manfaatnya.
- Abstraksi/dependency yang dipilih dan alasannya.
- Tes yang benar-benar dijalankan dan hasilnya.
- Risiko atau keputusan yang masih terbuka.
- Ide tambahan yang tidak diterapkan.

Untuk permintaan review saja, jangan mengubah kode; berikan temuan dan minta izin sebelum menerapkan perbaikan.

## 12. Kriteria selesai

Pekerjaan memenuhi skill ini apabila:
- hanya mengimplementasikan kebutuhan yang disetujui;
- tidak menambah abstraksi/dependensi tanpa alasan;
- tidak ada duplikasi yang jelas pada logika terkait;
- file dan class memiliki tanggung jawab yang masuk akal;
- kode tetap terbaca, aman, dan dapat diuji;
- tidak ada perilaku yang berubah secara tak sengaja;
- tes relevan dijalankan atau keterbatasannya dicatat;
- hasil dijelaskan secara transparan.
