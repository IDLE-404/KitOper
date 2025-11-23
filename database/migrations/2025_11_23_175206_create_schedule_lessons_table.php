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
        Schema::create('schedule_lessons', function (Blueprint $table) {
            $table->id();
            
            $table->string("group_name",50);;
            $table->unsignedTinyInteger('day_of_week');
            $table->string('day_name')->nullabel();
            $table->unsignedTinyInteger('pair_number');
            
            $table->string('subject')->nullable();
            $table->string('teacher')->nullable();
            $table->string('room')->nullable();
            $table->string('subgroup')->nullable();

            $table->boolean('is_replaced')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_lessons');
    }
};
