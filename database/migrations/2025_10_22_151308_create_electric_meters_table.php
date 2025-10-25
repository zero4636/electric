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
        Schema::create('electric_meters', function (Blueprint $table) {
            $table->id();
            $table->string('meter_number')->unique();
            $table->foreignId('organization_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('substation_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('meter_type',['RESIDENTIAL','COMMERCIAL','INDUSTRIAL']);
            $table->decimal('hsn',8,2)->default(1.0);
            $table->enum('status',['ACTIVE','INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electric_meters');
    }
};
