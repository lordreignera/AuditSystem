<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DetailedExcelAnalysis extends Command
{
    protected $signature = 'audit:detailed-analysis {file} {sheet} {startRow=1} {endRow=20}';
    protected $description = 'Detailed analysis of specific Excel sheet rows';

    public function handle()
    {
        $fileName = $this->argument('file');
        $sheetName = $this->argument('sheet');
        $startRow = (int) $this->argument('startRow');
        $endRow = (int) $this->argument('endRow');
        
        $filePath = storage_path("app/public/$fileName");
        
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }
        
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            
            $this->info("Detailed Analysis: $fileName -> $sheetName (Rows $startRow-$endRow)");
            $this->line(str_repeat('=', 80));
            
            for ($row = $startRow; $row <= $endRow; $row++) {
                $this->line("Row $row:");
                
                for ($col = 'A'; $col <= 'F'; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getCalculatedValue();
                    if (!empty(trim($cellValue))) {
                        $this->line("  $col: " . substr($cellValue, 0, 60) . (strlen($cellValue) > 60 ? '...' : ''));
                    }
                }
                $this->line("");
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
