<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teachers')) {
            return;
        }
        if (!Schema::hasColumn('teachers', 'initials')) {
            return;
        }

        DB::statement('ALTER TABLE `teachers` MODIFY `initials` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('teachers')) {
            return;
        }
        if (!Schema::hasColumn('teachers', 'initials')) {
            return;
        }

        DB::statement('ALTER TABLE `teachers` MODIFY `initials` VARCHAR(20) NULL');
    }
};
