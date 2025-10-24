<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * migrasi ini untuk mengupdate kolom `icon` di tabel `ticket_types`
     * karena data sebelumnya iconnya sudah deprecated
     */
    public function up(): void
    {
        // Update semua baris di kolom `icon`
        DB::table('ticket_types')->update([
            'icon' => 'heroicon-o-document-text',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Opsional: rollback, misal set ke null
        DB::table('ticket_types')->update([
            'icon' => null,
        ]);
    }
};
