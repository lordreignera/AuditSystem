<?php

namespace App\Imports;

use App\Models\Template;
use App\Models\Section;
use App\Models\Question;
use App\Models\Response;
use App\Models\AuditReviewTypeAttachment;
use App\Models\Audit;
use App\Models\ReviewType;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewTypeAttachmentImport implements WithMultipleSheets
{
    protected $audit;
    protected $reviewType;
    protected $attachment;
    protected $importMode;
    protected $locationName;

    public function __construct(Audit $audit, ReviewType $reviewType, $importMode = 'new', $locationName = null, $attachmentId = null)
    {
        $this->audit = $audit;
        $this->reviewType = $reviewType;
        $this->importMode = $importMode; // 'new' or 'update'
        $this->locationName = $locationName;
        
        if ($importMode === 'update' && $attachmentId) {
            $this->attachment = AuditReviewTypeAttachment::findOrFail($attachmentId);
        }
    }

    public function sheets(): array
    {
        // Get all templates for this review type in this audit
        $templates = Template::where('review_type_id', $this->reviewType->id)
            ->where('audit_id', $this->audit->id)
            ->where('is_active', true)
            ->with(['sections.questions'])
            ->ordered()
            ->get();

        $sheets = [];
        foreach ($templates as $template) {
            $sheetName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $template->name);
            $sheetName = substr($sheetName, 0, 31);
            $sheets[$sheetName] = new TemplateImportSheet($template, $this);
        }

        return $sheets;
    }

    public function getAttachment()
    {
        if ($this->importMode === 'new') {
            // Create new attachment (duplicate)
            if (!$this->attachment) {
                // Find master attachment
                $masterAttachment = AuditReviewTypeAttachment::where('audit_id', $this->audit->id)
                    ->where('review_type_id', $this->reviewType->id)
                    ->where('duplicate_number', 1)
                    ->whereNull('master_attachment_id')
                    ->first();

                if (!$masterAttachment) {
                    throw new \Exception('Master attachment not found for this review type.');
                }

                // Get next duplicate number
                $nextDuplicateNumber = AuditReviewTypeAttachment::where('audit_id', $this->audit->id)
                    ->where('review_type_id', $this->reviewType->id)
                    ->max('duplicate_number') + 1;

                // Create new attachment
                $this->attachment = AuditReviewTypeAttachment::create([
                    'audit_id' => $this->audit->id,
                    'review_type_id' => $this->reviewType->id,
                    'master_attachment_id' => $masterAttachment->id,
                    'duplicate_number' => $nextDuplicateNumber,
                    'location_name' => $this->locationName ?: "Imported Location {$nextDuplicateNumber}"
                ]);
            }
        }

        return $this->attachment;
    }
}

class TemplateImportSheet implements ToCollection, WithHeadingRow
{
    protected $template;
    protected $parentImport;

    public function __construct(Template $template, ReviewTypeAttachmentImport $parentImport)
    {
        $this->template = $template;
        $this->parentImport = $parentImport;
    }

    public function collection(Collection $rows)
    {
        $attachment = $this->parentImport->getAttachment();
        
        DB::beginTransaction();
        
        try {
            foreach ($rows as $row) {
                // Skip empty rows
                if (empty($row['question_text']) || empty($row['answer'])) {
                    continue;
                }

                // Find the question by matching template, section, and question details
                $question = $this->findQuestionByRow($row);
                
                if (!$question) {
                    Log::warning('Question not found for row: ' . json_encode($row->toArray()));
                    continue;
                }

                // Process the answer based on response type
                $answer = $this->processAnswer($row['answer'], $question->response_type);
                
                // Create or update response
                Response::updateOrCreate(
                    [
                        'audit_id' => $attachment->audit_id,
                        'attachment_id' => $attachment->id,
                        'question_id' => $question->id,
                        'created_by' => auth()->id(),
                    ],
                    [
                        'answer' => $answer,
                        'audit_note' => $row['audit_note'] ?? '',
                    ]
                );
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Import failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function findQuestionByRow($row)
    {
        // Find question by matching template name, section name, and question text
        return Question::whereHas('section.template', function($query) use ($row) {
                $query->where('name', $row['template_name'])
                      ->where('audit_id', $this->parentImport->audit->id)
                      ->where('review_type_id', $this->parentImport->reviewType->id);
            })
            ->whereHas('section', function($query) use ($row) {
                $query->where('name', $row['section_name']);
            })
            ->where('question_text', $row['question_text'])
            ->first();
    }

    protected function processAnswer($answer, $responseType)
    {
        if (empty($answer)) {
            return '';
        }

        // Handle different response types
        switch ($responseType) {
            case 'table':
                // If it's already JSON, return as is, otherwise try to decode
                if (is_string($answer) && $this->isJson($answer)) {
                    return $answer;
                }
                return json_encode($answer);
                
            case 'yes_no':
                // Normalize yes/no responses
                $answer = strtolower(trim($answer));
                return in_array($answer, ['yes', 'y', '1', 'true']) ? 'yes' : 'no';
                
            case 'number':
                return (string) floatval($answer);
                
            default:
                return (string) $answer;
        }
    }

    protected function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
