<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * migrasi ini dilakukan karena banyak data log yang menyimpan nama icon
     * dan icon-icon tersebut sudah deprecated
     */
    public function up(): void
    {
        // Hapus semua data di table notifications
        DB::table('notifications')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada rollback, bisa dikosongkan atau log jika perlu
    }
};
