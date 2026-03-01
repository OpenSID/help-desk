<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProblemCategory;

class ProblemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Website tidak bisa diakses', 'code' => 'W001', 'description' => 'Masalah terkait website yang tidak dapat diakses oleh pengguna.'],
            ['name' => 'Login gagal', 'code' => 'L001', 'description' => 'Masalah yang terjadi saat pengguna mencoba untuk login tetapi gagal.'],
            ['name' => 'Akses lambat', 'code' => 'N001', 'description' => 'Masalah terkait kecepatan akses yang lambat atau tidak stabil.'],
            ['name' => 'Error database', 'code' => 'D001', 'description' => 'Masalah yang berhubungan dengan database, seperti koneksi gagal atau query error.'],
            ['name' => 'Error tampilan', 'code' => 'UI001', 'description' => 'Masalah yang terkait dengan tampilan antarmuka pengguna, seperti layout yang rusak atau elemen yang tidak muncul dengan benar.'],
            ['name' => 'Fitur tidak berfungsi', 'code' => 'F001', 'description' => 'Masalah di mana fitur tertentu dalam aplikasi tidak berfungsi sesuai harapan.'],
        ];

        foreach ($data as $item) {
            ProblemCategory::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
