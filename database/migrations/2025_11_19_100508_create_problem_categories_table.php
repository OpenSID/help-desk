<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('problem_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Contoh: "Website tidak bisa diakses"
            $table->string('code')->nullable()->unique(); // Contoh: "W001"
            $table->text('description')->nullable(); // Penjelasan detail kategori
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('problem_categories')->insert([
            [
                'name' => 'Website tidak bisa diakses',
                'code' => 'W001',
                'description' => 'Masalah terkait website yang tidak dapat diakses oleh pengguna.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Login gagal',
                'code' => 'L001',
                'description' => 'Masalah yang terjadi saat pengguna mencoba untuk login tetapi gagal.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Akses lambat',
                'code' => 'N001',
                'description' => 'Masalah terkait kecepatan akses yang lambat atau tidak stabil.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Error database',
                'code' => 'D001',
                'description' => 'Masalah yang berhubungan dengan database, seperti koneksi gagal atau query error.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Error tampilan',
                'code' => 'UI001',
                'description' => 'Masalah yang terkait dengan tampilan antarmuka pengguna, seperti layout yang rusak atau elemen yang tidak muncul dengan benar.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fitur tidak berfungsi',
                'code' => 'F001',
                'description' => 'Masalah di mana fitur tertentu dalam aplikasi tidak berfungsi sesuai harapan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_categories');
    }
};
