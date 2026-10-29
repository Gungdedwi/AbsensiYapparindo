# Render Deployment Plan — Laravel + Supabase PostgreSQL

**Status:** DRAFT — BELUM DISETUJUI.  
**Tujuan:** prototipe/testing awal. Bukan persetujuan membuat service atau melakukan deployment.

## 1. Keputusan saat ini

- Pengguna sudah memiliki akun Render.
- Hosting prototipe yang dipilih untuk dievaluasi: **Render Free Web Service dengan Docker**.
- Repo kode: https://github.com/Gungdedwi/AbsensiYapparindo
- Branch awal: `main`.
- Repo terdeteksi publik dan kosong pada pemeriksaan 29 Oktober 2026. Jangan menganggap status ini tetap sama tanpa memeriksa ulang.
- Database: Supabase PostgreSQL; credential tetap di server-side secrets.
- Hosting produksi belum diputuskan.
- Belum ada service Render yang dibuat oleh proses ini; tidak ada deployment dilakukan.

## 2. Kenapa Docker

Render mendukung PHP/Laravel melalui Docker. Runtime native Render terutama berfokus pada Node.js, Python, Ruby, Go, Rust, dan Elixir; untuk PHP, gunakan Dockerfile. Panduan resmi Render Laravel yang tersedia saat penulisan menggunakan Laravel 11 sebagai contoh, sehingga file contoh tidak boleh ditempel mentah untuk proyek Laravel 13. Gunakan pola arsitektur, bukan versi dependency yang sudah usang.

Sumber resmi:
- Render Docker: https://render.com/docs/docker
- Render Laravel + Docker: https://render.com/docs/deploy-php-laravel-docker
- Render Web Services: https://render.com/docs/web-services

## 3. Susunan layanan

```text
Browser Guru/Admin
       |
       | HTTPS
       v
Render Web Service (Docker: Laravel + PHP-FPM + Nginx atau runtime PHP web yang ditinjau)
       |
       | PostgreSQL TLS
       v
Supabase PostgreSQL
```

GitHub menyimpan source code dan riwayat perubahan; bukan runtime PHP. GitHub Pages bukan hosting yang sesuai untuk aplikasi Laravel penuh.

## 4. Konfigurasi Docker yang akan disiapkan setelah approval

Ketika pengguna menyetujui implementasi, rencanakan file:
- `Dockerfile`
- `.dockerignore`
- konfigurasi web server/runtime yang diperlukan
- entrypoint/start script bila memang dibutuhkan
- endpoint health-check yang aman
- dokumentasi deployment serta proses migrasi

Ketentuan:
- Gunakan versi PHP stabil yang masih didukung Laravel dan sudah menerima security patch terbaru dari upstream/vendor.
- Pin base image pada branch/patch yang ditinjau; jangan menggunakan `latest` secara membabi buta.
- Audit OS packages dan Composer dependencies.
- Pisahkan build assets frontend dari runtime image jika membantu ukuran dan supply chain.
- Jalankan `composer install --no-dev --prefer-dist --optimize-autoloader` sebagai bagian build sesuai lockfile dan kebijakan Composer yang disetujui.
- `npm ci` dan `npm run build` hanya jika proyek memang memiliki asset pipeline yang memakai Node.
- Jangan copy `.env`, Git history, credential files, database dump, student/staff data, local logs, `vendor` atau `node_modules` dari host secara tidak terkontrol.
- `.dockerignore` harus mengecualikan `.env*` kecuali file contoh yang sudah di-redact, `.git`, test artifacts sensitif, logs, backup, serta data nyata.
- Jangan melakukan `chmod -R 777`.
- Container harus mendengarkan pada `0.0.0.0` dan port yang disediakan/dikonfigurasi Render (default web port Render biasanya `10000`; verifikasi ulang di dokumentasi/dashboard saat implementasi).
- Pastikan server web merutekan request ke `public/index.php`, bukan root repository.
- `APP_DEBUG=false` untuk layanan yang berjalan sebagai production environment, walaupun hanya prototipe yang dapat diakses internet.
- Pastikan HTTPS asset URLs dan proxy configuration berjalan benar.
- Health check hendaknya mengembalikan 2xx bila proses web siap, tidak membuka data privat, tidak memerlukan login, dan tidak mengungkap kredensial.
- Jangan menjalankan migrasi database otomatis di setiap startup container.

## 5. Render Dashboard fields

Setelah aplikasi dan tes siap dan deployment telah disetujui:

