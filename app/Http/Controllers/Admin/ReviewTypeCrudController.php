<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewType;
use App\Models\Template;
use App\Models\Section;
use App\Models\Question;
use App\Models\Audit;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewTypeCrudController extends Controller
{
    /**
     * Display a listing of review types with their structures
     */
    public function index()
    {
        $reviewTypes = ReviewType::with([
            'templates' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT TEMPLATES
                      ->orderBy('name');
            },
            'templates.sections' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT SECTIONS
                      ->orderBy('order');
            },
            'templates.sections.questions' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT QUESTIONS
                      ->orderBy('order');
            }
        ])->where('is_active', true)->get();

        return view('admin.review-types.crud-index', compact('reviewTypes'));
    }

    /**
     * Show detailed view of a specific review type
     */
    public function show(ReviewType $reviewType)
    {
        $reviewType->load([
            'templates' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT TEMPLATES
                      ->orderBy('name');
            },
            'templates.sections' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT SECTIONS
                      ->orderBy('order');
            },
            'templates.sections.questions' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT QUESTIONS
                      ->orderBy('order');
            }
        ]);

        return view('admin.review-types.crud-show', compact('reviewType'));
    }

    /**
     * Show form to create audit based on review type template
     */
    public function createAudit(ReviewType $reviewType, Template $template)
    {
        // Ensure we're only working with default templates
        if ($template->audit_id !== null) {
            abort(403, 'Cannot create audit from audit-specific template.');
        }
        
        $template->load([
            'sections' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT SECTIONS
                      ->orderBy('order');
            },
            'sections.questions' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id') // ONLY DEFAULT QUESTIONS
                      ->orderBy('order');
            }
        ]);

        return view('admin.review-types.create-audit', compact('reviewType', 'template'));
    }

    /**
     * Store a new audit with responses
     */
    public function storeAudit(Request $request, ReviewType $reviewType, Template $template)
    {
        $request->validate([
            'review_code' => 'required|string|max:255|unique:audits,review_code',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'lead_auditor' => 'required|string|max:255',
            'team_members' => 'nullable|string',
            'responses' => 'required|array',
            'responses.*' => 'nullable|string',
            'audit_notes' => 'nullable|array',
            'audit_notes.*' => 'nullable|string',
        ]);

        // Create the audit
        $audit = Audit::create([
            'review_code' => $request->review_code,
            'review_type_id' => $reviewType->id,
            'template_id' => $template->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'location' => $request->location,
            'lead_auditor' => $request->lead_auditor,
            'team_members' => $request->team_members,
            'status' => 'in_progress',
            'created_by' => Auth::id(),
        ]);

        // Store responses
        foreach ($request->responses as $questionId => $answer) {
            if (!empty($answer) || !empty($request->audit_notes[$questionId])) {
                Response::create([
                    'audit_id' => $audit->id,
                    'question_id' => $questionId,
                    'answer' => $answer,
                    'audit_note' => $request->audit_notes[$questionId] ?? null,
                    'created_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('admin.audits.show', $audit)
            ->with('success', 'Audit created successfully with responses.');
    }

    /**
     * Show existing audit for editing
     */
    public function editAudit(Audit $audit)
    {
        $audit->load([
            'reviewType',
            'template.sections' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'template.sections.questions' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'responses'
        ]);

        // Create responses array for easy access
        $existingResponses = $audit->responses->keyBy('question_id');

        return view('admin.review-types.edit-audit', compact('audit', 'existingResponses'));
    }

    /**
     * Update existing audit responses
     */
    public function updateAudit(Request $request, Audit $audit)
    {
        $request->validate([
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'team_members' => 'nullable|string',
            'status' => 'required|in:in_progress,completed,cancelled',
            'responses' => 'required|array',
            'responses.*' => 'nullable|string',
            'audit_notes' => 'nullable|array',
            'audit_notes.*' => 'nullable|string',
        ]);

        // Update audit basic info
        $audit->update([
            'end_date' => $request->end_date,
            'team_members' => $request->team_members,
            'status' => $request->status,
        ]);

        // Update or create responses
        foreach ($request->responses as $questionId => $answer) {
            $response = Response::where('audit_id', $audit->id)
                ->where('question_id', $questionId)
                ->first();

            if ($response) {
                $response->update([
                    'answer' => $answer,
                    'audit_note' => $request->audit_notes[$questionId] ?? null,
                ]);
            } elseif (!empty($answer) || !empty($request->audit_notes[$questionId])) {
                Response::create([
                    'audit_id' => $audit->id,
                    'question_id' => $questionId,
                    'answer' => $answer,
                    'audit_note' => $request->audit_notes[$questionId] ?? null,
                    'created_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('admin.audits.show', $audit)
            ->with('success', 'Audit updated successfully.');
    }

    



}
