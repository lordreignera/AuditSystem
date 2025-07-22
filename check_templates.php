<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$booklets = [
    'District Tool V1.xlsx',
    'Provincial Tool V1.xlsx',
    'Health Facility Tool V1.xlsx',
];

foreach ($booklets as $fileName) {
    $filePath = "storage/app/public/$fileName";
    echo "\n============================\n";
    echo "Examining: $fileName\n";
    echo "============================\n";

    if (!file_exists($filePath)) {
        echo "File not found: $fileName\n";
        continue;
    }

    $spreadsheet = IOFactory::load($filePath);

    // List all sheet names
    echo "Sheet names:\n";
    foreach ($spreadsheet->getSheetNames() as $sheetName) {
        echo " - >" . $sheetName . "< (len:" . strlen($sheetName) . ")\n";
    }

    // Try to get the "Background" sheet (case-insensitive, trims, and allows for variants)
    $backgroundSheet = null;
    foreach ($spreadsheet->getSheetNames() as $name) {
        $nameTrimmed = strtolower(trim($name));
        if ($nameTrimmed === 'background' || $nameTrimmed === 'background information' || preg_match('/^background\b/i', $nameTrimmed)) {
            $backgroundSheet = $spreadsheet->getSheetByName($name);
            echo "Found background sheet: >$name<\n";
            break;
        }
    }

    if (!$backgroundSheet) {
        echo "No sheet named 'Background' found in $fileName.\n";
        continue;
    }

    $highestRow = $backgroundSheet->getHighestRow();
    $highestCol = $backgroundSheet->getHighestColumn();
    $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

    echo "Background sheet structure:\n";
    for ($row = 1; $row <= $highestRow; $row++) {
        $cells = [];
        for ($col = 1; $col <= $highestColIndex; $col++) {
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $cellValue = trim($backgroundSheet->getCell($cellCoordinate)->getFormattedValue() ?? '');
            $cells[] = $cellValue;
        }
        // Only print non-empty rows
        if (count(array_filter($cells, fn($v) => $v !== '')) > 0) {
            echo "Row $row: | " . implode(' | ', $cells) . " |\n";
        }
    }

    // Print merged cells info
    $mergedCells = $backgroundSheet->getMergeCells();
    if (!empty($mergedCells)) {
        echo "\nMerged cells:\n";
        foreach ($mergedCells as $range) {
            echo " - $range\n";
        }
    } else {
        echo "\nNo merged cells found.\n";
    }
    echo "\n";
}