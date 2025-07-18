<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        //
    }
};
