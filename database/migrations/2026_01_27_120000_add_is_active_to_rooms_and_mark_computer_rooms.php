<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('type');
                $table->index('is_active', 'rooms_is_active_idx');
            }
        });

        // Компьютерные кабинеты на текущий семестр (можно расширить позже).
        $computerCodes = [
            'А/т',
            'К/з it',
            '1it',
            '2it',
            '3it',
            '4it',
            '5it',
            '2 э. хаб',
        ];

        DB::table('rooms')
            ->whereIn('code', $computerCodes)
            ->update([
                'type' => 'computer',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'is_active')) {
                $table->dropIndex('rooms_is_active_idx');
                $table->dropColumn('is_active');
            }
        });
    }
};

