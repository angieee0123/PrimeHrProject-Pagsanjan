<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
            $table->string('username')->unique()->nullable()->after('employee_id');
            $table->enum('role', ['employee', 'hr', 'admin'])->default('employee')->after('password');

            $table->dropColumn(['name', 'email_verified_at', 'remember_token']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'username', 'role']);
            $table->string('name')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
    }
};
