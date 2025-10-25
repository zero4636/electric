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
        Schema::create('electricity_tariffs', function (Blueprint $table) {
            $table->id();
            $table->enum('tariff_type',['RESIDENTIAL','COMMERCIAL','INDUSTRIAL']);
            $table->decimal('price_per_kwh',10,2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_tariffs');
    }
};
