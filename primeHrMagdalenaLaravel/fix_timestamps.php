<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = [
    'employees',
    'addresses',
    'contacts',
    'government_ids',
    'educations',
    'eligibilities',
    'work_experiences',
    'trainings',
    'family_members',
    'documents',
    'legal_requirements',
    'employment_details'
];

foreach ($tables as $table) {
    try {
        $columns = DB::select("SHOW COLUMNS FROM $table");
        $columnNames = array_column($columns, 'Field');

        if (!in_array('created_at', $columnNames)) {
            DB::statement("ALTER TABLE $table ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
            echo "✓ Added created_at to $table\n";
        } else {
            echo "✓ $table already has created_at\n";
        }

        if (!in_array('updated_at', $columnNames)) {
            DB::statement("ALTER TABLE $table ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "✓ Added updated_at to $table\n";
        } else {
            echo "✓ $table already has updated_at\n";
        }
    } catch (\Exception $e) {
        echo "✗ Error with $table: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ All tables updated with timestamps!\n";
