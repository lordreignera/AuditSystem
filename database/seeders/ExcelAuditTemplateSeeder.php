<?php

namespace Database\Seeders;

use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelAuditTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Excel audit template import...');
        
        $excelFiles = [
            'District Tool V1.xlsx' => 'District',
            'Provincial Tool V1.xlsx' => 'Province/region', 
            'Health Facility Tool V1.xlsx' => 'Health Facility',
        ];

        // Extract health facility background template ONCE
        $healthFacilityBackground = $this->extractHealthFacilityBackgroundTemplate();

        foreach ($excelFiles as $fileName => $reviewTypeName) {
            $this->processExcelFile($fileName, $reviewTypeName, $healthFacilityBackground);
        }
        
        $this->command->info('Excel audit templates imported successfully!');
    }

    /**
     * Extract the Background sheet from Health Facility Tool V1.xlsx
     */
    private function extractHealthFacilityBackgroundTemplate()
    {
        $filePath = storage_path('app/public/Health Facility Tool V1.xlsx');
        if (!file_exists($filePath)) return null;
        $spreadsheet = IOFactory::load($filePath);

        $backgroundSheet = null;
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (preg_match('/^background\b/i', trim($name))) {
                $backgroundSheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }
        if (!$backgroundSheet) return null;

        $highestRow = $backgroundSheet->getHighestRow();
        $highestCol = $backgroundSheet->getHighestColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        $rows = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $cells = [];
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $cellValue = trim($backgroundSheet->getCell($cellCoordinate)->getFormattedValue() ?? '');
                $cells[] = $cellValue;
            }
            if (count(array_filter($cells, fn($v) => $v !== '')) > 0) {
                $rows[] = $cells;
            }
        }
        $mergedCells = $backgroundSheet->getMergeCells();
        return ['rows' => $rows, 'merged_cells' => $mergedCells];
    }
    
    private function processExcelFile($fileName, $reviewTypeName, $healthFacilityBackground)
    {
        $filePath = storage_path("app/public/$fileName");
        
        if (!file_exists($filePath)) {
            $this->command->warn("File not found: $fileName");
            return;
        }
        
        $this->command->info("Processing: $fileName");
        
        try {
            $reviewType = ReviewType::where('name', $reviewTypeName)->first();
            if (!$reviewType) {
                $this->command->error("Review type not found: {$reviewTypeName}");
                return;
            }
            
            $spreadsheet = IOFactory::load($filePath);
            $allSheetNames = $spreadsheet->getSheetNames();
            
            // Import EVERY sheet as a separate template
            foreach ($allSheetNames as $sheetName) {
                $this->processWorksheet($spreadsheet, $sheetName, $sheetName, $reviewType, $healthFacilityBackground);
            }
            
        } catch (\Exception $e) {
            $this->command->error("Error processing $fileName: " . $e->getMessage());
        }
    }
    
    private function processWorksheet($spreadsheet, $sheetName, $templateName, $reviewType, $healthFacilityBackground)
    {
        $this->command->line("  Processing sheet: $sheetName");

        try {
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $worksheet->getHighestRow();
            $highestCol = $worksheet->getHighestColumn();
            $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

            // Always create the template for every sheet
            $template = Template::firstOrCreate([
                'review_type_id' => $reviewType->id,
                'name' => $templateName,
            ], [
                'description' => "Audit template for {$templateName} - {$reviewType->name} level",
                'is_default' => true,
                'is_active' => true,
            ]);
            
            // Special handling for Background sheet: use Health Facility structure for ALL review types
            if (preg_match('/^background\b/i', trim($sheetName)) && $healthFacilityBackground) {
                $section = Section::firstOrCreate([
                    'template_id' => $template->id,
                    'name' => 'Background',
                ], [
                    'description' => 'Background information',
                    'order' => 1,
                    'is_active' => true,
                ]);

                $qOrder = 1;
                foreach ($healthFacilityBackground['rows'] as $cells) {
                    // Detect table header: 2+ consecutive non-empty cells
                    $consec = 0; $maxConsec = 0;
                    foreach ($cells as $cell) {
                        if ($cell !== '') { $consec++; $maxConsec = max($maxConsec, $consec); }
                        else { $consec = 0; }
                    }
                    if ($maxConsec >= 2) {
                        // Table (add 2 blank rows for user input)
                        $header = $cells;
                        $tableColCount = count($header);
                        $tableRows = []; $tableRows[] = $header;
                        for ($i = 0; $i < 2; $i++) $tableRows[] = array_fill(0, $tableColCount, '');

                        Question::firstOrCreate([
                            'section_id' => $section->id,
                            'question_text' => implode(' ', array_filter($header)) ?: 'Table',
                        ], [
                            'response_type' => 'table',
                            'options' => json_encode([
                                'rows' => $tableRows,
                                'merged_cells' => $healthFacilityBackground['merged_cells'],
                            ]),
                            'order' => $qOrder++,
                            'is_required' => false,
                            'is_active' => true,
                        ]);
                        continue;
                    }
                    // Otherwise, treat as text question
                    $questionText = '';
                    foreach ($cells as $cell) {
                        if ($cell !== '') { $questionText = $cell; break; }
                    }
                    if ($questionText) {
                        Question::firstOrCreate([
                            'section_id' => $section->id,
                            'question_text' => $questionText,
                        ], [
                            'response_type' => 'text',
                            'options' => null,
                            'order' => $qOrder++,
                            'is_required' => false,
                            'is_active' => true,
                        ]);
                    }
                }

                $this->command->line("    Created Background section and questions for $templateName (using Health Facility structure)");
                return;
            }

            // --- All other sheets: use your existing logic below ---
            $sectionOrder = 1;
            $questionOrder = 1;

            // First pass: check if any section header exists
            $foundSection = false;
            for ($row = 1; $row <= $highestRow; $row++) {
                $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2) . $row;
                $cellB = trim($worksheet->getCell($cellCoordinate)->getCalculatedValue() ?? '');
                if (preg_match('/^SECTION\s+\d+:/i', $cellB)) {
                    $foundSection = true;
                    break;
                }
            }

            // If no section header, handle multiple tables and non-contiguous areas
            if (!$foundSection) {
                $currentSection = Section::firstOrCreate([
                    'template_id' => $template->id,
                    'name' => $sheetName,
                ], [
                    'description' => 'Auto-generated section for entire sheet',
                    'order' => $sectionOrder++,
                    'is_active' => true,
                ]);

                $row = 1;
                $minTableCols = 3;
                $maxEmptyRows = 2;
                $tables = [];
                $currentTable = [];
                $descriptionRows = [];
                $emptyRowCount = 0;
                $tableStarted = false;
                $tableStartRow = null;

                // Get merged cells info
                $mergedCells = [];
                foreach ($worksheet->getMergeCells() as $range) {
                    $mergedCells[] = $range;
                }

                while ($row <= $highestRow) {
                    $cells = [];
                    for ($col = 1; $col <= $highestColIndex; $col++) {
                        $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                        $cellValue = trim($worksheet->getCell($cellCoordinate)->getFormattedValue() ?? '');
                        $cells[] = $cellValue;
                    }
                    $nonEmptyCount = count(array_filter($cells, fn($v) => $v !== ''));

                    if (!$tableStarted) {
                        if ($nonEmptyCount >= $minTableCols) {
                            $tableStarted = true;
                            $currentTable = [];
                            $tableStartRow = $row;
                            $currentTable[] = $cells;
                            $emptyRowCount = 0;
                        } else {
                            if ($nonEmptyCount > 0) {
                                $descriptionRows[] = implode(' ', array_filter($cells));
                            }
                        }
                    } else {
                        if ($nonEmptyCount == 0) {
                            $emptyRowCount++;
                            if ($emptyRowCount >= $maxEmptyRows) {
                                // End of current table
                                if (count($currentTable) > 1) {
                                    $tables[] = [
                                        'rows' => $currentTable,
                                        'start_row' => $tableStartRow,
                                    ];
                                }
                                $tableStarted = false;
                                $currentTable = [];
                                $emptyRowCount = 0;
                            } else {
                                $currentTable[] = $cells;
                            }
                        } else {
                            $emptyRowCount = 0;
                            $currentTable[] = $cells;
                        }
                    }
                    $row++;
                }
                // Save last table if still open
                if ($tableStarted && count($currentTable) > 1) {
                    $tables[] = [
                        'rows' => $currentTable,
                        'start_row' => $tableStartRow,
                    ];
                }

                // Now save each table as a separate question
                foreach ($tables as $i => $table) {
                    // Filter merged cells for this table only
                    $tableMergedCells = [];
                    foreach ($mergedCells as $range) {
                        // Parse range like "E9:G10"
                        if (preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $range, $m)) {
                            $startRow = intval($m[2]);
                            $endRow = intval($m[4]);
                            if ($startRow >= $table['start_row'] && $endRow <= $table['start_row'] + count($table['rows']) - 1) {
                                $tableMergedCells[] = $range;
                            }
                        }
                    }
                    $desc = !empty($descriptionRows) ? implode(' ', $descriptionRows) : "Table ".($i+1)." in $sheetName";
                    Question::firstOrCreate([
                        'section_id' => $currentSection->id,
                        'question_text' => $desc,
                    ], [
                        'response_type' => 'table',
                        'options' => json_encode([
                            'rows' => $table['rows'],
                            'merged_cells' => $tableMergedCells,
                        ]),
                        'order' => $questionOrder++,
                        'is_required' => false,
                        'is_active' => true,
                    ]);
                    // Only use descriptionRows for the first table
                    $descriptionRows = [];
                }

                $this->command->line("    Created template: $templateName with " . $template->sections()->count() . " sections");
                return;
            }

            // If there are sections, use your existing logic
            $row = 1;
            $currentSection = null;
            while ($row <= $highestRow) {
                // Read all columns for this row
                $cells = [];
                for ($col = 1; $col <= $highestColIndex; $col++) {
                    $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                    $cellValue = trim($worksheet->getCell($cellCoordinate)->getCalculatedValue() ?? '');
                    $cells[] = $cellValue;
                }
            
                // Assign to A, B, C, D for backward compatibility
                $cellA = $cells[0] ?? '';
                $cellB = $cells[1] ?? '';
                $cellC = $cells[2] ?? '';
                $cellD = $cells[3] ?? '';

                // Check for section headers
                if (preg_match('/^SECTION\s+\d+:/i', $cellB)) {
                    $currentSection = Section::firstOrCreate([
                        'template_id' => $template->id,
                        'name' => $cellB,
                    ], [
                        'description' => '',
                        'order' => $sectionOrder++,
                        'is_active' => true,
                    ]);
                    $questionOrder = 1;
                    $row++;
                    continue;
                }

                // Normal question logic (as before)
                $questionText = '';
                if (!empty($cellB) && !preg_match('/^(instruction|all yellow|name|location|date|team)/i', $cellB)) {
                    $questionText = $cellB;
                } elseif (!empty($cellA) && strlen($cellA) > 10) {
                    $questionText = $cellA;
                }

                if (!empty($questionText) && $this->isValidQuestion($questionText)) {
                    if (!$currentSection) {
                        $currentSection = Section::firstOrCreate([
                            'template_id' => $template->id,
                            'name' => 'General Information',
                        ], [
                            'description' => 'General questions and information',
                            'order' => $sectionOrder++,
                            'is_active' => true,
                        ]);
                    }

                    $responseType = $this->guessResponseType($questionText, $cellC, $cellD);
                    // Only allow valid response types
                    $allowedTypes = ['text', 'table', 'yes_no', 'date', 'number', 'select', 'textarea'];
                    if (!in_array($responseType, $allowedTypes)) {
                        $responseType = 'text';
                    }
                    $options = $this->extractOptions($cellC, $cellD);

                    // Only allow options for certain types
                    if (!in_array($responseType, ['select', 'yes_no', 'table'])) {
                        $options = null;
                    }

                    Question::firstOrCreate([
                        'section_id' => $currentSection->id,
                        'question_text' => $questionText,
                    ], [
                        'response_type' => $responseType,
                        'options' => $options,
                        'order' => $questionOrder++,
                        'is_required' => false,
                        'is_active' => true,
                    ]);
                }
                $row++;
            }
            
            $this->command->line("    Created template: $templateName with " . $template->sections()->count() . " sections");
            
        } catch (\Exception $e) {
            $this->command->error("    Error processing sheet $sheetName: " . $e->getMessage());
        }
    }

    // Helper: does this row look like a table header (many non-empty, not just numbers)
    private function looksLikeTableHeader($cells)
    {
        $nonEmpty = array_filter($cells, function($v) { return $v !== ''; });
        if (count($nonEmpty) < 3) return false;
        $textCount = 0;
        foreach ($nonEmpty as $cell) {
            if (preg_match('/[a-zA-Z]/', $cell)) $textCount++;
        }
        return $textCount >= 2;
    }
    
    private function isValidQuestion($text)
    {
        // Filter out non-questions
        $excludePatterns = [
            '/^(instructions?|all yellow|note|nb|hint)/i',
            '/^(name|location|date|team|position|phone|email)$/i',
            '/^\s*$/i',
        ];
        
        foreach ($excludePatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return false;
            }
        }
        
        return strlen($text) > 5;
    }
    
    private function guessResponseType($questionText, $cellC, $cellD)
    {
        $combined = strtolower($questionText . ' ' . $cellC . ' ' . $cellD);
        
        if (preg_match('/\b(yes|no)\b.*\b(yes|no)\b/i', $combined)) return 'yes_no';
        if (preg_match('/date/i', $combined)) return 'date';
        if (preg_match('/number|count|quantity|how many/i', $combined)) return 'number';
        if (preg_match('/select|choose|option/i', $combined)) return 'select';
        if (preg_match('/table|matrix|list of/i', $combined)) return 'table';
        if (strlen($questionText) > 100) return 'textarea';
        
        return 'text';
    }
    
    private function extractOptions($cellC, $cellD)
    {
        $options = [];
        
        // Check for Yes/No options
        if (preg_match('/\b(yes|no)\b.*\b(yes |no)\b/i', $cellC . ' ' . $cellD)) {
            $options = ['yes', 'no'];
        } elseif (!empty($cellC)) {
            // Split by commas or semicolons
            $opts = preg_split('/[;,]/', $cellC);
            foreach ($opts as $opt) {
                $trimmed = trim($opt);
                if (!empty($trimmed)) {
                    $options[] = $trimmed;
                }
            }
        } 
        return !empty($options) ? json_encode($options) : null;
    }
}