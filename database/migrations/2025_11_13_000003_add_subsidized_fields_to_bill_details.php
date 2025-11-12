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
        Schema::table('bill_details', function (Blueprint $table) {
            // Add subsidized_applied to track how much subsidized kWh was deducted
            $table->decimal('subsidized_applied', 10, 2)
                ->default(0)
                ->after('consumption')
                ->comment('Số kWh bao cấp đã áp dụng');
            
            // Add chargeable_kwh for clarity (consumption - subsidized_applied)
            $table->decimal('chargeable_kwh', 10, 2)
                ->nullable()
                ->after('subsidized_applied')
                ->comment('Số kWh tính tiền (sau khi trừ bao cấp)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_details', function (Blueprint $table) {
            $table->dropColumn(['subsidized_applied', 'chargeable_kwh']);
        });
    }
};
