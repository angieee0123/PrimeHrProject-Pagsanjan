<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Models\LeaveAccrualRate;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Services\CscTimeConversionService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\LeaveFormDataService;
use App\Services\LeaveImportService;

class LeaveController extends Controller
{
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header section (Rows 1-5)
            $sheet->setCellValue('A1', 'Employee Name:');
            $sheet->setCellValue('B1', '[Your Name]');
            $sheet->setCellValue('A2', 'Position:');
            $sheet->setCellValue('B2', '[Your Position]');
            $sheet->setCellValue('A3', 'Department:');
            $sheet->setCellValue('B3', '[Your Department]');
            $sheet->setCellValue('A4', 'Date Range:');
            $sheet->setCellValue('B4', 'Jun-19 to May-20');
            $sheet->setCellValue('A5', 'Notes:');
            $sheet->setCellValue('B5', 'Historical leave records');
            
            // Column headers (Row 6)
            $sheet->setCellValue('A6', 'Month/Year');
            $sheet->setCellValue('B6', 'Notes');
            $sheet->setCellValue('D6', 'VL Earned');
            $sheet->setCellValue('F6', 'VL Used');
            $sheet->setCellValue('H6', 'SL Earned');
            $sheet->setCellValue('J6', 'SL Used');
            $sheet->setCellValue('M6', 'VL Balance');
            $sheet->setCellValue('N6', 'SL Balance');
            
            // Style headers
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0B044D'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ];
            
            foreach (['A', 'B', 'D', 'F', 'H', 'J', 'M', 'N'] as $col) {
                $sheet->getStyle($col . '6')->applyFromArray($headerStyle);
            }
            
            // Add sample rows with sample data (7-14)
            $sampleData = [
                ['Jun-19', 'VL1', 2.5, 1.0, 1.5, 0.5, 1.5, 1.0],
                ['Jul-19', 'FL1', 2.5, 2.5, 1.5, 0.0, 0.0, 1.5],
                ['Aug-19', 'T(0-2-10)', 2.5, 0.0, 1.5, 1.0, 2.5, 0.5],
                ['Sep-19', '', 2.5, 0.5, 1.5, 0.5, 2.0, 1.0],
                ['Oct-19', 'VL1', 2.5, 2.0, 1.5, 1.5, 0.5, 0.0],
                ['Nov-19', '', 2.5, 1.5, 1.5, 0.0, 1.5, 1.5],
                ['Dec-19', 'SL1', 2.5, 0.0, 1.5, 1.0, 2.5, 0.5],
                ['Jan-20', '', 2.5, 0.5, 1.5, 0.5, 2.0, 1.0],
            ];
            
            for ($row = 7; $row <= 14; $row++) {
                $dataIndex = $row - 7;
                $data = $sampleData[$dataIndex] ?? [];
                
                if (!empty($data)) {
                    $sheet->setCellValue('A' . $row, $data[0] ?? '');
                    $sheet->setCellValue('B' . $row, $data[1] ?? '');
                    $sheet->setCellValue('D' . $row, $data[2] ?? '');
                    $sheet->setCellValue('F' . $row, $data[3] ?? '');
                    $sheet->setCellValue('H' . $row, $data[4] ?? '');
                    $sheet->setCellValue('J' . $row, $data[5] ?? '');
                    $sheet->setCellValue('M' . $row, $data[6] ?? '');
                    $sheet->setCellValue('N' . $row, $data[7] ?? '');
                }
            }
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(12);
            $sheet->getColumnDimension('J')->setWidth(12);
            $sheet->getColumnDimension('M')->setWidth(12);
            $sheet->getColumnDimension('N')->setWidth(12);
            
            // Generate file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'Leave_Records_Template_' . now()->format('Y-m-d_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate template: ' . $e->getMessage(),
            ], 500);
        }
    }
}
