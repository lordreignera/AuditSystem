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
                            {{-- Auditors can duplicate templates but not preview --}}
                            @unless(auth()->user()->hasRole('Auditor'))
                                <button class="btn btn-outline-primary btn-sm me-2" type="button" onclick="previewTemplate({{ $template->id }})">
                                    <i class="mdi mdi-eye"></i> Preview
                                </button>
                            @endunless
                            
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
                                    @unless(auth()->user()->hasRole('Auditor'))
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
                                    @endunless
                                </div>
                                @if($section->description)
                                    <small class="text-muted">{{ $section->description }}</small>
                                @endif
                            </div>
                            <div class="card-body">
                                @foreach($section->questions as $questionIndex => $question)
        
                                    <form method="POST" action="{{ route('admin.review-types-crud.save-responses', $reviewType->id) }}" class="mb-4 template-response-form">
                                        @csrf
                                        <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                                        <input type="hidden" name="attachment_id" value="{{ $reviewType->attachmentId }}">
                                        <input type="hidden" name="redirect_to" value="{{ route('admin.audits.dashboard', $audit) }}">
                                        <input type="hidden" name="active_template_index" class="active-template-index-input" value="0">
                                        @php
                                            $existingResponse = $question->responses()
                                                ->where('audit_id', $audit->id)
                                                ->where('attachment_id', $reviewType->attachmentId)
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
                                                        </div>
                                                    @else
                                                        <div class="mb-2">
                                                            <label class="form-label">Answer</label>
                                                            @switch($question->response_type)
                                                                @case('text')
                                                                    <input type="text" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') }}">
                                                                    @break
                                                                
                                                                @case('textarea')
                                                                    <textarea name="answers[{{ $question->id }}][answer]" class="form-control" rows="3">{{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') }}</textarea>
                                                                    @break
                                                                
                                                                @case('number')
                                                                    <input type="number" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') }}">
                                                                    @break
                                                                
                                                                @case('date')
                                                                    <input type="date" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') }}">
                                                                    @break
                                                                
                                                                @case('yes_no')
                                                                    <select name="answers[{{ $question->id }}][answer]" class="form-select">
                                                                        <option value="">-- Select --</option>
                                                                        @if($question->options && is_array($question->options) && count($question->options) >= 2)
                                                                            <option value="{{ $question->options[0] }}" {{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') == $question->options[0] ? 'selected' : '' }}>{{ $question->options[0] }}</option>
                                                                            <option value="{{ $question->options[1] }}" {{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') == $question->options[1] ? 'selected' : '' }}>{{ $question->options[1] }}</option>
                                                                        @else
                                                                            <option value="Yes" {{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                                            <option value="No" {{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') == 'No' ? 'selected' : '' }}>No</option>
                                                                        @endif
                                                                    </select>
                                                                    @break
                                                                
                                                                @case('select')
                                                                    @if($question->options && is_array($question->options) && count($question->options) > 0)
                                                                        <select name="answers[{{ $question->id }}][answer]" class="form-select">
                                                                            <option value="">-- Select --</option>
                                                                            @foreach($question->options as $option)
                                                                                <option value="{{ $option }}" {{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    @else
                                                                        <input type="text" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') }}" placeholder="No options defined">
                                                                    @endif
                                                                    @break
                                                                
                                                                @default
                                                                    <input type="text" name="answers[{{ $question->id }}][answer]" class="form-control" value="{{ old('answers.' . $question->id . '.answer', $existingResponse->answer ?? '') }}">
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
                                                @unless(auth()->user()->hasRole('Auditor'))
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
                                                @endunless
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
@else
    <div class="alert alert-warning text-center">
        <i class="mdi mdi-alert-circle-outline fa-2x mb-3"></i>
        <h5>No Templates Found</h5>
        <p class="mb-0">No templates are available for this review type and location.</p>
    </div>
@endif

{{-- Render table modals for this specific attachment --}}
@php
$sectionTableQuestions = [];
if($reviewType->auditTemplates && $reviewType->auditTemplates->count() > 0) {
    foreach($reviewType->auditTemplates as $template) {
        foreach($template->sections as $section) {
            foreach($section->questions as $question) {
                if($question->response_type === 'table') {
                    $sectionTableQuestions[] = $question;
                }
            }
        }
    }
}
@endphp

@foreach($sectionTableQuestions as $modalQuestion)
    @php
        $options = is_string($modalQuestion->options) ? json_decode($modalQuestion->options, true) : $modalQuestion->options;
        $rows = $options['rows'] ?? [];
        $existingResponse = $modalQuestion->responses()
            ->where('audit_id', $audit->id)
            ->where('attachment_id', $reviewType->attachmentId)
            ->where('created_by', auth()->id())
            ->first();
        // Always show the audit-specific saved table (including headers) if available, otherwise fall back to default
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
                    <form method="POST" action="{{ route('admin.review-types-crud.save-responses', $reviewType->id) }}">
                        @csrf
                        <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                        <input type="hidden" name="attachment_id" value="{{ $reviewType->attachmentId }}">
                        <input type="hidden" name="redirect_to" value="{{ route('admin.audits.dashboard', $audit) }}#tableModal-{{ $modalQuestion->id }}">
                        <div class="mb-3">
                            <label for="headerRows-{{ $modalQuestion->id }}" class="form-label">Number of Header Rows</label>
                            <input type="number" min="1" max="{{ count($rows) }}" class="form-control form-control-sm w-auto d-inline-block" style="width: 80px;" name="answers[{{ $modalQuestion->id }}][header_rows]" id="headerRows-{{ $modalQuestion->id }}" value="{{ $options['header_rows'] ?? 1 }}">
                            <small class="text-muted ms-2">Set how many rows at the top are table headers.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="editableTable-{{ $modalQuestion->id }}">
                                <tbody>
                                @php
                                    // Use header_rows from saved response if available, else from options
                                    $headerRows = $options['header_rows'] ?? 1;
                                    if ($existingResponse && isset($existingResponse->answer)) {
                                        $saved = is_array($existingResponse->answer) ? $existingResponse->answer : json_decode($existingResponse->answer, true);
                                        if (isset($existingResponse->answer_header_rows)) {
                                            $headerRows = $existingResponse->answer_header_rows;
                                        } elseif (isset($options['header_rows'])) {
                                            $headerRows = $options['header_rows'];
                                        }
                                    }
                                @endphp
                                @if(count($tableToShow))
                                    @foreach($tableToShow as $r => $row)
                                        <tr>
                                            @foreach($row as $c => $cell)
                                                @if($r < $headerRows)
                                                    <th>
                                                        <input type="text"
                                                            class="form-control form-control-sm fw-bold text-center"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $c }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $c, $tableToShow[$r][$c] ?? '') }}"
                                                            placeholder="Header {{ $c+1 }}">
                                                    </th>
                                                @else
                                                    <td>
                                                        <input type="text"
                                                            class="form-control"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $c }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $c, $tableToShow[$r][$c] ?? '') }}">
                                                    </td>
                                                @endif
                                            @endforeach
                                            @if($r < $headerRows && count($row) < $colCount)
                                                @for($i = count($row); $i < $colCount; $i++)
                                                    <th>Header {{ $i+1 }}</th>
                                                @endfor
                                            @elseif($r >= $headerRows && count($row) < $colCount)
                                                @for($i = count($row); $i < $colCount; $i++)
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $i }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $i, $tableToShow[$r][$i] ?? '') }}">
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
