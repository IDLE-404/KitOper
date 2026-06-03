<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('form_two_templates')) {
            Schema::create('form_two_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('course');
                $table->string('name');
                $table->string('group_tokens');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['course', 'is_active']);
            });
        }

        if (!Schema::hasTable('form_two_template_items')) {
            Schema::create('form_two_template_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('form_two_templates')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('subject_name');
                $table->boolean('include_subgroup_two')->default(false);
                $table->timestamps();

                $table->index(['template_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('form_two_template_items');
        Schema::dropIfExists('form_two_templates');
    }
};

