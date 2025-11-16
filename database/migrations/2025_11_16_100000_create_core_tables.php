<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Consolidated migration creating all core tables with final schema
     * Replaces 20+ incremental migrations for cleaner structure
     */
    public function up(): void
    {
        // 1. Organization Units (2-level hierarchy + independent consumers)
        Schema::create('organization_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->enum('type', ['UNIT', 'CONSUMER'])->comment('UNIT=Đơn vị chủ quản, CONSUMER=Hộ tiêu thụ');
            $table->string('tax_code')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable()->comment('Địa chỉ hộ tiêu thụ điện');
            $table->string('building')->nullable()->comment('Nhà/Tòa nhà');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->string('password_hash')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('parent_id', 'idx_org_units_parent');
            $table->index('type', 'idx_org_units_type');
            $table->index('status', 'idx_org_units_status');
            $table->index(['status', 'type'], 'idx_org_units_status_type');
        });

        // 2. Substations (Trạm biến áp)
        Schema::create('substations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('location')->nullable();
            $table->string('address', 500)->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
            
            // Indexes
            $table->index('status', 'idx_substations_status');
            $table->index('code', 'idx_substations_code');
        });

        // 3. Electric Meters (Công tơ điện)
        Schema::create('electric_meters', function (Blueprint $table) {
            $table->id();
            $table->string('meter_number')->unique();
            $table->foreignId('organization_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('substation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tariff_type_id')->nullable()->constrained('tariff_types')->nullOnDelete()->comment('Loại biểu giá');
            $table->enum('meter_type', ['RESIDENTIAL', 'COMMERCIAL', 'INDUSTRIAL']);
            $table->enum('phase_type', ['1_PHASE', '3_PHASE'])->nullable()->comment('Loại công tơ: 1 pha hoặc 3 pha');
            $table->string('installation_location')->nullable()->comment('Vị trí đặt công tơ');
            $table->decimal('hsn', 8, 2)->default(1.0)->comment('Hệ số nhân');
            $table->decimal('subsidized_kwh', 10, 2)->default(0)->comment('Số kWh được bao cấp mỗi tháng');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
            
            // Indexes
            $table->index('organization_unit_id', 'idx_meters_org_unit');
            $table->index('substation_id', 'idx_meters_substation');
            $table->index('tariff_type_id', 'idx_meters_tariff_type');
            $table->index('meter_type', 'idx_meters_type');
            $table->index('status', 'idx_meters_status');
            $table->index(['organization_unit_id', 'status'], 'idx_meters_org_status');
        });

        // 4. Meter Readings (Chỉ số công tơ)
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('electric_meter_id')->constrained()->cascadeOnDelete();
            $table->date('reading_date');
            $table->decimal('reading_value', 10, 2);
            $table->string('reader_name')->nullable()->comment('Người ghi');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('electric_meter_id', 'idx_readings_meter');
            $table->index('reading_date', 'idx_readings_date');
            $table->index(['electric_meter_id', 'reading_date'], 'idx_readings_meter_date');
        });

        // 5. Electricity Tariffs (Biểu giá điện)
        Schema::create('electricity_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tariff_type_id')->nullable()->constrained('tariff_types')->nullOnDelete();
            $table->enum('tariff_type', ['RESIDENTIAL', 'COMMERCIAL', 'INDUSTRIAL']); // Legacy, use tariff_type_id
            $table->decimal('price_per_kwh', 10, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('tariff_type_id', 'idx_tariffs_tariff_type_id');
            $table->index('tariff_type', 'idx_tariffs_type');
            $table->index('effective_from', 'idx_tariffs_from');
            $table->index('effective_to', 'idx_tariffs_to');
            $table->index(['tariff_type', 'effective_from', 'effective_to'], 'idx_tariffs_active');
        });

        // 6. Bills (Hóa đơn)
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->constrained()->cascadeOnDelete();
            $table->date('billing_month')->comment('Tháng lập hóa đơn');
            $table->date('due_date')->comment('Hạn thanh toán');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['UNPAID', 'PARTIAL', 'PAID', 'OVERDUE'])->default('UNPAID')->comment('Trạng thái thanh toán');
            $table->timestamps();
            
            // Indexes
            $table->index('organization_unit_id', 'idx_bills_org_unit');
            $table->index('billing_month', 'idx_bills_month');
            $table->index('payment_status', 'idx_bills_status');
            $table->index(['organization_unit_id', 'billing_month'], 'idx_bills_org_month');
            $table->index(['payment_status', 'billing_month'], 'idx_bills_status_month');
        });

        // 7. Bill Details (Chi tiết hóa đơn)
        Schema::create('bill_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('electric_meter_id')->constrained()->cascadeOnDelete();
            $table->decimal('consumption', 10, 2)->comment('Tổng điện tiêu thụ (kWh)');
            $table->decimal('subsidized_applied', 10, 2)->default(0)->comment('Số kWh bao cấp đã áp dụng');
            $table->decimal('chargeable_kwh', 10, 2)->nullable()->comment('Số kWh tính tiền (sau khi trừ bao cấp)');
            $table->decimal('price_per_kwh', 10, 2);
            $table->decimal('hsn', 8, 2);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            
            // Indexes
            $table->index('bill_id', 'idx_bill_details_bill');
            $table->index('electric_meter_id', 'idx_bill_details_meter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_details');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('electricity_tariffs');
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('electric_meters');
        Schema::dropIfExists('substations');
        Schema::dropIfExists('organization_units');
    }
};
