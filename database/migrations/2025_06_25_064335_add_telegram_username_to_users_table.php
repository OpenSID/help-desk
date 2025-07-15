<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk menambahkan kolom 'telegram_username' pada tabel users.
 *
 * - Kolom ini digunakan untuk menyimpan username Telegram user.
 * - Kolom bersifat nullable dan diletakkan setelah kolom 'email'.
 * - Mendukung rollback untuk menghapus kolom jika diperlukan.
 *
 * @package Database\Migrations
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi.
     * Menambahkan kolom 'telegram_username' ke tabel users.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom telegram_username setelah email, nullable
            $table->string('telegram_username')->nullable()->after('email');
        });
    }

    /**
     * Membalikkan migrasi.
     * Menghapus kolom 'telegram_username' dari tabel users.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom telegram_username
            $table->dropColumn('telegram_username');
        });
    }
};
