<?php
// Standalone script to parse the Excel file and print its structure/content

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$excelPath = __DIR__ . '/storage/app/public/District Health Office Audit Tool - PAK (Responses).xlsx';

if (!file_exists($excelPath)) {
    die("File not found: $excelPath\n");
}

$spreadsheet = IOFactory::load($excelPath);
$sheet = $spreadsheet->getActiveSheet();

// Print the first 10 rows and columns for preview
echo "Preview of the Excel file:\n";
foreach ($sheet->getRowIterator(1, 10) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $rowData = [];
    foreach ($cellIterator as $cell) {
        $rowData[] = $cell->getValue();
    }
    echo implode(" | ", $rowData) . "\n";
}

echo "\nTotal Rows: " . $sheet->getHighestRow() . "\n";
echo "Total Columns: " . $sheet->getHighestColumn() . "\n";

// Optionally, you can dump the full structure or specific logic here
// ...
