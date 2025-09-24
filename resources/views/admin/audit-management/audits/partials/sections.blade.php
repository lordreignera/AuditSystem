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
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
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
                    <h5 class="modal-title text-dark" style="background-color: white" id="tableModalLabel-{{ $modalQuestion->id }}">
                        Table Question: {{ $modalQuestion->question_text }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-white">
                    <form method="POST" action="{{ route('admin.review-types-crud.save-responses', $reviewType->id) }}" onsubmit="saveColumnWidthsSections('editableTable-{{ $modalQuestion->id }}')">
                        @csrf
                        <input type="hidden" name="audit_id" value="{{ $audit->id }}">
                        <input type="hidden" name="attachment_id" value="{{ $reviewType->attachmentId }}">
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}#tableModal-{{ $modalQuestion->id }}">
                        <input type="hidden" name="answers[{{ $modalQuestion->id }}][column_widths]" id="columnWidthsSections-{{ $modalQuestion->id }}" value="">
                        <div class="mb-3">
                            <label for="headerRows-{{ $modalQuestion->id }}" class="form-label">Number of Header Rows</label>
                            <input type="number" min="1" max="{{ count($rows) }}" class="form-control form-control-sm w-auto d-inline-block" style="width: 80px;" name="answers[{{ $modalQuestion->id }}][header_rows]" id="headerRows-{{ $modalQuestion->id }}" value="{{ $options['header_rows'] ?? 1 }}">
                            <small class="text-muted ms-2">Set how many rows at the top are table headers.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered resizable-table" id="editableTable-{{ $modalQuestion->id }}">
                                <thead>
                                    <tr class="header-row">
                                        @php
                                            $colCount = max(1, collect($tableToShow)->max(function($row) { return count($row); }));
                                            if ($colCount < 2) $colCount = 2; // minimum 2 columns
                                        @endphp
                                        @for($c = 0; $c < $colCount; $c++)
                                            <th class="resizable-th" style="position: relative; min-width: 120px;">
                                                @if(isset($tableToShow[0][$c]))
                                                    {{ $tableToShow[0][$c] }}
                                                @else
                                                    Header {{ $c + 1 }}
                                                @endif
                                                <div class="column-resizer"></div>
                                            </th>
                                        @endfor
                                    </tr>
                                </thead>
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
                                        @if($r >= $headerRows)
                                            <tr>
                                                @foreach($row as $c => $cell)
                                                    <td>
                                                        <input type="text"
                                                            class="form-control"
                                                            name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $c }}]"
                                                            value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $c, $tableToShow[$r][$c] ?? '') }}">
                                                    </td>
                                                @endforeach
                                                @if(count($row) < $colCount)
                                                    @for($i = count($row); $i < $colCount; $i++)
                                                        <td>
                                                            <input type="text" class="form-control"
                                                                name="answers[{{ $modalQuestion->id }}][table][{{ $r }}][{{ $i }}]"
                                                                value="{{ old('answers.' . $modalQuestion->id . '.table.' . $r . '.' . $i, $tableToShow[$r][$i] ?? '') }}">
                                                        </td>
                                                    @endfor
                                                @endif
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
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
                            <button type="button" class="btn btn-info btn-sm"
                                onclick="resetColumnWidthsSections('editableTable-{{ $modalQuestion->id }}')" 
                                title="Reset all columns to equal width">
                                <i class="mdi mdi-table-column-width"></i> Reset Columns</button>
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

@push('scripts')
<style>
/* Resizable Table Styles */
.resizable-table {
    table-layout: fixed;
    width: 100%;
}

.resizable-th {
    position: relative;
    overflow: hidden;
    padding-right: 20px !important;
    min-width: 100px !important; /* Increased minimum width */
}

.column-resizer {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 12px; /* Increased width for easier grabbing */
    cursor: col-resize;
    background: transparent;
    border-right: 3px solid #dee2e6;
    transition: border-color 0.2s;
    z-index: 10;
}

