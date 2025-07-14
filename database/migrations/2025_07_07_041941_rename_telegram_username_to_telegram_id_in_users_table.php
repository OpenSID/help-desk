<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk mengganti nama kolom 'telegram_username' menjadi 'telegram_id' pada tabel users.
 *
 * - Kolom 'telegram_username' diubah menjadi 'telegram_id' untuk menyesuaikan kebutuhan aplikasi.
 * - Mendukung rollback untuk mengembalikan nama kolom seperti semula.
 *
 * @package Database\Migrations
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi.
     * Mengubah nama kolom 'telegram_username' menjadi 'telegram_id' pada tabel users.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename kolom dari telegram_username ke telegram_id
            $table->renameColumn('telegram_username', 'telegram_id');
        });
    }

    /**
     * Membalikkan migrasi.
     * Mengembalikan nama kolom 'telegram_id' menjadi 'telegram_username' pada tabel users.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename kolom dari telegram_id ke telegram_username
            $table->renameColumn('telegram_id', 'telegram_username');
        });
    }
};
