<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Убираем FK — группы в 4 разных таблицах, FK невозможен
            $table->dropForeign(['group_id']);
            // Курс группы (1–4)
            $table->unsignedTinyInteger('group_course')->nullable()->after('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('group_course');
            $table->foreign('group_id')->references('id')->on('first_course_group')->nullOnDelete();
        });
    }
};
