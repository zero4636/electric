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
        // Add indexes to organization_units table
        Schema::table('organization_units', function (Blueprint $table) {
            $table->index('parent_id', 'idx_org_units_parent');
            $table->index('type', 'idx_org_units_type');
            $table->index('status', 'idx_org_units_status');
            $table->index(['status', 'type'], 'idx_org_units_status_type');
        });

        // Add indexes to substations table
        Schema::table('substations', function (Blueprint $table) {
            $table->index('status', 'idx_substations_status');
            $table->index('code', 'idx_substations_code');
        });

        // Add indexes to electric_meters table
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->index('organization_unit_id', 'idx_meters_org_unit');
            $table->index('substation_id', 'idx_meters_substation');
            $table->index('meter_type', 'idx_meters_type');
            $table->index('status', 'idx_meters_status');
            $table->index(['organization_unit_id', 'status'], 'idx_meters_org_status');
        });

        // Add indexes to meter_readings table
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->index('electric_meter_id', 'idx_readings_meter');
            $table->index('reading_date', 'idx_readings_date');
            $table->index(['electric_meter_id', 'reading_date'], 'idx_readings_meter_date');
        });

        // Add indexes to electricity_tariffs table
        Schema::table('electricity_tariffs', function (Blueprint $table) {
            $table->index('tariff_type', 'idx_tariffs_type');
            $table->index('effective_from', 'idx_tariffs_from');
            $table->index('effective_to', 'idx_tariffs_to');
            $table->index(['tariff_type', 'effective_from', 'effective_to'], 'idx_tariffs_active');
        });

        // Add indexes to bills table
        Schema::table('bills', function (Blueprint $table) {
            $table->index('organization_unit_id', 'idx_bills_org_unit');
            $table->index('billing_date', 'idx_bills_date');
            $table->index('status', 'idx_bills_status');
            $table->index(['organization_unit_id', 'billing_date'], 'idx_bills_org_date');
            $table->index(['status', 'billing_date'], 'idx_bills_status_date');
        });

        // Add indexes to bill_details table
        Schema::table('bill_details', function (Blueprint $table) {
            $table->index('bill_id', 'idx_bill_details_bill');
            $table->index('electric_meter_id', 'idx_bill_details_meter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_units', function (Blueprint $table) {
            $table->dropIndex('idx_org_units_parent');
            $table->dropIndex('idx_org_units_type');
            $table->dropIndex('idx_org_units_status');
            $table->dropIndex('idx_org_units_status_type');
        });

        Schema::table('substations', function (Blueprint $table) {
            $table->dropIndex('idx_substations_status');
            $table->dropIndex('idx_substations_code');
        });

        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropIndex('idx_meters_org_unit');
            $table->dropIndex('idx_meters_substation');
            $table->dropIndex('idx_meters_type');
            $table->dropIndex('idx_meters_status');
            $table->dropIndex('idx_meters_org_status');
        });

        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropIndex('idx_readings_meter');
            $table->dropIndex('idx_readings_date');
            $table->dropIndex('idx_readings_meter_date');
        });

        Schema::table('electricity_tariffs', function (Blueprint $table) {
            $table->dropIndex('idx_tariffs_type');
            $table->dropIndex('idx_tariffs_from');
            $table->dropIndex('idx_tariffs_to');
            $table->dropIndex('idx_tariffs_active');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('idx_bills_org_unit');
            $table->dropIndex('idx_bills_date');
            $table->dropIndex('idx_bills_status');
            $table->dropIndex('idx_bills_org_date');
            $table->dropIndex('idx_bills_status_date');
        });

        Schema::table('bill_details', function (Blueprint $table) {
            $table->dropIndex('idx_bill_details_bill');
            $table->dropIndex('idx_bill_details_meter');
        });
    }
};
