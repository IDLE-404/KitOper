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
        if (!Schema::hasTable('form_two_normatives')) {
            Schema::create('form_two_normatives', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('first_course_group')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('first_course_subjects')->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->unsignedTinyInteger('month');
                $table->unsignedSmallInteger('year');
                $table->integer('total_hours')->default(0);
                $table->integer('hours_per_class')->default(2);
                $table->timestamps();

                $table->unique(['group_id', 'subject_id', 'teacher_id', 'month', 'year'], 'form2_normative_unique');
            });
            return;
        }

        Schema::table('form_two_normatives', function (Blueprint $table) {
            if (!Schema::hasColumn('form_two_normatives', 'group_id')) {
                $table->foreignId('group_id')->after('id')->constrained('first_course_group')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('form_two_normatives', 'subject_id')) {
                $table->foreignId('subject_id')->after('group_id')->constrained('first_course_subjects')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('form_two_normatives', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('subject_id')->constrained('teachers')->nullOnDelete();
            }
            if (!Schema::hasColumn('form_two_normatives', 'month')) {
                $table->unsignedTinyInteger('month')->after('teacher_id')->default(1);
            }
            if (!Schema::hasColumn('form_two_normatives', 'year')) {
                $table->unsignedSmallInteger('year')->after('month')->default((int) date('Y'));
            }
            if (!Schema::hasColumn('form_two_normatives', 'total_hours')) {
                $table->integer('total_hours')->default(0)->after('year');
            }
            if (!Schema::hasColumn('form_two_normatives', 'hours_per_class')) {
                $table->integer('hours_per_class')->default(2)->after('total_hours');
            }
            if (!Schema::hasColumn('form_two_normatives', 'created_at')) {
                $table->timestamps();
            }
            $table->unique(['group_id', 'subject_id', 'teacher_id', 'month', 'year'], 'form2_normative_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_two_normatives');
    }
};
