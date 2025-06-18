<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_workout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')
                ->onDelete('cascade');
            $table->foreignId('workout_id')->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_workout');
    }
};
