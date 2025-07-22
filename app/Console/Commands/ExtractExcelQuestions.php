<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExtractExcelQuestions extends Command
{
    protected $signature = 'audit:extract-questions {file?} {sheet?}';
    protected $description = 'Extract questions from Excel audit templates';

    public function handle()
    {
        $fileName = $this->argument('file');
        $sheetName = $this->argument('sheet');
        
        if (!$fileName) {
            $files = ['District Tool V1.xlsx', 'Health Facility Tool V1.xlsx', 'Provincial Tool V1.xlsx'];
            $fileName = $this->choice('Which file would you like to analyze?', $files);
        }
        
        $filePath = storage_path("app/public/$fileName");
        
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }
        
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            if (!$sheetName) {
                $worksheetNames = $spreadsheet->getSheetNames();
                $this->info("Available sheets:");
                foreach ($worksheetNames as $name) {
                    $this->line("- $name");
                }
                $sheetName = $this->choice('Which sheet would you like to extract?', $worksheetNames);
            }
            
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $worksheet->getHighestRow();
            
            $this->info("Extracting questions from: $fileName -> $sheetName");
            $this->line(str_repeat('=', 60));
            
            $questions = [];
            $currentSection = '';
            $questionNumber = 1;
            
            for ($row = 1; $row <= $highestRow; $row++) {
                $cellA = trim($worksheet->getCell('A' . $row)->getCalculatedValue() ?? '');
                $cellB = trim($worksheet->getCell('B' . $row)->getCalculatedValue() ?? '');
                $cellC = trim($worksheet->getCell('C' . $row)->getCalculatedValue() ?? '');
                
                // Check if this is a section header
                if (preg_match('/^SECTION\s+\d+:/i', $cellA)) {
                    $currentSection = $cellA;
                    $this->info("\n📁 $currentSection");
                    continue;
                }
                
                // Check if this is a question
                if (!empty($cellA) && (
                    preg_match('/^\d+[\.\)]\s*/', $cellA) || // Numbered question
                    preg_match('/^[a-z]\)/', $cellA) || // Letter question
                    (!empty($cellB) && preg_match('/yes|no|select|date|number/i', $cellB)) // Has response type
                )) {
                    $questionText = $cellA;
                    $responseType = $this->guessResponseType($cellB, $cellC);
                    
                    $this->line("  ❓ Q{$questionNumber}: " . substr($questionText, 0, 80) . (strlen($questionText) > 80 ? '...' : ''));
                    $this->line("     Type: $responseType");
                    
                    $questions[] = [
                        'section' => $currentSection,
                        'question' => $questionText,
                        'response_type' => $responseType,
                        'row' => $row
                    ];
                    
                    $questionNumber++;
                }
            }
            
            $this->line(str_repeat('=', 60));
            $this->info("Total questions found: " . count($questions));
            
            // Ask if user wants to save this structure
            if ($this->confirm('Would you like to save this structure as a PHP array?')) {
                $this->saveStructureAsArray($fileName, $sheetName, $questions);
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
    
    private function guessResponseType($cellB, $cellC)
    {
        $combined = strtolower($cellB . ' ' . $cellC);
        
        if (preg_match('/yes.*no|y\/n/i', $combined)) return 'yes_no';
        if (preg_match('/select|choose|option/i', $combined)) return 'select';
        if (preg_match('/date/i', $combined)) return 'date';
        if (preg_match('/number|count|quantity/i', $combined)) return 'number';
        if (preg_match('/table|matrix/i', $combined)) return 'table';
        if (strlen($cellB) > 100) return 'textarea';
        
        return 'text';
    }
    
    private function saveStructureAsArray($fileName, $sheetName, $questions)
    {
        $reviewType = '';
        if (str_contains($fileName, 'District')) $reviewType = 'District';
        elseif (str_contains($fileName, 'Provincial')) $reviewType = 'Province'; 
        elseif (str_contains($fileName, 'Health Facility')) $reviewType = 'Health Facility';
        
        $templateName = ucwords(str_replace(['_', '-'], ' ', $sheetName));
        
        $structure = [
            'review_type' => $reviewType,
            'template' => $templateName,
            'sections' => []
        ];
        
        $sections = [];
        foreach ($questions as $q) {
            $sectionName = $q['section'] ?: 'General Questions';
            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = [];
            }
            $sections[$sectionName][] = [
                'question_text' => $q['question'],
                'response_type' => $q['response_type'],
                'is_required' => true
            ];
        }
        
        $structure['sections'] = $sections;
        
        $output = "<?php\n\nreturn " . var_export($structure, true) . ";\n";
        
        $outputFile = storage_path("app/audit_structures/{$reviewType}_{$templateName}.php");
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $output);
        
        $this->info("Structure saved to: $outputFile");
    }
}
