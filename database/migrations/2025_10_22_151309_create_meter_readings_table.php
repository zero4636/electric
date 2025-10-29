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
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('electric_meter_id')->constrained()->cascadeOnDelete();
            $table->date('reading_date');
            $table->decimal('reading_value',10,2);
            $table->string('reader_name')->nullable(); // Người ghi
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
