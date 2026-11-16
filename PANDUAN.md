# Ruang3D

Aplikasi pencatatan keuangan usaha Print 3D berbasis Laravel dan MySQL.

## Menjalankan

Jalankan `php artisan serve --host=0.0.0.0 --port=8013`, kemudian buka http://localhost:8013.
Untuk akses dari ponsel di Wi-Fi yang sama, gunakan alamat IP komputer dengan port 8013.
Pada Laragon, arahkan document root virtual host ke folder `public` proyek ini.

Database: `keuangan_3d`. Konfigurasi koneksi tersedia pada `.env`.

## Hak akses

- Pengelola: melihat dashboard dan transaksi serta menambah, mengedit, dan menghapus pemasukan/pengeluaran melalui modal. Penghapusan memerlukan konfirmasi.
- Pemodal: melihat dashboard, daftar transaksi, dan bukti pembelian.

Akun awal dibuat dengan `php artisan db:seed`; password acak ditampilkan saat akun pertama kali dibuat. Seeder tidak mengubah akun yang sudah ada.

Login menggunakan username `pengelola` atau `pemodal`, dengan password akun masing-masing. Akun lama otomatis mendapat username dari bagian email sebelum tanda @ saat migrasi. Menu **Ubah Password** tersedia untuk kedua peran; masukkan password lama, password baru minimal 8 karakter, dan konfirmasinya.

Setiap pengeluaran wajib memiliki bukti JPG, PNG, atau PDF maksimal 5 MB. Lampiran disimpan di `storage/app/private/receipts` dan hanya tersedia setelah login melalui tombol **View lampiran**. Cadangkan database beserta folder lampiran.

Saldo usaha adalah seluruh pemasukan (termasuk modal) dikurangi pengeluaran, bukan laba bersih.

## Pemeriksaan

`php artisan test` memeriksa login, pembatasan peran, validasi bukti, dan akses lampiran privat menggunakan database SQLite sementara.
