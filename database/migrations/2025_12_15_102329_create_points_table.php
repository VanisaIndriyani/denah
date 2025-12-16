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
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_plan_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('point'); // point atau area
            $table->decimal('x', 10, 2); // koordinat X
            $table->decimal('y', 10, 2); // koordinat Y
            $table->string('category'); // 'diatas_nab' atau 'dibawah_nab'
            $table->text('notes')->nullable();
            $table->json('coordinates')->nullable(); // untuk area (polygon)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};
