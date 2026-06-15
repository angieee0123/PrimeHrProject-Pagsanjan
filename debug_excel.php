<?php
require_once 'primeHrMagdalenaLaravel/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'Leave_Records_Template_2026-06-15_034829.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    
    echo "=== EXCEL FILE STRUCTURE ===\n";
    echo "Highest Row: " . $worksheet->getHighestDataRow() . "\n";
    echo "Highest Column: " . $worksheet->getHighestDataColumn() . "\n\n";
    
    echo "=== FIRST 20 ROWS (All Columns) ===\n";
    for ($row = 1; $row <= min(20, $worksheet->getHighestDataRow()); $row++) {
        echo "Row $row: ";
        for ($col = 'A'; $col <= 'N'; $col++) {
            $cell = $worksheet->getCell($col . $row);
            $value = $cell->getCalculatedValue();
            if ($value !== null && $value !== '') {
                echo "$col=" . var_export($value, true) . " | ";
            }
        }
        echo "\n";
    }
    
    echo "\n=== CHECKING FOR MONTH NAMES IN COLUMN A ===\n";
    for ($row = 1; $row <= $worksheet->getHighestDataRow(); $row++) {
        $colA = trim((string)$worksheet->getCell('A' . $row)->getCalculatedValue());
        if ($colA !== '') {
            echo "Row $row, Col A: '$colA'\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
