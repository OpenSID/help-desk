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
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('ticket_id');
            $table->longText('content');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('owner_id', 'comments_owner_id_foreign');
            $table->index('ticket_id', 'comments_ticket_id_foreign');

            // Foreign Keys
            $table->foreign('owner_id', 'comments_owner_id_foreign')
                  ->references('id')->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            $table->foreign('ticket_id', 'comments_ticket_id_foreign')
                  ->references('id')->on('tickets')
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
        Schema::dropIfExists('comments');
    }
};
