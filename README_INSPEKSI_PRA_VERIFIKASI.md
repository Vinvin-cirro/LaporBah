# LaporBah - Inspeksi Sebelum Verifikasi

Patch ini menggabungkan basis `LaporBah(20261125-172722).zip` dengan pembaruan tim Officer `LaporBah_Tim_Officer.zip`, lalu menambahkan workflow inspeksi pra-verifikasi.

## Cakupan
- Hanya kategori `pju_rusak` dan `area_gelap` yang dapat diberi tugas inspeksi saat laporan masih `pending`.
- Governance tetap dapat memilih verifikasi langsung dari halaman detail laporan.
- Jika informasi laporan ambigu, Governance dapat menugaskan tim Officer untuk memeriksa lokasi.
- Status laporan warga tetap `pending` selama inspeksi.
- Officer menerima tugas, menyimpan catatan pemeriksaan, mengunggah minimal satu foto, lalu mengirim hasil inspeksi.
- Governance dapat menyetujui hasil (status laporan menjadi `verified`), menolak laporan, atau meminta pemeriksaan ulang.
- Setelah laporan diverifikasi, penugasan penanganan/perbaikan menggunakan workflow `handling` yang sudah ada.
- `begal_curas` dan `lain_lain` tidak mendapatkan opsi inspeksi pra-verifikasi.

## Pemasangan
1. Backup project dan database.
2. Ekstrak folder `LaporBah` dari ZIP ini ke folder project, lalu pilih merge/replace file.
3. Jangan mengganti `.env`, `vendor`, `node_modules`, atau file upload lokal.
4. Jalankan:

   ```bash
   php artisan migrate
   php artisan optimize:clear
   ```

5. Jalankan pengujian di lingkungan PHP yang memiliki ekstensi PHPUnit lengkap:

   ```bash
   php artisan test --filter=InspectionAssignmentTest
   ```

## Migration baru
`database/migrations/2026_11_26_000002_add_assignment_type_to_report_assignments.php`

Kolom `assignment_type` memiliki default `handling`, sehingga assignment lama tetap kompatibel. Tipe baru `inspection` menandai pemeriksaan sebelum laporan diverifikasi.

## Catatan pengujian
Pemeriksaan sintaks PHP pada controller, model, service, migration, route, dan test yang diubah berhasil. Eksekusi PHPUnit dan kompilasi Blade tidak dapat dijalankan di lingkungan pembuat ZIP karena ekstensi PHP `dom`, `mbstring`, dan `xmlwriter` tidak tersedia. Jalankan test dan buka halaman Governance/Officer di lingkungan Laravel lokal setelah pemasangan.
