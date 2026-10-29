# Test Plan — Aplikasi Absensi SMK

**Status:** DRAFT — BELUM DISETUJUI

## 1. Tujuan

Membuktikan bahwa fungsi dan laporan sesuai spesifikasi serta melindungi data, tidak hanya bahwa halaman dapat dibuka.

## 2. Tingkat tes

- Unit tests untuk aturan status, perhitungan rekap, dan definisi metrik.
- Integration tests untuk PostgreSQL, transaksi, autentikasi, hak akses, import/export.
- End-to-end tests untuk alur guru dan admin.
- Security tests untuk akses tanpa izin dan manipulasi ID.
- Manual responsive/accessibility review pada mobile dan desktop.

## 3. Kasus uji utama

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| T-001 | Login dengan kredensial benar | Pengguna masuk dan diarahkan sesuai peran |
| T-002 | Kredensial salah | Login ditolak tanpa membocorkan detail sensitif |
| T-003 | Guru mengakses sesi milik guru lain | Ditolak di backend |
| T-004 | Guru mengirim role/user ID lain di payload | Tidak dapat menaikkan hak akses |
| T-005 | Absensi mempunyai Hadir/Sakit/Izin/Alpa | Rekap cocok dengan detail |
| T-006 | Absensi Training atau DW | Tidak dihitung Hadir kelas maupun Alpa |
| T-007 | Satu siswa dikirim dua kali untuk sesi sama | Duplikasi ditolak/ditangani idempotent |
| T-008 | Absensi siswa belum diisi | Tidak dilaporkan sebagai Alpa |
| T-009 | Siswa pindah kelas setelah riwayat lama | Laporan sesi lama mempertahankan anggota pada saat itu |
| T-010 | Dua sesi pada tanggal yang sama | Keduanya dapat direkam sebagai sesi berbeda |
| T-011 | Jurnal sesi sudah lengkap | Tidak muncul sebagai jurnal yang belum diisi |
| T-012 | Guru hadir tetapi belum membuat agenda | Muncul di Bagian I bila kewajiban agenda terpenuhi |
| T-013 | Semua agenda lengkap dan data kehadiran lengkap | Bagian I menampilkan “Semua Membuat Agenda” |
| T-014 | Data kehadiran/agenda belum lengkap | Bagian I memberi peringatan, tidak membuat klaim lengkap |
| T-015 | Guru tidak hadir terkonfirmasi | Masuk Bagian III kategori tepat |
| T-016 | Data kehadiran pegawai kosong | Tidak otomatis masuk daftar tidak hadir |
| T-017 | Sesi berkondisi khusus | Bagian II menampilkan status dan alasan yang benar |
| T-018 | Rentang tanggal melintasi tengah malam/zona waktu | Filter tanggal sesuai aturan yang disepakati |
| T-019 | Import dengan kolom salah | Baris/kolom error ditandai, tidak diimpor diam-diam |
| T-020 | Import berisi duplikat | Deteksi dan tindakan sesuai mode impor |
| T-021 | Export PDF/Excel | Data, filter, total, format dan akses benar |
| T-022 | Input text diawali karakter formula spreadsheet | Dinetralkan sesuai konteks ekspor |
| T-023 | Membuat draf WhatsApp | Tanggal dan tiga bagian sesuai filter data |
| T-024 | Membuka WhatsApp dari pratinjau | Teks terisi; aplikasi tidak menandai pesan terkirim |
| T-025 | Pesan terlalu panjang | Pengguna mendapat alternatif salin/unduh `.txt`, bukan potongan diam-diam |
| T-026 | Sesi login habis | UI mengarah ke login dengan aman |
| T-027 | Pengguna tanpa izin meminta ekspor | Ditolak di backend |
| T-028 | Uji file di mobile | Tabel/form bisa digunakan tanpa teks terlalu kecil |
| T-029 | Error koneksi ketika menyimpan absensi batch | Tidak ada keadaan setengah tersimpan yang tidak diketahui |
| T-030 | Restore backup uji | Prosedur dapat dipulihkan di lingkungan nonproduction |
| T-031 | Aplikasi sebelumnya telah dimuat online, lalu perangkat offline | App shell dan data yang telah disiapkan tetap dapat dibuka |
| T-032 | Simpan absensi saat offline | Status lokal terlihat sebagai pending, bukan sukses server |
| T-033 | Koneksi pulih dan sync dikirim ulang | Record diterima sekali, tanpa duplikasi |
| T-034 | Respons server hilang setelah record diterima | Retry idempotent tidak membuat record kedua |
| T-035 | Hak akses/penugasan berubah sebelum sync | Server menolak atau mengarahkan ke konflik; tidak menulis tanpa izin |
| T-036 | Dua perangkat mengisi siswa-sesi yang sama | Konflik ditampilkan dan data tidak ditimpa diam-diam |
| T-037 | Logout dengan antrean offline | UI memberi peringatan; antrean tidak dihapus tanpa keputusan pengguna |
| T-038 | Dashboard/WhatsApp saat record belum sync | Laporan memberi peringatan dan tidak mengaku lengkap |
| T-039 | Browser tidak mendukung Background Sync | Sinkronisasi manual/app-resume tetap bekerja |
| T-040 | Data browser dibersihkan | Batas retensi dan risiko kehilangan lokal terdokumentasi; UI tidak mengklaim dapat memulihkan otomatis |

## 4. Data uji

Pakai fixture sintetis:
- beberapa kelas untuk X/XI/XII;
- guru yang mempunyai satu dan beberapa penugasan;
- guru tanpa penugasan;
- siswa aktif, siswa yang pindah kelas di periode berbeda;
- siswa Hadir/Sakit/Izin/Alpa/Training/DW/Belum Diisi;
- pegawai berstatus hadir, tidak hadir, dan belum dicatat;
- agenda ada, belum ada, dan tidak wajib;
- sesi normal, sesi berstatus khusus, pembatalan, perubahan jadwal;
- file impor valid, invalid, duplikat;
- string panjang dan karakter khusus di field teks.

Jangan memakai data produksi nyata.

## 5. Exit criteria

- Tidak ada kegagalan critical/high yang belum diselesaikan atau diterima secara eksplisit oleh pemilik sistem.
- Tes rekap, akses lintas guru, export filter, dan Bagian I/II/III lulus.
- Semua tes yang diblokir/dilewati tercatat.
- UI diuji pada desktop dan mobile.
- Backup/restore diuji sebelum rilis operasional.
- Hasil tes dilaporkan dengan status nyata, tidak hanya code inspection.