.column-resizer:hover {
    border-right-color: #007bff;
    background: rgba(0, 123, 255, 0.15);
    width: 15px; /* Wider on hover for easier targeting */
}

.column-resizer.resizing {
    border-right-color: #007bff;
    background: rgba(0, 123, 255, 0.25);
    width: 15px;
}

.resizable-table th input,
.resizable-table td input {
    width: 100%;
    border: none;
    padding: 8px;
    background: transparent;
    margin: 0;
    min-width: 0; /* Allow inputs to shrink */
    box-sizing: border-box;
}
    width: 100%;
    border: none;
    background: transparent;
    padding: 8px 5px;
}

.resizable-table th input {
    font-weight: bold;
    text-align: center;
    background: rgba(0, 123, 255, 0.1);
}

.resizable-table th input:focus,
.resizable-table td input:focus {
    outline: 2px solid #007bff;
    outline-offset: -2px;
    background: #fff;
}

/* Better table appearance */
.resizable-table th {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    font-weight: 600;
}

.resizable-table td {
    border: 1px solid #dee2e6;
    padding: 0;
}

/* Header editing */
.header-edit-row th {
    background: rgba(0, 123, 255, 0.05);
}

.table-controls {
    margin: 10px 0;
    display: flex;
    gap: 10px;
    align-items: center;
}

.header-toggle {
    margin-left: auto;
}
</style>

<script>
// Column Width Persistence Functions for Sections
function saveColumnWidthsSections(tableId) {
    try {
        let table = document.getElementById(tableId);
        if (!table) return;
        
        let columnWidths = [];
        let headerCells = table.querySelectorAll('thead th');
        
        headerCells.forEach(function(cell, index) {
            let width = cell.style.width || cell.offsetWidth + 'px';
            columnWidths.push(width);
        });
        
        // Save to hidden input
        let hiddenInput = document.getElementById('columnWidthsSections-' + tableId.split('-')[1]);
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(columnWidths);
        }
        
        // Also save to localStorage for backup
        localStorage.setItem('columnWidthsSections-' + tableId, JSON.stringify(columnWidths));
    } catch (error) {
        console.log('Error saving column widths:', error);
    }
}

function restoreColumnWidthsSections(tableId) {
    try {
        let table = document.getElementById(tableId);
        if (!table) return;
        
        // First try to get from server-side data (form submission)
        let questionId = tableId.split('-')[1];
        let savedWidths = null;
        
        // Check if we have server-side saved widths
        let serverData = window.savedColumnWidthsSections && window.savedColumnWidthsSections[questionId];
        if (serverData) {
            savedWidths = JSON.parse(serverData);
        } else {
            // Fallback to localStorage
            let storedWidths = localStorage.getItem('columnWidthsSections-' + tableId);
            if (storedWidths) {
                savedWidths = JSON.parse(storedWidths);
            }
        }
        
        if (savedWidths && savedWidths.length > 0) {
            let headerCells = table.querySelectorAll('thead th');
            
            headerCells.forEach(function(cell, index) {
                if (savedWidths[index]) {
                    cell.style.width = savedWidths[index];
                }
            });
            
            // Update total table width
            updateTableWidthSectionsIndependent(tableId);
        }
    } catch (error) {
        console.log('Error restoring column widths:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all resizable tables that are already visible
    document.querySelectorAll('.resizable-table').forEach(function(table) {
        initResizableColumns(table);
        addHeaderEditingCapability(table);
        restoreColumnWidthsSections(table.id);
    });
    
    // Listen for Bootstrap modal show events to initialize resizable columns in modals
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.target;
        const table = modal.querySelector('.resizable-table');
        if (table) {
            initResizableColumns(table);
            addHeaderEditingCapability(table);
            restoreColumnWidthsSections(table.id);
        }
    });
});

