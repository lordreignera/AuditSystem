@extends('admin.admin_layout')

@section('title', 'Audit Dashboard - ' . $audit->name)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Custom table styling for better appearance */
    .table-editable {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background-color: #ffffff !important;
        color: #333333 !important;
    }
    .table-editable th {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
        padding: 12px 8px;
        font-weight: 600;
        color: #495057 !important;
    }
    .table-editable td {
        background-color: #ffffff !important;
        border: 1px solid #dee2e6;
        padding: 8px;
        color: #333333 !important;
    }
    .table-editable input {
        min-height: 38px;
        transition: all 0.2s ease;
        background-color: #ffffff !important;
        color: #212529 !important;
    }
    .table-editable input:focus {
        background-color: #fff3cd !important;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25) !important;
        border: 1px solid #0d6efd !important;
    }
    .table-editable th input {
        font-weight: 600;
        color: #495057 !important;
        background-color: #ffffff !important;
    }
    /* Header rows input styling */
    .header-rows-input {
        max-width: 100px;
        display: inline-block;
    }
    /* Modal improvements */
    .modal-xl { max-width: 95%; }
    .modal-body { background-color: #ffffff !important; }
    .table-responsive { background-color: #ffffff !important; }
    /* Button improvements */
    .table-action-buttons {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        margin: 15px 0;
    }
    /* Force table text visibility - Additional Override */
    .table, .table * { color: #333333 !important; }
    .table thead th {
        background-color: #f8f9fa !important;
        color: #495057 !important;
        border-bottom: 2px solid #dee2e6 !important;
    }
    .table tbody td {
        background-color: #ffffff !important;
        color: #333333 !important;
        border-bottom: 1px solid #dee2e6 !important;
    }
    /* Specific styling for table cells with content */
    .table td, .table th {
        color: #333333 !important;
        font-weight: 500;
        padding: 12px 8px;
    }
    /* Override any inherited styles */
    .card-body .table,
    .card-body .table td,
    .card-body .table th {
        color: #333333 !important;
        background-color: #ffffff !important;
    }
</style>

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
                <div class="row g-3">
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
                <div class="d-flex gap-2">
                    <!-- Global import helper (direct users to per-review-type modal) -->
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importBookletModal-global">
                        <i class="mdi mdi-file-excel me-1"></i>Import Booklet (XLSX)
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReviewTypeModal">
                        <i class="mdi mdi-plus me-1"></i>Attach Review Type
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($attachedReviewTypes && $attachedReviewTypes->count() > 0)
                    <div class="accordion" id="reviewTypesAccordion">
                        @foreach($attachedReviewTypes as $index => $reviewType)
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="heading{{ $index }}">
                                    <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div class="d-flex align-items-center">
                                                <strong>{{ $reviewType->name }}</strong>
                                                @if($reviewType->isMaster)
                                                    <span class="badge bg-success ms-2">{{ $reviewType->locationName }}</span>
                                                    <span class="badge bg-primary ms-2">Master</span>
                                                @else
                                                    <span class="badge bg-info ms-2">{{ $reviewType->locationName }}</span>
                                                    <span class="badge bg-secondary ms-2">Duplicate #{{ $reviewType->duplicateNumber }}</span>
                                                @endif>
                                                <span class="badge bg-primary ms-2">{{ $reviewType->auditTemplates->count() }} Templates</span>

                                                <!-- Rename Location Button -->
                                                <button class="btn btn-outline-secondary btn-sm ms-2" type="button" onclick="renameLocation({{ $reviewType->attachmentId ?? 0 }}, '{{ $reviewType->locationName ?? '' }}')" title="Rename Location">
                                                    <i class="mdi mdi-pencil"></i> Rename
                                                </button>

                                                @php
                                                    // Selected location for this review type
                                                    $selectedAttachmentId = request('selected_attachment_' . $reviewType->id, $reviewType->attachmentId);
                                                    // Build export URLs (template + current)
                                                    $exportUrlTemplate = url('admin/audits/'.$audit->id.'/attachments/__ATTACH_ID__/export-booklet');
                                                    $exportUrlCurrent  = url('admin/audits/'.$audit->id.'/attachments/'.$selectedAttachmentId.'/export-booklet');
                                                @endphp

                                                <!-- Export Booklet (XLSX) -->
                                            
                                               

                                                <a href="{{ route('admin.attachments.export.booklet', [$audit->id, $selectedAttachmentId]) }}" 
                                                class="btn btn-sm btn-outline-success">
                                                    <i class="mdi mdi-download"></i> Export
                                                </a>

                                                <!-- Import Booklet (XLSX) -->
                                                <button class="btn btn-outline-primary btn-sm ms-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#importBookletModal-{{ $reviewType->id }}"
                                                        title="Import booklet (XLSX)">
                                                    <i class="mdi mdi-file-excel-box"></i> Import Booklet
                                                </button>

                                                <!-- Remove Duplicate Button - Only for Duplicates -->
                                                @if($reviewType->isDuplicate)
                                                    <button class="btn btn-outline-danger btn-sm ms-2" type="button" onclick="removeDuplicate({{ $reviewType->attachmentId }}, '{{ $reviewType->locationName }}')" title="Remove This Duplicate">
                                                        <i class="mdi mdi-delete"></i> Remove Duplicate
                                                    </button>
                                                @endif

                                                <!-- Sync Table Structure Button - Only for Masters -->
                                                @if($reviewType->isMaster)
                                                    <form method="POST" action="{{ route('admin.audits.sync-table-structures', ['audit' => $audit->id, 'reviewTypeId' => $reviewType->id]) }}" class="d-inline ms-2" onsubmit="return confirm('Are you sure you want to sync the table structures for this review type? This will overwrite audit-specific table questions with the default template structure.');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-info btn-sm" title="Sync Table Structure">
                                                            <i class="mdi mdi-sync"></i> Sync Table Structure
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Duplicate/Detach - Only for Masters -->
                                                @if($reviewType->isMaster)
                                                    <button class="btn btn-outline-success btn-sm ms-2" type="button" onclick="duplicateReviewType({{ $reviewType->id }})" title="Create Duplicate for Another Location">
                                                        <i class="mdi mdi-content-copy"></i> Duplicate for Location
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm ms-2" type="button" onclick="detachReviewType({{ $reviewType->id }})" title="Detach Review Type (removes all instances)">
                                                        <i class="mdi mdi-close"></i> Detach
                                                    </button>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="badge bg-info">
                                                    {{ $reviewType->auditTemplates->sum(fn($template) => $template->sections->count()) }} Sections
                                                </span>
                                                <span class="badge bg-success">
                                                    {{ $reviewType->auditTemplates->sum(fn($template) => $template->sections->sum(fn($section) => $section->questions->count())) }} Questions
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#reviewTypesAccordion">
                                    <div class="accordion-body">
                                        @php
                                            // Selected attachment ID for this review type (used in forms & modals)
                                            $selectedAttachmentId = request('selected_attachment_' . $reviewType->id, $reviewType->attachmentId);
                                        @endphp

                                        <!-- Location selector for grouped view -->
                                        @if($reviewType->isMaster && isset($reviewType->duplicates) && $reviewType->duplicates->count() > 0)
                                            <div class="mb-3">
                                                <label for="locationSelector{{ $reviewType->id }}" class="form-label fw-bold">
                                                    <i class="mdi mdi-map-marker"></i> Select Location:
                                                </label>
                                                <select class="form-select" id="locationSelector{{ $reviewType->id }}" onchange="switchLocation({{ $reviewType->id }}, this.value)">
                                                    <option value="{{ $reviewType->attachmentId }}" {{ $selectedAttachmentId == $reviewType->attachmentId ? 'selected' : '' }}>
                                                        {{ $reviewType->locationName }} (Master)
                                                    </option>
                                                    @foreach($reviewType->duplicates as $duplicate)
                                                        <option value="{{ $duplicate->attachmentId }}" {{ $selectedAttachmentId == $duplicate->attachmentId ? 'selected' : '' }}>
                                                            {{ $duplicate->locationName }} (Duplicate #{{ $duplicate->duplicateNumber }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Location-specific action buttons -->
                                            <div class="mb-3" id="locationActions{{ $reviewType->id }}">
                                                <!-- Master location actions -->
                                                <div class="location-actions {{ $selectedAttachmentId == $reviewType->attachmentId ? '' : 'd-none' }}" data-attachment-id="{{ $reviewType->attachmentId }}">
                                                    <button class="btn btn-sm btn-warning me-2" onclick="renameLocation({{ $reviewType->attachmentId }}, '{{ $reviewType->locationName }}')">
                                                        <i class="mdi mdi-pencil"></i> Rename Master
                                                    </button>
                                                </div>
                                                <!-- Duplicate location actions -->
                                                @foreach($reviewType->duplicates as $duplicate)
                                                    <div class="location-actions {{ $selectedAttachmentId == $duplicate->attachmentId ? '' : 'd-none' }}" data-attachment-id="{{ $duplicate->attachmentId }}">
                                                        <button class="btn btn-sm btn-warning me-2" onclick="renameLocation({{ $duplicate->attachmentId }}, '{{ $duplicate->locationName }}')">
                                                            <i class="mdi mdi-pencil"></i> Rename Duplicate
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="removeDuplicate({{ $duplicate->attachmentId }}, '{{ $duplicate->locationName }}')">
                                                            <i class="mdi mdi-delete"></i> Remove Duplicate
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @php $templateCount = $reviewType->auditTemplates->count(); @endphp
                                        @if($templateCount > 0)
                                            {{-- Render all templates, only one visible at a time --}}
                                            @foreach($reviewType->auditTemplates as $templateIndex => $template)
                                                <div class="template-panel" id="template-panel-{{ $reviewType->id }}-{{ $templateIndex }}" style="display: {{ (int)old('active_template_index', request('active_template_index', 0)) == $templateIndex ? 'block' : ($templateIndex == 0 ? 'block' : 'none') }};">
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
                                                                            @if($reviewType->isMaster)
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
                                                                            @else
                                                                                <div class="text-muted small">
                                                                                    <i class="mdi mdi-information me-1"></i>Structure managed by master
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        @if($section->description)
                                                                            <small class="text-muted">{{ $section->description }}</small>
                                                                        @endif
                                                                    </div>
                                                                    <div class="card-body">
                                                                        @foreach($section->questions as $questionIndex => $question)
                                                                            @php
                                                                                $existingResponse = $question->responses()
                                                                                    ->where('audit_id', $audit->id)
                                                                                    ->where('attachment_id', $selectedAttachmentId)
                                                                                    ->where('created_by', auth()->id())
                                                                                    ->first();

                                                                                // Normalize non-table answer for input value
                                                                                $existingValue = '';
                                                                                if ($existingResponse) {
                                                                                    $ans = $existingResponse->answer ?? '';
                                                                                    if (is_array($ans)) {
                                                                                        $existingValue = $ans['value'] ?? '';
                                                                                    } else {
                                                                                        $existingValue = $ans;
                                                                                    }
                                                                                }
                                                                            @endphp

                                                                            <form method="POST" action="{{ route('admin.review-types-crud.save-responses', $reviewType->id) }}" class="mb-4 template-response-form">
                                                                                @csrf
                                                                                <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                                                                                <input type="hidden" name="attachment_id" value="{{ $selectedAttachmentId }}">
                                                                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                                                                <input type="hidden" name="active_template_index" class="active-template-index-input" value="{{ old('active_template_index', request('active_template_index', 0)) }}">

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
                                                                                                </div>
                                                                                            @else
                                                                                                <div class="mb-2">
                                                                                                    <label class="form-label">Answer</label>
                                                                                                    @switch($question->response_type)
                                                                                                        @case('text')
                                                                                                            <input type="text" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingValue) }}">
                                                                                                            @break
                                                                                                        @case('textarea')
                                                                                                            <textarea name="answers[{{ $question->id }}][answer]" class="form-control" rows="3">{{ old('answers.' . $question->id . '.answer', $existingValue) }}</textarea>
                                                                                                            @break
                                                                                                        @case('number')
                                                                                                            <input type="number" step="any" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingValue) }}">
                                                                                                            @break
                                                                                                        @case('date')
                                                                                                            <input type="date" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingValue) }}">
                                                                                                            @break
                                                                                                        @case('yes_no')
                                                                                                            @php
                                                                                                                $yes = $question->options[0] ?? 'Yes';
                                                                                                                $no  = $question->options[1] ?? 'No';
                                                                                                            @endphp
                                                                                                            <select name="answers[{{ $question->id }}][answer]" class="form-select">
                                                                                                                <option value="">-- Select --</option>
                                                                                                                <option value="{{ $yes }}" {{ old('answers.' . $question->id . '.answer', $existingValue) == $yes ? 'selected' : '' }}>{{ $yes }}</option>
                                                                                                                <option value="{{ $no }}"  {{ old('answers.' . $question->id . '.answer', $existingValue) == $no ? 'selected' : '' }}>{{ $no }}</option>
                                                                                                            </select>
                                                                                                            @break
                                                                                                        @case('select')
                                                                                                            @if($question->options && is_array($question->options) && count($question->options) > 0)
                                                                                                                <select name="answers[{{ $question->id }}][answer]" class="form-select">
                                                                                                                    <option value="">-- Select --</option>
                                                                                                                    @foreach($question->options as $option)
                                                                                                                        <option value="{{ $option }}" {{ old('answers.' . $question->id . '.answer', $existingValue) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                                                                                    @endforeach
                                                                                                                </select>
                                                                                                            @else
                                                                                                                <input type="text" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingValue) }}" placeholder="No options defined">
                                                                                                            @endif
                                                                                                            @break
                                                                                                        @default
                                                                                                            <input type="text" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingValue) }}">
                                                                                                    @endswitch
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
                                                                                        @if($reviewType->isMaster)
                                                                                            <div class="btn-group btn-group-sm">
                                                                                                <button class="btn btn-outline-warning" type="button" onclick="editQuestion({{ $question->id }})">
                                                                                                    <i class="mdi mdi-pencil"></i>
                                                                                                </button>
                                                                                                <button class="btn btn-outline-danger" type="button" onclick="deleteQuestion({{ $question->id }})">
                                                                                                    <i class="mdi mdi-delete"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        @endif
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
                                                    <button class="btn btn-outline-secondary" type="button" id="prev-template-btn-{{ $reviewType->id }}" onclick="showPrevTemplate({{ $reviewType->id }}, {{ $templateCount }})">
                                                        &laquo; Previous Template
                                                    </button>
                                                    <button class="btn btn-outline-secondary" type="button" id="next-template-btn-{{ $reviewType->id }}" onclick="showNextTemplate({{ $reviewType->id }}, {{ $templateCount }})">
                                                        Next Template &raquo;
                                                    </button>
                                                </div>
                                            @endif
                                        @endif

                                        <!-- END Import modal -->

                                        <!-- Danger Zone -->
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
                                    </div>
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
        // Use selected attachment ID for table modals too
        $selectedAttachmentId = request('selected_attachment_' . $modalReviewType->id, $modalReviewType->attachmentId);
        $existingResponse = $modalQuestion->responses()
            ->where('audit_id', $audit->id)
            ->where('attachment_id', $selectedAttachmentId)
            ->where('created_by', auth()->id())
            ->first();

        // Always show the saved table if available, otherwise fall back to default
        $tableToShow = [];
        if ($existingResponse && $existingResponse->answer) {
            $tableToShow = is_array($existingResponse->answer) ? $existingResponse->answer : json_decode($existingResponse->answer, true);
        } else {
            $tableToShow = $rows;
        }
        $colCount = 2;
        if (is_array($tableToShow) && count($tableToShow) && is_array(reset($tableToShow)) && count(reset($tableToShow)) > $colCount) {
            $colCount = count(reset($tableToShow));
        }
        // Header rows
        $headerRows = $options['header_rows'] ?? 1;
        if ($existingResponse && isset($existingResponse->answer_header_rows)) {
            $headerRows = $existingResponse->answer_header_rows;
        }
    @endphp
    <div class="modal fade" id="tableModal-{{ $modalQuestion->id }}" tabindex="-1" aria-labelledby="tableModalLabel-{{ $modalQuestion->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="tableModalLabel-{{ $modalQuestion->id }}">
                        Table Question: {{ $modalQuestion->question_text }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('admin.review-types-crud.save-responses', $modalReviewType->id) }}">
                        @csrf
                        <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                        <input type="hidden" name="attachment_id" value="{{ $selectedAttachmentId }}">
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}#tableModal-{{ $modalQuestion->id }}">
                        <div class="mb-3">
                            <label for="headerRows-{{ $modalQuestion->id }}" class="form-label fw-semibold">Number of Header Rows</label>
                            <input type="number" min="1" class="form-control form-control-sm header-rows-input" name="answers[{{ $modalQuestion->id }}][header_rows]" id="headerRows-{{ $modalQuestion->id }}" value="{{ $headerRows }}">
                            <small class="text-muted">Set how many rows at the top are table headers.</small>
                        </div>
                        <div class="table-responsive" style="background-color: #ffffff;">
                            <table class="table table-bordered table-hover table-editable" id="editableTable-{{ $modalQuestion->id }}" style="background-color: #ffffff !important;">
                                <tbody>
                                @if(is_array($tableToShow) && count($tableToShow))
                                    @foreach($tableToShow as $r => $row)
                                        <tr>
                                            @foreach($row as $c => $cell)
                                                @if($r < $headerRows)
                                                    <th style="background-color: #f8f9fa; vertical-align: middle;">
                                                        <input type="text"
                                                            class="form-control form-control-sm fw-bold text-center border-0"
                                                            style="background: transparent; box-shadow: none;"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $c }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $c, $tableToShow[$r][$c] ?? '') }}"
                                                            placeholder="Header {{ $c+1 }}">
                                                    </th>
                                                @else
                                                    <td style="vertical-align: middle;">
                                                        <input type="text"
                                                            class="form-control form-control-sm border-0"
                                                            style="background: transparent; box-shadow: none;"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $c }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $c, $tableToShow[$r][$c] ?? '') }}">
                                                    </td>
                                                @endif
                                            @endforeach
                                            {{-- Pad to colCount if needed --}}
                                            @if($r < $headerRows && count($row) < $colCount)
                                                @for($i = count($row); $i < $colCount; $i++)
                                                    <th style="background-color: #f8f9fa; vertical-align: middle;">
                                                        <input type="text"
                                                            class="form-control form-control-sm fw-bold text-center border-0"
                                                            style="background: transparent; box-shadow: none;"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $i }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $i, $tableToShow[$r][$i] ?? '') }}"
                                                            placeholder="Header {{ $i+1 }}">
                                                    </th>
                                                @endfor
                                            @elseif($r >= $headerRows && count($row) < $colCount)
                                                @for($i = count($row); $i < $colCount; $i++)
                                                    <td style="vertical-align: middle;">
                                                        <input type="text"
                                                            class="form-control form-control-sm border-0"
                                                            style="background: transparent; box-shadow: none;"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $i }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $i, $tableToShow[$r][$i] ?? '') }}">
                                                    </td>
                                                @endfor
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th style="background-color: #f8f9fa; vertical-align: middle;">
                                            <input type="text"
                                                class="form-control form-control-sm fw-bold text-center border-0"
                                                style="background: transparent; box-shadow: none;"
                                                name="answers[{{ $modalQuestion->id }}][table][0][0]"
                                                placeholder="Header 1">
                                        </th>
                                        <th style="background-color: #f8f9fa; vertical-align: middle;">
                                            <input type="text"
                                                class="form-control form-control-sm fw-bold text-center border-0"
                                                style="background: transparent; box-shadow: none;"
                                                name="answers[{{ $modalQuestion->id }}][table][0][1]"
                                                placeholder="Header 2">
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <input type="text"
                                                class="form-control form-control-sm border-0"
                                                style="background: transparent; box-shadow: none;"
                                                name="answers[{{ $modalQuestion->id }}][table][1][0]">
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <input type="text"
                                                class="form-control form-control-sm border-0"
                                                style="background: transparent; box-shadow: none;"
                                                name="answers[{{ $modalQuestion->id }}][table][1][1]">
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="table-action-buttons">
                            <div class="d-flex gap-2 flex-wrap justify-content-center">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow({{ $modalQuestion->id }})">
                                    <i class="mdi mdi-plus"></i> Add Row
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addColumn({{ $modalQuestion->id }})">
                                    <i class="mdi mdi-plus"></i> Add Column
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteRow({{ $modalQuestion->id }})">
                                    <i class="mdi mdi-minus"></i> Delete Row
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteColumn({{ $modalQuestion->id }})">
                                    <i class="mdi mdi-minus"></i> Delete Column
                                </button>
                            </div>
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

