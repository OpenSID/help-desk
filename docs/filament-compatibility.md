# Dokumentasi Patch Kompatibilitas Filament

## Latar Belakang & Masalah
Saat melakukan upgrade dari **Filament v2 ke v3**, sering terjadi masalah kompatibilitas terkait "Access Level" pada method di trait `InteractsWithForms`.

**Issue Utama:**
Method `getFormStatePath()` pada `Filament\Forms\Concerns\InteractsWithForms` didefinisikan sebagai `protected`. Namun, pada beberapa implementasi (terutama yang berinteraksi dengan Livewire v3 atau interface pihak ketiga tertentu), method ini dibutuhkan dengan visibilitas `public`.

Jika tidak di-patch, aplikasi akan mengalami **Fatal Error** seperti:
> `Access level to Filament\Forms\Concerns\InteractsWithForms::getFormStatePath() must be public (as in class ...)`

Referensi Diskusi:
- [GitHub Issue #13931](https://github.com/filamentphp/filament/issues/13931)
- [Answer Overflow Discussion](https://www.answeroverflow.com/m/1167103504364601494)

## Solusi: Patching
Kita menerapkan patch manual untuk mengubah visibilitas `getFormStatePath()` dari `protected` menjadi `public`.

### Penjelasan File Terkait

Berikut adalah fungsi dan alasan perubahan pada setiap file:

#### 1. `patches/filament-forms-access-level.patch`
Ini adalah file *diff* yang berisi instruksi perubahan kode sebenarnya.
- **Perubahan:** Mengubah baris `protected function getFormStatePath(): ?string` menjadi `public function getFormStatePath(): ?string` di file `vendor/filament/forms/src/Concerns/InteractsWithForms.php`.

#### 2. `patches/apply-patches.php`
Script PHP kustom untuk menerapkan patch secara langsung.
- **Alasan:** Meskipun kita bisa menggunakan plugin composer, script ini bertindak sebagai mekanisme *fallback* yang kuat. Ia berjalan setelah `composer install` atau `composer update` untuk memastikan file vendor *selalu* diperbaiki, bahkan jika plugin patch gagal atau tidak terkonfigurasi dengan benar di environment tertentu.
- **Fungsi:** 
  1. Mencari file target di folder `vendor`.
  2. Mengecek apakah file sudah di-patch (mencari string `public function`).
  3. Jika belum, melakukan *find & replace* teks dan menyimpan file.

#### 3. `composer.json`
Kami memodifikasi bagian `scripts` untuk menjalankan `patches/apply-patches.php`.
- **Perubahan:** Menambahkan perintah `@php patches/apply-patches.php` pada event:
  - `post-autoload-dump`
  - `post-install-cmd`
  - `post-update-cmd`
- **Tujuan:** Menjamin patch otomatis diterapkan setiap kali dependensi diperbarui atau autoloader di-generate ulang.

#### 4. `patches.lock.json`
File ini dihasilkan oleh plugin `cweagans/composer-patches`.
- **Fungsi:** Menyimpan *state* patch yang diterapkan, termasuk hash/checksum, untuk memastikan integritas dan idempotensi patch jika menggunakan plugin composer patch standar.

#### 5. `.gitattributes`
- **Fungsi:** Menetapkan atribut file.
- **Relevansi:** Memastikan file `.patch` memiliki *Line Ending* yang konsisten (LF), sehingga patch dapat diterapkan dengan sukses di berbagai sistem operasi (Windows/Linux) tanpa *hunk failed* error karena perbedaan whitespace.

---

## Cara Verifikasi
Untuk memastikan patch berhasil diterapkan:
1. Jalankan `composer install` atau `composer dump-autoload`.
2. Anda akan melihat output di terminal:
   ```
   ✓ Filament Forms patch applied successfully
   ```
   atau
   ```
   ✓ Filament forms already patched
   ```
3. Cek file `vendor/filament/forms/src/Concerns/InteractsWithForms.php`, pastikan method `getFormStatePath` aksesnya adalah `public`.
