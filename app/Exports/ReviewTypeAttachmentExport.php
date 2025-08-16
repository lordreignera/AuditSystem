<?php

namespace App\Exports;

use App\Models\AuditReviewTypeAttachment;
use App\Models\Template;
use App\Models\Response;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class ReviewTypeAttachmentExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $attachment;
    protected $audit;

    public function __construct(AuditReviewTypeAttachment $attachment)
    {
        $this->attachment = $attachment;
        $this->audit = $attachment->audit;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Get all templates for this review type in this audit
        $templates = Template::where('review_type_id', $this->attachment->review_type_id)
            ->where('audit_id', $this->audit->id)
            ->where('is_active', true)
            ->with(['sections.questions'])
            ->ordered()
            ->get();

        foreach ($templates as $template) {
            $sheets[] = new TemplateSheet($template, $this->attachment);
        }

        return $sheets;
    }
}

class TemplateSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $template;
    protected $attachment;

    public function __construct(Template $template, AuditReviewTypeAttachment $attachment)
    {
        $this->template = $template;
        $this->attachment = $attachment;
    }

    public function collection()
    {
        $data = new Collection();
        
        foreach ($this->template->sections as $section) {
            foreach ($section->questions as $question) {
                // Get the response for this specific attachment
                $response = Response::where('audit_id', $this->attachment->audit_id)
                    ->where('attachment_id', $this->attachment->id)
                    ->where('question_id', $question->id)
                    ->first();

                $data->push([
                    'template_name' => $this->template->name,
                    'template_order' => $this->template->order,
                    'section_name' => $section->name,
                    'section_order' => $section->order,
                    'question_text' => $question->question_text,
                    'question_order' => $question->order,
                    'response_type' => $question->response_type,
                    'options' => is_array($question->options) 
                        ? json_encode($question->options) 
                        : ($question->options ?: ''),
                    'is_required' => $question->is_required ? 'Yes' : 'No',
                    'answer' => $response ? $response->answer : '',
                    'audit_note' => $response ? $response->audit_note : '',
                    'attachment_info' => $this->attachment->getContextualLocationName(),
                    'duplicate_number' => $this->attachment->duplicate_number,
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Template Name',
            'Template Order',
            'Section Name', 
            'Section Order',
            'Question Text',
            'Question Order',
            'Response Type',
            'Options',
            'Required',
            'Answer',
            'Audit Note',
            'Location',
            'Duplicate Number'
        ];
    }

    public function title(): string
    {
        // Clean template name for sheet title (Excel sheet names have restrictions)
        $cleanName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $this->template->name);
        return substr($cleanName, 0, 31); // Excel sheet name limit
    }
}
