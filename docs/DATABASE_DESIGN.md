# Rancangan Database Konseptual

**Status:** DRAFT — BELUM DISETUJUI. Dokumen ini bukan SQL, migration, atau izin mengubah Supabase.

## 1. Prinsip rancangan

- Database utama yang diusulkan adalah PostgreSQL pada Supabase.
- Laravel mengakses database secara server-side melalui kredensial yang disimpan aman.
- Tentukan schema PostgreSQL khusus untuk tabel internal Laravel jika diperlukan; dokumentasi Supabase merekomendasikan agar tabel aplikasi tidak tanpa sengaja diekspos melalui `public` Data API.
- Rancangan final harus mengikuti SRS dan persetujuan pengguna.
- Satu sumber kebenaran untuk detail absensi; angka rekap diturunkan dari records.
- Gunakan timestamp/date dengan zona waktu dan aturan periode yang jelas.
- Hindari delete cascade pada data historis yang perlu dipertahankan.
- Jangan membuat SQL atau migration sebelum persetujuan rancangan.

## 2. ERD konseptual (usulan)

```mermaid
erDiagram
    USERS ||--o{ USER_ROLES : assigned
    ROLES ||--o{ USER_ROLES : contains
    USERS o|--o| STAFF_PROFILES : may_link
    STAFF_PROFILES ||--o{ TEACHING_ASSIGNMENTS : teaches
    ACADEMIC_YEARS ||--o{ SEMESTERS : includes
    ACADEMIC_YEARS ||--o{ CLASS_ENROLLMENTS : scopes
    CLASSES ||--o{ CLASS_ENROLLMENTS : contains
    STUDENTS ||--o{ CLASS_ENROLLMENTS : enrolls
    SUBJECTS ||--o{ TEACHING_ASSIGNMENTS : subject
    CLASSES ||--o{ TEACHING_ASSIGNMENTS : class
    SEMESTERS ||--o{ TEACHING_ASSIGNMENTS : period
    TEACHING_ASSIGNMENTS ||--o{ SCHEDULE_TEMPLATES : has
    SCHEDULE_TEMPLATES ||--o{ LESSON_SESSIONS : generates
    STAFF_PROFILES ||--o{ LESSON_SESSIONS : responsible
    LESSON_SESSIONS ||--o| LESSON_JOURNALS : has
    LESSON_SESSIONS ||--o{ STUDENT_ATTENDANCE : records
    STUDENTS ||--o{ STUDENT_ATTENDANCE : attends
    STAFF_PROFILES ||--o{ DAILY_AGENDAS : writes
    STAFF_PROFILES ||--o{ STAFF_ATTENDANCE : recorded_for
    USERS ||--o{ AUDIT_LOGS : performs
```

**Catatan:** diagram konseptual; nama/relasi harus ditinjau bersama aturan jadwal, pegawai, dan akses.

## 3. Entitas konseptual

| Entitas | Tujuan | Catatan penting |
|---|---|---|
| `users` | Akun login aplikasi | Email/username harus unik sesuai kebijakan; password hanya disimpan sebagai hash melalui Laravel. |
| `roles` / `user_roles` | Peran dan relasi akun-peran | Bisa disederhanakan jika satu akun pasti satu peran, setelah dikonfirmasi. |
| `staff_profiles` | Profil guru/pegawai | Dapat terkait akun nullable bila pegawai hanya dicatat, bukan login; kategori kepegawaian harus terpisah dari status hadir. |
| `academic_years` | Tahun ajaran | Tidak boleh mengubah makna catatan lama. |
| `semesters` | Semester dalam tahun ajaran | Aturan tanggal mulai/akhir perlu ditentukan. |
| `classes` | Kelas/rombongan belajar | Kode dan label kelas konsisten, termasuk tingkat X/XI/XII. |
| `students` | Profil siswa | Simpan atribut minimum yang diperlukan. |
| `class_enrollments` | Riwayat siswa berada di kelas | Tanggal mulai/akhir untuk melindungi sejarah keanggotaan. |
| `subjects` | Mata pelajaran | Jangan mengandalkan free-text untuk seluruh mata pelajaran. |
| `teaching_assignments` | Guru + kelas + mata pelajaran + periode | Sumber kewenangan guru dan konteks pelajaran. |
| `schedule_templates` | Pola jadwal rutin | Hari/jam/period; bukan satu-satunya sumber sesi aktual. |
| `lesson_sessions` | Sesi aktual pada tanggal tertentu | Memiliki kelas/penugasan/tanggal/rentang jam/status pelaksanaan. Satu tanggal dapat memiliki banyak sesi. |
| `lesson_journals` | Nomor pertemuan dan materi | Relasi ke sesi aktual; aturan nomor pertemuan perlu diputuskan. |
| `student_attendance` | Satu status per siswa per sesi | Constraint unik konseptual `(lesson_session_id, student_id)`. |
| `daily_agendas` | Agenda harian guru/pegawai | Terpisah dari jurnal; aturan wajib isi per orang/hari masih terbuka. |
| `staff_attendance` | Kehadiran guru/pegawai per tanggal/shift | Jangan menyimpulkan tidak hadir dari record kosong. |
| `audit_logs` | Jejak perubahan penting | Minimalkan data yang disimpan; rencanakan retensi dan akses. |

