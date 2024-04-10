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
        Schema::create('routine_workout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('workout_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->tinyInteger('series');
            $table->tinyInteger('repetitions');
            $table->tinyInteger('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routine_workout');
    }
};
