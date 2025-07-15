<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Insert default data ke tabel master_applications.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('master_applications')) {
            DB::table('master_applications')->insert([
                ['name' => 'OpenSID', 'color' => '#2563eb', 'created_at' => now(), 'updated_at' => now()], // biru
                ['name' => 'PBB', 'color' => '#16a34a', 'created_at' => now(), 'updated_at' => now()], // hijau
                ['name' => 'API', 'color' => '#f59e42', 'created_at' => now(), 'updated_at' => now()], // oranye
                ['name' => 'Layanan', 'color' => '#eab308', 'created_at' => now(), 'updated_at' => now()], // kuning
                ['name' => 'Dasbor SiapPakai', 'color' => '#a21caf', 'created_at' => now(), 'updated_at' => now()], // ungu
                ['name' => 'Modul', 'color' => '#db2777', 'created_at' => now(), 'updated_at' => now()], // pink
                ['name' => 'Tema', 'color' => '#0ea5e9', 'created_at' => now(), 'updated_at' => now()], // biru muda
                ['name' => 'KelolaDesa', 'color' => '#f43f5e', 'created_at' => now(), 'updated_at' => now()], // merah
                ['name' => 'LayananDesa', 'color' => '#10b981', 'created_at' => now(), 'updated_at' => now()], // hijau tosca
            ]);
        }
    }

    /**
     * Hapus data default dari tabel master_applications.
     *
     * @return void
     */
    public function down()
    {
        DB::table('master_applications')
            ->whereIn('name', [
                'OpenSID', 'PBB', 'API', 'Layanan', 'Dasbor SiapPakai', 'Modul', 'Tema', 'KelolaDesa', 'LayananDesa'
            ])->delete();
    }
};
