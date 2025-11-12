<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add address to substations (areas info merged)
        Schema::table('substations', function (Blueprint $table) {
            $table->string('address', 500)->nullable()->after('location');
        });

        // Copy data from areas to meters before dropping
        // Update electric_meters to use substation_id from their area's substation
        DB::statement('
            UPDATE electric_meters em
            INNER JOIN areas a ON em.area_id = a.id
            SET em.substation_id = a.substation_id
            WHERE em.area_id IS NOT NULL AND em.substation_id IS NULL
        ');

        // Remove area_id foreign key and column
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });

        // Drop and recreate substation_id foreign key as required
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropForeign(['substation_id']);
        });
        
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->unsignedBigInteger('substation_id')->nullable(false)->change();
            $table->foreign('substation_id')->references('id')->on('substations')->onDelete('cascade');
        });

        // Drop areas table
        Schema::dropIfExists('areas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate areas table
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('address', 500)->nullable();
            $table->foreignId('substation_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });

        // Revert electric_meters
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropForeign(['substation_id']);
            $table->renameColumn('substation_id', 'area_id');
        });

        Schema::table('electric_meters', function (Blueprint $table) {
            $table->foreignId('substation_id')->nullable()->after('organization_unit_id')->constrained()->onDelete('set null');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });

        // Remove address from substations
        Schema::table('substations', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
