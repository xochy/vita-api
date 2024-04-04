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
        Schema::create('muscle_workout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muscle_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('workout_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('priority', ['principal', 'secondary', 'antagonist']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muscle_workout');
    }
};
