<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('issue_sources')) {
            DB::table('issue_sources')->insert([
                ['name' => 'Sumber Masalah Pada Server', 'color' => '#2563eb', 'created_at' => now(), 'updated_at' => now()], // biru
                ['name' => 'Sumber Masalah Pada Aplikasi', 'color' => '#16a34a', 'created_at' => now(), 'updated_at' => now()], // hijau
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('issue_sources')
            ->whereIn('name', [
                'Sumber Masalah Pada Server', 'Sumber Masalah Pada Aplikasi'
            ])->delete();
    }
};
