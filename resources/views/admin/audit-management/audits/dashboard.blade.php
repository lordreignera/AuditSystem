@extends('admin.admin_layout')

@section('title', 'Audit Dashboard - ' . $audit->name)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Breadcrumb -->
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.audits.index') }}">Audits</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $audit->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Audit Header -->
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-white">{{ $audit->name }}</h3>
                        <p class="mb-0 text-white-50">
                            <i class="mdi mdi-map-marker me-1"></i>{{ $audit->country->name }} | 
                            <i class="mdi mdi-calendar me-1"></i>{{ $audit->start_date->format('M j, Y') }}
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-light">{{ $audit->review_code }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Description</h6>
                        <p class="text-muted">{{ $audit->description ?? 'No description provided' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Start Date</h6>
                        <p class="text-muted">{{ $audit->start_date->format('M j, Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>End Date</h6>
                        <p class="text-muted">{{ $audit->end_date ? $audit->end_date->format('M j, Y') : 'Not set' }}</p>
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

<!-- Review Types Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Review Types & Templates</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReviewTypeModal">
                    <i class="mdi mdi-plus me-1"></i>Attach Review Type
                </button>
            </div>
            <div class="card-body">
                @if($attachedReviewTypes && $attachedReviewTypes->count() > 0)
                    <div class="accordion" id="reviewTypesAccordion">
                        @foreach($attachedReviewTypes as $index => $reviewType)
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="heading{{ $index }}">
                                    <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div>
                                                <strong>{{ $reviewType->name }}</strong>
                                                <span class="badge bg-primary ms-2">{{ $reviewType->auditTemplates->count() }} Templates</span>
                                            </div>
                                            <div>
                                                <span class="badge bg-info">{{ $reviewType->auditTemplates->sum(fn($template) => $template->sections->count()) }} Sections</span>
                                                <span class="badge bg-success">{{ $reviewType->auditTemplates->sum(fn($template) => $template->sections->sum(fn($section) => $section->questions->count())) }} Questions</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#reviewTypesAccordion">
                                    <div class="accordion-body">
                                    @php
                                        $templateCount = $reviewType->auditTemplates->count();
                                    @endphp
                                    @if($templateCount > 0)
                                        {{-- Render all templates, only one visible at a time --}}
                                        @foreach($reviewType->auditTemplates as $templateIndex => $template)
                                            <div class="template-panel" id="template-panel-{{ $reviewType->id }}-{{ $templateIndex }}" style="display: {{ $templateIndex == 0 ? 'block' : 'none' }};">
                                                <div class="card mb-4" id="template-{{ $template->id }}">
                                                    <div class="card-header bg-light">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0">
                                                                <i class="mdi mdi-file-document-outline me-2"></i>
                                                                {{ $template->name }}
                                                            </h6>
                                                            <div>
                                                                <button class="btn btn-outline-primary btn-sm me-2" type="button" onclick="previewTemplate({{ $template->id }})">
                                                                    <i class="mdi mdi-eye"></i> Preview
                                                                </button>
                                                                <button class="btn btn-outline-success btn-sm" type="button" onclick="duplicateTemplate({{ $template->id }})">
                                                                    <i class="mdi mdi-content-copy"></i> Duplicate
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        @foreach($template->sections as $section)
                                                            <div class="card mb-3 section-card" id="section-{{ $section->id }}">
                                                                <div class="card-header bg-light">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <h6 class="mb-0">
                                                                            <i class="mdi mdi-folder-outline me-2"></i>
                                                                            {{ $section->name }}
                                                                        </h6>
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button class="btn btn-outline-success" type="button" onclick="addQuestion({{ $section->id }})">
                                                                                <i class="mdi mdi-plus"></i> Add Question
                                                                            </button>
                                                                            <button class="btn btn-outline-warning" type="button" onclick="editSection({{ $section->id }})">
                                                                                <i class="mdi mdi-pencil"></i>
                                                                            </button>
                                                                            <button class="btn btn-outline-danger" type="button" onclick="deleteSection({{ $section->id }})">
                                                                                <i class="mdi mdi-delete"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    @if($section->description)
                                                                        <small class="text-muted">{{ $section->description }}</small>
                                                                    @endif
                                                                </div>
                                                                <div class="card-body">
                                                                    @foreach($section->questions as $questionIndex => $question)
                                                                        <form method="POST" action="{{ route('admin.review-types-crud.save-responses', $reviewType->id) }}" class="mb-4">
                                                                            @csrf
                                                                            <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                                                                            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                                                                            @php
                                                                                $existingResponse = $question->responses()
                                                                                    ->where('audit_id', $audit->id)
                                                                                    ->where('created_by', auth()->id())
                                                                                    ->first();
                                                                            @endphp

                                                                            <div class="question-item p-3 border rounded" id="question-{{ $question->id }}">
                                                                                <div class="d-flex justify-content-between align-items-start">
                                                                                    <div class="flex-grow-1">
                                                                                        <h6 class="text-primary">{{ $questionIndex + 1 }}. {{ $question->question_text }}</h6>
                                                                                        @if($question->description)
                                                                                            <p class="text-muted small mb-2">{{ $question->description }}</p>
                                                                                        @endif

                                                                                        @if($question->response_type === 'table')
                                                                                            <div class="mb-2">
                                                                                                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#tableModal-{{ $question->id }}">
                                                                                                    <i class="mdi mdi-table"></i> View Table
                                                                                                </button>
                                                                                                {{-- Display the saved table answer (if any) --}}
                                                                                                @if($existingResponse && $existingResponse->answer)
                                                                                                    @php
                                                                                                        $tableData = is_array($existingResponse->answer) ? $existingResponse->answer : json_decode($existingResponse->answer, true);
                                                                                                    @endphp
                                                                                                    @if(is_array($tableData) && count($tableData))
                                                                                                        <div class="table-responsive mt-2">
                                                                                                            <table class="table table-bordered table-sm">
                                                                                                                <tbody>
                                                                                                                @foreach($tableData as $row)
                                                                                                                    <tr>
                                                                                                                        @foreach($row as $cell)
                                                                                                                            <td>{{ $cell }}</td>
                                                                                                                        @endforeach
                                                                                                                    </tr>
                                                                                                                @endforeach
                                                                                                                </tbody>
                                                                                                            </table>
                                                                                                        </div>
                                                                                                    @endif
                                                                                                @endif
                                                                                            </div>
                                                                                        @else
                                                                                            <div class="mb-2">
                                                                                                <label class="form-label">Answer</label>
                                                                                                <input type="text" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') }}">
                                                                                            </div>
                                                                                        @endif

                                                                                        <div class="mb-2">
                                                                                            <label class="form-label">Audit Note</label>
                                                                                            <textarea name="answers[{{ $question->id }}][audit_note]" class="form-control" rows="2">{{ old('answers.' . $question->id . '.audit_note', $existingResponse->audit_note ?? '') }}</textarea>
                                                                                        </div>

                                                                                        <div class="row mt-2">
                                                                                            <div class="col-md-6">
                                                                                                <span class="badge bg-secondary">{{ ucfirst($question->response_type) }}</span>
                                                                                                @if($question->is_required)
                                                                                                    <span class="badge bg-warning">Required</span>
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="col-md-6 text-end">
                                                                                                <small class="text-muted">Order: {{ $question->order }}</small>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="btn-group btn-group-sm">
                                                                                        <button class="btn btn-outline-warning" type="button" onclick="editQuestion({{ $question->id }})">
                                                                                            <i class="mdi mdi-pencil"></i>
                                                                                        </button>
                                                                                        <button class="btn btn-outline-danger" type="button" onclick="deleteQuestion({{ $question->id }})">
                                                                                            <i class="mdi mdi-delete"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="text-end mt-3">
                                                                                    <button type="submit" class="btn btn-primary">
                                                                                        <i class="mdi mdi-content-save"></i> Save
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @if($templateCount > 1)
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <button class="btn btn-outline-secondary"
                                                    type="button"
                                                    id="prev-template-btn-{{ $reviewType->id }}"
                                                    onclick="showPrevTemplate({{ $reviewType->id }}, {{ $templateCount }})">
                                                    &laquo; Previous Template
                                                </button>
                                                <button class="btn btn-outline-secondary"
                                                    type="button"
                                                    id="next-template-btn-{{ $reviewType->id }}"
                                                    onclick="showNextTemplate({{ $reviewType->id }}, {{ $templateCount }})">
                                                    Next Template &raquo;
                                                </button>
                                            </div>
                                        @endif

                                    @endif

                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card border-danger">
                                                <div class="card-header bg-danger text-white">
                                                    <h6 class="mb-0">Danger Zone</h6>
                                                </div>
                                                <div class="card-body">
                                                    <button class="btn btn-outline-danger" onclick="removeReviewType({{ $reviewType->id }})">
                                                        <i class="mdi mdi-delete"></i> Remove Review Type
                                                    </button>
                                                    <small class="text-muted d-block mt-2">This will remove the review type and all its templates, sections, and questions from this audit.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- End Danger zone --}}
                                    @endphp
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="mdi mdi-clipboard-text mdi-48px text-muted"></i>
                        <p class="mt-2 text-muted">No review types attached to this audit yet.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReviewTypeModal">
                            <i class="mdi mdi-plus me-1"></i>Attach First Review Type
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Render all table modals for all table questions at the END of the page, outside all forms/cards --}}
@php
$tableQuestions = [];
foreach($attachedReviewTypes as $reviewType) {
    foreach($reviewType->auditTemplates as $template) {
        foreach($template->sections as $section) {
            foreach($section->questions as $question) {
                if($question->response_type === 'table') {
                    $tableQuestions[] = [$question, $reviewType];
                }
            }
        }
    }
}
@endphp

@foreach($tableQuestions as [$modalQuestion, $modalReviewType])
    @php
        $options = is_string($modalQuestion->options) ? json_decode($modalQuestion->options, true) : $modalQuestion->options;
        $rows = $options['rows'] ?? [];
        $existingResponse = $modalQuestion->responses()
            ->where('audit_id', $audit->id)
            ->where('created_by', auth()->id())
            ->first();
        $existingTable = $existingResponse && $existingResponse->answer ? (is_array($existingResponse->answer) ? $existingResponse->answer : json_decode($existingResponse->answer, true)) : [];
        $colCount = 2;
        if (count($rows) && count($rows[0]) > $colCount) $colCount = count($rows[0]);
        elseif (isset($existingTable[0]) && count($existingTable[0]) > $colCount) $colCount = count($existingTable[0]);
    @endphp
    <div class="modal fade" id="tableModal-{{ $modalQuestion->id }}" tabindex="-1" aria-labelledby="tableModalLabel-{{ $modalQuestion->id }}" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content bg-white">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-dark" id="tableModalLabel-{{ $modalQuestion->id }}">
                        Table Answer: {{ $modalQuestion->question_text }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-white">
                    <form method="POST" action="{{ route('admin.review-types-crud.save-responses', $modalReviewType->id) }}">
                        @csrf
                        <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="editableTable-{{ $modalQuestion->id }}">
                                <tbody>
                                @if(count($rows))
                                    @foreach($rows as $r => $row)
                                        <tr>
                                            @foreach($row as $c => $cell)
                                                @if($r === 0)
                                                    <th>{{ $cell ?? 'Header '.($c+1) }}</th>
                                                @else
                                                    <td>
                                                        <input type="text"
                                                            class="form-control"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $c }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $c, $existingTable[$r][$c] ?? '') }}">
                                                    </td>
                                                @endif
                                            @endforeach
                                            @if($r === 0 && count($row) < $colCount)
                                                @for($i = count($row); $i < $colCount; $i++)
                                                    <th>Header {{ $i+1 }}</th>
                                                @endfor
                                            @elseif($r > 0 && count($row) < $colCount)
                                                @for($i = count($row); $i < $colCount; $i++)
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $i }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $i, $existingTable[$r][$i] ?? '') }}">
                                                    </td>
                                                @endfor
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th>Header 1</th>
                                        <th>Header 2</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control"
                                                name="answers[{{ $modalQuestion->id }}][table][1][0]">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control"
                                                name="answers[{{ $modalQuestion->id }}][table][1][1]">
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="addRow({{ $modalQuestion->id }})">Add Row</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="addColumn({{ $modalQuestion->id }})">Add Column</button>
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="deleteRow({{ $modalQuestion->id }})">Delete Row</button>
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="deleteColumn({{ $modalQuestion->id }})">Delete Column</button>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Save Table
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- modals -->
@include('admin.audit-management.audits.modals')

<!-- scripts -->
@include('admin.audit-management.audits.scripts')

@endsection