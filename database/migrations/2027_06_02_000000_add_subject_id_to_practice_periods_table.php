<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('practice_periods', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('group_id');
                $table->index(['course', 'subject_id'], 'practice_periods_course_subject_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('practice_periods', function (Blueprint $table) {
            if (Schema::hasColumn('practice_periods', 'subject_id')) {
                $table->dropIndex('practice_periods_course_subject_idx');
                $table->dropColumn('subject_id');
            }
        });
    }
};
