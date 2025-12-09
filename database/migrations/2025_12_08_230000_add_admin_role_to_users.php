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
        // Add role column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin'])->default('admin')->after('email');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('remember_token');
        });

        // Keep user_organization_units table as is - already exists
        // Just make sure it has the right structure
        if (!Schema::hasTable('user_organization_units')) {
            Schema::create('user_organization_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('organization_unit_id')->constrained()->onDelete('cascade');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'organization_unit_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['role', 'created_by']);
        });
    }
};
