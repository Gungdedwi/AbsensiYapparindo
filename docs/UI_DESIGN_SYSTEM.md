# UI Design System — Aplikasi Absensi SMK

**Status:** DRAFT — BELUM DISETUJUI. Ini adalah arah awal, bukan palet/komponen final.

## 1. Prinsip desain

- Prioritas utama: cepat dibaca dan dioperasikan guru/admin saat bekerja, bukan sekadar tampak dekoratif.
- Mobile-first untuk absensi dan formulir; dashboard admin harus nyaman pada laptop dan tablet.
- Tabel dan filter menjadi komponen utama. Jangan memaksa seluruh konten ke dalam kartu.
- Gunakan hierarki informasi yang jelas, spacing konsisten, dan gaya antarmuka yang sesuai dengan konteks sekolah.
- Hindari pola generik AI: gradien ungu/biru tanpa alasan, semua elemen berupa kartu membulat, ikon dekoratif berlebihan, animasi masuk pada semua elemen, teks abu-abu berkontras rendah, font yang dipilih default tanpa pertimbangan, serta grafik yang tidak membantu keputusan.
- Desain harus tetap sederhana dan jelas ketika data kosong, loading, gagal, atau banyak baris.

## 2. Arah visual sementara

- Arah: profesional, tenang, fokus pada data, berkarakter namun tidak kaku.
- Warna permukaan: latar netral terang atau netral gelap sesuai pengujian keterbacaan; pilih satu mode utama terlebih dahulu.
- Warna aksen: satu warna identitas utama yang konsisten. Jangan pilih warna sekolah seolah-olah sudah diketahui; minta logo/palet bila ada.
- Status:
  - sukses/terverifikasi;
  - peringatan/perlu tindak lanjut;
  - error/ditolak;
  - informasi/netral;
  - belum diisi.
  Jangan menggunakan warna saja untuk membedakan status; selalu tambahkan teks/ikon/label.
- Tipografi: font UI dengan keterbacaan yang baik dan dukungan Bahasa Indonesia; hirarki ukuran yang terukur.
- Ikon: satu keluarga ikon, hanya untuk membantu pemahaman.
- Motion: singkat dan bermakna; hormati `prefers-reduced-motion`.

## 3. Layar utama untuk dirancang

1. Login.
2. Dashboard admin.
3. Dashboard guru.
4. Data master dan pratinjau impor.
5. Jadwal serta daftar sesi aktual.
6. Form absensi siswa per sesi.
7. Jurnal/materi mengajar.
8. Agenda harian guru/pegawai.
9. Pencatatan kehadiran pegawai.
10. Daftar jam/jurnal yang belum lengkap atau berkondisi khusus.
11. Laporan dan pratinjau PDF/Excel.
12. Generator/pratinjau WhatsApp dengan bagian I/II/III.
13. Kelola pengguna/hak akses bila berada dalam cakupan.

## 4. Perilaku komponen yang wajib dirancang

- Loading, sukses, error, empty state, akses ditolak, sesi habis.
- Konfirmasi ketika meninggalkan form yang belum disimpan.
- Filter dengan indikator yang aktif dan reset filter.
- Validasi di dekat field yang bermasalah.
- Aksi destruktif/berdampak besar memerlukan konfirmasi.
- Tabel pada ponsel harus mempunyai strategi yang jelas (scroll horizontal, row detail, atau list); jangan menyusutkan teks sampai tak terbaca.
- Absensi harus mudah dipakai dengan sentuhan dan navigasi keyboard.
- Ringkasan hitungan harus menjelaskan arti tiap kategori dan jumlah yang belum diisi.
- Aksesibilitas keyboard, label form, fokus terlihat, kontras WCAG yang sesuai, serta semantik tombol/link harus diperiksa.

## 5. Skill referensi desain pihak ketiga

Kandidat: `https://github.com/arham777/ui-ux-kit`.

- Gunakan hanya jika salinan repo/skill sudah tersedia dan isi/lisensinya diperiksa.
- Gunakan untuk mengarahkan pencarian referensi nyata, menghindari pola generik, menyusun satu konsep visual, menguji kontras dan interaksi, serta membuat design system.
- Skill ini bukan pengganti spesifikasi sekolah atau validasi pengguna.
- Jangan menyalin desain website pihak ketiga secara utuh. Ambil prinsip dan pola yang relevan kemudian susun desain sesuai aplikasi SMK.
- Pertahankan aksesibilitas, kinerja, dan keterbacaan meskipun referensi visual menampilkan efek yang lebih kompleks.

## 6. Output fase desain

- `docs/UI_DESIGN_SYSTEM.md` dengan nilai token yang final.
- Referensi dan alasan pemilihannya.
- Wireframe/deskripsi tiap layar.
- Komponen serta states.
- Breakpoint responsif.
- Pemeriksaan aksesibilitas.
- Checklist anti-generik yang konkret.
- Mockup atau gambar referensi jika diminta/disetujui.
- Review pengguna sebelum frontend diimplementasikan.


## 7. Status koneksi dan sinkronisasi

Mode offline membutuhkan desain khusus; jangan hanya menampilkan toast kecil.
- Indikator Online/Offline terlihat tetapi tidak mendominasi.
- Waktu terakhir sinkron ditampilkan di layar kerja yang relevan.
- Record lokal yang belum dikirim memakai label **Tersimpan di perangkat — belum ke server**.
- Antrean pending menampilkan jumlah item dan tombol **Sinkronkan sekarang**.
- Konflik dan penolakan menampilkan tindakan yang harus dilakukan, bukan hanya warna merah.
- Draft yang belum sinkron tidak boleh berstatus "Berhasil dikirim".
- Jika data jadwal/roster terlalu lama, beri peringatan yang menjelaskan bahwa data perlu diperbarui saat online.
- Saat logout dan terdapat data pending, tampilkan konfirmasi yang tidak menyesatkan dan jangan membuang data tanpa keputusan pengguna.
- Pada halaman admin/report, beri banner data belum lengkap bila ada sync tertunda yang relevan.
