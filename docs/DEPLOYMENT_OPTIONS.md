# Opsi Hosting dan Operasional

**Status:** DRAFT — BELUM DISETUJUI  
**Pembaruan:** 29 Oktober 2026

## 1. Keputusan saat ini

Pengguna telah membuat akun Render dan memilih **Render Free Web Service + Docker sebagai target awal prototipe/pengujian**. Ini belum merupakan izin untuk membuat service atau melakukan deploy. Hosting produksi masih belum diputuskan.

- Repository: https://github.com/Gungdedwi/AbsensiYapparindo
- Branch: `main`
- Status repository ketika diperiksa: publik dan kosong.
- Runtime aplikasi: Laravel/PHP; karena Render tidak menyediakan runtime native PHP, penggunaan Docker adalah jalur yang perlu dievaluasi.

Detail teknis ada di `docs/RENDER_DEPLOYMENT.md`.

## 2. Batas Render Free

Render secara eksplisit menyatakan Free instances tidak untuk aplikasi produksi. Batas penting yang tercatat pada halaman resmi:
- web service spin down setelah 15 menit tanpa traffic dan bangun sekitar satu menit ketika menerima request;
- filesystem ephemeral, data file lokal hilang pada restart/redeploy/spin-down;
- 750 instance hours per workspace per bulan dan batas bandwidth/build pipeline;
- tidak mendukung persistent disk, dashboard shell/SSH, one-off jobs, scaling melebihi satu instance, atau inbound private network;
- batas penggunaan dapat menangguhkan layanan/build sesuai kondisi workspace.

Sumber resmi:
- https://render.com/docs/free
- https://render.com/docs/web-services
- https://render.com/docs/docker
- https://render.com/docs/configure-environment-variables
- https://render.com/docs/health-checks

Jangan menyebut free service selalu aktif atau sesuai produksi tanpa mitigasi dan keputusan eksplisit.

## 3. Arsitektur yang diusulkan

```text
Browser guru/admin
   | HTTPS
   v
Render Web Service — Docker (Laravel + PHP web runtime)
   | PostgreSQL over TLS
   v
Supabase PostgreSQL
```

GitHub menyimpan source code, tidak menjalankan backend Laravel. GitHub Pages cocok untuk situs statis, bukan Laravel server-side.

## 4. Docker, source, dan deployment

- Gunakan Docker runtime dan `Dockerfile` yang ditinjau.
- Server web mendengarkan pada `0.0.0.0` dan port Render yang dikonfigurasi (default web port biasanya 10000; cek lagi saat implementasi).
- Hubungkan GitHub melalui Git Provider integration jika auto-deploy/PR preview dibutuhkan; gunakan branch `main` setelah tes dan persetujuan.
- Jangan menghubungkan akun, membuat service, atau deploy hanya karena akun telah dibuat.
- Simpan environment secrets di Render Dashboard; jangan commit ke Git, `render.yaml`, Docker image, atau log.
- Jangan menyalin `.env` ke image.
- Jangan mengeksekusi `php artisan migrate --force` otomatis pada setiap container startup.
- Health check harus aman dan tidak membocorkan informasi.
- Periksa patch PHP aktual dan security advisories, `composer.lock`, dan hasil `composer audit --locked`.

Render Laravel Docker guide yang tersedia menggunakan Laravel 11 sebagai contoh. Gunakan sebagai pengarah pola Docker saja, bukan file dependency yang langsung dicopy untuk Laravel 13:
- https://render.com/docs/deploy-php-laravel-docker

## 5. Supabase connection

Pilih mode koneksi dari Supabase Dashboard. Menurut dokumentasi resmi:
- Direct connection cocok untuk backend persistent jika IPv6 tersedia atau IPv4 add-on sesuai paket.
- Shared Pooler Session Mode dapat dipakai jika hosting hanya memiliki akses IPv4.
- Transaction Mode lebih cocok untuk fungsi serverless/edge serta mempunyai keterbatasan.

Pastikan SSL/TLS diaktifkan; preferensikan verifikasi sertifikat server jika didukung oleh driver. Jangan membangun host/username pooler dengan tebakan dan jangan mengekspos Supabase secret/service-role key ke browser.

Sumber:
- https://supabase.com/docs/guides/database/connecting-to-postgres

## 6. Secrets dan environment

Gunakan Render Environment untuk `APP_KEY`, `APP_URL`, `APP_ENV`, `APP_DEBUG=false`, logging, dan credential database sesuai konfigurasi Laravel. Nama/format variabel harus diverifikasi terhadap aplikasi yang benar-benar dibuat. Jangan tempel nilai secret di chat.

Render docs:
- https://render.com/docs/configure-environment-variables

## 7. Migration dan data permanen

- Tidak ada SSH/shell Dashboard atau one-off jobs pada Render Free; rancangkan migrasi dari lingkungan tepercaya atau workflow yang disetujui.
- Jalankan migrasi hanya dengan persetujuan, setelah backup/rollback plan dan review.
- Jangan melakukan migrasi destruktif tanpa backup.
- Data inti tinggal di Supabase, bukan filesystem Render.
- File upload/arsip permanen memerlukan storage persisten yang disetujui (misalnya Supabase Storage privat).
- Uji restore backup sebelum aplikasi digunakan secara operasional.

## 8. Kapan perlu mempertimbangkan hosting lain

Evaluasi hosting berbayar atau alternatif jika sekolah membutuhkan:
- tanpa cold start;
- layanan aktif sepanjang jam operasional;
- backup terjadwal dan restore yang jelas;
- worker/scheduler/one-off tasks;
- penyimpanan file persisten;
- kapasitas/availability dan kontrol akses operasional lebih tinggi.

Bandingkan kebutuhan biaya penuh; jangan mengklaim total biaya nol tanpa mengevaluasi domain, backup, storage, build, bandwidth, dan batas provider.

## 9. Urutan aman

1. Selesaikan dan setujui dokumen rancangan.
2. Implementasikan aplikasi dan tes secara lokal.
3. Siapkan Dockerfile/.dockerignore dan uji build.
4. Tinjau security/dependency/runtime dan cek secret scan.
5. Putuskan koneksi Supabase dan migration workflow.
6. Minta izin untuk menghubungkan GitHub/Render dan menaruh secrets.
7. Deploy sebagai prototipe hanya setelah persetujuan.
8. Jalankan smoke tests, verifikasi akses, dan laporkan URL/commit/batas free.
9. Jangan gunakan data sekolah nyata sampai keputusan produksi, backup dan pemulihan disetujui.

## 10. Produksi

Render Free hanya untuk prototipe/demo. Sebelum operasional sekolah, pilih hosting produksi dan dokumentasikan RTO/RPO, backup/restore, logging/monitoring, respons insiden, patching, dan biaya.
