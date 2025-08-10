@extends('admin.admin_layout')

@section('title', $template->name . ' - Template Management')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2 style="color: #2d3748 !important; font-weight: 600;">{{ $template->name }}</h2>
                    <p class="mb-md-0" style="color: #718096 !important;">
                        {{ $template->description ?: 'Template for ' . $template->reviewType->name }}
                    </p>
                    <span class="badge bg-info mt-1">{{ $template->reviewType->name }}</span>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary mb-3 mb-md-0">
                        <i class="mdi mdi-arrow-left"></i> Back to Templates
                    </a>
                    <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-warning mb-3 mb-md-0">
                        <i class="mdi mdi-pencil"></i> Edit Template
                    </a>
                    <button type="button" class="btn btn-primary mb-3 mb-md-0" data-bs-toggle="modal" data-bs-target="#addSectionModal" style="color: #ffffff !important;">
                        <i class="mdi mdi-folder-plus"></i> Add Section
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Statistics -->
<div class="row">
    <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0">{{ $template->sections->count() }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Sections</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-primary">
                            <span class="mdi mdi-folder icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0">{{ $template->sections->sum(function($s) { return $s->questions->count(); }) }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Total Questions</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-success">
                            <span class="mdi mdi-help-circle icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
        <div class="card audit-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                            <h3 class="mb-0">{{ $template->sections->sum(function($s) { return $s->questions->where('is_required', true)->count(); }) }}</h3>
                        </div>
                        <h6 class="text-muted font-weight-normal">Required Questions</h6>
                    </div>
                    <div class="col-3">
                        <div class="icon icon-box-warning">
                            <span class="mdi mdi-alert-circle icon-item"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Sections and Questions -->
@if($template->sections->isEmpty())
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="mdi mdi-folder-outline text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">No sections created yet</h4>
                        <p class="text-muted">Start building your template by adding sections and questions.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal" style="color: #ffffff !important;">
                            <i class="mdi mdi-folder-plus"></i> Add First Section
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    @foreach($template->sections as $section)
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <!-- Section Header -->
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 text-white">
                                    <span class="badge bg-light text-dark me-2">{{ $section->order }}</span>
                                    {{ $section->name }}
                                </h5>
                                @if($section->description)
                                    <p class="mb-0 text-white-50">{{ $section->description }}</p>
                                @endif
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2">{{ $section->questions->count() }} questions</span>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-light" 
                                            onclick="editSection({{ $section->id }}, '{{ $section->name }}', '{{ $section->description }}')"
                                            title="Edit Section">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="addQuestion({{ $section->id }})"
                                            title="Add Question">
                                        <i class="mdi mdi-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light text-danger" 
                                            onclick="deleteSection({{ $section->id }})"
                                            title="Delete Section">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div class="card-body">
                        @if($section->questions->isEmpty())
                            <div class="text-center py-3 text-muted">
                                <em>No questions in this section yet.</em>
                                <button type="button" class="btn btn-sm btn-primary ms-2" 
                                        onclick="addQuestion({{ $section->id }})" style="color: #ffffff !important;">
                                    <i class="mdi mdi-plus"></i> Add First Question
                                </button>
                            </div>
                        @else
                            @foreach($section->questions as $question)
                                <div class="border rounded p-3 mb-3 question-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-secondary me-2">{{ $question->order }}</span>
                                                <div>
                                                    <h6 class="mb-1">{{ $question->question_text }}</h6>
                                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $question->response_type)) }}</span>
                                                        @if($question->is_required)
                                                            <span class="badge bg-warning">Required</span>
                                                        @endif
                                                    </div>
                                                    @if($question->options && in_array($question->response_type, ['select', 'multiple_choice', 'checkbox', 'radio']))
                                                        <div class="mt-2">
                                                            <small class="text-muted">Options:</small>
                                                            @php
                                                                $options = is_array($question->options) ? $question->options : json_decode($question->options, true);
                                                            @endphp
                                                            @if($options && !isset($options['columns']))
                                                                <div class="mt-1">
                                                                    @foreach($options as $option)
                                                                        <span class="badge bg-light text-dark me-1">{{ $option }}</span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @elseif($question->response_type === 'table' && $question->options)
                                                        @php
                                                            $tableData = is_array($question->options) ? $question->options : json_decode($question->options, true);
                                                        @endphp
                                                        @if($tableData && isset($tableData['columns']))
                                                            <div class="mt-2">
                                                                <small class="text-muted">Table Structure:</small>
                                                                <div class="table-responsive mt-1">
                                                                    <table class="table table-sm table-bordered">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                @foreach($tableData['columns'] as $column)
                                                                                    <th style="font-size: 11px;">{{ $column }}</th>
                                                                                @endforeach
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @for($i = 0; $i < min(2, $tableData['rows'] ?? 1); $i++)
                                                                                <tr>
                                                                                    @foreach($tableData['columns'] as $column)
                                                                                        <td style="font-size: 10px; padding: 4px;">...</td>
                                                                                    @endforeach
                                                                                </tr>
                                                                            @endfor
                                                                            @if(($tableData['rows'] ?? 1) > 2)
                                                                                <tr>
                                                                                    <td colspan="{{ count($tableData['columns']) }}" class="text-center text-muted" style="font-size: 10px;">
                                                                                        ... {{ $tableData['rows'] - 2 }} more rows
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <small class="text-info">{{ count($tableData['columns']) }} columns × {{ $tableData['rows'] ?? 1 }} rows</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="editQuestion({{ $question->id }})"
                                                    title="Edit Question">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteQuestion({{ $question->id }})"
                                                    title="Delete Question">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.templates.add-section', $template) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="section_name" class="form-label">Section Name *</label>
                        <input type="text" class="form-control" id="section_name" name="name" required
                               placeholder="e.g., General Information, Safety Standards">
                    </div>
                    <div class="mb-3">
                        <label for="section_description" class="form-label">Description</label>
                        <textarea class="form-control" id="section_description" name="description" rows="3"
                                  placeholder="Optional description of this section"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="color: #ffffff !important;">
                        <i class="mdi mdi-folder-plus"></i> Add Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.templates.update-section', $template) }}">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_section_id" name="section_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_section_name" class="form-label">Section Name *</label>
                        <input type="text" class="form-control" id="edit_section_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_section_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_section_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="mdi mdi-content-save"></i> Update Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.templates.add-question', $template) }}">
                @csrf
                <input type="hidden" id="add_question_section_id" name="section_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="question_text" class="form-label">Question Text *</label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="2" required
                                  placeholder="Enter the question text"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="question_type" class="form-label">Question Type *</label>
                                <select class="form-control" id="question_type" name="question_type" required onchange="toggleOptions()">
                                    <option value="">Select Type</option>
                                    <option value="text">Text Input</option>
                                    <option value="textarea">Text Area</option>
                                    <option value="select">Dropdown</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="radio">Radio Button</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="time">Time</option>
                                    <option value="datetime">Date & Time</option>
                                    <option value="file">File Upload</option>
                                    <option value="table">Table</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1">
                                <label class="form-check-label" for="is_required">
                                    Required Question
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="options_section" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Options</label>
                            <div id="options_container">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="options[]" placeholder="Option 1">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">
                                <i class="mdi mdi-plus"></i> Add Option
                            </button>
                        </div>
                    </div>
                    
                    <!-- Table Configuration -->
                    <div id="table_section" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Table Configuration</label>
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="table_columns" class="form-label">Table Columns</label>
                                    <div id="table_columns_container">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="table_columns[]" placeholder="Column 1">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeTableColumn(this)">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTableColumn()">
                                        <i class="mdi mdi-plus"></i> Add Column
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <label for="table_rows" class="form-label">Default Rows</label>
                                    <input type="number" class="form-control" id="table_rows" name="table_rows" min="1" max="20" value="3">
                                    <small class="text-muted">Initial number of rows</small>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="allow_add_rows" name="allow_add_rows" value="1" checked>
                                        <label class="form-check-label" for="allow_add_rows">
                                            Allow adding rows
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="color: #ffffff !important;">
                        <i class="mdi mdi-help-circle-outline"></i> Add Question
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSection(id, name, description) {
    document.getElementById('edit_section_id').value = id;
    document.getElementById('edit_section_name').value = name;
    document.getElementById('edit_section_description').value = description || '';
    new bootstrap.Modal(document.getElementById('editSectionModal')).show();
}

