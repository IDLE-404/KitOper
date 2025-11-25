<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_two_records', function (Blueprint $table) {
            if (!Schema::hasColumn('form_two_records', 'class_date')) {
                $table->date('class_date')->nullable()->after('year');
            }
            if (!Schema::hasColumn('form_two_records', 'lesson_number')) {
                $table->unsignedTinyInteger('lesson_number')->nullable()->after('class_date');
            }
            if (!Schema::hasColumn('form_two_records', 'subgroup')) {
                $table->string('subgroup', 2)->nullable()->after('lesson_number');
            }

            $table->index(
                ['group_id', 'class_date', 'lesson_number', 'subgroup', 'mode'],
                'form2_group_date_lesson_mode_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('form_two_records', function (Blueprint $table) {
            $table->dropIndex('form2_group_date_lesson_mode_idx');
            if (Schema::hasColumn('form_two_records', 'subgroup')) {
                $table->dropColumn('subgroup');
            }
            if (Schema::hasColumn('form_two_records', 'lesson_number')) {
                $table->dropColumn('lesson_number');
            }
            if (Schema::hasColumn('form_two_records', 'class_date')) {
                $table->dropColumn('class_date');
            }
        });
    }
};