<!-- Global Import Helper Modal -->
<div class="modal fade" id="importBookletModal-global" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Booklet (XLSX)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">To import responses, open a Review Type below and click its “Import Booklet” button so we can apply the data to the correct location.</p>
            </div>
        </div>
    </div>
</div>

<!-- modals -->
@include('admin.audit-management.audits.modals')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        new bootstrap.Modal(modal);
    });

    // Global variables for location switching
    window.currentLocations = @json($attachedReviewTypes->mapWithKeys(function($reviewType) {
        return [$reviewType->id => $reviewType->attachmentId];
    }));

    // Function to switch location and reload responses
    window.switchLocation = function(reviewTypeId, attachmentId) {
        // Update current location
        window.currentLocations[reviewTypeId] = attachmentId;

        // Show/hide appropriate action buttons
        const actionsContainer = document.getElementById(`locationActions${reviewTypeId}`);
        if (actionsContainer) {
            const allActions = actionsContainer.querySelectorAll('.location-actions');
            allActions.forEach(action => {
                if (action.dataset.attachmentId === attachmentId) {
                    action.classList.remove('d-none');
                } else {
                    action.classList.add('d-none');
                }
            });
        }

        // Update Export Booklet link dynamically before reload (nice UX)
        const btn = document.getElementById(`export-booklet-btn-${reviewTypeId}`);
        if (btn) {
            const tmpl = btn.getAttribute('data-url-template'); // .../__ATTACH_ID__/export-booklet
            if (tmpl) {
                btn.href = tmpl.replace('__ATTACH_ID__', attachmentId);
            }
        }

        // Reload the page with the selected location to show correct responses
        const url = new URL(window.location);
        url.searchParams.set('selected_attachment_' + reviewTypeId, attachmentId);
        window.location.href = url.toString();
    };

    window.showPrevTemplate = function(reviewTypeId, templateCount) {
        let current = getActiveTemplateIndex(reviewTypeId, templateCount);
        let prev = (current - 1 + templateCount) % templateCount;
        setActiveTemplate(reviewTypeId, prev, templateCount);
    };
    window.showNextTemplate = function(reviewTypeId, templateCount) {
        let current = getActiveTemplateIndex(reviewTypeId, templateCount);
        let next = (current + 1) % templateCount;
        setActiveTemplate(reviewTypeId, next, templateCount);
    };
    function getActiveTemplateIndex(reviewTypeId, templateCount) {
        let panels = document.querySelectorAll(`[id^='template-panel-${reviewTypeId}-']`);
        for (let i = 0; i < panels.length; i++) {
            if (panels[i].style.display !== 'none') return i;
        }
        return 0;
    }
    function setActiveTemplate(reviewTypeId, index, templateCount) {
        for (let i = 0; i < templateCount; i++) {
            let panel = document.getElementById(`template-panel-${reviewTypeId}-${i}`);
            if (panel) panel.style.display = (i === index) ? 'block' : 'none';
        }
        let forms = document.querySelectorAll(`#collapse${reviewTypeId} form.template-response-form`);
        forms.forEach(form => {
            let input = form.querySelector('input.active-template-index-input');
            if (input) input.value = index;
        });
    }
    document.querySelectorAll('.template-response-form').forEach(form => {
        let input = form.querySelector('input.active-template-index-input');
        if (input) {
            let reviewTypeId = form.closest('.accordion-collapse').id.replace('collapse', '');
            let index = parseInt(input.value || '0', 10);
            let templateCount = document.querySelectorAll(`[id^='template-panel-${reviewTypeId}-']`).length;
            setActiveTemplate(reviewTypeId, index, templateCount);
        }
    });
});
</script>

<!-- scripts -->
@include('admin.audit-management.audits.scripts')

@endsection