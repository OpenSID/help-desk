<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('problem_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Contoh: "Website tidak bisa diakses"
            $table->string('code')->nullable()->unique(); // Contoh: "W001"
            $table->text('description')->nullable(); // Penjelasan detail kategori
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_categories');
    }
};
