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
        // Modify activity_log table - change properties column collation to utf8mb4_bin
        Schema::table('activity_log', function (Blueprint $table) {
            $table->longText('properties')->charset('utf8mb4')->collation('utf8mb4_bin')->nullable()->change();
        });

        // Drop unique indexes from company_users table
        if (Schema::hasTable('company_users')) {
            Schema::table('company_users', function (Blueprint $table) {
            // Check if the unique index exists before dropping
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('company_users');
            if (array_key_exists('company_users_user_id_company_id_unique', $indexes)) {
                $table->dropUnique(['user_id', 'company_id']);
            }
            });
        }

        // Drop unique indexes from favorite_projects table
        if (Schema::hasTable('favorite_projects')) {
            Schema::table('favorite_projects', function (Blueprint $table) {
            // Check if the unique index exists before dropping
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('favorite_projects');
            if (array_key_exists('favorite_projects_user_id_project_id_unique', $indexes)) {
                $table->dropUnique(['user_id', 'project_id']);
            }
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
        // Reverse the changes - change properties column back to default collation
        Schema::table('activity_log', function (Blueprint $table) {
            $table->longText('properties')->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable()->change();
        });

        // Add back unique indexes to company_users table
        Schema::table('company_users', function (Blueprint $table) {
            $table->unique(['user_id', 'company_id']);
        });

        // Add back unique indexes to favorite_projects table
        Schema::table('favorite_projects', function (Blueprint $table) {
            $table->unique(['user_id', 'project_id']);
        });
    }
};
