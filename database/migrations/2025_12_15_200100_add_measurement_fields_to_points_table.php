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
        Schema::table('points', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('floor_plan_id')->constrained()->onDelete('set null');
            $table->string('parameter')->nullable()->after('category');
            $table->decimal('value', 10, 2)->nullable()->after('parameter');
            $table->string('unit', 50)->nullable()->after('value');
            $table->boolean('meets_nab')->nullable()->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn(['room_id', 'parameter', 'value', 'unit', 'meets_nab']);
        });
    }
};

