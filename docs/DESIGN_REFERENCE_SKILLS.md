# Referensi Skill Desain UI yang Menghindari AI-Slop

**Status:** kandidat riset; belum diinstal ke repository.

## Rekomendasi utama: ui-ux-kit

Repo: https://github.com/arham777/ui-ux-kit  
Skill: https://github.com/arham777/ui-ux-kit/blob/main/SKILL.md

Mengapa cocok:
- README menjelaskan cakupan dashboard/admin, app/product, mobile, dan design system.
- Mengarahkan agent mencari referensi desain nyata dan membuat satu konsep yang disengaja.
- Menggunakan checklist yang dapat diperiksa, bukan hanya instruksi “buat lebih bagus”.
- Memuat referensi yang dibaca sesuai kebutuhan, sehingga mendukung alur token-efficient.
- Menghasilkan `DESIGN_SYSTEM.md` sebagai sumber kebenaran untuk konsistensi halaman.
- Repo menyatakan lisensi MIT dan memberikan petunjuk penggunaan untuk Google Antigravity.

## Alternatif: unslop-ui-skill

Repo: https://github.com/claudiusararu/unslop-ui-skill

Cocok sebagai katalog anti-pattern generik yang spesifik, seperti pilihan font default, gradien ungu-biru yang tidak beralasan, komposisi berulang, dan kontras rendah. Dari deskripsi repository, fokusnya lebih kepada mendeteksi pola UI generik; untuk aplikasi ini, `ui-ux-kit` tampak lebih menyeluruh untuk alur referensi → design concept → design system.

## Cara menambahkan secara aman

Belum ada repo pihak ketiga yang diklon atau dipasang oleh dokumen ini. Sebelum pemasangan:
1. Tinjau README, seluruh `SKILL.md`, referensi yang akan dibaca agent, `LICENSE`, dan perubahan terbaru.
2. Pastikan versi yang dimasukkan ke proyek dicatat, agar hasil desain dapat direproduksi.
3. Simpan sebagai skill opsional terpisah tanpa menimpa `school-attendance-ui-ux-designer`.
4. Tambahkan aturan pemakaian pada `AGENTS.md` (aturan kondisional sudah disediakan).
5. Uji bahwa skill dapat membaca referensi yang ada, menghasilkan `DESIGN_SYSTEM.md`, dan tidak melewati persetujuan desain.
6. Jangan mengizinkan installer menjalankan perubahan yang tidak dipahami atau menyalin secret.

README upstream menyarankan clone ke `./ui-ux-kit` lalu merujuk `ui-ux-kit/SKILL.md` dari `AGENTS.md` atau workflow Antigravity. Ikuti instruksi upstream setelah meninjau struktur yang aktual. Jangan menganggap folder tersebut sudah tersedia sebelum memeriksanya.

## Prinsip pemakaian

Skill pihak ketiga adalah alat bantu. SRS, keputusan pengguna, aksesibilitas, kebutuhan sekolah, dan approval gate tetap menjadi sumber kebenaran. Jangan mengambil screenshot/asset berhak cipta dan menggunakannya sebagai UI final tanpa izin.
