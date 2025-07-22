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
                                    <option value="text">Text Input</option>
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
                    <div class="form-group mb-3" id="options_container" style="display: none;">
                        <label for="options" class="form-label">Options (JSON format)</label>
                        <textarea class="form-control" id="options" name="options" rows="3" placeholder='["Option 1", "Option 2", "Option 3"]'></textarea>
                        <small class="text-muted">For select options, enter as JSON array</small>
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
                                    <option value="text">Text Input</option>
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
                                <label for="edit_order" class="form-label">Order Number</label>
                                <input type="number" class="form-control" id="edit_order" name="order" value="1" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3" id="edit_options_container" style="display: none;">
                        <label for="edit_options" class="form-label">Options (JSON format)</label>
                        <textarea class="form-control" id="edit_options" name="options" rows="3" placeholder='["Option 1", "Option 2", "Option 3"]'></textarea>
                        <small class="text-muted">For select options, enter as JSON array</small>
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