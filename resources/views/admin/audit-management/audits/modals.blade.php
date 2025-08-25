<!-- Import Booklet Modal (Reusable for each Review Type) -->
@foreach($attachedReviewTypes as $reviewType)
<div class="modal fade" id="importBookletModal-{{ $reviewType->id }}" tabindex="-1" aria-labelledby="importBookletModalLabel-{{ $reviewType->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #2574fa;">
                <h5 class="modal-title text-white" id="importBookletModalLabel-{{ $reviewType->id }}">Import Booklet ({{ $reviewType->name }})</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.reviewtypes.import.booklet', [$audit->id, $reviewType->id]) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">XLSX File</label>
                        <input type="file" name="excel_file" accept=".xlsx" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Import Mode</label>
                        <select name="import_mode" class="form-select import-mode-select" data-reviewtype="{{ $reviewType->id }}">
                            <option value="update" selected>Update selected location</option>
                            <option value="new">Create new location</option>
                        </select>
                    </div>
                    <div class="mb-3 update-fields" id="updateFields-{{ $reviewType->id }}">
                        <label class="form-label">Attachment (Location) to update</label>
                        <input type="text" class="form-control" value="{{ request('selected_attachment_' . $reviewType->id, $reviewType->attachmentId) }}" disabled>
                        <input type="hidden" name="attachment_id" value="{{ request('selected_attachment_' . $reviewType->id, $reviewType->attachmentId) }}">
                    </div>
                    <div class="mb-3 new-fields d-none" id="newFields-{{ $reviewType->id }}">
                        <label class="form-label">New Location Name</label>
                        <input type="text" name="location_name" class="form-control" placeholder="e.g., District 4">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.import-mode-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            var reviewTypeId = this.getAttribute('data-reviewtype');
            var updateFields = document.getElementById('updateFields-' + reviewTypeId);
            var newFields = document.getElementById('newFields-' + reviewTypeId);
            if (this.value === 'new') {
                updateFields.classList.add('d-none');
                newFields.classList.remove('d-none');
            } else {
                newFields.classList.add('d-none');
                updateFields.classList.remove('d-none');
            }
        });
    });
});
</script>
<!-- Add Review Type Modal -->
<div class="modal fade" id="addReviewTypeModal" tabindex="-1" aria-labelledby="addReviewTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #2574fa;">
                <h5 class="modal-title text-white" id="addReviewTypeModalLabel">Attach Review Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.audits.attach-review-type', $audit) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="review_type_id" class="form-label">Select Review Type</label>
                        <select class="form-control"
                                id="review_type_id" name="review_type_id" required onchange="showTemplateInfo()">
                            <option value="">Choose a review type...</option>
                            @foreach($availableReviewTypes as $reviewType)
                                <option value="{{ $reviewType->id }}" data-template-count="{{ $reviewType->templates->count() }}">
                                    {{ $reviewType->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">All templates, sections, and questions will be automatically copied and customized for this audit.</small>
                    </div>
                    
                    <div id="template_info" style="display: none;">
                        <div class="alert alert-info" style="background-color: #eaf3ff; border-color: #2574fa; color: #222;">
                            <i class="mdi mdi-information-outline me-2"></i>
                            <span id="template_info_text"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Attach Review Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #2574fa;">
                <h5 class="modal-title text-white" id="addSectionModalLabel">Add New Section</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.audits.add-section', $audit) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="add_section_template_id" name="template_id">
                    <div class="form-group mb-3">
                        <label for="section_name" class="form-label">Section Name</label>
                        <input type="text" class="form-control" id="section_name" name="name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="section_description" class="form-label">Description</label>
                        <textarea class="form-control" id="section_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="section_order" class="form-label">Order Number</label>
                        <input type="number" class="form-control" id="section_order" name="order" value="1" min="1" required>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #2574fa;">
                <h5 class="modal-title text-white" id="addQuestionModalLabel">Add New Question</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.audits.add-question', $audit) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="add_question_section_id" name="section_id">
                    <div class="form-group mb-3">
                        <label for="question_text" class="form-label">Question Text</label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                        <small class="text-muted">For table questions, use | to separate columns and new lines for rows</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="response_type" class="form-label">Response Type</label>
                                <select class="form-control"
                                        id="response_type" name="response_type" required onchange="toggleQuestionOptions()">
                                    <option value="text" selected>Text Input (Default)</option>
                                    <option value="textarea">Text Area</option>
                                    <option value="yes_no">Yes/No</option>
                                    <option value="select">Select Option</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="table">Table/Matrix</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="order" class="form-label">Order Number</label>
                                <input type="number" class="form-control" id="order" name="order" value="1" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Yes/No Options (User-friendly) -->
                    <div class="form-group mb-3" id="yes_no_options_container" style="display: none;">
                        <label class="form-label">Yes/No Options</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="yes_option" class="form-label">Yes Option</label>
                                <input type="text" class="form-control" id="yes_option" value="Yes" placeholder="Yes">
                            </div>
                            <div class="col-md-6">
                                <label for="no_option" class="form-label">No Option</label>
                                <input type="text" class="form-control" id="no_option" value="No" placeholder="No">
                            </div>
                        </div>
                        <small class="text-muted">Customize the Yes/No options if needed</small>
                    </div>
                    
                    <!-- Select Options (User-friendly) -->
                    <div class="form-group mb-3" id="select_options_container" style="display: none;">
                        <label class="form-label">Select Options</label>
                        <div id="select_options_list">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control select-option" placeholder="Option 1">
                                <button class="btn btn-outline-danger" type="button" onclick="removeSelectOption(this)">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSelectOption()">
                            <i class="mdi mdi-plus"></i> Add Option
                        </button>
                        <small class="text-muted d-block mt-1">Add multiple options for the dropdown</small>
                    </div>
                    
                    <!-- Hidden JSON field for options -->
                    <input type="hidden" id="options" name="options"  />

                    <!-- Advanced Options (JSON) - Hidden by default -->
                    <div class="form-group mb-3" id="advanced_options_container" style="display: none;">
                        <label for="advanced_options" class="form-label">Advanced Options (JSON format)</label>
                        <textarea class="form-control" id="advanced_options" rows="3" placeholder='["Option 1", "Option 2", "Option 3"]'></textarea>
                        <small class="text-muted">For advanced users: enter as JSON array</small>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="toggleAdvancedOptions()">
                            Use Simple Options
                        </button>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_required" name="is_required" value="1">
                        <label class="form-check-label" for="is_required">
                            Required Question
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Template Modal -->
<div class="modal fade" id="editTemplateModal" tabindex="-1" aria-labelledby="editTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #2574fa;">
                <h5 class="modal-title text-white" id="editTemplateModalLabel">Edit Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.audits.update-template', $audit) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_template_id" name="template_id">
                    <div class="form-group mb-3">
                        <label for="edit_template_name" class="form-label">Template Name</label>
                        <input type="text" class="form-control" id="edit_template_name" name="name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_template_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_template_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_template_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_template_is_active">
                            Active Template
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #2574fa;">
                <h5 class="modal-title text-white" id="editSectionModalLabel">Edit Section</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.audits.update-section', $audit) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_section_id" name="section_id">
                    <div class="form-group mb-3">
                        <label for="edit_section_name" class="form-label">Section Name</label>
                        <input type="text" class="form-control" id="edit_section_name" name="name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_section_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_section_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_section_order" class="form-label">Order Number</label>
                        <input type="number" class="form-control" id="edit_section_order" name="order" value="1" min="1" required>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_section_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_section_is_active">
                            Active Section
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #2574fa;">
                <h5 class="modal-title text-white" id="editQuestionModalLabel">Edit Question</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.audits.update-question', $audit) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_question_id" name="question_id">
                    <div class="form-group mb-3">
                        <label for="edit_question_text" class="form-label">Question Text</label>
                        <textarea class="form-control" id="edit_question_text" name="question_text" rows="3" required></textarea>
                        <small class="text-muted">For table questions, use | to separate columns and new lines for rows</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit_response_type" class="form-label">Response Type</label>
                                <select class="form-control"
                                        id="edit_response_type" name="response_type" required onchange="toggleEditQuestionOptions()">
                                    <option value="textarea">Text Area (Default)</option>
                                    <option value="text">Text Input</option>
                                    <option value="yes_no">Yes/No</option>
                                    <option value="select">Select Option</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="table">Table/Matrix</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit_order" class="form-label">Order Number</label>
                                <input type="number" class="form-control" id="edit_order" name="order" value="1" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Yes/No Options (User-friendly) -->
                    <div class="form-group mb-3" id="edit_yes_no_options_container" style="display: none;">
                        <label class="form-label">Yes/No Options</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="edit_yes_option" class="form-label">Yes Option</label>
                                <input type="text" class="form-control" id="edit_yes_option" value="Yes" placeholder="Yes">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_no_option" class="form-label">No Option</label>
                                <input type="text" class="form-control" id="edit_no_option" value="No" placeholder="No">
                            </div>
                        </div>
                        <small class="text-muted">Customize the Yes/No options if needed</small>
                    </div>
                    
                    <!-- Select Options (User-friendly) -->
                    <div class="form-group mb-3" id="edit_select_options_container" style="display: none;">
                        <label class="form-label">Select Options</label>
                        <div id="edit_select_options_list">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control edit-select-option" placeholder="Option 1">
                                <button class="btn btn-outline-danger" type="button" onclick="removeEditSelectOption(this)">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addEditSelectOption()">
                            <i class="mdi mdi-plus"></i> Add Option
                        </button>
                        <small class="text-muted d-block mt-1">Add multiple options for the dropdown</small>
                    </div>
                    
                    <!-- Hidden JSON field for options -->
                    <input type="hidden" id="edit_options" name="options" />

                    <!-- Advanced Options (JSON) - Hidden by default -->
                    <div class="form-group mb-3" id="edit_advanced_options_container" style="display: none;">
                        <label for="edit_advanced_options" class="form-label">Advanced Options (JSON format)</label>
                        <textarea class="form-control" id="edit_advanced_options" rows="3" placeholder='["Option 1", "Option 2", "Option 3"]'></textarea>
                        <small class="text-muted">For advanced users: enter as JSON array</small>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="toggleEditAdvancedOptions()">
                            Use Simple Options
                        </button>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_required" name="is_required" value="1">
                        <label class="form-check-label" for="edit_is_required">
                            Required Question
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Active Question
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Duplicate Modal -->
<div class="modal fade" id="removeDuplicateModal" tabindex="-1" aria-labelledby="removeDuplicateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #dc3545;">
                <h5 class="modal-title text-white" id="removeDuplicateModalLabel">
                    <i class="mdi mdi-delete me-2"></i>Remove Duplicate Location
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="removeDuplicateForm" method="POST">
                @csrf
                <input type="hidden" id="remove_attachment_id" name="attachment_id" value="">
                <div class="modal-body">
                    <div class="alert alert-warning" style="background-color: #fff3cd; border-color: #ffecb5; color: #664d03;">
                        <i class="mdi mdi-alert me-2"></i>
                        <strong>⚠️ Are you sure?</strong>
                    </div>
                    
                    <p>You are about to remove the duplicate location: <strong id="remove_location_name"></strong></p>
                    
                    <p>This will permanently delete:</p>
                    <ul class="list-unstyled ms-3">
                        <li><i class="mdi mdi-close text-danger me-2"></i>This duplicate location</li>
                        <li><i class="mdi mdi-close text-danger me-2"></i>All responses for this location</li>
                    </ul>
                    
                    <div class="alert alert-info" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460;">
                        <i class="mdi mdi-information me-2"></i>
                        <strong>Note:</strong> The master location and other duplicates will remain unaffected.
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="mdi mdi-delete me-1"></i>Yes, Remove Duplicate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detach Review Type Modal -->
<div class="modal fade" id="detachReviewTypeModal" tabindex="-1" aria-labelledby="detachReviewTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #dc3545;">
                <h5 class="modal-title text-white" id="detachReviewTypeModalLabel">
                    <i class="mdi mdi-alert-circle me-2"></i>Detach Review Type
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="detachReviewTypeForm" method="POST">
                @csrf
                <input type="hidden" id="detach_review_type_id" name="review_type_id" value="">
                <div class="modal-body">
                    <div class="alert alert-danger" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24;">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        <strong>⚠️ Warning: This action cannot be undone!</strong>
                    </div>
                    
                    <p>This will permanently remove:</p>
                    <ul class="list-unstyled ms-3">
                        <li><i class="mdi mdi-close text-danger me-2"></i><strong>ALL locations</strong> (master and duplicates)</li>
                        <li><i class="mdi mdi-close text-danger me-2"></i><strong>ALL templates, sections, and questions</strong></li>
                        <li><i class="mdi mdi-close text-danger me-2"></i><strong>ALL responses and data</strong></li>
                    </ul>
                    
                    <p class="mb-0"><strong>Are you absolutely sure you want to proceed?</strong></p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="mdi mdi-delete me-1"></i>Yes, Detach Everything
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Location Modal -->
<div class="modal fade" id="renameLocationModal" tabindex="-1" aria-labelledby="renameLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #6c757d;">
                <h5 class="modal-title text-white" id="renameLocationModalLabel">
                    <i class="mdi mdi-pencil me-2"></i>Rename Location
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="renameLocationForm" method="POST">
                @csrf
                <input type="hidden" id="rename_attachment_id" name="attachment_id" value="">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="rename_location_name" class="form-label">
                            <i class="mdi mdi-map-marker me-1"></i>Location Name
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="rename_location_name" 
                               name="location_name" 
                               placeholder="Enter new location name" 
                               required>
                        <small class="text-muted">
                            Update the name for this location (e.g., "Kampala District", "Mulago Hospital")
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-pencil me-1"></i>Update Name
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Duplicate Location Modal -->
<div class="modal fade" id="duplicateLocationModal" tabindex="-1" aria-labelledby="duplicateLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #fff; color: #222;">
            <div class="modal-header" style="background-color: #28a745;">
                <h5 class="modal-title text-white" id="duplicateLocationModalLabel">
                    <i class="mdi mdi-content-copy me-2"></i>Create Duplicate for Another Location
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="duplicateLocationForm" method="POST">
                @csrf
                <input type="hidden" id="duplicate_review_type_id" name="review_type_id" value="">
                <div class="modal-body">
                    <div class="alert alert-info" style="background-color: #eaf3ff; border-color: #17a2b8; color: #222;">
                        <i class="mdi mdi-information-outline me-2"></i>
                        <strong>Creating a duplicate location:</strong><br>
                        • Shares the same templates, sections, and questions as the master<br>
                        • Maintains independent response data for this location<br>
                        • Changes to structure (by master) will affect all duplicates
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="location_name" class="form-label">
                            <i class="mdi mdi-map-marker me-1"></i>Location Name
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="location_name" 
                               name="location_name" 
                               placeholder="e.g., Province/District/Facility name" 
                               required>
                        <small class="text-muted">
                            Enter a descriptive name for this location (e.g., "Kampala District", "Mulago Hospital", "Northern Province")
                        </small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">
                            <i class="mdi mdi-file-document-outline me-1"></i>What will be duplicated:
                        </label>
                        <ul class="list-unstyled ms-3">
                            <li><i class="mdi mdi-check text-success me-2"></i>Same template structure</li>
                            <li><i class="mdi mdi-check text-success me-2"></i>Same sections and questions</li>
                            <li><i class="mdi mdi-close text-danger me-2"></i>Independent responses (blank)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-content-copy me-1"></i>Create Duplicate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>