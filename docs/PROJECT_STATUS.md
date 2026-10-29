# Project Status

**Status:** Fase perencanaan awal — belum disetujui untuk implementasi  
**Tanggal:** 29 Oktober 2026

## Yang tersedia

- `PROMPT.md`: prompt utama proyek.
- `AGENTS.md`: aturan singkat untuk agent proyek.
- Delapan skill spesialis direncanakan pada `.agents/skills/`.
- Paket dokumen perencanaan dalam `docs/`.

## Dokumen yang disusun dalam paket ini

- `PROJECT_BRIEF.md`
- `SRS.md`
- `WORKFLOWS.md`
- `DATABASE_DESIGN.md`
- `ROLE_PERMISSION_MATRIX.md`
- `WHATSAPP_REPORT_SPEC.md`
- `IMPORT_EXPORT_SPEC.md`
- `UI_DESIGN_SYSTEM.md`
- `DESIGN_REFERENCE_SKILLS.md`
- `SECURITY_THREAT_MODEL.md`
- `TEST_PLAN.md`
- `DEPLOYMENT_OPTIONS.md`
- `RENDER_DEPLOYMENT.md`
- `OFFLINE_MODE_SPEC.md`
- `DECISIONS.md`

## Batasan pekerjaan saat ini

- Tidak ada source code aplikasi dibuat.
- Tidak ada dependency dipasang.
- Tidak ada database/schema Supabase dibuat atau diubah.
- Tidak ada data sekolah yang diimpor.
- Tidak ada deployment dilakukan.
- Dokumen masih draf untuk ditinjau dan disetujui pengguna.

## Kebutuhan konektivitas terbaru

Sekolah memiliki sinyal lemah/intermiten. `OFFLINE_MODE_SPEC.md` telah ditambahkan sebagai rancangan PWA/offline dan sinkronisasi tertunda. Cakupan offline, retensi data lokal, konflik, dan browser target masih menunggu persetujuan. Tidak ada kode offline diimplementasikan.

## Kebutuhan offline terbaru

Kebutuhan sinyal lemah dicatat pada `OFFLINE_MODE_SPEC.md`. Rekomendasi: PWA/offline-first untuk absensi siswa dan jurnal/materi, dengan sinkronisasi tertunda. Cakupan, retensi data lokal, target browser dan aturan konflik masih menunggu persetujuan. Belum ada kode offline diimplementasikan.

## Langkah berikutnya

1. Letakkan paket di root repository proyek.
2. Pastikan `PROMPT.md` dan delapan skill tersedia di struktur yang benar.
3. Review dokumen draf.
4. Putuskan pertanyaan yang terbuka.
5. Minta AI melakukan revisi dokumen yang diperlukan.
6. Berikan persetujuan eksplisit untuk rancangan final sebelum coding atau perubahan database.
