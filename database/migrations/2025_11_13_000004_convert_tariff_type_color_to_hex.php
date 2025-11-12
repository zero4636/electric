<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Map old named colors to hex values
        $map = [
            'primary' => '#3b82f6', // blue-500
            'success' => '#22c55e', // green-500
            'warning' => '#f59e0b', // amber-500
            'danger'  => '#ef4444', // red-500
            'info'    => '#06b6d4', // cyan-500
        ];

        foreach ($map as $name => $hex) {
            DB::table('tariff_types')
                ->where('color', $name)
                ->update(['color' => $hex]);
        }
    }

    public function down(): void
    {
        // Best-effort reverse mapping to 'primary' for all, since exact names may be ambiguous
        // You can adjust this if needed
    }
};
