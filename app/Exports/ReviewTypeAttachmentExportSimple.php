<?php

namespace App\Exports;

use App\Models\AuditReviewTypeAttachment;
use App\Models\Template;
use App\Models\Response;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class ReviewTypeAttachmentExportSimple implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $attachment;
    protected $audit;

    public function __construct(AuditReviewTypeAttachment $attachment)
    {
        $this->attachment = $attachment;
        $this->audit = $attachment->audit;
    }

    public function headings(): array
    {
        return [
            'Template Name',
            'Section Name', 
            'Question Text',
            'Question Type',
            'Response/Answer'
        ];
    }

    public function collection()
    {
        $data = collect();
        
        // Get all templates for this review type in this audit
        $templates = Template::where('review_type_id', $this->attachment->review_type_id)
            ->where('audit_id', $this->audit->id)
            ->where('is_active', true)
            ->with(['sections.questions'])
            ->ordered()
            ->get();

        foreach ($templates as $template) {
            foreach ($template->sections as $section) {
                foreach ($section->questions as $question) {
                    // Get response for this question and attachment
                    $response = Response::where([
                        'audit_id' => $this->audit->id,
                        'template_id' => $template->id,
                        'section_id' => $section->id,
                        'question_id' => $question->id,
                        'audit_review_type_attachment_id' => $this->attachment->id
                    ])->first();

                    $answer = '';
                    if ($response) {
                        switch ($question->type) {
                            case 'yes_no':
                            case 'yes_no_na':
                                $answer = $response->yes_no_response ?? '';
                                break;
                            case 'text':
                                $answer = $response->text_response ?? '';
                                break;
                            case 'number':
                                $answer = $response->number_response ?? '';
                                break;
                            case 'date':
                                $answer = $response->date_response ?? '';
                                break;
                            case 'table':
                                $answer = $response->table_response ? json_encode($response->table_response) : '';
                                break;
                            default:
                                $answer = $response->text_response ?? '';
                        }
                    }

                    $data->push([
                        'template_name' => $template->name,
                        'section_name' => $section->name,
                        'question_text' => $question->question_text,
                        'question_type' => $question->type,
                        'response' => $answer
                    ]);
                }
            }
        }

        return $data;
    }
}
