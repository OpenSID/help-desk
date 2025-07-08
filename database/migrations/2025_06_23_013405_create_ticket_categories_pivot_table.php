<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel pivot untuk menghubungkan antara model Ticket dengan model TicketCategory.
     * Tabel ini digunakan untuk menghubungkan sebuah tiket dengan lebih dari satu kategori.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_categories_pivot', function (Blueprint $table) {
            // Setiap kategori tiket memiliki relasi one to many dengan tiket,
            // maka kita menggunakan foreign key untuk menghubungkan keduanya.
            $table->foreignId('ticket_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Setiap tiket memiliki relasi one to many dengan kategori tiket,
            // maka kita menggunakan foreign key untuk menghubungkan keduanya.
            $table->foreignId('ticket_category_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Kita menggunakan primary key yang terdiri dari dua kolom yaitu ticket_id dan ticket_category_id.
            // Hal ini untuk memastikan bahwa tidak ada duplikasi data dalam tabel pivot.
            $table->primary(['ticket_id', 'ticket_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_categories_pivot');
    }
};
