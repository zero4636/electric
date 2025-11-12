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
            // Thêm thông tin tòa nhà và vị trí chi tiết
            $table->string('building')->nullable()->after('meter_number')->comment('Tòa nhà (VD: B1, D5, A17)');
            $table->string('floor')->nullable()->after('building')->comment('Tầng (VD: T1, T2, T3)');
            
            // Thêm loại công tơ (1 pha / 3 pha)
            $table->enum('phase_type', ['1_PHASE', '3_PHASE'])->nullable()->after('meter_type')->comment('Loại công tơ: 1 pha hoặc 3 pha');
            
            // Thêm số kWh được bao cấp
            $table->decimal('subsidized_kwh', 10, 2)->default(0)->after('hsn')->comment('Số kWh được bao cấp mỗi tháng');
            
            // Sửa installation_location thành nullable và thêm comment
            $table->string('installation_location')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropColumn(['building', 'floor', 'phase_type', 'subsidized_kwh']);
        });
    }
};
