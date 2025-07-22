<?php


require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Path to your Excel file
$filePath = __DIR__ . '/storage/app/public/District Tool V1.xlsx';

// List the sheet names you want to dump
$sheetNames = [
    'Stock Dispatch recon - DVS ',
    'Stock Dispatch recon - Tehsil',
    'Stock Out ',
];

if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$spreadsheet = IOFactory::load($filePath);

$allData = [];

foreach ($sheetNames as $sheetName) {
    $worksheet = $spreadsheet->getSheetByName($sheetName);
    if (!$worksheet) {
        echo "Sheet not found: $sheetName\n";
        continue;
    }

    $highestRow = $worksheet->getHighestRow();
    $highestCol = $worksheet->getHighestColumn();
    $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

    $rows = [];
    for ($row = 1; $row <= $highestRow; $row++) {
        $cells = [];
        for ($col = 1; $col <= $highestColIndex; $col++) {
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $cellValue = $worksheet->getCell($cellCoordinate)->getFormattedValue();
            $cells[] = $cellValue;
        }
        $rows[] = $cells;
    }

    $mergedCells = [];
    foreach ($worksheet->getMergeCells() as $range) {
        $mergedCells[] = $range;
    }

    $allData[$sheetName] = [
        'rows' => $rows,
        'merged_cells' => $mergedCells,
    ];
}

// Save to JSON file
$outputPath = __DIR__ . '/storage/app/public/dump_excel_tables.json';
file_put_contents($outputPath, json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Dumped table structures to: $outputPath\n";