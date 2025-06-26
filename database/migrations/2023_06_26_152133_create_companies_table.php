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
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_disabled')->default(false);
            $table->unsignedBigInteger('responsible_id');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('responsible_id', 'companies_responsible_id_foreign');

            // Foreign Keys
            $table->foreign('responsible_id', 'companies_responsible_id_foreign')
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
        Schema::dropIfExists('companies');
    }
};
