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
        if (!Schema::hasTable('chats')) {
            Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->longText('message');
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('ticket_id', 'chats_ticket_id_foreign');
            $table->index('user_id', 'chats_user_id_foreign');

            // Foreign Keys
            $table->foreign('ticket_id', 'chats_ticket_id_foreign')
                  ->references('id')->on('tickets')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            $table->foreign('user_id', 'chats_user_id_foreign')
                  ->references('id')->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
