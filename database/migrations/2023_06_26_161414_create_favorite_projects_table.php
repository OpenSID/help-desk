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
        if (!Schema::hasTable('favorite_projects')) {
            Schema::create('favorite_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->timestamps();

            // Indexes
            $table->index('user_id', 'favorite_projects_user_id_foreign');
            $table->index('project_id', 'favorite_projects_project_id_foreign');

            // Foreign Keys
            $table->foreign('user_id', 'favorite_projects_user_id_foreign')
                  ->references('id')->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            $table->foreign('project_id', 'favorite_projects_project_id_foreign')
                  ->references('id')->on('projects')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            // Unique constraint to prevent duplicate user-project pairs
            $table->unique(['user_id', 'project_id']);
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
        Schema::dropIfExists('favorite_projects');
    }
};