function addQuestion(sectionId) {
    document.getElementById('add_question_section_id').value = sectionId;
    new bootstrap.Modal(document.getElementById('addQuestionModal')).show();
}

function deleteSection(sectionId) {
    if (confirm('Are you sure you want to delete this section and all its questions?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.templates.delete-section', $template) }}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
            <input type="hidden" name="section_id" value="${sectionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.templates.delete-question', $template) }}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
            <input type="hidden" name="question_id" value="${questionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function editQuestion(questionId) {
    // For simplicity, redirect to edit (you can implement inline editing later)
    alert('Question editing will be implemented in the next update. For now, delete and recreate the question.');
}

function toggleOptions() {
    const questionType = document.getElementById('question_type').value;
    const optionsSection = document.getElementById('options_section');
    const tableSection = document.getElementById('table_section');
    
    // Hide all sections first
    optionsSection.style.display = 'none';
    tableSection.style.display = 'none';
    
    if (['select', 'multiple_choice', 'checkbox', 'radio'].includes(questionType)) {
        optionsSection.style.display = 'block';
    } else if (questionType === 'table') {
        tableSection.style.display = 'block';
    }
}

function addOption() {
    const container = document.getElementById('options_container');
    const optionCount = container.children.length + 1;
    const newOption = document.createElement('div');
    newOption.className = 'input-group mb-2';
    newOption.innerHTML = `
        <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount}">
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
            <i class="mdi mdi-delete"></i>
        </button>
    `;
    container.appendChild(newOption);
}

function removeOption(button) {
    button.parentElement.remove();
}

function addTableColumn() {
    const container = document.getElementById('table_columns_container');
    const columnCount = container.children.length + 1;
    const newColumn = document.createElement('div');
    newColumn.className = 'input-group mb-2';
    newColumn.innerHTML = `
        <input type="text" class="form-control" name="table_columns[]" placeholder="Column ${columnCount}">
        <button type="button" class="btn btn-outline-danger" onclick="removeTableColumn(this)">
            <i class="mdi mdi-delete"></i>
        </button>
    `;
    container.appendChild(newColumn);
}

function removeTableColumn(button) {
    const container = document.getElementById('table_columns_container');
    if (container.children.length > 1) {
        button.parentElement.remove();
    } else {
        alert('At least one column is required for table questions.');
    }
}

function removeOption(button) {
    button.parentElement.remove();
}
</script>
@endsection
