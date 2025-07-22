<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalyzeExcelStructure extends Command
{
    protected $signature = 'audit:analyze-excel {file?}';
    protected $description = 'Analyze Excel file structure for audit templates';

    public function handle()
    {
        $fileName = $this->argument('file');
        
        if (!$fileName) {
            $this->info('Available Excel files:');
            $files = collect(['District Tool V1.xlsx', 'Health Facility Tool V1.xlsx', 'Provincial Tool V1.xlsx']);
            foreach ($files as $file) {
                $this->line("- $file");
            }
            $fileName = $this->choice('Which file would you like to analyze?', $files->toArray());
        }
        
        $filePath = storage_path("app/public/$fileName");
        
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }
        
        $this->info("Analyzing: $fileName");
        $this->line(str_repeat('=', 50));
        
        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($filePath);
            $worksheetNames = $spreadsheet->getSheetNames();
            
            $this->info("Number of sheets: " . count($worksheetNames));
            $this->line("");
            
            foreach ($worksheetNames as $index => $sheetName) {
                $this->info("Sheet $index: $sheetName");
                
                // Get worksheet
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                
                $this->line("  Dimensions: {$highestColumn}{$highestRow}");
                
                // Show first few rows with data
                $this->line("  Sample data:");
                for ($row = 1; $row <= min(5, $highestRow); $row++) {
                    $rowData = [];
                    for ($col = 'A'; $col <= min('E', $highestColumn); $col++) {
                        $cellValue = $worksheet->getCell($col . $row)->getCalculatedValue();
                        if (!empty($cellValue)) {
                            $rowData[] = substr($cellValue, 0, 30); // Limit length
                        }
                    }
                    if (!empty($rowData)) {
                        $this->line("    Row $row: " . implode(' | ', $rowData));
                    }
                }
                $this->line("");
            }
            
        } catch (\Exception $e) {
            $this->error("Error reading Excel file: " . $e->getMessage());
        }
    }
}
