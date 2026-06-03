<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('teachers', 'default_room_id')) {
                $table->unsignedBigInteger('default_room_id')->nullable()->after('initials');
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'default_room_id')) {
                $table->foreign('default_room_id')->references('id')->on('rooms')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'default_room_id')) {
                $table->dropForeign(['default_room_id']);
                $table->dropColumn('default_room_id');
            }
        });
    }
};
