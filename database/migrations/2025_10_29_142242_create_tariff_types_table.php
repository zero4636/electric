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
        Schema::create('tariff_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã loại biểu giá (RESIDENTIAL, COMMERCIAL...)');
            $table->string('name', 100)->comment('Tên loại biểu giá (Dân cư, Thương mại...)');
            $table->text('description')->nullable()->comment('Mô tả chi tiết');
            $table->string('color', 20)->default('primary')->comment('Màu badge (primary, success, warning...)');
            $table->string('icon', 50)->nullable()->comment('Icon hiển thị');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE')->comment('Trạng thái');
            $table->integer('sort_order')->default(0)->comment('Thứ tự sắp xếp');
            $table->timestamps();
            
            $table->index('status');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariff_types');
    }
};
