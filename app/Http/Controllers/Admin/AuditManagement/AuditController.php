<?php

namespace App\Http\Controllers\Admin\AuditManagement;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Country;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;
use App\Models\AuditReviewTypeAttachment;
use App\Models\AuditTemplateCustomization;
use App\Models\AuditQuestionCustomization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Response;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view audits'])->only(['index', 'show']);
        $this->middleware(['auth', 'permission:create audits'])->only(['create', 'store']);
        $this->middleware(['auth', 'permission:edit audits'])->only(['edit', 'update']);
        $this->middleware(['auth', 'permission:delete audits'])->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // If user is an auditor, show only assigned audits
        if ($user->hasRole('Auditor')) {
            $audits = $user->assignedAudits()->with('country')->paginate(10);
        } else {
            // Superadmin can see all audits
            $audits = Audit::with('country')->paginate(10);
        }
        
        return view('admin.audit-management.audits.index', compact('audits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::where('is_active', true)->get();
        return view('admin.audit-management.audits.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'participants' => 'nullable|array',
            'participants.*' => 'string',
            'start_date' => 'required|date',
            'duration_value' => 'nullable|integer|min:1',
            'duration_unit' => 'nullable|in:days,months,years',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Generate unique review code
        $data['review_code'] = Audit::generateReviewCode();

        // Filter out empty participants
        if (isset($data['participants'])) {
            $data['participants'] = array_filter($data['participants'], function($participant) {
                return !empty(trim($participant));
            });
        }

        $audit = Audit::create($data);

        return redirect()->route('admin.audits.index')
            ->with('success', 'Audit created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Audit $audit)
    {
        $user = auth()->user();
        
        // If user is an auditor, check if they have access to this audit
        if ($user->hasRole('Auditor')) {
            $hasAccess = $user->assignedAudits()->where('audits.id', $audit->id)->exists();
            if (!$hasAccess) {
                abort(403, 'You do not have access to this audit.');
            }
        }
        
        $audit->load('country');
        return view('admin.audit-management.audits.show', compact('audit'));
    }

    /**
     * Show audit dashboard for managing review types, templates, sections, and questions - NEW APPROACH
     */
    public function dashboard(Audit $audit)
    {
        $user = auth()->user();
        
        // If user is an auditor, check if they have access to this audit
        if ($user->hasRole('Auditor')) {
            $hasAccess = $user->assignedAudits()->where('audits.id', $audit->id)->exists();
            if (!$hasAccess) {
                abort(403, 'You do not have access to this audit.');
            }
        }
        
        $audit->load([
            'country',
            'responses'
        ]);

        // Get all available review types for adding new ones - ONLY with default templates
        $availableReviewTypes = ReviewType::where('is_active', true)
            ->with(['templates' => function($query) {
                $query->whereNull('audit_id') // ONLY DEFAULT TEMPLATES
                      ->where('is_active', true);
            }])
            ->get();

        // Get existing responses grouped by attachment and question
        // This ensures responses are isolated between master and duplicates
        $existingResponses = collect();
        if ($audit->responses) {
            $existingResponses = $audit->responses->groupBy('attachment_id');
        }

        // Get attached review types through the new attachment system
        $attachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->with('reviewType')
            ->orderBy('review_type_id')
            ->orderBy('duplicate_number')
            ->get();
            
        $attachedReviewTypes = collect();
        
        // Group attachments by review_type_id to handle master-duplicate relationships
        $reviewTypeGroups = $attachments->groupBy('review_type_id');
        
        foreach ($reviewTypeGroups as $reviewTypeId => $groupAttachments) {
            // Find the master attachment (should be the first one when ordered by duplicate_number)
            $masterAttachment = $groupAttachments->where('master_attachment_id', null)->first();
            
            if (!$masterAttachment) {
                \Log::warning("Audit {$audit->id} has attachments for review type {$reviewTypeId} but no master attachment!");
                continue;
            }
            
            $reviewType = $masterAttachment->reviewType;
            
            // Add master information
            $reviewType->attachmentId = $masterAttachment->id;
            $reviewType->isMaster = true;
            $reviewType->isDuplicate = false;
            $reviewType->duplicateNumber = $masterAttachment->duplicate_number;
            $reviewType->locationName = $masterAttachment->getContextualLocationName();
            $reviewType->masterAttachmentId = null;
            
            // Get templates (masters and duplicates share the same templates)
            $auditTemplates = Template::where('review_type_id', $reviewType->id)
                ->where('audit_id', $audit->id)
                ->where('is_active', true)
                ->with(['sections.questions'])
                ->ordered() // Use the systematic ordering scope
                ->get();
            
            // If no audit-specific templates exist, this attachment is broken - skip it
            if ($auditTemplates->isEmpty()) {
                \Log::warning("Audit {$audit->id} has attachment for review type {$reviewType->id} but no audit-specific templates!");
                continue;
            }
            
            $reviewType->auditTemplates = $auditTemplates;
            
            // Add duplicate information
            $duplicates = $groupAttachments->where('master_attachment_id', '!=', null)->sortBy('duplicate_number');
            $reviewType->duplicates = $duplicates->map(function($duplicate) {
                return (object)[
                    'attachmentId' => $duplicate->id,
                    'duplicateNumber' => $duplicate->duplicate_number,
                    'locationName' => $duplicate->getContextualLocationName(),
                    'masterAttachmentId' => $duplicate->master_attachment_id
                ];
            });
            
            $attachedReviewTypes->push($reviewType);
        }

        return view('admin.audit-management.audits.dashboard', compact('audit', 'availableReviewTypes', 'existingResponses', 'attachedReviewTypes'));
    }

    /**
     * Sync all audit-specific questions' options with the default template's questions for a given audit and review type.
     * This will overwrite the options (table structure) for all audit-specific questions to match the default template.
     */
    public function syncAuditTableStructures(Request $request, Audit $audit, $reviewTypeId)
    {
        // Get default templates for this review type (not audit-specific)
        $defaultTemplates = Template::where('review_type_id', $reviewTypeId)
            ->whereNull('audit_id')
            ->with('sections.questions')
            ->ordered() // Use systematic ordering
            ->get();

        // Get audit-specific templates for this review type
        $auditTemplates = Template::where('review_type_id', $reviewTypeId)
            ->where('audit_id', $audit->id)
            ->with('sections.questions')
            ->ordered() // Use the systematic ordering scope
            ->get();

        // Map default questions by a unique key (e.g., section order + question order)
        $defaultQuestions = [];
        foreach ($defaultTemplates as $template) {
            foreach ($template->sections as $section) {
                foreach ($section->questions as $question) {
                    $key = $template->order . '-' . $section->order . '-' . $question->order;
                    $defaultQuestions[$key] = $question;
                }
            }
        }

        // Update audit-specific questions' options to match default
        foreach ($auditTemplates as $template) {
            foreach ($template->sections as $section) {
                foreach ($section->questions as $question) {
                    $key = $template->order . '-' . $section->order . '-' . $question->order;
                    if (isset($defaultQuestions[$key])) {
                        $defaultOptions = is_array($defaultQuestions[$key]->options)
                            ? $defaultQuestions[$key]->options
                            : (json_decode($defaultQuestions[$key]->options, true) ?: []);
                        $question->options = json_encode($defaultOptions);
                        $question->save();
                    }
                }
            }
        }

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Audit table structures synced with default template.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Audit $audit)
    {
        $countries = Country::where('is_active', true)->get();
        return view('admin.audit-management.audits.edit', compact('audit', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'participants' => 'nullable|array',
            'participants.*' => 'string',
            'start_date' => 'required|date',
            'duration_value' => 'nullable|integer|min:1',
            'duration_unit' => 'nullable|in:days,months,years',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Filter out empty participants
        if (isset($data['participants'])) {
            $data['participants'] = array_filter($data['participants'], function($participant) {
                return !empty(trim($participant));
            });
        }

        $audit->update($data);

        return redirect()->route('admin.audits.index')
            ->with('success', 'Audit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Audit $audit)
    {
        $audit->delete();

        return redirect()->route('admin.audits.index')
            ->with('success', 'Audit deleted successfully.');
    }

    /**
     * Attach a review type to the audit - NEW APPROACH
     */
    public function attachReviewType(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'review_type_id' => 'required|exists:review_types,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $reviewTypeId = $request->review_type_id;
        
        try {
            // Check if already attached
            $existingAttachment = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('review_type_id', $reviewTypeId)
                ->first();
                
            if ($existingAttachment) {
                return redirect()->back()->with('error', 'This review type is already attached to this audit.');
            }
            
            // Create the master attachment record
            $attachment = AuditReviewTypeAttachment::create([
                'audit_id' => $audit->id,
                'review_type_id' => $reviewTypeId,
                'master_attachment_id' => null, // This is the master
                'duplicate_number' => 1, // Master is always 1
                'location_name' => null // Will be set contextually based on review type
            ]);

            // Get all default templates for this review type
            $defaultTemplates = Template::where('review_type_id', $reviewTypeId)
                ->whereNull('audit_id') // Default templates only
                ->where('is_active', true)
                ->with(['sections.questions'])
                ->ordered() // Use systematic ordering
                ->get();

            if ($defaultTemplates->isEmpty()) {
                return redirect()->back()->with('error', 'No active templates found for this review type.');
            }

            // Create audit-specific copies of all templates, sections, and questions
            foreach ($defaultTemplates as $defaultTemplate) {
                // Create audit-specific template copy
                $auditTemplate = $defaultTemplate->replicate();
                $auditTemplate->audit_id = $audit->id;
                $auditTemplate->is_default = false;
                $auditTemplate->name = $defaultTemplate->name; // Keep original name for first instance
                $auditTemplate->save();

                // Copy all sections for this template
                foreach ($defaultTemplate->sections as $defaultSection) {
                    $auditSection = $defaultSection->replicate();
                    $auditSection->template_id = $auditTemplate->id;
                    $auditSection->audit_id = $audit->id;
                    $auditSection->save();

                    // Copy all questions for this section
                    foreach ($defaultSection->questions as $defaultQuestion) {
                        $auditQuestion = $defaultQuestion->replicate();
                        $auditQuestion->section_id = $auditSection->id;
                        $auditQuestion->audit_id = $audit->id;
                        $auditQuestion->save();
                    }
                }
            }

            $reviewType = ReviewType::findOrFail($reviewTypeId);
            $templateCount = $defaultTemplates->count();
            
            return redirect()->route('admin.audits.dashboard', $audit)
                ->with('success', "Review type '{$reviewType->name}' attached successfully with {$templateCount} template(s). Audit-specific copies created to protect default templates!");
                
        } catch (\Exception $e) {
            \Log::error('Error attaching review type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while attaching the review type: ' . $e->getMessage());
        }
    }

    /**
     * Detach a review type from the audit - NEW APPROACH
     */
    public function detachReviewType(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'review_type_id' => 'required|exists:review_types,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $reviewTypeId = $request->review_type_id;
        
        try {
            // Find ALL attachments for this review type (master + duplicates)
            $attachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('review_type_id', $reviewTypeId)
                ->get();
                
            if ($attachments->isEmpty()) {
                return redirect()->back()->with('error', 'This review type is not attached to this audit.');
            }
            
            $attachmentCount = $attachments->count();
            
            // Delete all audit-specific templates, sections, and questions for this review type
            // Since duplicates share the same templates as master, we only need to delete templates once
            $auditTemplates = Template::where('review_type_id', $reviewTypeId)
                ->where('audit_id', $audit->id)
                ->get();
                
            // First, delete ALL responses for this review type from ALL attachments
            $attachmentIds = $attachments->pluck('id');
            Response::where('audit_id', $audit->id)
                ->whereIn('attachment_id', $attachmentIds)
                ->delete();
                
            foreach ($auditTemplates as $template) {
                // Also delete any remaining responses by question (safety net)
                foreach ($template->sections as $section) {
                    foreach ($section->questions as $question) {
                        $question->responses()->where('audit_id', $audit->id)->delete();
                    }
                }
                
                // Delete the template (cascade will handle sections and questions)
                $template->delete();
            }
            
            // Delete ALL attachment records (master + duplicates)
            foreach ($attachments as $attachment) {
                $attachment->delete();
            }
            
            // Clean up any legacy customizations (if they exist)
            AuditTemplateCustomization::where('audit_id', $audit->id)
                ->whereHas('defaultTemplate', function($query) use ($reviewTypeId) {
                    $query->where('review_type_id', $reviewTypeId);
                })
                ->delete();
                
            AuditQuestionCustomization::where('audit_id', $audit->id)
                ->whereHas('defaultQuestion.section.template', function($query) use ($reviewTypeId) {
                    $query->where('review_type_id', $reviewTypeId);
                })
                ->delete();

            $reviewType = ReviewType::findOrFail($reviewTypeId);
            
            $locationInfo = $attachmentCount > 1 ? " (including {$attachmentCount} locations)" : "";
            
            return redirect()->route('admin.audits.dashboard', $audit)
                ->with('success', "Review type '{$reviewType->name}' detached successfully{$locationInfo}. Default templates remain intact!");
                
        } catch (\Exception $e) {
            \Log::error('Error detaching review type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while detaching the review type: ' . $e->getMessage());
        }
    }

    /**
     * Add a new section to template
     */
    public function addSection(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:templates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $template = Template::findOrFail($request->template_id);

        Section::create([
            'template_id' => $template->id,
            'audit_id' => $template->audit_id,
            'name' => $request->name,
            'description' => $request->description,
            'order' => $request->order,
            'is_active' => true,
        ]);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Section added successfully.');
    }

    public function updateSection(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $section = Section::findOrFail($request->section_id);
        $section->update([
            'name' => $request->name,
            'description' => $request->description,
            'order' => $request->order,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Section updated successfully.');
    }

    public function deleteSection(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|exists:sections,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $section = Section::findOrFail($request->section_id);

        // delete all questions for this section
        foreach ($section->questions as $question) {
            $question->delete();
        }
        $section->delete();

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Section deleted successfully.');
    }

    public function addQuestion(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|exists:sections,id',
            'question_text' => 'required|string',
            'response_type' => 'required|in:text,textarea,yes_no,select,number,date,table',
            'options' => 'nullable|json',
            'is_required' => 'boolean',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $section = Section::findOrFail($request->section_id);

        $data = [
            'section_id' => $section->id,
            'audit_id' => $section->audit_id,
            'question_text' => $request->question_text,
            'response_type' => $request->response_type,
            'is_required' => $request->is_required ?? false,
            'order' => $request->order,
            'is_active' => true,
        ];

        // Handle options - keep it simple, just save what's provided
        if ($request->options) {
            $data['options'] = json_decode($request->options, true);
        } else {
            $data['options'] = null;
        }

        Question::create($data);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Question added successfully.');
    }

    public function updateQuestion(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,id',
            'question_text' => 'required|string',
            'response_type' => 'required|in:text,textarea,yes_no,select,number,date,table',
            'options' => 'nullable|json',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $question = Question::findOrFail($request->question_id);

        $data = [
            'question_text' => $request->question_text,
            'response_type' => $request->response_type,
            'is_required' => $request->is_required ?? false,
            'is_active' => $request->is_active ?? true,
            'order' => $request->order,
        ];

        // Handle options - keep it simple, just save what's provided
        if ($request->options) {
            $data['options'] = json_decode($request->options, true);
        } else {
            $data['options'] = null;
        }

        $question->update($data);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Question updated successfully.');
    }

    public function deleteQuestion(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $question = Question::findOrFail($request->question_id);
        $question->delete();

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Question deleted successfully.');
    }

    public function updateTemplate(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:templates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $template = Template::findOrFail($request->template_id);
        $template->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Template updated successfully.');
    }

    public function duplicateTemplate(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:templates,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $originalTemplate = Template::with('sections.questions')->findOrFail($request->template_id);

        // Create a copy of the template
        $duplicateTemplate = $originalTemplate->replicate();
        $duplicateTemplate->name = $originalTemplate->name . ' (Copy)';
        $duplicateTemplate->is_default = false;
        $duplicateTemplate->audit_id = $audit->id;
        $duplicateTemplate->save();

        // Copy sections and questions
        foreach ($originalTemplate->sections as $originalSection) {
            $duplicateSection = $originalSection->replicate();
            $duplicateSection->template_id = $duplicateTemplate->id;
            $duplicateSection->audit_id = $audit->id;
            $duplicateSection->save();

            foreach ($originalSection->questions as $originalQuestion) {
                $duplicateQuestion = $originalQuestion->replicate();
                $duplicateQuestion->section_id = $duplicateSection->id;
                $duplicateQuestion->audit_id = $audit->id;
                $duplicateQuestion->save();
            }
        }

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Template duplicated successfully.');
    }

    public function storeResponses(Request $request, $reviewTypeId)
    {
        $request->validate([
            'audit_id' => 'required|exists:audits,id',
            'attachment_id' => 'required|exists:audit_review_type_attachments,id',
            'answers' => 'required|array',
        ]);


        foreach ($request->answers as $questionId => $data) {
            // Handle table questions: Save as JSON if 'table' present
            if (isset($data['table'])) {
                $answer = json_encode($data['table']);
            } else {
                $answer = $data['answer'] ?? '';
            }

            // Update header_rows in question options if present
            if (isset($data['header_rows'])) {
                $question = Question::find($questionId);
                if ($question) {
                    $options = is_array($question->options) ? $question->options : (json_decode($question->options, true) ?: []);
                    $options['header_rows'] = (int)$data['header_rows'];
                    $question->options = json_encode($options);
                    $question->save();
                }
            }

            Response::updateOrCreate(
                [
                    'audit_id' => $request->audit_id,
                    'attachment_id' => $request->attachment_id,
                    'question_id' => $questionId,
                    'created_by' => auth()->id(),
                ],
                [
                    'answer' => $answer,
                    'audit_note' => $data['audit_note'] ?? '',
                ]
            );
        }

        // Redirect back to the current page (stay on same page) or fallback to audit page
        $redirectUrl = $request->input('redirect_to');
        
        // If no specific redirect URL provided, use the previous URL (current page)
        if (!$redirectUrl) {
            $redirectUrl = url()->previous();
        }
        
        return redirect($redirectUrl)
            ->with('success', 'Response saved successfully.');
    }

    public function duplicateReviewType(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'review_type_id' => 'required|exists:review_types,id',
            'location_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $reviewTypeId = $request->review_type_id;
        
        // Find the master attachment for this review type
        $masterAttachment = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->where('review_type_id', $reviewTypeId)
            ->where('duplicate_number', 1)
            ->whereNull('master_attachment_id')
            ->first();

        if (!$masterAttachment) {
            return redirect()->back()->with('error', 'Master attachment not found for this review type.');
        }

        // Get the next duplicate number
        $nextDuplicateNumber = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->where('review_type_id', $reviewTypeId)
            ->max('duplicate_number') + 1;

        // Create the duplicate attachment record
        $duplicateAttachment = AuditReviewTypeAttachment::create([
            'audit_id' => $audit->id,
            'review_type_id' => $reviewTypeId,
            'master_attachment_id' => $masterAttachment->id,
            'duplicate_number' => $nextDuplicateNumber,
            'location_name' => $request->location_name
        ]);

        // Duplicates share the same templates/sections/questions as master
        // No need to create copies - they will reference the same structure
        // Only responses will be separate for each duplicate

        $locationName = $duplicateAttachment->getContextualLocationName();
        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', "Review type duplicated successfully for {$locationName}. Shares same templates/structure as master, but responses are independent.");
    }

    public function renameLocation(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'attachment_id' => 'required|exists:audit_review_type_attachments,id',
            'location_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attachment = AuditReviewTypeAttachment::findOrFail($request->attachment_id);
        
        // Verify this attachment belongs to the current audit
        if ($attachment->audit_id !== $audit->id) {
            return redirect()->back()->with('error', 'Invalid attachment for this audit.');
        }

        // Only allow renaming duplicates (not masters) unless it's a single instance
        if ($attachment->isMaster()) {
            $duplicateCount = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->where('review_type_id', $attachment->review_type_id)
                ->where('duplicate_number', '>', 1)
                ->count();
                
            if ($duplicateCount > 0) {
                return redirect()->back()->with('error', 'Cannot rename master when duplicates exist. Only duplicates can be renamed.');
            }
        }

        $attachment->update([
            'location_name' => $request->location_name
        ]);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', "Location renamed to '{$request->location_name}' successfully.");
    }

    public function renameFacility(Request $request, Audit $audit)
    {
        // Legacy method - redirect to new renameLocation method
        return $this->renameLocation($request, $audit);
    }

    /**
     * Remove a specific duplicate attachment (not the master)
     */
    public function removeDuplicate(Request $request, Audit $audit)
    {
        $request->validate([
            'attachment_id' => 'required|exists:audit_review_type_attachments,id'
        ]);

        $attachment = AuditReviewTypeAttachment::findOrFail($request->attachment_id);

        // Security check: ensure this attachment belongs to the audit
        if ($attachment->audit_id !== $audit->id) {
            return redirect()->back()->with('error', 'Attachment does not belong to this audit.');
        }

        // Security check: prevent removing master attachments
        if ($attachment->isMaster()) {
            return redirect()->back()->with('error', 'Cannot remove master attachment. Use detach to remove the entire review type.');
        }

        $locationName = $attachment->getContextualLocationName();

        // Delete all responses for this specific attachment
        Response::where('audit_id', $audit->id)
            ->where('attachment_id', $attachment->id)
            ->delete();

        // Delete the attachment itself
        $attachment->delete();

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', "Duplicate location '{$locationName}' has been removed successfully.");
    }

    /**
     * Load sections content for a specific attachment via AJAX
     */
    public function loadSections(Request $request, Audit $audit)
    {
        $reviewTypeId = $request->input('review_type_id');
        $attachmentId = $request->input('attachment_id');
        
        \Log::info("loadSections called", [
            'audit_id' => $audit->id,
            'review_type_id' => $reviewTypeId,
            'attachment_id' => $attachmentId
        ]);
        
        // Find the attachment
        $attachment = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->where('id', $attachmentId)
            ->where('review_type_id', $reviewTypeId)
            ->first();
        
        if (!$attachment) {
            \Log::warning("Attachment not found", [
                'audit_id' => $audit->id,
                'review_type_id' => $reviewTypeId,
                'attachment_id' => $attachmentId
            ]);
            
            // Let's also check what attachments DO exist for this audit
            $existingAttachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)->get();
            \Log::info("Existing attachments for audit {$audit->id}", [
                'attachments' => $existingAttachments->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "Attachment not found. Audit ID: {$audit->id}, Review Type ID: {$reviewTypeId}, Attachment ID: {$attachmentId}"
            ]);
        }
        
        // Get templates for this review type
        $auditTemplates = Template::where('review_type_id', $reviewTypeId)
            ->where('audit_id', $audit->id)
            ->where('is_active', true)
            ->with(['sections.questions'])
            ->ordered() // Use the systematic ordering scope
            ->get();
        
        // Create a simple review type object for the view
        $reviewType = (object)[
            'id' => $reviewTypeId,
            'attachmentId' => $attachment->id,
            'isMaster' => $attachment->isMaster(),
            'isDuplicate' => $attachment->isDuplicate(),
            'auditTemplates' => $auditTemplates
        ];
        
        try {
            $html = view('admin.audit-management.audits.partials.sections', [
                'audit' => $audit,
                'reviewType' => $reviewType
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rendering sections: ' . $e->getMessage()
            ]);
        }
    }
}