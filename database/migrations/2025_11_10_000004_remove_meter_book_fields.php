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
            $table->dropColumn(['meter_book_code', 'meter_book_page']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electric_meters', function (Blueprint $table) {
            $table->string('meter_book_code', 50)->nullable()->after('meter_number');
            $table->integer('meter_book_page')->nullable()->after('meter_book_code');
        });
    }
};