Entitas tambahan seperti `schedule_exceptions`, `report_runs`, `agenda_obligations`, atau tabel referensi status dapat dibutuhkan setelah aturan operasional diperjelas.

## 4. Status absensi siswa

Status bisnis yang diminta:
- `PRESENT` — Hadir di kelas.
- `SICK` — Sakit.
- `EXCUSED` — Izin.
- `ABSENT` — Alpa/tidak hadir tanpa keterangan sesuai kebijakan.
- `TRAINING` — status khusus; bukan hadir di kelas dan bukan Alpa.
- `DAILY_WORKER` — DW; status khusus, bukan hadir di kelas dan bukan Alpa.

Catatan:
- Kode adalah nama konseptual; label UI menggunakan Bahasa Indonesia.
- Status “Belum Diisi” sebaiknya direpresentasikan sebagai belum adanya record atau mekanisme eksplisit yang disetujui, bukan disimpan sebagai Alpa.
- `staff_profiles.employment_category` seperti Pegawai Training bukan status siswa `TRAINING`; keduanya konsep berbeda.
- Jika Training/DW punya periode dan lokasi, desain relasi/status perlu menyimpan metadata minimum yang sudah disetujui.
- Definisi “total siswa hadir” harus secara eksplisit mengecualikan Training/DW bila itu aturan sekolah.

## 5. Sesi aktual dan jadwal

Gunakan jadwal rutin untuk membentuk sesi aktual, tetapi setiap tanggal perlu data sesi nyata agar perubahan jadwal, libur, ujian, tugas luar, dan sesi pengganti bisa dicatat. Tabel `lesson_sessions` harus membedakan antara:
- dijadwalkan;
- jurnal sudah lengkap;
- jurnal belum diisi;
- guru bertugas di luar kelas;
- guru memberi tugas;
- guru hadir tetapi siswa tidak ada;
- sesi dibatalkan/diganti;
- status belum dikonfirmasi.

Status final perlu disetujui sekolah; daftar ini contoh klasifikasi awal untuk mencegah laporan menyalahartikan semua sesi tidak normal sebagai guru mangkir.

## 6. Constraint dan integritas yang wajib dirancang

1. Primary key stabil pada setiap entitas.
2. Foreign key untuk setiap relasi penting.
3. Unik per siswa per sesi absensi.
4. Validasi tanggal dan periode siswa di kelas saat sesi berlangsung.
5. Aturan unik sesi harus membolehkan lebih dari satu sesi di tanggal yang sama.
6. Transaksi saat membuat sesi dan roster/absensi batch.
7. Otorisasi server-side memakai penugasan, bukan hanya ID yang dikirim frontend.
8. Rekap status diturunkan dari records dan bisa direkonsiliasi.
9. Perubahan historis tidak boleh mengubah keanggotaan kelas lama tanpa aturan.
10. Penghapusan/penonaktifan data master mempertahankan arsip absensi yang sudah ada.
11. Gunakan indeks sesuai query yang benar-benar diperlukan.
12. Data kosong, status belum konfirmasi, dan nol adalah keadaan yang berbeda.

## 7. Opsi schema dan koneksi Supabase

- **Koneksi Supabase:** Menggunakan **Session Pooler (IPv4)** melalui TLS dengan port pooler (default 5432 / session pooler port Supabase). Hal ini menjamin kompatibilitas penuh dengan lingkungan hosting Render Free dan jaringan internet pengembang lokal yang belum mendukung routing native IPv6.
- **Isolasi Schema:** Pertimbangkan schema PostgreSQL khusus (misal `app` atau schema yang tidak otomatis dipublikasikan ke Supabase Data API) agar tabel internal Laravel tidak terekspos langsung ke browser tanpa RLS.
- Kredensial database disimpan secara aman di environment variables server-side (`.env` lokal / Render Environment) dan tidak pernah dikirim ke browser.

## 8. Status keputusan desain data (Fase 1)

- **Akun & Peran:** Fokus pada Guru dan Admin; satu akun terikat profil guru atau admin.
- **Status Sesi & Jurnal:** Berstatus `DRAFT` atau `SUBMITTED` (langsung final submitted oleh guru tanpa tahap verifikasi bertingkat).
- **Status Training/DW Siswa:** Nilai enum `TRAINING` dan `DAILY_WORKER` dipilih manual oleh guru pengajar pada saat sesi kelas berlangsung.
- **Entitas Notifikasi:** Menambahkan entitas `notifications` (atau Laravel standard notifications table) untuk mengirimkan pemberitahuan ke akun guru saat terjadi resolusi konflik di mana catatan di server telah dibuat/diubah oleh admin.
- **Zona Waktu:** Semua field timestamp/date mengacu pada `Asia/Makassar` (WITA).

## 9. Gerbang persetujuan

Sebelum implementasi teknis (Fase 2), ERD dan struktur migration Laravel akan disiapkan berdasarkan keputusan di atas. Jangan menulis SQL, migration, atau seeding sampai izin Fase 2 diberikan.
