# Threat Model — Aplikasi Absensi SMK

**Status:** DRAFT — BELUM DISETUJUI

## 1. Aset

- Akun, password hash, sesi, token, dan secret deployment.
- Identitas siswa/guru/pegawai dan riwayat kelas/penugasan.
- Absensi individual siswa dan status Training/DW.
- Jurnal materi, jadwal, agenda harian, kehadiran pegawai.
- Laporan harian, file PDF/Excel, draf WhatsApp.
- Audit logs, database backups, file impor.

## 2. Batas kepercayaan

- Browser pengguna.
- Backend Laravel.
- PostgreSQL Supabase.
- File impor/ekspor.
- Git/repository dan agent coding.
- Hosting/CI pipeline.
- WhatsApp yang dibuka melalui tautan (di luar kontrol aplikasi setelah berpindah ke WhatsApp).

## 3. Ancaman dan mitigasi yang direncanakan

| Ancaman | Dampak | Kontrol wajib |
|---|---|---|
| Guru memanipulasi ID untuk mengakses kelas lain | Kebocoran/ubah absensi | Otorisasi objek di backend, tes akses negatif |
| Eskalasi guru ke admin | Perubahan data luas | Role management server-side, larangan percaya role dari client |
| Credential masuk Git/log | Pengambilalihan layanan | `.env` di-ignore, redaksi log, secret scanning/rotasi |
| SQL injection/validasi lemah | Kerusakan/kebocoran data | Query berparameter/ORM aman, validasi server |
| XSS/HTML injection di materi atau alasan | Eksekusi script/penipuan | Output encoding kontekstual dan sanitasi sesuai kebutuhan |
| Export bypass filter | Kebocoran laporan lintas kelas | Otorisasi dan filter di endpoint ekspor |
| Spreadsheet formula injection | Formula berbahaya pada file | Escaping/sanitizing nilai teks sebelum Excel |
| Data kosong dianggap Alpa/tidak hadir | Laporan salah dan kerugian operasional | Status belum diisi terpisah; data completeness gate |
| Training/DW salah dihitung hadir/Alpa | Rekap salah | Enum/status eksplisit dan tes rekap |
| Kehadiran/agendas/jurnal tercampur | Laporan operasional salah | Model data dan workflow terpisah |
| Alasan medis tersebar di laporan luas | Privasi | Minimisasi data, akses terbatas, template yang disetujui |
| Import berbahaya/duplikat | Data rusak | Size limit, schema validation, preview, idempotency strategy, audit |
| Free DB dijeda/kuota penuh | Aplikasi tidak tersedia | Backup independen, monitoring limit, prosedur restore/upgrade |
| Pesan dianggap terkirim padahal hanya membuka WhatsApp | Salah komunikasi | Status draf dibedakan dari status terkirim; kirim dilakukan pengguna |
| Kehilangan data akibat migrasi | Gangguan layanan | Backup, migration review, transaksi/rollback plan, approval |

## 4. Kebijakan data

- Gunakan data sintetis selama pengembangan.
- Minimalkan data personal yang disimpan/diambil.
- Jangan memasukkan data medis rinci dalam laporan grup kecuali kebijakan sekolah menyatakan perlu dan audiensnya berhak.
- Jangan mengirim data siswa ke layanan pihak ketiga tanpa evaluasi dan persetujuan.
- Tentukan retensi untuk audit, file impor, ekspor, dan backup.

## 5. Pengujian keamanan wajib

- Tanpa login → operasi terproteksi ditolak.
- Guru A → tidak dapat membaca/ubah kelas guru B.
- Manipulasi user_id/role/status pada payload → tidak dapat meningkatkan hak akses.
- Export dengan filter palsu → tetap tidak melampaui hak akses.
- Input formula/spreadsheet dan teks HTML → ditangani aman.
- Secret tidak terlihat dalam response/log.
- Import yang invalid → ditolak atau dilaporkan per baris tanpa kerusakan parsial tak diketahui.
- Laporan WhatsApp/CSV/PDF tidak memasukkan field yang dilarang.

## 6. Batasan

Ini belum merupakan audit keamanan teknis karena belum ada aplikasi kode. Setelah implementasi, lakukan review berbasis kode dan tes sesuai skill Application Security Engineer. Jangan menyatakan sistem aman sebelum bukti tes tersedia.


