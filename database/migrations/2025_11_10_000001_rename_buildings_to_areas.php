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
        // Rename buildings table to areas
        Schema::rename('buildings', 'areas');
        
        // Rename foreign key column in electric_meters
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->renameColumn('building_id', 'area_id');
            $table->foreign('area_id')->references('id')->on('areas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->renameColumn('area_id', 'building_id');
            $table->foreign('building_id')->references('id')->on('buildings')->nullOnDelete();
        });
        
        Schema::rename('areas', 'buildings');
    }
};
