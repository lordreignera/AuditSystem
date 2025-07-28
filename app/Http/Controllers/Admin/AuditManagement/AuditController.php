<?php

namespace App\Http\Controllers\Admin\AuditManagement;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Country;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;
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
        $audits = Audit::with('country')->paginate(10);
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
        $audit->load('country');
        return view('admin.audit-management.audits.show', compact('audit'));
    }

    /**
     * Show audit dashboard for managing review types, templates, sections, and questions
     */
    public function dashboard(Audit $audit)
    {
        $audit->load([
            'country',
            'reviewTypes',
            'responses'
        ]);

        // Get all available review types for adding new ones
        $availableReviewTypes = ReviewType::where('is_active', true)->with('templates')->get();

        // Get existing responses grouped by question
        $existingResponses = collect();
        if ($audit->responses) {
            $existingResponses = $audit->responses->keyBy('question_id');
        }

        // Get all templates for attached review types, but only those created for this audit
        $attachedReviewTypes = $audit->reviewTypes;
        foreach ($attachedReviewTypes as $reviewType) {
            $auditTemplates = Template::where('review_type_id', $reviewType->id)
                ->where('audit_id', $audit->id)
                ->with('sections.questions')
                ->get();

            $reviewType->auditTemplates = $auditTemplates;
        }

        return view('admin.audit-management.audits.dashboard', compact('audit', 'availableReviewTypes', 'existingResponses', 'attachedReviewTypes'));
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
     * Attach a review type to the audit
     */
    public function attachReviewType(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'review_type_id' => 'required|exists:review_types,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $reviewType = ReviewType::findOrFail($request->review_type_id);

        // Get all default templates (not audit-specific)
        $templates = $reviewType->templates()->where('is_active', true)->whereNull('audit_id')->with('sections.questions')->get();

        if ($templates->isEmpty()) {
            return redirect()->back()->with('error', 'No active templates found for this review type.');
        }

        // Check if this review type is already attached to this audit
        if ($audit->reviewTypes()->where('review_type_id', $request->review_type_id)->exists()) {
            return redirect()->back()->with('error', 'This review type is already attached to this audit.');
        }

        $createdTemplates = [];

        foreach ($templates as $originalTemplate) {
            // Create a copy of the template for this audit
            $auditTemplate = $originalTemplate->replicate();
            $auditTemplate->name = $originalTemplate->name;
            $auditTemplate->review_type_id = $reviewType->id;
            $auditTemplate->is_default = false;
            $auditTemplate->audit_id = $audit->id;
            $auditTemplate->save();

            $createdTemplates[] = $auditTemplate;

            // Copy sections and questions for this template
            foreach ($originalTemplate->sections as $originalSection) {
                $auditSection = $originalSection->replicate();
                $auditSection->template_id = $auditTemplate->id;
                $auditSection->audit_id = $audit->id;
                $auditSection->save();

                // Copy questions for this section
                foreach ($originalSection->questions as $originalQuestion) {
                    $auditQuestion = $originalQuestion->replicate();
                    $auditQuestion->section_id = $auditSection->id;
                    $auditQuestion->audit_id = $audit->id;
                    // --- CRITICAL: Deep copy the options field as JSON string, unless already string ---
                    $auditQuestion->options = is_array($originalQuestion->options) ? json_encode($originalQuestion->options) : $originalQuestion->options;
                    $auditQuestion->save();
                }
            }
        }

        // For the pivot table, use the first template as the primary one
        $primaryTemplate = $createdTemplates[0];

        // Attach the review type to the audit with the primary template
        $audit->reviewTypes()->attach($request->review_type_id, [
            'template_id' => $primaryTemplate->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Review type attached successfully with ' . count($createdTemplates) . ' template(s) and all their sections and questions.');
    }

    /**
     * Remove a review type from the audit
     */
    public function detachReviewType(Request $request, Audit $audit)
    {
        $validator = Validator::make($request->all(), [
            'review_type_id' => 'required|exists:review_types,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Remove all templates, sections, questions for this audit/review_type
        $templates = Template::where('review_type_id', $request->review_type_id)
            ->where('audit_id', $audit->id)
            ->get();
        foreach ($templates as $template) {
            foreach ($template->sections as $section) {
                foreach ($section->questions as $question) {
                    $question->delete();
                }
                $section->delete();
            }
            $template->delete();
        }

        $audit->reviewTypes()->detach($request->review_type_id);

        return redirect()->route('admin.audits.dashboard', $audit)
            ->with('success', 'Review type and its templates removed successfully.');
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

        if ($request->options) {
            $data['options'] = json_decode($request->options, true);
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
            'answers' => 'required|array',
        ]);

        foreach ($request->answers as $questionId => $data) {
            // Handle table questions: Save as JSON if 'table' present
            if (isset($data['table'])) {
                $answer = json_encode($data['table']);
            } else {
                $answer = $data['answer'] ?? '';
            }

            Response::updateOrCreate(
                [
                    'audit_id' => $request->audit_id,
                    'question_id' => $questionId,
                    'created_by' => auth()->id(),
                ],
                [
                    'answer' => $answer,
                    'audit_note' => $data['audit_note'] ?? '',
                ]
            );
        }

        // Redirect back to the URL specified in the form, fallback to audit page
        return redirect($request->input('redirect_to', route('admin.audits.show', $request->audit_id)))
            ->with('success', 'Response saved successfully.');
    }
};