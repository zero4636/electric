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
        // Thêm thông tin contact vào organization_units
        Schema::table('organization_units', function (Blueprint $table) {
            $table->string('representative_name')->nullable()->after('contact_phone');
            $table->string('representative_phone')->nullable()->after('representative_name');
            $table->string('building')->nullable()->after('address'); // Tòa nhà
            $table->string('floor')->nullable()->after('building'); // Tầng
            $table->string('room')->nullable()->after('floor'); // Phòng
        });

        // Thêm cột location chi tiết cho electric_meters
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->string('installation_location')->nullable()->after('substation_id'); // Vị trí lắp đặt
            $table->string('meter_book_code')->nullable()->after('meter_number'); // Mã quyển
            $table->integer('meter_book_page')->nullable()->after('meter_book_code'); // Trang
        });

        // Thêm reference đến reading trước đó trong bill_details
        Schema::table('bill_details', function (Blueprint $table) {
            $table->foreignId('start_reading_id')->nullable()->after('electric_meter_id')->constrained('meter_readings')->nullOnDelete();
            $table->foreignId('end_reading_id')->nullable()->after('start_reading_id')->constrained('meter_readings')->nullOnDelete();
            $table->decimal('subsidized_amount', 12, 2)->default(0)->after('hsn'); // Bao cấp
        });

        // Tạo bảng buildings để quản lý tòa nhà
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Mã tòa: D5, A17, B1, etc.
            $table->string('name'); // Tên: Nhà D5, Tòa A17
            $table->text('address')->nullable();
            $table->foreignId('substation_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('total_floors')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });

        // Liên kết electric_meters với buildings
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->foreignId('building_id')->nullable()->after('substation_id')->constrained()->nullOnDelete();
            $table->string('floor_number')->nullable()->after('building_id'); // Tầng
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_details', function (Blueprint $table) {
            $table->dropForeign(['start_reading_id']);
            $table->dropForeign(['end_reading_id']);
            $table->dropColumn(['start_reading_id', 'end_reading_id', 'subsidized_amount']);
        });

        Schema::table('electric_meters', function (Blueprint $table) {
            $table->dropForeign(['building_id']);
            $table->dropColumn(['building_id', 'floor_number', 'installation_location', 'meter_book_code', 'meter_book_page']);
        });

        Schema::dropIfExists('buildings');

        Schema::table('organization_units', function (Blueprint $table) {
            $table->dropColumn(['representative_name', 'representative_phone', 'building', 'floor', 'room']);
        });
    }
};
