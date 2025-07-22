<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$excelFiles = [
    'District Tool V1.xlsx',
    'Health Facility Tool V1.xlsx', 
    'Provincial Tool V1.xlsx'
];

foreach ($excelFiles as $fileName) {
    $filePath = storage_path('app/public/' . $fileName);
    
    if (!file_exists($filePath)) {
        echo "File not found: $fileName\n";
        continue;
    }
    
    echo "=== ANALYZING: $fileName ===\n";
    
    try {
        $spreadsheet = IOFactory::load($filePath);
        $worksheetNames = $spreadsheet->getSheetNames();
        
        foreach ($worksheetNames as $sheetName) {
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            
            echo "\nSheet: $sheetName\n";
            echo "Dimensions: {$highestColumn}{$highestRow}\n";
            
            // Look for rows with pipe delimiters (table structures)
            for ($row = 1; $row <= min($highestRow, 20); $row++) {
                $rowData = [];
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getCalculatedValue();
                    if (!empty($cellValue)) {
                        $rowData[] = $cellValue;
                    }
                }
                
                $rowText = implode(' | ', $rowData);
                if (strpos($rowText, '|') !== false || count($rowData) > 3) {
                    echo "  Row $row: $rowText\n";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "Error reading $fileName: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
}
