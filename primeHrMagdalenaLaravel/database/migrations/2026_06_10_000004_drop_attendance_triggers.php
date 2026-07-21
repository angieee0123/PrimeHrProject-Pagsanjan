<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS attendance_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS attendance_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS attendance_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS attendance_before_update');
    }

    public function down(): void
    {
        // Cannot restore triggers without knowing their original definition
    }
};
