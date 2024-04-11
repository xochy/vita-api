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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')
                ->constrained('goals')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('frequency_id')
                ->constrained('frequencies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('physical_condition_id')
                ->constrained('physical_conditions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
