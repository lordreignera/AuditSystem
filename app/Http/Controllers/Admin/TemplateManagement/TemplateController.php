<?php

namespace App\Http\Controllers\Admin\TemplateManagement;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\ReviewType;
use App\Models\Section;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage templates']);
    }

    /**
     * Display a listing of default templates
     */
    public function index()
    {
        $templates = Template::with(['reviewType', 'sections.questions'])
            ->where('is_default', true)
            ->whereNull('audit_id')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.template-management.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        $reviewTypes = ReviewType::where('is_active', true)->orderBy('name')->get();
        return view('admin.template-management.templates.create', compact('reviewTypes'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'review_type_id' => 'required|exists:review_types,id',
            'is_active' => 'boolean',
        ]);

        $template = Template::create([
            'name' => $request->name,
            'description' => $request->description,
            'review_type_id' => $request->review_type_id,
            'is_default' => true,
            'audit_id' => null,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Template created successfully. You can now add sections and questions.');
    }

    /**
     * Display the specified template with sections and questions
     */
    public function show(Template $template)
    {
        // Ensure we're only showing default templates
        if (!$template->is_default || $template->audit_id !== null) {
            abort(404, 'Template not found.');
        }

        $template->load([
            'reviewType',
            'sections' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id')
                      ->orderBy('order');
            },
            'sections.questions' => function($query) {
                $query->where('is_active', true)
                      ->whereNull('audit_id')
                      ->orderBy('order');
            }
        ]);

        return view('admin.template-management.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(Template $template)
    {
        // Ensure we're only editing default templates
        if (!$template->is_default || $template->audit_id !== null) {
            abort(404, 'Template not found.');
        }

        $reviewTypes = ReviewType::where('is_active', true)->orderBy('name')->get();
        return view('admin.template-management.templates.edit', compact('template', 'reviewTypes'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, Template $template)
    {
        // Ensure we're only updating default templates
        if (!$template->is_default || $template->audit_id !== null) {
            abort(404, 'Template not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'review_type_id' => 'required|exists:review_types,id',
            'is_active' => 'boolean',
        ]);

        $template->update([
            'name' => $request->name,
            'description' => $request->description,
            'review_type_id' => $request->review_type_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template and all its sections/questions
     */
    public function destroy(Template $template)
    {
        // Ensure we're only deleting default templates
        if (!$template->is_default || $template->audit_id !== null) {
            abort(404, 'Template not found.');
        }

        DB::transaction(function () use ($template) {
            // Delete all questions in all sections
            foreach ($template->sections as $section) {
                $section->questions()->delete();
            }

            // Delete all sections
            $template->sections()->delete();

            // Delete the template
            $template->delete();
        });

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template and all its content deleted successfully.');
    }

    /**
     * Add a new section to the template
     */
    public function addSection(Request $request, Template $template)
    {
        // Ensure we're only working with default templates
        if (!$template->is_default || $template->audit_id !== null) {
            abort(404, 'Template not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Get the next order number
        $nextOrder = $template->sections()->max('order') + 1;

        $section = Section::create([
            'template_id' => $template->id,
            'name' => $request->name,
            'description' => $request->description,
            'order' => $nextOrder,
            'is_active' => true,
            'audit_id' => null,
        ]);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Section added successfully.');
    }

    /**
     * Update a section
     */
    public function updateSection(Request $request, Template $template)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $section = Section::findOrFail($request->section_id);

        // Ensure the section belongs to this template and is a default section
        if ($section->template_id !== $template->id || $section->audit_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        $section->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Section updated successfully.');
    }

    /**
     * Delete a section and all its questions
     */
    public function deleteSection(Request $request, Template $template)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::findOrFail($request->section_id);

        // Ensure the section belongs to this template and is a default section
        if ($section->template_id !== $template->id || $section->audit_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($section) {
            // Delete all questions in this section
            $section->questions()->delete();

            // Delete the section
            $section->delete();
        });

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Section and all its questions deleted successfully.');
    }

    /**
     * Add a new question to a section
     */
    public function addQuestion(Request $request, Template $template)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:text,textarea,select,multiple_choice,checkbox,radio,number,date,time,datetime,file,table',
            'options' => 'nullable|array',
            'table_columns' => 'nullable|array',
            'table_rows' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
        ]);

        $section = Section::findOrFail($request->section_id);

        // Ensure the section belongs to this template and is a default section
        if ($section->template_id !== $template->id || $section->audit_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        // Get the next order number for this section
        $nextOrder = $section->questions()->max('order') + 1;

        // Prepare options/table data
        $optionsData = null;
        if ($request->question_type === 'table') {
            $optionsData = [
                'columns' => $request->table_columns ?? [],
                'rows' => $request->table_rows ?? 1,
            ];
        } elseif ($request->options) {
            $optionsData = $request->options;
        }

        $question = Question::create([
            'section_id' => $section->id,
            'question_text' => $request->question_text,
            'response_type' => $request->question_type, // Map question_type to response_type
            'options' => $optionsData ? json_encode($optionsData) : null,
            'is_required' => $request->is_required ?? false,
            'order' => $nextOrder,
            'is_active' => true,
            'audit_id' => null,
        ]);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Question added successfully.');
    }

    /**
     * Update a question
     */
    public function updateQuestion(Request $request, Template $template)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:text,textarea,select,multiple_choice,checkbox,radio,number,date,time,datetime,file,table',
            'options' => 'nullable|array',
            'table_columns' => 'nullable|array',
            'table_rows' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
        ]);

        $question = Question::findOrFail($request->question_id);

        // Ensure the question belongs to this template and is a default question
        if ($question->section->template_id !== $template->id || $question->audit_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        // Prepare options/table data
        $optionsData = null;
        if ($request->question_type === 'table') {
            $optionsData = [
                'columns' => $request->table_columns ?? [],
                'rows' => $request->table_rows ?? 1,
            ];
        } elseif ($request->options) {
            $optionsData = $request->options;
        }

        $question->update([
            'question_text' => $request->question_text,
            'response_type' => $request->question_type, // Map question_type to response_type
            'options' => $optionsData ? json_encode($optionsData) : null,
            'is_required' => $request->is_required ?? false,
        ]);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Delete a question
     */
    public function deleteQuestion(Request $request, Template $template)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        $question = Question::findOrFail($request->question_id);

        // Ensure the question belongs to this template and is a default question
        if ($question->section->template_id !== $template->id || $question->audit_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        $question->delete();

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Duplicate a template with all its sections and questions
     */
    public function duplicate(Template $template)
    {
        // Ensure we're only duplicating default templates
        if (!$template->is_default || $template->audit_id !== null) {
            abort(404, 'Template not found.');
        }

        DB::transaction(function () use ($template, &$newTemplate) {
            // Create a copy of the template
            $newTemplate = $template->replicate();
            $newTemplate->name = $template->name . ' (Copy)';
            $newTemplate->save();

            // Copy all sections and their questions
            foreach ($template->sections as $section) {
                $newSection = $section->replicate();
                $newSection->template_id = $newTemplate->id;
                $newSection->save();

                foreach ($section->questions as $question) {
                    $newQuestion = $question->replicate();
                    $newQuestion->section_id = $newSection->id;
                    $newQuestion->save();
                }
            }
        });

        return redirect()->route('admin.templates.show', $newTemplate)
            ->with('success', 'Template duplicated successfully.');
    }
}
