<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\AuditReviewTypeAttachment;
use App\Models\ReviewType;
use App\Helpers\SimpleExcelExporter;
use App\Imports\ReviewTypeAttachmentImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ExcelImportExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:edit audits']);
    }

    /**
     * Export an attachment's data to Excel
     */
    public function exportAttachment(Audit $audit, $attachmentId)
    {
        try {
            $attachment = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('id', $attachmentId)
                ->with('reviewType')
                ->firstOrFail();

            $reviewTypeName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $attachment->reviewType->name);
            $locationName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $attachment->getContextualLocationName());
            $fileName = "ReviewType_{$reviewTypeName}_Audit_{$audit->id}_Attachment_{$attachment->duplicate_number}_{$locationName}.xlsx";

            // Use CSV export since Excel package is having issues
            $data = $this->getAttachmentData($attachment);
            $headers = ['Template Name', 'Section Name', 'Question Text', 'Question Type', 'Response/Answer'];
            $csvFileName = str_replace('.xlsx', '.csv', $fileName);
            return SimpleExcelExporter::downloadCSV($data, $headers, $csvFileName);

        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    private function getAttachmentData($attachment)
    {
        $data = collect();
        
        // Get all templates for this review type in this audit
        $templates = \App\Models\Template::where('review_type_id', $attachment->review_type_id)
            ->where('audit_id', $attachment->audit_id)
            ->where('is_active', true)
            ->with(['sections.questions'])
            ->ordered()
            ->get();

        foreach ($templates as $template) {
            foreach ($template->sections as $section) {
                foreach ($section->questions as $question) {
                    // Get response for this question and attachment
                    $response = \App\Models\Response::where([
                        'audit_id' => $attachment->audit_id,
                        'template_id' => $template->id,
                        'section_id' => $section->id,
                        'question_id' => $question->id,
                        'audit_review_type_attachment_id' => $attachment->id
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

    private function getBlankTemplateData($attachment)
    {
        $data = collect();
        
        // Get all templates for this review type in this audit
        $templates = \App\Models\Template::where('review_type_id', $attachment->review_type_id)
            ->where('audit_id', $attachment->audit_id)
            ->where('is_active', true)
            ->with(['sections.questions'])
            ->ordered()
            ->get();

        foreach ($templates as $template) {
            foreach ($template->sections as $section) {
                foreach ($section->questions as $question) {
                    // For blank template, we don't include any responses
                    $data->push([
                        'template_name' => $template->name,
                        'section_name' => $section->name,
                        'question_text' => $question->question_text,
                        'question_type' => $question->type,
                        'response' => '' // Empty for blank template
                    ]);
                }
            }
        }

        return $data;
    }

    /**
     * Show import form
     */
    public function showGeneralImportForm(Audit $audit)
    {
        // Get all available review types for this audit
        $attachedReviewTypes = $audit->reviewTypes()->with(['templates'])->get();
        
        return view('admin.audit-management.audits.general-import-excel', compact('audit', 'attachedReviewTypes'))
            ->with('importDisabled', true)
            ->with('importMessage', 'Excel import is temporarily unavailable. Please use CSV export for now.');
    }

    public function showImportForm(Audit $audit, $reviewTypeId)
    {
        $reviewType = ReviewType::findOrFail($reviewTypeId);
        
        // Get existing attachments for this review type
        $attachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->where('review_type_id', $reviewTypeId)
            ->with('reviewType')
            ->orderBy('duplicate_number')
            ->get();

        return view('admin.audit-management.audits.import-excel', compact('audit', 'reviewType', 'attachments'));
    }

    /**
     * Import Excel file to create new attachment or update existing
     */
    public function importExcel(Request $request, Audit $audit, $reviewTypeId)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'import_mode' => 'required|in:new,update',
            'location_name' => 'required_if:import_mode,new|string|max:255',
            'attachment_id' => 'required_if:import_mode,update|exists:audit_review_type_attachments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $reviewType = ReviewType::findOrFail($reviewTypeId);
            
            // Verify review type is attached to this audit
            $existingAttachment = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('review_type_id', $reviewTypeId)
                ->exists();
                
            if (!$existingAttachment) {
                return redirect()->back()->with('error', 'This review type is not attached to this audit.');
            }

            $importMode = $request->import_mode;
            $locationName = $request->location_name;
            $attachmentId = $request->attachment_id;

            // Create the import instance
            $import = new ReviewTypeAttachmentImport(
                $audit, 
                $reviewType, 
                $importMode, 
                $locationName, 
                $attachmentId
            );

            // Process the Excel file
            Excel::import($import, $request->file('excel_file'));

            $attachment = $import->getAttachment();
            $locationInfo = $attachment->getContextualLocationName();

            if ($importMode === 'new') {
                $message = "Successfully imported responses for new location: {$locationInfo}";
            } else {
                $message = "Successfully updated responses for location: {$locationInfo}";
            }

            return redirect()->route('admin.audits.dashboard', $audit)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download blank template for a review type
     */
    public function downloadBlankTemplate(Audit $audit, $reviewTypeId)
    {
        try {
            $reviewType = ReviewType::findOrFail($reviewTypeId);
            
            // Get master attachment to use as template base
            $masterAttachment = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('review_type_id', $reviewTypeId)
                ->where('duplicate_number', 1)
                ->whereNull('master_attachment_id')
                ->firstOrFail();

            // Create a temporary attachment object for blank template
            $blankAttachment = new AuditReviewTypeAttachment([
                'id' => null,
                'audit_id' => $audit->id,
                'review_type_id' => $reviewTypeId,
                'duplicate_number' => 0,
                'location_name' => 'BLANK_TEMPLATE'
            ]);
            $blankAttachment->reviewType = $reviewType;

            $reviewTypeName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $reviewType->name);
            $fileName = "BLANK_TEMPLATE_{$reviewTypeName}_Audit_{$audit->id}.csv";

            // Create blank template data
            $data = $this->getBlankTemplateData($blankAttachment);
            $headers = ['Template Name', 'Section Name', 'Question Text', 'Question Type', 'Response/Answer'];
            return SimpleExcelExporter::downloadCSV($data, $headers, $fileName);

        } catch (\Exception $e) {
            Log::error('Blank template download failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Preview Excel file before import
     */
    public function previewImport(Request $request, Audit $audit, $reviewTypeId)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid file'], 400);
        }

        try {
            // Basic validation - check if file can be read
            $data = Excel::toArray(new ReviewTypeAttachmentImport($audit, ReviewType::findOrFail($reviewTypeId)), $request->file('excel_file'));
            
            $totalRows = 0;
            $sheetsInfo = [];
            
            foreach ($data as $sheetName => $rows) {
                $sheetsInfo[] = [
                    'name' => $sheetName,
                    'rows' => count($rows) - 1, // Subtract header row
                ];
                $totalRows += count($rows) - 1;
            }

            return response()->json([
                'success' => true,
                'preview' => [
                    'total_rows' => $totalRows,
                    'sheets' => $sheetsInfo,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Preview failed: ' . $e->getMessage()], 400);
        }
    }
}
