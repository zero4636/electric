<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Map enum values to tariff_type IDs (from TariffType seeder)
        // Seeder creates: RESIDENTIAL=1, COMMERCIAL=2, INDUSTRIAL=3
        
        // Backfill electricity_tariffs.tariff_type_id from tariff_type enum
        DB::statement("
            UPDATE electricity_tariffs et
            INNER JOIN tariff_types tt ON (
                (et.tariff_type = 'RESIDENTIAL' AND tt.code = 'RESIDENTIAL') OR
                (et.tariff_type = 'COMMERCIAL' AND tt.code = 'COMMERCIAL') OR
                (et.tariff_type = 'INDUSTRIAL' AND tt.code = 'INDUSTRIAL')
            )
            SET et.tariff_type_id = tt.id
            WHERE et.tariff_type_id IS NULL
        ");
        
        // Backfill electric_meters.tariff_type_id from meter_type enum
        DB::statement("
            UPDATE electric_meters em
            INNER JOIN tariff_types tt ON (
                (em.meter_type = 'RESIDENTIAL' AND tt.code = 'RESIDENTIAL') OR
                (em.meter_type = 'COMMERCIAL' AND tt.code = 'COMMERCIAL') OR
                (em.meter_type = 'INDUSTRIAL' AND tt.code = 'INDUSTRIAL')
            )
            SET em.tariff_type_id = tt.id
            WHERE em.tariff_type_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset tariff_type_id to null
        DB::statement("UPDATE electricity_tariffs SET tariff_type_id = NULL");
        DB::statement("UPDATE electric_meters SET tariff_type_id = NULL");
    }
};
