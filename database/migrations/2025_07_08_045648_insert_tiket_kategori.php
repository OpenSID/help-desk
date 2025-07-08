<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk mengisi kategori tiket.
     *
     * @return void
     */
    public function up()
    {
        // Insert tiket kategori jika tabel ticket_categories ada
        if (Schema::hasTable('ticket_categories')) {
            DB::table('ticket_categories')->insert([
                // Kategori Melalui Issue
                [
                    'name' => 'Melalui Issue',
                    'color' => '#2563eb', // Biru
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Kategori Tindak Lanjut DevOps
                [
                    'name' => 'Tindak Lanjut DevOps',
                    'color' => '#22c55e', // Hijau
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Kategori Tindak Lanjut Programmer
                [
                    'name' => 'Tindak Lanjut Programmer',
                    'color' => '#f59e42', // Kuning
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                // Kategori Diteruskan ke Dinkominfo
                [
                    'name' => 'Diteruskan ke Dinkominfo',
                    'color' => '#e11d48', // Merah
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Revert kembali migrasi yang sudah dijalankan.
     *
     * @return void
     */
    public function down()
    {
         if (Schema::hasTable('ticket_categories')) {
            DB::table('ticket_categories')->whereIn('name', [
                'Melalui Issue',
                'Tindak Lanjut DevOps',
                'Tindak Lanjut Programmer',
                'Diteruskan ke Dinkominfo',
            ])->delete();
        }
    }
};
