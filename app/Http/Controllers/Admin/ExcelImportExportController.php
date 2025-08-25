<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\AuditReviewTypeAttachment;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;
use App\Models\Response;
use App\Models\AuditTemplateCustomization;
use App\Models\AuditQuestionCustomization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelImportExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:edit audits']);
    }

    /**
     * Export a specific attachment (location) as an XLSX booklet:
     * - One sheet per template
     * - Includes effective (customized) structure
     * - Includes per-attachment responses
     */
    public function exportAttachmentXlsx(Audit $audit, $attachmentId)
    {
        try {
            $attachment = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('id', $attachmentId)
                ->with('reviewType')
                ->firstOrFail();

            $reviewType = $attachment->reviewType;
            $templates = $this->getEffectiveTemplatesForAudit($audit, $reviewType->id);

            if ($templates->isEmpty()) {
                return redirect()->back()->with('error', 'No templates found to export for this review type.');
            }

            $spreadsheet = new Spreadsheet();
            // Remove default blank sheet
            $spreadsheet->removeSheetByIndex(0);

            // Index sheet
            $index = new Worksheet($spreadsheet, 'Index');
            $spreadsheet->addSheet($index, 0);
            $r = 1;
            $index->setCellValue("A{$r}", 'Audit');       $index->setCellValue("B{$r}", $audit->name); $r++;
            $index->setCellValue("A{$r}", 'Review Type'); $index->setCellValue("B{$r}", $reviewType->name); $r++;
            $index->setCellValue("A{$r}", 'Location');     $index->setCellValue("B{$r}", $attachment->getContextualLocationName()); $r += 2;
            $index->setCellValue("A{$r}", 'Templates'); $r++;

            $usedNames = ['Index'];
            foreach ($templates as $i => $tpl) {
                $sheetName = $this->sanitizeSheetName($tpl['name'], $usedNames);
                $usedNames[] = $sheetName;
                $index->setCellValue('A' . ($r + $i), $tpl['name']);

                $sheet = new Worksheet($spreadsheet, $sheetName);
                $spreadsheet->addSheet($sheet);

                $this->writeTemplateSheetWithResponses($sheet, $tpl, $audit, $attachment);
            }

            $spreadsheet->setActiveSheetIndex(0);

            $reviewTypeName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $reviewType->name);
            $locationName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $attachment->getContextualLocationName());
            $fileName = "Audit_{$audit->id}_{$reviewTypeName}_{$locationName}_booklet.xlsx";

            $writer = new Xlsx($spreadsheet);
            return new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            }, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                'Cache-Control'       => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            ]);
        } catch (\Exception $e) {
            Log::error('Export XLSX failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import a booklet XLSX for a Review Type:
     * - import_mode: new|update
     * - location_name (required if new)
     * - attachment_id (required if update)
     * - The workbook must be the same format as exportAttachmentXlsx:
     *   One sheet per template, columns A-K as defined below.
     * This writes Response records aligned to the specific attachment.
     */
// ...existing code...
    public function importBooklet(Request $request, Audit $audit, ReviewType $reviewType)
    {
        $validator = Validator::make($request->all(), [
            'excel_file'    => 'required|file|mimes:xlsx|max:20480', // 20MB
            'import_mode'   => 'required|in:new,update',
            'location_name' => 'required_if:import_mode,new|string|max:255',
            'attachment_id' => 'required_if:import_mode,update|exists:audit_review_type_attachments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Resolve attachment (new or update)
            $attachment = $this->resolveAttachmentForImport(
                $audit,
                $reviewType,
                $request->string('import_mode')->toString(),
                $request->string('location_name')->toString(),
                $request->input('attachment_id')
            );

            $file = $request->file('excel_file')->getRealPath();
            $reader = new XlsxReader();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);

            // Build template lookup: prefer audit-specific else default
            $templateMap = $this->buildTemplateLookup($audit, $reviewType->id);

            // Initialize counters for logging
            $importStats = [
                'total_rows' => 0,
                'responses_imported' => 0,
                'questions_not_found' => 0,
                'templates_processed' => 0,
                'table_questions_imported' => 0,
            ];

            $notFoundQuestions = [];

            $sheetCount = $spreadsheet->getSheetCount();
            for ($i = 0; $i < $sheetCount; $i++) {
                $sheet = $spreadsheet->getSheet($i);
                $sheetName = trim($sheet->getTitle());
                
                if (strtolower($sheetName) === 'index') {
                    continue; // skip index
                }

                Log::info("Processing sheet: {$sheetName}");

                $template = $this->matchTemplateBySheetName($sheetName, $templateMap);
                if (!$template) {
                    Log::warning("Template not found for sheet: {$sheetName}");
                    continue;
                }

                $importStats['templates_processed']++;
                Log::info("Template matched: {$template->name} (ID: {$template->id})");

                $highestRow = $sheet->getHighestDataRow();

                // --- Group table answers by question ---
                $tableAnswers = []; // [question_id => [ [row1], [row2], ... ] ]
                $tableNotes = [];   // [question_id => [note1, note2, ...]]

                for ($row = 2; $row <= $highestRow; $row++) {
                    $importStats['total_rows']++;
                    
                    $sectionName   = trim((string) $sheet->getCell("B{$row}")->getValue());
                    $questionText  = trim((string) $sheet->getCell("E{$row}")->getValue());
                    $responseType  = trim((string) $sheet->getCell("F{$row}")->getValue());
                    $answerCell    = $sheet->getCell("J{$row}")->getValue();
                    $auditNoteCell = $sheet->getCell("K{$row}")->getValue();

                    if ($sectionName === '' && $questionText === '') {
                        continue;
                    }

                    // Try exact match first
                    $question = $this->findQuestion($template->id, $sectionName, $questionText);
                    if (!$question) {
                        // Try a more lenient match (normalize whitespace)
                        $question = $this->findQuestionLenient($template->id, $sectionName, $questionText);
                    }
                    
                    if (!$question) {
                        $importStats['questions_not_found']++;
                        $notFoundQuestions[] = [
                            'sheet' => $sheetName,
                            'section' => $sectionName,
                            'question' => $questionText,
                            'row' => $row
                        ];
                        Log::warning("Question not found - Sheet: {$sheetName}, Section: {$sectionName}, Question: {$questionText}");
                        continue;
                    }

                    Log::debug("Question matched: {$question->question_text} (ID: {$question->id})");

                    if ($responseType === 'table') {
                        // Split the answerCell by tab (as exported)
                        $rowData = is_string($answerCell) ? preg_split('/\t/', $answerCell) : (array)$answerCell;
                        $tableAnswers[$question->id][] = $rowData;
                        $tableNotes[$question->id][] = is_null($auditNoteCell) ? '' : (string)$auditNoteCell;
                        continue;
                    }

                    $normalizedAnswer = $this->normalizeAnswerForStore($answerCell, $responseType);

                    $response = Response::updateOrCreate(
                        [
                            'audit_id'      => $audit->id,
                            'attachment_id' => $attachment->id,
                            'question_id'   => $question->id,
                        ],
                        [
                            'answer'     => $normalizedAnswer,
                            'audit_note' => is_null($auditNoteCell) ? '' : (string)$auditNoteCell,
                            'created_by' => auth()->id(),
                        ]
                    );

                    $importStats['responses_imported']++;
                    Log::debug("Response saved for question ID: {$question->id}");
                }

                // Save grouped table answers
                foreach ($tableAnswers as $questionId => $tableRows) {
                    $notes = isset($tableNotes[$questionId]) ? implode("\n", $tableNotes[$questionId]) : '';
                    
                    $response = Response::updateOrCreate(
                        [
                            'audit_id'      => $audit->id,
                            'attachment_id' => $attachment->id,
                            'question_id'   => $questionId,
                        ],
                        [
                            'answer'     => ['value' => $tableRows, 'type' => 'table'],
                            'audit_note' => $notes,
                            'created_by' => auth()->id(),
                        ]
                    );

                    $importStats['table_questions_imported']++;
                    $importStats['responses_imported']++;
                    Log::debug("Table response saved for question ID: {$questionId}");
                }
            }

            // Log import summary
            Log::info('Import completed', $importStats);

            // Create success message with stats
            $successMessage = "Booklet imported successfully! ";
            $successMessage .= "Imported {$importStats['responses_imported']} responses ";
            $successMessage .= "from {$importStats['templates_processed']} templates.";
            
            if ($importStats['questions_not_found'] > 0) {
                $successMessage .= " Note: {$importStats['questions_not_found']} questions were not found and skipped.";
            }

            // If in debug mode, also log questions not found
            if (!empty($notFoundQuestions)) {
                Log::warning('Questions not found during import:', $notFoundQuestions);
            }

            return redirect()->route('admin.audits.dashboard', $audit)
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            Log::error('Import booklet failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'audit_id' => $audit->id,
                'review_type_id' => $reviewType->id,
                'file_name' => $request->file('excel_file')->getClientOriginalName()
            ]);
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // Enhanced question finding with better logging
    private function findQuestion(int $templateId, string $sectionName, string $questionText): ?Question
    {
        return Question::whereHas('section', function ($q) use ($templateId, $sectionName) {
                $q->where('template_id', $templateId)
                ->where('name', $sectionName);
            })
            ->where('question_text', $questionText)
            ->first();
    }

    private function findQuestionLenient(int $templateId, string $sectionName, string $questionText): ?Question
    {
        $normSec = $this->normalizeName($sectionName);
        $normQ   = $this->normalizeSpace($questionText);

        return Question::whereHas('section', function ($q) use ($templateId, $normSec) {
                $q->where('template_id', $templateId);
            })
            ->get()
            ->filter(function (Question $q) use ($normSec, $normQ) {
                return $this->normalizeName($q->section->name) === $normSec
                    && $this->normalizeSpace($q->question_text) === $normQ;
            })
            ->first();
    }

    // Add a debug method to check what questions exist in a template
    public function debugTemplateStructure(Audit $audit, ReviewType $reviewType)
    {
        $templateMap = $this->buildTemplateLookup($audit, $reviewType->id);
        
        $structure = [];
        foreach ($templateMap as $normalizedName => $template) {
            $sections = [];
            foreach ($template->sections as $section) {
                $questions = [];
                foreach ($section->questions as $question) {
                    $questions[] = [
                        'id' => $question->id,
                        'text' => $question->question_text,
                        'type' => $question->response_type
                    ];
                }
                $sections[] = [
                    'id' => $section->id,
                    'name' => $section->name,
                    'questions' => $questions
                ];
            }
            $structure[] = [
                'template_name' => $template->name,
                'normalized_name' => $normalizedName,
                'sections' => $sections
            ];
        }
        
        Log::info('Template structure for debugging:', $structure);
        return response()->json($structure);
    }
// ...existing code...
// ...existing

    // Removed duplicate writeTemplateSheetWithResponses method to fix redeclaration error.

    /**
     * Effective templates for audit + reviewType:
     * - Prefer audit-specific templates
     * - Else use defaults overlaid with this audit's customizations
     */
    private function getEffectiveTemplatesForAudit(Audit $audit, int $reviewTypeId)
    {
        // 1) Audit-specific templates if any
        $auditTemplates = Template::where('review_type_id', $reviewTypeId)
            ->where('audit_id', $audit->id)
            ->with([
                'sections' => fn($q) => $q->orderBy('order'),
                'sections.questions' => fn($q) => $q->orderBy('order'),
            ])
            ->orderBy('id')
            ->get();

        if ($auditTemplates->isNotEmpty()) {
            return $auditTemplates->map(function (Template $t) {
                return [
                    'id'          => $t->id,
                    'name'        => $t->name,
                    'description' => $t->description,
                    'sections'    => $t->sections->map(function (Section $s) {
                        return [
                            'id'          => $s->id,
                            'name'        => $s->name,
                            'description' => $s->description,
                            'order'       => $s->order,
                            'questions'   => $s->questions->map(function (Question $q) {
                                return [
                                    'id'            => $q->id,
                                    'question_text' => $q->question_text,
                                    'response_type' => $q->response_type,
                                    'options'       => $q->options,
                                    'order'         => $q->order,
                                    'is_required'   => (bool)$q->is_required,
                                    'description'   => $q->description,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all(),
                ];
            });
        }

        // 2) Defaults + overlays
        $defaults = Template::where('review_type_id', $reviewTypeId)
            ->whereNull('audit_id')
            ->with([
                'sections' => fn($q) => $q->orderBy('order'),
                'sections.questions' => fn($q) => $q->orderBy('order'),
            ])
            ->orderBy('id')
            ->get();

        if ($defaults->isEmpty()) {
            return collect();
        }

        $templateIds = $defaults->pluck('id');
        $tmplCus = AuditTemplateCustomization::where('audit_id', $audit->id)
            ->whereIn('default_template_id', $templateIds)
            ->get()
            ->keyBy('default_template_id');

        $defaultQuestions = $defaults->flatMap(fn(Template $t) => $t->sections->flatMap(fn(Section $s) => $s->questions));
        $questionIds = $defaultQuestions->pluck('id');
        $qCus = AuditQuestionCustomization::where('audit_id', $audit->id)
            ->whereIn('default_question_id', $questionIds)
            ->get()
            ->keyBy('default_question_id');

        return $defaults->map(function (Template $t) use ($tmplCus, $qCus) {
            $tc = $tmplCus->get($t->id);
            $name = $tc->name ?? $t->name;
            $desc = $tc->description ?? $t->description;

            $sections = $t->sections->map(function (Section $s) use ($qCus) {
                $qs = $s->questions->map(function (Question $q) use ($qCus) {
                    $qc = $qCus->get($q->id);
                    return [
                        'id'            => $q->id,
                        'question_text' => $qc->question_text ?? $q->question_text,
                        'response_type' => $q->response_type,
                        'options'       => $qc->options ?? $q->options,
                        'order'         => $qc->order ?? $q->order,
                        'is_required'   => isset($qc->is_required) ? (bool)$qc->is_required : (bool)$q->is_required,
                        'description'   => $qc->description ?? $q->description,
                    ];
                })->sortBy('order')->values()->all();

                return [
                    'id'          => $s->id,
                    'name'        => $s->name,
                    'description' => $s->description,
                    'order'       => $s->order,
                    'questions'   => $qs,
                ];
            })->values()->all();

            return [
                'id'          => $t->id,
                'name'        => $name,
                'description' => $desc,
                'sections'    => $sections,
            ];
        });
    }

    /**
     * Build a lookup array of templates by normalized name:
     * - Prefer audit-specific templates, fallback to defaults
     */
    private function buildTemplateLookup(Audit $audit, int $reviewTypeId): array
    {
        $map = [];

        // Audit-specific first
        $auditTemplates = Template::where('review_type_id', $reviewTypeId)
            ->where('audit_id', $audit->id)
            ->with('sections.questions')
            ->get();
        foreach ($auditTemplates as $t) {
            $map[$this->normalizeName($t->name)] = $t;
        }

        // Defaults (only add if not already present)
        $defaultTemplates = Template::where('review_type_id', $reviewTypeId)
            ->whereNull('audit_id')
            ->with('sections.questions')
            ->get();
        foreach ($defaultTemplates as $t) {
            $key = $this->normalizeName($t->name);
            if (!isset($map[$key])) {
                $map[$key] = $t;
            }
        }

        return $map;
    }

    private function matchTemplateBySheetName(string $sheetName, array $templateMap): ?Template
    {
        $key = $this->normalizeName($sheetName);
        return $templateMap[$key] ?? null;
    }

    private function resolveAttachmentForImport(
        Audit $audit,
        ReviewType $reviewType,
        string $mode,
        ?string $locationName,
        $attachmentId
    ): AuditReviewTypeAttachment {
        if ($mode === 'update') {
            return AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('review_type_id', $reviewType->id)
                ->where('id', $attachmentId)
                ->firstOrFail();
        }

        // new: create duplicate based on master
        $master = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->where('review_type_id', $reviewType->id)
            ->where('duplicate_number', 1)
            ->whereNull('master_attachment_id')
            ->firstOrFail();

        $nextDup = (int) AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->where('review_type_id', $reviewType->id)
            ->max('duplicate_number') + 1;

        return AuditReviewTypeAttachment::create([
            'audit_id'            => $audit->id,
            'review_type_id'      => $reviewType->id,
            'master_attachment_id'=> $master->id,
            'duplicate_number'    => $nextDup,
            'location_name'       => $locationName ?: "Imported Location {$nextDup}",
        ]);
    }

    private function formatAnswerForExport($answer, string $responseType): string
    {
        if ($answer === null) return '';
        if (is_array($answer)) {
            // If array has scalar "value", show that; else JSON
            if (array_key_exists('value', $answer) && (is_scalar($answer['value']) || $answer['value'] === null)) {
                return (string) ($answer['value'] ?? '');
            }
            return json_encode($answer);
        }
        if (is_scalar($answer)) return (string)$answer;
        return '';
    }

    private function normalizeAnswerForStore($raw, string $responseType): array
    {
        // Always store as array in Response.answer
        if ($raw === null) return ['value' => null, 'type' => $responseType];

        $str = is_scalar($raw) ? (string)$raw : json_encode($raw);

        switch ($responseType) {
            case 'table':
                // Expect JSON string or keep as raw string
                if ($this->isJson($str)) {
                    return ['value' => json_decode($str, true), 'type' => $responseType];
                }
                return ['value' => $str, 'type' => $responseType];

            case 'yes_no':
                $v = strtolower(trim($str));
                $bool = in_array($v, ['yes', 'y', '1', 'true'], true);
                return ['value' => $bool ? 'yes' : 'no', 'type' => $responseType];

            case 'number':
                $num = is_numeric($str) ? 0 + $str : null;
                return ['value' => $num, 'type' => $responseType];

            default:
                return ['value' => $str, 'type' => $responseType];
        }
    }

    private function encodeOptions($options): string
    {
        if (is_array($options)) return json_encode($options);
        if (is_string($options) && $options !== '') return $options;
        return '';
    }

    private function sanitizeSheetName(string $name, array $used): string
    {
        $clean = preg_replace('/[:\\\\\\/\\?\\*\\[\\]]+/', ' ', trim($name));
        $clean = mb_substr($clean, 0, 31);
        if ($clean === '' || in_array($clean, $used, true)) {
            $base = $clean !== '' ? $clean : 'Sheet';
            $i = 1;
            $try = $base;
            while (in_array($try, $used, true)) {
                $suffix = '('.$i++.')';
                $try = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
            }
            $clean = $try;
        }
        return $clean;
    }

    private function normalizeName(string $s): string
    {
        $s = strtolower(trim($s));
        // remove non-alnum except spaces
        $s = preg_replace('/[^a-z0-9 ]+/', ' ', $s);
        // collapse spaces
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    private function normalizeSpace(string $s): string
    {
        return preg_replace('/\s+/', ' ', trim($s));
    }

    private function isJson($string): bool
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function showImportForm(Audit $audit, ReviewType $reviewType)
    {
        // Get existing attachments for this review type
        $attachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->where('review_type_id', $reviewType->id)
            ->orderBy('duplicate_number')
            ->get();

        return view('admin.audit-management.audits.import-excel', compact('audit', 'reviewType', 'attachments'));
    }

    // Add this method to download blank templates
    public function downloadBlankTemplate(Audit $audit, ReviewType $reviewType)
    {
        try {
            // Create a temporary attachment to use existing export logic
            $tempAttachment = new AuditReviewTypeAttachment([
                'audit_id' => $audit->id,
                'review_type_id' => $reviewType->id,
                'duplicate_number' => 1,
                'location_name' => 'Template'
            ]);
            
            // Set the relationships
            $tempAttachment->audit = $audit;
            $tempAttachment->reviewType = $reviewType;

            return $this->exportAttachmentXlsx($audit, null, $tempAttachment, true);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Template download failed: ' . $e->getMessage());
        }
    }

    
// ...existing code...
    private function writeTemplateSheetWithResponses(Worksheet $sheet, array $tpl, Audit $audit, AuditReviewTypeAttachment $attachment): void
    {
        $row = 1;
        $headers = [
            'A1' => 'Section Order',
            'B1' => 'Section Name',
            'C1' => 'Section Description',
            'D1' => 'Question Order',
            'E1' => 'Question Text',
            'F1' => 'Response Type',
            'G1' => 'Is Required',
            'H1' => 'Options (JSON)',
            'I1' => 'Question Description',
            'J1' => 'Response/Answer',
            'K1' => 'Audit Note',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $row = 2;
        foreach ($tpl['sections'] as $section) {
            foreach ($section['questions'] as $q) {
                $resp = \App\Models\Response::where('audit_id', $audit->id)
                    ->where('attachment_id', $attachment->id)
                    ->where('question_id', $q['id'])
                    ->first();

                // Handle table questions specially
                if ($q['response_type'] === 'table') {
                    $table = $resp ? (is_array($resp->answer) ? $resp->answer : json_decode($resp->answer, true)) : [];
                    if (is_array($table) && count($table)) {
                        foreach ($table as $tRow) {
                            $sheet->setCellValue("A{$row}", $section['order']);
                            $sheet->setCellValue("B{$row}", $section['name']);
                            $sheet->setCellValue("C{$row}", $section['description'] ?? '');
                            $sheet->setCellValue("D{$row}", $q['order']);
                            $sheet->setCellValue("E{$row}", $q['question_text']);
                            $sheet->setCellValue("F{$row}", $q['response_type']);
                            $sheet->setCellValue("G{$row}", $q['is_required'] ? 'Yes' : 'No');
                            $sheet->setCellValue("H{$row}", $this->encodeOptions($q['options'] ?? null));
                            $sheet->setCellValue("I{$row}", $q['description'] ?? '');
                            // Join table row cells with tab or comma for readability
                            $sheet->setCellValue("J{$row}", is_array($tRow) ? implode("\t", $tRow) : $tRow);
                            $sheet->setCellValue("K{$row}", $resp ? (string)($resp->audit_note ?? '') : '');
                            $row++;
                        }
                        continue; // Already wrote all table rows
                    }
                }

                // Non-table or empty table
                $answerString = $this->formatAnswerForExport($resp ? $resp->answer : null, $q['response_type']);
                $sheet->setCellValue("A{$row}", $section['order']);
                $sheet->setCellValue("B{$row}", $section['name']);
                $sheet->setCellValue("C{$row}", $section['description'] ?? '');
                $sheet->setCellValue("D{$row}", $q['order']);
                $sheet->setCellValue("E{$row}", $q['question_text']);
                $sheet->setCellValue("F{$row}", $q['response_type']);
                $sheet->setCellValue("G{$row}", $q['is_required'] ? 'Yes' : 'No');
                $sheet->setCellValue("H{$row}", $this->encodeOptions($q['options'] ?? null));
                $sheet->setCellValue("I{$row}", $q['description'] ?? '');
                $sheet->setCellValue("J{$row}", $answerString);
                $sheet->setCellValue("K{$row}", $resp ? (string)($resp->audit_note ?? '') : '');
                $row++;
            }
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');
    }
// ...existing code...


}