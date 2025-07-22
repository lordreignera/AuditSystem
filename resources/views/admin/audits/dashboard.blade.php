@extends('admin.admin_layout')

@section('title', $reviewType->name . ' - Template Details')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end flex-wrap">
                <div class="me-md-3 me-xl-5">
                    <h2>{{ $reviewType->name }}</h2>
                    <p class="mb-md-0">{{ $reviewType->description }}</p>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <a href="{{ route('admin.review-types-crud.index') }}" 
                   class="btn btn-outline-primary mb-3 mb-md-0">
                    <i class="mdi mdi-arrow-left mr-1"></i>
                    Back to All Review Types
                </a>
            </div>
        </div>
    </div>
</div>

@if($reviewType->templates->isEmpty())
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <i class="mdi mdi-file-document-outline text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">No templates available</h4>
                        <p class="text-muted">This review type doesn't have any default templates yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Templates -->
    @foreach($reviewType->templates as $template)
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <!-- Template Header -->
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 text-white">{{ $template->name }}</h4>
                                <p class="mb-0 text-white-50">{{ $template->description }}</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="text-right mr-3">
                                    <small class="text-white-50 d-block">Sections: {{ $template->sections->count() }}</small>
                                    <small class="text-white-50 d-block">Questions: {{ $template->sections->sum(function($s) { return $s->questions->count(); }) }}</small>
                                </div>
                                <a href="{{ route('admin.review-types-crud.create-audit', [$reviewType, $template]) }}" 
                                   class="btn btn-light btn-sm">
                                    <i class="mdi mdi-plus mr-1"></i>
                                    Create Audit
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Sections and Questions -->
                    <div class="card-body">
                        @foreach($template->sections as $section)
                            <div class="border rounded p-3 mb-3">
                                <!-- Section Header -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-1">
                                            <span class="badge badge-primary mr-2">{{ $section->order }}</span>
                                            {{ $section->name }}
                                        </h5>
                                        @if($section->description)
                                            <p class="text-muted mb-0">{{ $section->description }}</p>
                                        @endif
                                    </div>
                                    <span class="badge badge-secondary">{{ $section->questions->count() }} questions</span>
                                </div>

                                @if($section->questions->isEmpty())
                                    <div class="text-center py-3 text-muted">
                                        <em>No questions in this section</em>
                                    </div>
                                @else
                                    <!-- Questions -->
                                    <div class="ml-3">
                                        @foreach($section->questions as $question)
                                            <div class="border rounded p-3 mb-3 bg-light">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0 mr-3">
                                                        <span class="badge badge-outline-primary">Q{{ $question->order }}</span>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-2 font-weight-medium">{{ $question->question_text }}</p>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge badge-info mr-2">{{ ucfirst($question->response_type) }}</span>
                                                            @if($question->is_required)
                                                                <span class="badge badge-danger mr-2">Required</span>
                                                            @endif
                                                            @if($question->response_type === 'table')
                                                                <button type="button" class="btn btn-outline-info btn-sm mr-2" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#tableModal{{ $question->id }}">
                                                                    <i class="mdi mdi-table"></i> View Table
                                                                </button>
                                                            @endif
                                                            @php
                                                            $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                                                            @endphp
                                                            @if($options && is_array($options) && $question->response_type !== 'table')
                                                                <small class="text-muted">Options: {{ implode(', ', $options) }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- Table Structure Modals -->
@foreach($reviewType->templates as $template)
    @foreach($template->sections as $section)
        @foreach($section->questions as $question)
            @if($question->response_type === 'table')
                <!-- Modal for Question {{ $question->id }} -->
                <div class="modal fade" id="tableModal{{ $question->id }}" tabindex="-1" 
                     aria-labelledby="tableModalLabel{{ $question->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content bg-white">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title text-dark" id="tableModalLabel{{ $question->id }}">
                                    Table Structure: {{ $question->question_text }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body bg-white">
                                <div class="mb-3">
                                    <strong class="text-dark">Template:</strong> <span class="text-dark">{{ $template->name }}</span> | 
                                    <strong class="text-dark">Section:</strong> <span class="text-dark">{{ $section->name }}</span>
                                </div>
                                <div class="table-responsive">
                                    @php
                                        $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                                        $rows = $options['rows'] ?? [];
                                        $merged = $options['merged_cells'] ?? [];

                                        // Build a map of merged cells for quick lookup
                                        $mergeMap = [];
                                        foreach ($merged as $range) {
                                            if (preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $range, $m)) {
                                                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($m[1]) - 1;
                                                $startRow = $m[2] - 1;
                                                $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($m[3]) - 1;
                                                $endRow = $m[4] - 1;
                                                for ($r = $startRow; $r <= $endRow; $r++) {
                                                    for ($c = $startCol; $c <= $endCol; $c++) {
                                                        $mergeMap["$r:$c"] = [
                                                            'base' => ($r == $startRow && $c == $startCol),
                                                            'rowspan' => $endRow - $startRow + 1,
                                                            'colspan' => $endCol - $startCol + 1,
                                                            'startRow' => $startRow,
                                                            'startCol' => $startCol,
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    @if($rows && count($rows) > 0)
                                        <table class="table table-bordered border-dark table-striped">
                                            <tbody>
                                            @for($r = 0; $r < count($rows); $r++)
                                                <tr>
                                                    @for($c = 0; $c < count($rows[$r]); $c++)
                                                        @php
                                                            $cellKey = "$r:$c";
                                                            $merge = $mergeMap[$cellKey] ?? null;
                                                            // Only render the top-left cell of a merged region
                                                            if ($merge && !$merge['base']) continue;
                                                            $rowspan = $merge['rowspan'] ?? 1;
                                                            $colspan = $merge['colspan'] ?? 1;
                                                        @endphp
                                                        @if($r === 0)
                                                            <th
                                                                @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif
                                                                @if($colspan > 1) colspan="{{ $colspan }}" @endif
                                                                class="text-dark fw-bold border-dark align-middle"
                                                                style="background: #e9ecef;"
                                                            >
                                                                {{ $rows[$r][$c] }}
                                                            </th>
                                                        @else
                                                            <td
                                                                @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif
                                                                @if($colspan > 1) colspan="{{ $colspan }}" @endif
                                                                class="text-dark border-dark align-middle"
                                                            >
                                                                {{ $rows[$r][$c] }}
                                                            </td>
                                                        @endif
                                                    @endfor
                                                </tr>
                                            @endfor
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="alert alert-warning">
                                            <h6 class="text-dark">Question Text:</h6>
                                            <p class="text-dark">{{ $question->question_text }}</p>
                                            <hr>
                                            <p class="text-dark">This question requires a table-based response. Please structure your answer accordingly.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endforeach
@endforeach

@endsection