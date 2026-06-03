<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        $now = now();

        $allCodes = [
            'А/т',
            'К/з it',
            '1it',
            '3it',
            '4it',
            '5it',
            '2 э. хаб',
            '1',
            '2',
            '2с',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
        ];

        $computerCodes = [
            'А/т',
            'К/з it',
            '1it',
            '3it',
            '4it',
            '5it',
            '2 э. хаб',
            '1',
            '2',
            '2с',
            '3',
            '5',
            '6',
            '7',
        ];

        $existing = DB::table('rooms')
            ->whereIn('code', $allCodes)
            ->pluck('id', 'code');

        $toInsert = [];
        foreach ($allCodes as $code) {
            if (isset($existing[$code])) {
                continue;
            }
            $toInsert[] = [
                'code' => $code,
                'type' => in_array($code, $computerCodes, true) ? 'computer' : 'standard',
                'is_active' => Schema::hasColumn('rooms', 'is_active') ? true : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($toInsert) {
            // Убираем null-поля, если в таблице нет is_active.
            $hasIsActive = Schema::hasColumn('rooms', 'is_active');
            if (!$hasIsActive) {
                $toInsert = array_map(function (array $row) {
                    unset($row['is_active']);
                    return $row;
                }, $toInsert);
            }
            DB::table('rooms')->insert($toInsert);
        }

        // Обновим тип для компьютерных кабинетов, даже если они уже существовали.
        DB::table('rooms')
            ->whereIn('code', $computerCodes)
            ->update([
                'type' => 'computer',
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Данные не удаляем намеренно.
    }
};
