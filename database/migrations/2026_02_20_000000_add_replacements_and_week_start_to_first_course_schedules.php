<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('first_course_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('first_course_schedules', 'week_start')) {
                $table->date('week_start')->nullable()->after('id');
                $table->index(['group_id', 'week_start'], 'first_course_schedules_group_week_idx');
            }

            // Подгруппа 1 — числитель
            $table->boolean('is_absent_1_num')->default(false)->after('room_id');
            $table->boolean('is_replacement_1_num')->default(false)->after('is_absent_1_num');
            $table->unsignedBigInteger('replacement_teacher_id_1_num')->nullable()->after('is_replacement_1_num');
            $table->string('replacement_comment_1_num', 255)->nullable()->after('replacement_teacher_id_1_num');

            // Подгруппа 1 — знаменатель
            $table->boolean('is_absent_1_den')->default(false)->after('replacement_comment_1_num');
            $table->boolean('is_replacement_1_den')->default(false)->after('is_absent_1_den');
            $table->unsignedBigInteger('replacement_teacher_id_1_den')->nullable()->after('is_replacement_1_den');
            $table->string('replacement_comment_1_den', 255)->nullable()->after('replacement_teacher_id_1_den');

            // Подгруппа 2 — числитель
            $table->boolean('is_absent_2_num')->default(false)->after('room_id_2');
            $table->boolean('is_replacement_2_num')->default(false)->after('is_absent_2_num');
            $table->unsignedBigInteger('replacement_teacher_id_2_num')->nullable()->after('is_replacement_2_num');
            $table->string('replacement_comment_2_num', 255)->nullable()->after('replacement_teacher_id_2_num');

            // Подгруппа 2 — знаменатель
            $table->boolean('is_absent_2_den')->default(false)->after('room_id_denominator_2');
            $table->boolean('is_replacement_2_den')->default(false)->after('is_absent_2_den');
            $table->unsignedBigInteger('replacement_teacher_id_2_den')->nullable()->after('is_replacement_2_den');
            $table->string('replacement_comment_2_den', 255)->nullable()->after('replacement_teacher_id_2_den');
        });
    }

    public function down(): void
    {
        Schema::table('first_course_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('first_course_schedules', 'week_start')) {
                $table->dropIndex('first_course_schedules_group_week_idx');
                $table->dropColumn('week_start');
            }

            $table->dropColumn([
                'is_absent_1_num',
                'is_replacement_1_num',
                'replacement_teacher_id_1_num',
                'replacement_comment_1_num',
                'is_absent_1_den',
                'is_replacement_1_den',
                'replacement_teacher_id_1_den',
                'replacement_comment_1_den',
                'is_absent_2_num',
                'is_replacement_2_num',
                'replacement_teacher_id_2_num',
                'replacement_comment_2_num',
                'is_absent_2_den',
                'is_replacement_2_den',
                'replacement_teacher_id_2_den',
                'replacement_comment_2_den',
            ]);
        });
    }
};