1. Klik **New → Web Service**.
2. Pilih repository lewat **Git Provider integration** setelah menghubungkan GitHub secara eksplisit. Metode memasukkan public repo URL berbeda dan tidak mendukung auto-deploy/pull request previews seperti integrasi Git Provider.
3. Pilih `Gungdedwi/AbsensiYapparindo` dan branch `main` setelah memverifikasi bahwa commit berisi aplikasi yang sudah disetujui.
4. Pilih runtime/language **Docker** dan pastikan Dockerfile berada di path yang ditentukan.
5. Pilih region setelah meninjau jarak ke Supabase dan jaringan; jangan menentukan region tanpa mengetahui region database.
6. Pilih **Free** untuk prototipe saja dan pastikan tidak ada add-on/plan berbayar yang dipilih tanpa izin.
7. Atur health-check path setelah route dibuat dan diuji.
8. Jangan aktifkan auto-deploy tanpa persetujuan terhadap kebijakan branch dan CI. Pilihan aman untuk awal: deploy manual setelah perubahan melewati tes/review.
9. Jangan menggunakan public preview yang membocorkan data sekolah atau mengaktifkan demo dengan kredensial default.

Dokumentasi:
- https://render.com/docs/your-first-deploy
- https://render.com/docs/web-services
- https://render.com/docs/health-checks

## 6. Environment variables/secrets

Isi nilainya hanya di **Render Dashboard → service → Environment** atau secret manager yang disetujui. Jangan meminta pengguna mengirim nilainya lewat chat dan jangan menulis nilai rahasia di `render.yaml`, Git, Dockerfile atau laporan.

