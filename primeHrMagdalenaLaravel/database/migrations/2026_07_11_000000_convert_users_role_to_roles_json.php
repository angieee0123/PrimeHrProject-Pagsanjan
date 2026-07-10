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
        DB::statement("ALTER TABLE users ADD COLUMN roles JSON NULL AFTER role");
        DB::statement("UPDATE users SET roles = JSON_ARRAY(role) WHERE role IS NOT NULL");
        DB::statement("UPDATE users SET roles = JSON_ARRAY('employee') WHERE roles IS NULL");
        DB::statement("ALTER TABLE users MODIFY COLUMN roles JSON NOT NULL");
        DB::statement("ALTER TABLE users DROP COLUMN role");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users ADD COLUMN role ENUM('employee', 'hr', 'admin', 'mayor') NOT NULL DEFAULT 'employee' AFTER roles");
        DB::statement("UPDATE users SET role = JSON_UNQUOTE(JSON_EXTRACT(roles, '$[0]'))");
        DB::statement("ALTER TABLE users DROP COLUMN roles");
    }
};
