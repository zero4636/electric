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
        Schema::table('electric_meters', function (Blueprint $table) {
            // Add tariff_type_id foreign key (nullable for safe migration)
            $table->foreignId('tariff_type_id')
                ->nullable()
                ->after('meter_type')
                ->constrained('tariff_types')
                ->nullOnDelete()
                ->comment('Loại biểu giá điện');
            
            // Add index for better query performance
            $table->index('tariff_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropForeign(['tariff_type_id']);
            $table->dropIndex(['tariff_type_id']);
            $table->dropColumn('tariff_type_id');
        });
    }
};
