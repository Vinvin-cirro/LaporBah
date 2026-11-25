# Update LaporBah - Manajemen Tim Officer

## Fitur yang ditambahkan
- Governance/Superadmin dapat membuat, mengubah, mengaktifkan, dan menonaktifkan tim.
- Satu tim berisi satu atau lebih akun dengan role `officer`.
- Satu Officer dapat menjadi anggota beberapa tim sekaligus.
- Penugasan laporan baru diberikan kepada satu tim aktif.
- Semua anggota tim dapat melihat dan menangani penugasan tim dari dashboard, daftar tugas, dan detail tugas Officer.
- Riwayat assignment lama yang masih menggunakan satu `officer_id` tetap didukung.

## Cara memasang
1. Backup database dan folder project terlebih dahulu.
2. Ekstrak isi folder `LaporBah` dari ZIP ke folder project yang sedang digunakan (pilih merge/replace file, jangan hapus `.env`, `vendor`, atau file upload lokal).
3. Jalankan dari terminal pada folder project:

   ```bash
   php artisan migrate
   php artisan optimize:clear
   ```

4. Login sebagai Governance atau Superadmin.
5. Buka menu **Manajemen Tim**, buat tim, lalu pilih anggota Officer.
6. Buka detail laporan yang sudah diverifikasi dan pilih tim pada bagian penugasan.

## Catatan kompatibilitas
- Migration baru: `2026_11_26_000001_create_teams_and_link_report_assignments.php`.
- `report_assignments.officer_id` lama tetap dipertahankan sebagai petugas representatif untuk kompatibilitas data lama. Assignment tim memakai `team_id` sebagai relasi utama.
- Assignment lama tanpa `team_id` tetap dapat dilihat Officer yang ditugaskan sebelumnya.
- Menonaktifkan tim mencegah tim dipilih untuk penugasan baru, tetapi tidak menghapus histori atau menghentikan akses anggota pada tugas aktif.

## Pengujian
Ditambahkan `tests/Feature/TeamAssignmentTest.php` untuk menguji keanggotaan multi-tim dan akses tugas oleh anggota tim. Pemeriksaan sintaks PHP pada file yang diubah berhasil. Eksekusi test suite belum dapat dilakukan pada lingkungan pembuat ZIP karena ekstensi PHP `dom`, `mbstring`, `xml` dan driver PDO database tidak tersedia; jalankan `php artisan test` pada lingkungan Laravel lokal yang lengkap.


## Inspeksi sebelum verifikasi (PJU Rusak & Area Gelap)
- Untuk laporan `pju_rusak` dan `area_gelap` yang masih `pending`, Governance dapat memilih verifikasi langsung atau membuat penugasan bertipe `inspection` kepada tim Officer.
- Penugasan inspeksi tidak mengubah status laporan warga; laporan tetap `pending` sampai Governance meninjau hasilnya.
- Officer wajib menyimpan catatan pemeriksaan dan mengunggah minimal satu foto sebelum mengirim hasil inspeksi.
- Governance dapat menyetujui hasil (laporan menjadi `verified`), menolak laporan, atau meminta pemeriksaan ulang.
- Penugasan setelah laporan terverifikasi tetap menggunakan tipe `handling` dan workflow penanganan yang sudah ada.
- Inspeksi pra-verifikasi tidak tersedia untuk kategori `begal_curas` dan `lain_lain`.
- Migration tambahan: `2026_11_26_000002_add_assignment_type_to_report_assignments.php`.
