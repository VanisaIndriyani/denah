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
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('lighting_points')->nullable()->after('description')->comment('Jumlah titik pengukuran pencahayaan (simbol ❌)');
            $table->integer('dust_points')->nullable()->after('lighting_points')->comment('Jumlah titik pengukuran debu total (simbol ⭕)');
            $table->integer('air_quality_points')->nullable()->after('dust_points')->comment('Jumlah titik pengukuran kualitas udara (simbol 🔺)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['lighting_points', 'dust_points', 'air_quality_points']);
        });
    }
};