## 7. Risiko supply-chain AI skill dan pencurian token

Skill pihak ketiga, `AGENTS.md`, README, referensi, scripts, hooks, dan konfigurasi plugin dianggap input tidak tepercaya sampai ditinjau. Instruksi berbentuk Markdown dapat mendorong agent yang punya akses shell/file/network untuk melakukan tindakan yang tidak diinginkan.

Kontrol wajib:
- Review provenance, license, isi skill, referensi, script, hook, package manager files, workflows CI, dan perubahan upstream sebelum digunakan.
- Jangan menjalankan command/installer yang tidak dipahami atau script obfuscated.
- Jangan menampilkan atau mengirim `.env`, API key, password, cookie, database URL, Supabase secret/service-role key atau isi file privat ke endpoint luar.
- Jangan memakai credential pengguna atau memanggil API berbayar hanya karena skill menyuruhnya.
- Catat versi/commit skill pihak ketiga yang telah ditinjau.
- Gunakan Git secret scanning dan push protection bila tersedia, tanpa menganggapnya sempurna.
- Jika secret terungkap, redaksi nilainya, revoke/rotate melalui provider, periksa usage/audit logs, lalu bersihkan history dengan persetujuan dan rencana.
- Gunakan skill `.agents/skills/security-supply-chain-guardian/SKILL.md` untuk pekerjaan terkait.

## 8. Keamanan PHP runtime dan dependency

Per 29 Oktober 2026, arsip resmi PHP menyatakan PHP 8.5.12, 8.4.27, 8.3.36 dan 8.2.35 memperbaiki CVE-2026-109618 dan CVE-2026-109619 yang dapat memungkinkan unauthenticated RCE. Pada 26 Oktober diumumkan paket Windows rebuilt terkait Brotli/CVE-2026-111497; pada 23 Oktober ada advisory session upload progress yang harus dicek terhadap release terbaru. Selalu periksa ulang advisori aktual untuk runtime/vendor target.
- PHP archive: https://www.php.net/archive/2026.php
- PHP advisories: https://github.com/php/php-src/security/advisories
- Composer audit: https://getcomposer.org/doc/03-cli.md
- Laravel deployment: https://laravel.com/docs/13.x/deployment

Rilis tidak boleh dilakukan dengan runtime yang rentan diketahui tanpa keputusan risiko eksplisit dan mitigasi yang ditinjau. Patch permanen lebih diutamakan daripada mitigasi konfigurasi sementara. Jangan membuat perubahan produksi otomatis.


## 9. Risiko mode offline dan sinkronisasi

| Ancaman | Dampak | Kontrol |
|---|---|---|
| Perangkat bersama menampilkan roster/data user sebelumnya | Kebocoran privasi siswa | Data lokal minimum, user binding, lock/logout flow, jangan tampilkan data akun lain |
| Browser storage dibersihkan/perangkat hilang | Catatan offline hilang | Jelaskan batas storage, sync saat sinyal tersedia, backup data tidak mengandalkan browser |
| Request dikirim ulang setelah response hilang | Absensi ganda | Idempotency key dan unique constraint server-side |
| Hak akses berubah sebelum sync | Penulisan tanpa izin | Re-authorization di server saat sync, item ditolak/konflik secara eksplisit |
| Roster/jadwal sudah basi | Absensi sesi/anggota keliru | timestamp sinkronisasi, stale-data warning, server-side validation |
| XSS/third-party script membaca IndexedDB | Kebocoran data lokal | Minimalkan data, hindari script pihak ketiga, CSP/output encoding, dependency review |
| Logout menghapus antrean pending | Kehilangan data | warning/flow pending queue, jangan auto-delete tanpa konfirmasi |
| Admin membuat laporan sebelum sync selesai | Laporan sekolah tidak lengkap | sync status per user/sesi dan banner completeness |
| Background Sync tidak didukung browser | Record menunggu lama | manual sync dan retry saat app aktif/online |

IndexedDB/browser storage tidak boleh diklaim terenkripsi secara default. Retensi lokal, perangkat target, prosedur pengguna bersama dan konflik harus disetujui sebelum fitur offline dibangun.
