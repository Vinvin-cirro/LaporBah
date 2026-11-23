# Pembaruan Kategori LaporBah

Patch ini memperbarui kategori laporan menjadi empat nilai kanonis:

| Nilai database | Label |
| --- | --- |
| `pju_rusak` | PJU Rusak |
| `area_gelap` | Area Gelap |
| `begal_curas` | Begal/Curas |
| `lain_lain` | Lain-lain |

## Cara memasang

1. **Backup database** LaporBah terlebih dahulu.
2. Ekstrak seluruh isi ZIP ini ke folder utama proyek Laravel (folder yang berisi `artisan`). Pilih **overwrite/replace** jika diminta.
3. Buka terminal di folder proyek, lalu jalankan:

   ```bash
   php artisan migrate
   php artisan optimize:clear
   ```

4. Jalankan pengujian jika dependency proyek sudah terpasang:

   ```bash
   php artisan test
   ```

## Keamanan data

Migration baru hanya memperbarui nilai kolom `reports.category` yang lama:

- `begal` → `begal_curas`
- `dark` → `area_gelap`

Migration tidak menghapus, membuat ulang, atau mengganti ID laporan. Kolom lain seperti deskripsi, lokasi, koordinat, foto, status, tracking code, dan riwayat penugasan tidak disentuh. Tabel `reports` sudah menggunakan kolom string untuk kategori, jadi tidak diperlukan perubahan struktur tabel.

Jangan jalankan `php artisan migrate:fresh` atau menghapus database. Perintah tersebut dapat menghapus seluruh data aplikasi.

## File yang diperbarui

- `app/Models/Report.php`
- Controller submission, dashboard Citizen, dashboard Governance, daftar laporan, peta monitoring, dan statistik Governance
- Blade form laporan, detail/tracking, dashboard Citizen, dashboard Governance, daftar/detail laporan Governance, statistik, dan peta monitoring
- `database/seeders/DemoReportSeeder.php`
- Test feature terkait kategori laporan
- Migration baru untuk mapping nilai kategori lama

File `.env`, database aktif, `vendor`, `node_modules`, dan `.git` tidak disertakan dalam patch.
