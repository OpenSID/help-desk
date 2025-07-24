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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Dua status terpisah
            $table->enum('progress_status', [
                'Belum Mulai',
                'Sedang Berjalan',
                'Selesai'
            ])->default('Belum Mulai');

            $table->enum('access_status', ['Open', 'Closed'])->default('Open');
            $table->string('color')->default('#000000'); // Warna default kategori tiket
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('milestones');
    }
};
