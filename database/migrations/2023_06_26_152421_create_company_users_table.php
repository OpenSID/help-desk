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
        if (!Schema::hasTable('company_users')) {
            Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();

            // Indexes
            $table->index('user_id', 'company_users_user_id_foreign');
            $table->index('company_id', 'company_users_company_id_foreign');

            // Foreign Keys
            $table->foreign('user_id', 'company_users_user_id_foreign')
                  ->references('id')->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            $table->foreign('company_id', 'company_users_company_id_foreign')
                  ->references('id')->on('companies')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            // Unique constraint to prevent duplicate user-company pairs
            $table->unique(['user_id', 'company_id']);
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
        Schema::dropIfExists('company_users');
    }
};
