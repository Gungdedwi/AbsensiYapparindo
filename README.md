# Paket Perencanaan Aplikasi Absensi SMK

Paket ini berisi `AGENTS.md` dan dokumen perencanaan awal. Semua spesifikasi dalam `docs/` berstatus DRAFT dan belum merupakan persetujuan implementasi.

## Memasang
Ekstrak isi ZIP ke direktori root repository bersama `PROMPT.md` milik pengguna. Jika sudah ada `AGENTS.md` atau file dengan nama sama, tinjau perbedaan sebelum menimpa.

## Isi
- `AGENTS.md`: instruksi ringkas untuk agent, pemilihan skill, approval gate, aturan minimal-code, dan path skill pihak ketiga.
- `docs/`: project brief, SRS, workflows, desain database konseptual, hak akses, spesifikasi WhatsApp, impor/ekspor, UI design system, referensi skill desain, threat model, test plan, opsi deployment, keputusan, dan status proyek.

## Skill yang dirujuk
Delapan skill proyek ditambah `minimal-code-engineering` dan optional `.agents/skills/ui-ux-kit/`. Skill harus benar-benar tersedia sebelum agent mengaku membacanya.

## Batasan
Paket ini tidak berisi source code aplikasi, migration, SQL yang dijalankan, credential, koneksi Supabase aktif, data siswa nyata, atau deployment. AI harus meminta persetujuan eksplisit setelah rancangan ditinjau.
