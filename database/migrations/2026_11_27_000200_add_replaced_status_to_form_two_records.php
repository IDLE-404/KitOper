<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем статус replaced в enum.
        DB::statement("
            ALTER TABLE form_two_records
            MODIFY status ENUM('normal','sick','replacement','replaced') DEFAULT 'normal'
        ");
    }

    public function down(): void
    {
        // Возвращаем исходный перечень статусов.
        DB::statement("
            ALTER TABLE form_two_records
            MODIFY status ENUM('normal','sick','replacement') DEFAULT 'normal'
        ");
    }
};
