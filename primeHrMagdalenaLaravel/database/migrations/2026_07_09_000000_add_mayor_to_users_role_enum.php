<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('employee', 'hr', 'admin', 'mayor') NOT NULL DEFAULT 'employee'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'employee' WHERE role = 'mayor'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('employee', 'hr', 'admin') NOT NULL DEFAULT 'employee'");
    }
};