Nama yang kemungkinan diperlukan (verifikasi sesuai `config/database.php`, versi Laravel, dan container):
- `APP_ENV`
- `APP_DEBUG`
- `APP_KEY`
- `APP_URL`
- `LOG_CHANNEL`
- `DB_CONNECTION`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` **atau** satu URL koneksi yang benar-benar didukung oleh konfigurasi Laravel proyek
- konfigurasi TLS/SSL PostgreSQL sesuai driver dan mode koneksi
- session/cache driver yang disetujui dan tidak bergantung pada filesystem ephemeral

Jangan membuat dua sumber konfigurasi database yang berkonflik. Render memiliki mekanisme environment variables/secrets yang terpisah dari source code:
- https://render.com/docs/configure-environment-variables

## 7. Menghubungkan ke Supabase

Ambil host, port, username, dan mode koneksi dari tombol **Connect** pada Supabase Dashboard. Jangan menebak pooler host atau merakit string secara manual.

Menurut panduan resmi Supabase:
- Direct connection cocok untuk backend persistent bila jalur IPv6 tersedia atau menggunakan dukungan IPv4 yang sesuai.
- Shared Pooler **Session Mode** dapat dipakai untuk jaringan IPv4-only.
- Shared Pooler Transaction Mode lebih ditujukan ke fungsi serverless/edge berumur singkat dan mempunyai keterbatasan session state/prepared statements.

Untuk Render, lakukan tes konektivitas dari environment yang sesuai setelah izin diberikan. Jika lingkungan service IPv4-only, evaluasi session pooler; jika Direct Connection dapat dijangkau dengan aman, bandingkan latensi/keterbatasannya. Gunakan TLS; lebih baik verifikasi sertifikat server sesuai kemampuan driver daripada menganggap sekadar enkripsi `require` selalu memverifikasi identitas server.

Sumber resmi:
- https://supabase.com/docs/guides/database/connecting-to-postgres

Jangan mengekspos Supabase secret/service-role key di browser. Dalam rancangan Laravel server-side, koneksi database sebaiknya memakai credential server-side yang terlindungi dan hak akses minimum. Jika Data API dipakai dari browser pada masa depan, desain dan uji RLS secara eksplisit.

## 8. Migrasi database: tidak otomatis pada startup

Render Free tidak menyediakan SSH/shell access dari Dashboard dan tidak mendukung one-off jobs. Maka, jangan bergantung pada akses shell produksi untuk migrations.

Sebelum migration dibuat, tentukan proses yang ditinjau, misalnya:
- menjalankan migration dari lingkungan pengembang tepercaya menggunakan koneksi database yang dapat dijangkau dan credential tersimpan lokal;
- atau workflow migration terkontrol yang disetujui dan menggunakan secret dengan hak minimum, review, log redacted, backup serta rollback plan.

Supabase merekomendasikan direct connection untuk migrations/native database tools; koneksi itu dapat membutuhkan IPv6 atau dukungan IPv4. Jika developer network tidak memiliki jalur yang sesuai, jangan mengambil jalan pintas yang tidak diverifikasi—tentukan opsi migrasi setelah mengecek konektivitas dan batas layanan.

Jangan:
- memasukkan `php artisan migrate --force` tanpa kondisi ke entrypoint yang berjalan setiap kali container start;
- menjalankan migration di production saat startup secara bersamaan dari beberapa instance;
- menjalankan perubahan schema destruktif tanpa backup, review, dan persetujuan.

Sumber:
- https://render.com/docs/free
- https://render.com/docs/one-off-jobs
- https://supabase.com/docs/guides/database/connecting-to-postgres

## 9. Filesystem, uploads, sessions, dan cache

Render Free filesystem ephemeral. Jangan menyimpan data penting secara permanen di disk container.

- File impor Excel/CSV boleh diproses sementara, lalu dibersihkan.
- File PDF/Excel yang baru dibuat dapat di-stream/diunduh; jangan menganggap file lokal akan tersedia setelah restart.
- Jika sekolah memerlukan arsip file, gunakan layanan penyimpanan permanen yang disetujui (contohnya Supabase Storage dengan bucket privat, authorization, dan lifecycle yang sesuai).
- Jangan menyimpan SQLite, backup database, foto siswa, atau file operasional penting pada filesystem service.
- Pilih session/cache driver yang sesuai dengan jumlah instance dan kebutuhan persistensi. Database-backed sessions/cache dapat menambah query dan harus diuji; jangan mengasumsikan local file cache/session bertahan setelah redeploy.
- Jangan menambah layanan berbayar hanya untuk menghilangkan batas tanpa persetujuan.

## 10. Batas Free dan kesiapan produksi

Per dokumentasi Render yang diperiksa 29 Oktober 2026:
- Render menyatakan Free instances tidak untuk aplikasi produksi.
- Free Web Service spin down setelah 15 menit idle dan membutuhkan kira-kira satu menit untuk bangun.
- Filesystem ephemeral; tidak ada persistent disk.
- 750 jam instance gratis per workspace/bulan; outbound bandwidth dan build minutes memiliki batas.
- Tidak tersedia SSH/dashboard shell, one-off jobs, scaling lebih dari satu instance, atau inbound private network untuk Free Web Service.
- Free instances dapat restart dan rollback terbatas.

Sumber: https://render.com/docs/free

Gunakan untuk prototipe/demo. Sebelum data sekolah sebenarnya digunakan, putuskan hosting produksi dan backup/restore yang memenuhi kebutuhan. Jangan menganggap membuat akun Render sudah membuat layanan atau mengaktifkan hosting.

## 11. PWA dan Service Worker

Jika offline mode disetujui, Render harus menyajikan PWA melalui HTTPS. Pastikan manifest, service worker, scope/path, cache version, dan route fallback diuji pada domain aktual. Cache hanya app shell/aset minimum; jangan cache response privat secara luas. Jangan menaruh credential atau data sekolah sensitif di static assets. PWA offline behavior tetap bekerja pada client/browser setelah aset di-cache, tetapi sync membutuhkan koneksi ke Render dan Supabase. Render Free dapat cold start setelah idle, sehingga sync retry harus bertahan menghadapi timeout tanpa duplikasi. Dokumentasi PWA: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Offline_and_background_operation

## 12. Checklist sebelum deployment prototipe

Sebelum deployment:
- [ ] Pengguna menyetujui rancangan dan deployment target.
- [ ] Source code ada di branch/repo yang benar dan tidak ada secret.
- [ ] `.gitignore` dan `.dockerignore` diperiksa.
- [ ] PHP patch/security advisories dan `composer audit --locked` telah diperiksa.
- [ ] Build Docker berhasil lokal/CI.
- [ ] Unit/integration tests dan security review yang relevan lulus.
- [ ] `APP_DEBUG=false`, HTTPS/asset URL benar, dan secret di Render Environment.
- [ ] Koneksi Supabase TLS berhasil menggunakan credential yang benar.
- [ ] Migration strategy disetujui dan tidak berjalan otomatis pada setiap startup.
- [ ] Health-check path tidak membocorkan informasi sensitif.
- [ ] File permanen tidak bergantung pada filesystem Render.
- [ ] Tidak ada akun/password demo yang lemah.
- [ ] Smoke tests mencakup login, akses antar-guru, laporan dan ekspor.
- [ ] Backup/restore serta status produksi diputuskan.
- [ ] Setelah deploy, URL, commit, tes, dan batasan disampaikan dengan jujur.

## 13. Keputusan yang masih terbuka

- Apakah Render Free hanya untuk prototipe atau ingin dipakai lebih lama tanpa data operasional?
- Region Supabase dan Render yang dipilih.
- Metode koneksi PostgreSQL berdasarkan jaringan aktual.
- Migration workflow.
- Apakah file permanen perlu disimpan di Supabase Storage.
- Session/cache driver.
- Apakah auto-deploy diaktifkan setelah CI.
- Hosting, backup, retensi, dan kebutuhan produksi.