function initResizableColumns(table) {
    const resizers = table.querySelectorAll('.column-resizer');
    let isResizing = false;
    let currentResizer = null;
    let startX = 0;
    let startWidth = 0;

    resizers.forEach(function(resizer) {
        resizer.addEventListener('mousedown', function(e) {
            isResizing = true;
            currentResizer = resizer;
            startX = e.clientX;
            
            const th = resizer.closest('th');
            startWidth = parseInt(document.defaultView.getComputedStyle(th).width, 10);
            
            resizer.classList.add('resizing');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            
            e.preventDefault();
        });
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;
        
        const th = currentResizer.closest('th');
        const width = startWidth + e.clientX - startX;
        
        // Enhanced minimum and maximum width constraints
        const minWidth = 100; // Increased minimum width
        const maxWidth = 600; // Increased maximum width for full content display
        const constrainedWidth = Math.max(minWidth, Math.min(maxWidth, width));
        
        if (constrainedWidth !== parseInt(th.style.width, 10)) {
            // Set the specific column width without affecting others
            th.style.width = constrainedWidth + 'px';
            th.style.minWidth = constrainedWidth + 'px';
            th.style.maxWidth = constrainedWidth + 'px';
            
            // Update all cells in this specific column only
            const columnIndex = Array.from(th.parentNode.children).indexOf(th);
            const allRows = table.querySelectorAll('tr');
            
            allRows.forEach(function(row) {
                if (row.children[columnIndex]) {
                    const cell = row.children[columnIndex];
                    cell.style.width = constrainedWidth + 'px';
                    cell.style.minWidth = constrainedWidth + 'px';
                    cell.style.maxWidth = constrainedWidth + 'px';
                }
            });
            
            // Update table width to accommodate all columns independently
            updateTableWidthSectionsIndependent(table);
        }
    });

    document.addEventListener('mouseup', function() {
        if (!isResizing) return;
        
        isResizing = false;
        if (currentResizer) {
            currentResizer.classList.remove('resizing');
        }
        currentResizer = null;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    });
}

    document.addEventListener('mouseup', function() {
        if (!isResizing) return;
        
        isResizing = false;
        if (currentResizer) {
            currentResizer.classList.remove('resizing');
        }
        currentResizer = null;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    });
}

function addHeaderEditingCapability(table) {
    const tableId = table.id;
    const questionId = tableId.replace('editableTable-', '');
    
    // Add header editing capability
    const headerRow = table.querySelector('.header-row');
    if (headerRow) {
        // Make headers editable
        const headers = headerRow.querySelectorAll('th');
        headers.forEach(function(th, index) {
            const currentText = th.textContent.replace('Header ' + (index + 1), '').trim();
            const resizer = th.querySelector('.column-resizer');
            
            th.innerHTML = `
                <input type="text" 
                       class="form-control form-control-sm fw-bold text-center header-input" 
                       name="answers[${questionId}][table][0][${index}]" 
                       value="${currentText || 'Header ' + (index + 1)}" 
                       placeholder="Header ${index + 1}">
                <div class="column-resizer"></div>
            `;
        });
        
        // Re-initialize resizers after adding inputs
        initResizableColumns(table);
    }
}

// Enhanced table manipulation functions
    });
}

// Function to update table width based on sum of all column widths (independent resizing)
function updateTableWidthSectionsIndependent(table) {
    const headerRow = table.querySelector('thead tr');
    if (headerRow) {
        let totalWidth = 0;
        Array.from(headerRow.children).forEach(function(th) {
            const colWidth = parseInt(th.style.width || th.offsetWidth, 10);
            totalWidth += colWidth;
        });
        
        // Set table width to sum of all columns to maintain independence
        table.style.width = totalWidth + 'px';
        table.style.minWidth = totalWidth + 'px';
        
        // Ensure table container can scroll horizontally if needed
        const container = table.closest('.table-responsive');
        if (container) {
            container.style.overflowX = 'auto';
        }
    }
}

