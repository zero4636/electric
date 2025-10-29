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
        Schema::table('electricity_tariffs', function (Blueprint $table) {
            // Add tariff_type_id column
            $table->foreignId('tariff_type_id')->nullable()->after('id')->constrained('tariff_types')->nullOnDelete();
            
            // Add index
            $table->index('tariff_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electricity_tariffs', function (Blueprint $table) {
            $table->dropForeign(['tariff_type_id']);
            $table->dropIndex(['tariff_type_id']);
            $table->dropColumn('tariff_type_id');
        });
    }
};
