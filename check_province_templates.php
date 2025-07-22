<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Path to your Excel file
$filePath = __DIR__ . '/storage/app/public/District Tool V1.xlsx';

// Load the spreadsheet
if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$spreadsheet = IOFactory::load($filePath);

// List all available sheet names
$allSheetNames = $spreadsheet->getSheetNames();
echo "Available sheets:\n";
foreach ($spreadsheet->getSheetNames() as $name) {
    echo "- [$name]\n";
}

// You can now copy the exact sheet names from above and use them in your next script!