// Function to update table width based on column widths
function updateTableWidthSections(table) {
    // Use the independent version for better control
    updateTableWidthSectionsIndependent(table);
}

// Function to reset all columns to default width
function resetColumnWidthsSections(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const headerCells = table.querySelectorAll('thead th');
    const containerWidth = table.closest('.table-responsive').offsetWidth;
    const defaultWidth = Math.max(120, Math.floor(containerWidth / headerCells.length) - 10);
    
    headerCells.forEach(function(th, index) {
        th.style.width = defaultWidth + 'px';
        th.style.minWidth = defaultWidth + 'px';
        th.style.maxWidth = defaultWidth + 'px';
        
        // Update all cells in this column
        const allRows = table.querySelectorAll('tr');
        allRows.forEach(function(row) {
            if (row.children[index]) {
                const cell = row.children[index];
                cell.style.width = defaultWidth + 'px';
                cell.style.minWidth = defaultWidth + 'px';
                cell.style.maxWidth = defaultWidth + 'px';
            }
        });
    });
    
    // Reset table width to sum of columns
    updateTableWidthSectionsIndependent(table);
}

function addRow(questionId) {
    const table = document.getElementById('editableTable-' + questionId);
    const tbody = table.querySelector('tbody');
    const lastRow = tbody.querySelector('tr:last-child');
    const columnCount = lastRow ? lastRow.children.length : 2;
    
    const newRow = document.createElement('tr');
    const currentRowIndex = tbody.children.length + 1; // +1 for header row
    
    for (let i = 0; i < columnCount; i++) {
        const cell = document.createElement('td');
        cell.innerHTML = `<input type="text" class="form-control" name="answers[${questionId}][table][${currentRowIndex}][${i}]">`;
        newRow.appendChild(cell);
    }
    
    tbody.appendChild(newRow);
}

function addColumn(questionId) {
    const table = document.getElementById('editableTable-' + questionId);
    const headerRow = table.querySelector('.header-row');
    const rows = table.querySelectorAll('tr');
    const newColumnIndex = headerRow.children.length;
    
    // Add header
    const newHeader = document.createElement('th');
    newHeader.className = 'resizable-th';
    newHeader.style.position = 'relative';
    newHeader.style.minWidth = '120px';
    newHeader.innerHTML = `
        <input type="text" 
               class="form-control form-control-sm fw-bold text-center header-input" 
               name="answers[${questionId}][table][0][${newColumnIndex}]" 
               value="Header ${newColumnIndex + 1}" 
               placeholder="Header ${newColumnIndex + 1}">
        <div class="column-resizer"></div>
    `;
    headerRow.appendChild(newHeader);
    
    // Add cells to data rows
    const dataRows = table.querySelectorAll('tbody tr');
    dataRows.forEach(function(row, rowIndex) {
        const newCell = document.createElement('td');
        newCell.innerHTML = `<input type="text" class="form-control" name="answers[${questionId}][table][${rowIndex + 1}][${newColumnIndex}]">`;
        row.appendChild(newCell);
    });
    
    // Re-initialize resizers
    initResizableColumns(table);
}

function deleteRow(questionId) {
    const table = document.getElementById('editableTable-' + questionId);
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    if (rows.length > 1) {
        tbody.removeChild(rows[rows.length - 1]);
    } else {
        alert('At least one data row is required.');
    }
}

function deleteColumn(questionId) {
    const table = document.getElementById('editableTable-' + questionId);
    const headerRow = table.querySelector('.header-row');
    const allRows = table.querySelectorAll('tr');
    
    if (headerRow.children.length > 2) {
        const lastColumnIndex = headerRow.children.length - 1;
        
        allRows.forEach(function(row) {
            if (row.children[lastColumnIndex]) {
                row.removeChild(row.children[lastColumnIndex]);
            }
        });
    } else {
        alert('At least two columns are required.');
    }
}
</script>
@endpush
