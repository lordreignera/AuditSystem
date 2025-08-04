<script>
function showTemplateInfo() {
    const selectElement = document.getElementById('review_type_id');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const templateInfo = document.getElementById('template_info');
    const templateInfoText = document.getElementById('template_info_text');
    
    if (selectedOption.value) {
        const templateCount = selectedOption.dataset.templateCount;
        if (templateCount > 0) {
            templateInfoText.textContent = `This review type has ${templateCount} template(s). All templates with their sections and questions will be copied for customization.`;
            templateInfo.style.display = 'block';
        } else {
            templateInfoText.textContent = 'This review type has no templates available.';
            templateInfo.style.display = 'block';
        }
    } else {
        templateInfo.style.display = 'none';
    }
}

function toggleQuestionOptions() {
    const responseType = document.getElementById('response_type').value;
    const yesNoContainer = document.getElementById('yes_no_options_container');
    const selectContainer = document.getElementById('select_options_container');
    const advancedContainer = document.getElementById('advanced_options_container');
    
    // Hide all containers first
    yesNoContainer.style.display = 'none';
    selectContainer.style.display = 'none';
    advancedContainer.style.display = 'none';
    
    if (responseType === 'yes_no') {
        yesNoContainer.style.display = 'block';
        updateOptionsFromYesNo();
    } else if (responseType === 'select') {
        selectContainer.style.display = 'block';
        updateOptionsFromSelect();
    }
    
    // Always update the hidden options field
    updateOptionsField();
}

function toggleEditQuestionOptions() {
    const responseType = document.getElementById('edit_response_type').value;
    const yesNoContainer = document.getElementById('edit_yes_no_options_container');
    const selectContainer = document.getElementById('edit_select_options_container');
    const advancedContainer = document.getElementById('edit_advanced_options_container');
    
    // Hide all containers first
    yesNoContainer.style.display = 'none';
    selectContainer.style.display = 'none';
    advancedContainer.style.display = 'none';
    
    if (responseType === 'yes_no') {
        yesNoContainer.style.display = 'block';
        updateEditOptionsFromYesNo();
    } else if (responseType === 'select') {
        selectContainer.style.display = 'block';
        updateEditOptionsFromSelect();
    }
    
    // Always update the hidden options field
    updateEditOptionsField();
}

// Add Question Modal Functions
function updateOptionsFromYesNo() {
    const yesOption = document.getElementById('yes_option').value || 'Yes';
    const noOption = document.getElementById('no_option').value || 'No';
    document.getElementById('options').value = JSON.stringify([yesOption, noOption]);
}

function updateOptionsFromSelect() {
    const selectOptions = [];
    document.querySelectorAll('.select-option').forEach(input => {
        if (input.value.trim()) {
            selectOptions.push(input.value.trim());
        }
    });
    document.getElementById('options').value = JSON.stringify(selectOptions);
}

function updateOptionsField() {
    const responseType = document.getElementById('response_type').value;
    if (responseType === 'yes_no') {
        updateOptionsFromYesNo();
    } else if (responseType === 'select') {
        updateOptionsFromSelect();
    } else {
        document.getElementById('options').value = '';
    }
}

function addSelectOption() {
    const container = document.getElementById('select_options_list');
    const optionCount = container.children.length + 1;
    const optionDiv = document.createElement('div');
    optionDiv.className = 'input-group mb-2';
    optionDiv.innerHTML = `
        <input type="text" class="form-control select-option" placeholder="Option ${optionCount}" onchange="updateOptionsFromSelect()">
        <button class="btn btn-outline-danger" type="button" onclick="removeSelectOption(this)">
            <i class="mdi mdi-delete"></i>
        </button>
    `;
    container.appendChild(optionDiv);
    updateOptionsFromSelect();
}

function removeSelectOption(button) {
    const container = document.getElementById('select_options_list');
    if (container.children.length > 1) {
        button.parentElement.remove();
        updateOptionsFromSelect();
    }
}

// Edit Question Modal Functions
function updateEditOptionsFromYesNo() {
    const yesOption = document.getElementById('edit_yes_option').value || 'Yes';
    const noOption = document.getElementById('edit_no_option').value || 'No';
    document.getElementById('edit_options').value = JSON.stringify([yesOption, noOption]);
}

function updateEditOptionsFromSelect() {
    const selectOptions = [];
    document.querySelectorAll('.edit-select-option').forEach(input => {
        if (input.value.trim()) {
            selectOptions.push(input.value.trim());
        }
    });
    document.getElementById('edit_options').value = JSON.stringify(selectOptions);
}

function updateEditOptionsField() {
    const responseType = document.getElementById('edit_response_type').value;
    if (responseType === 'yes_no') {
        updateEditOptionsFromYesNo();
    } else if (responseType === 'select') {
        updateEditOptionsFromSelect();
    } else {
        document.getElementById('edit_options').value = '';
    }
}

function addEditSelectOption() {
    const container = document.getElementById('edit_select_options_list');
    const optionCount = container.children.length + 1;
    const optionDiv = document.createElement('div');
    optionDiv.className = 'input-group mb-2';
    optionDiv.innerHTML = `
        <input type="text" class="form-control edit-select-option" placeholder="Option ${optionCount}" onchange="updateEditOptionsFromSelect()">
        <button class="btn btn-outline-danger" type="button" onclick="removeEditSelectOption(this)">
            <i class="mdi mdi-delete"></i>
        </button>
    `;
    container.appendChild(optionDiv);
    updateEditOptionsFromSelect();
}

function removeEditSelectOption(button) {
    const container = document.getElementById('edit_select_options_list');
    if (container.children.length > 1) {
        button.parentElement.remove();
        updateEditOptionsFromSelect();
    }
}

// Advanced Options Toggle (for power users)
function toggleAdvancedOptions() {
    const simpleContainers = document.querySelectorAll('#yes_no_options_container, #select_options_container');
    const advancedContainer = document.getElementById('advanced_options_container');
    
    simpleContainers.forEach(container => container.style.display = 'none');
    advancedContainer.style.display = 'block';
}

function toggleEditAdvancedOptions() {
    const simpleContainers = document.querySelectorAll('#edit_yes_no_options_container, #edit_select_options_container');
    const advancedContainer = document.getElementById('edit_advanced_options_container');
    
    simpleContainers.forEach(container => container.style.display = 'none');
    advancedContainer.style.display = 'block';
}

// Add event listeners for Yes/No option inputs
document.addEventListener('DOMContentLoaded', function() {
    // Add Question Modal
    const yesOption = document.getElementById('yes_option');
    const noOption = document.getElementById('no_option');
    if (yesOption) yesOption.addEventListener('input', updateOptionsFromYesNo);
    if (noOption) noOption.addEventListener('input', updateOptionsFromYesNo);
    
    // Edit Question Modal
    const editYesOption = document.getElementById('edit_yes_option');
    const editNoOption = document.getElementById('edit_no_option');
    if (editYesOption) editYesOption.addEventListener('input', updateEditOptionsFromYesNo);
    if (editNoOption) editNoOption.addEventListener('input', updateEditOptionsFromYesNo);
});

function addSection(templateId) {
    document.getElementById('add_section_template_id').value = templateId;
    new bootstrap.Modal(document.getElementById('addSectionModal')).show();
}

function editTemplate(templateId) {
    fetch(`/admin/api/templates/${templateId}`)
        .then(response => response.json())
        .then(template => {
            document.getElementById('edit_template_id').value = template.id;
            document.getElementById('edit_template_name').value = template.name;
            document.getElementById('edit_template_description').value = template.description || '';
            document.getElementById('edit_template_is_active').checked = template.is_active;
            new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
        })
        .catch(error => {
            console.error('Error loading template:', error);
            alert('Error loading template data');
        });
}

function editSection(sectionId) {
    fetch(`/admin/api/sections/${sectionId}`)
        .then(response => response.json())
        .then(section => {
            document.getElementById('edit_section_id').value = section.id;
            document.getElementById('edit_section_name').value = section.name;
            document.getElementById('edit_section_description').value = section.description || '';
            document.getElementById('edit_section_order').value = section.order;
            document.getElementById('edit_section_is_active').checked = section.is_active;
            new bootstrap.Modal(document.getElementById('editSectionModal')).show();
        })
        .catch(error => {
            console.error('Error loading section:', error);
            alert('Error loading section data');
        });
}

function deleteSection(sectionId) {
    if (confirm('Are you sure you want to delete this section? This will also delete all questions in this section.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.audits.delete-section', $audit) }}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
            <input type="hidden" name="section_id" value="${sectionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function addQuestion(sectionId) {
    document.getElementById('add_question_section_id').value = sectionId;
    new bootstrap.Modal(document.getElementById('addQuestionModal')).show();
}

function editQuestion(questionId) {
    fetch(`/admin/api/questions/${questionId}`)
        .then(response => response.json())
        .then(question => {
            // Basic fields
            document.getElementById('edit_question_id').value = question.id;
            document.getElementById('edit_question_text').value = question.question_text;
            document.getElementById('edit_response_type').value = question.response_type;
            document.getElementById('edit_is_required').checked = question.is_required;
            document.getElementById('edit_is_active').checked = question.is_active;
            document.getElementById('edit_order').value = question.order;
            
            // Handle options based on response type
            if (question.options && question.options.length > 0) {
                document.getElementById('edit_options').value = JSON.stringify(question.options);
                
                if (question.response_type === 'yes_no' && question.options.length >= 2) {
                    document.getElementById('edit_yes_option').value = question.options[0];
                    document.getElementById('edit_no_option').value = question.options[1];
                } else if (question.response_type === 'select') {
                    // Clear existing select options
                    const container = document.getElementById('edit_select_options_list');
                    container.innerHTML = '';
                    
                    // Add each option
                    question.options.forEach((option, index) => {
                        const optionDiv = document.createElement('div');
                        optionDiv.className = 'input-group mb-2';
                        optionDiv.innerHTML = `
                            <input type="text" class="form-control edit-select-option" value="${option}" onchange="updateEditOptionsFromSelect()">
                            <button class="btn btn-outline-danger" type="button" onclick="removeEditSelectOption(this)">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        `;
                        container.appendChild(optionDiv);
                    });
                    
                    // Ensure at least one option exists
                    if (question.options.length === 0) {
                        addEditSelectOption();
                    }
                }
            } else {
                document.getElementById('edit_options').value = '';
                // Reset yes/no fields
                document.getElementById('edit_yes_option').value = 'Yes';
                document.getElementById('edit_no_option').value = 'No';
                // Clear select options
                const container = document.getElementById('edit_select_options_list');
                container.innerHTML = '<div class="input-group mb-2"><input type="text" class="form-control edit-select-option" placeholder="Option 1" onchange="updateEditOptionsFromSelect()"><button class="btn btn-outline-danger" type="button" onclick="removeEditSelectOption(this)"><i class="mdi mdi-delete"></i></button></div>';
            }
            
            toggleEditQuestionOptions();
            new bootstrap.Modal(document.getElementById('editQuestionModal')).show();
        })
        .catch(error => {
            console.error('Error loading question:', error);
            alert('Error loading question data');
        });
}

function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.audits.delete-question', $audit) }}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
            <input type="hidden" name="question_id" value="${questionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function removeReviewType(reviewTypeId) {
    if (confirm('Are you sure you want to remove this review type from the audit? This will remove all associated templates, sections, and questions.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.audits.detach-review-type', $audit) }}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="review_type_id" value="${reviewTypeId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function previewTemplate(templateId) {
    window.open(`/admin/templates/${templateId}/preview`, '_blank');
}

function duplicateTemplate(templateId) {
    if (confirm('Are you sure you want to duplicate this template? This will create a copy with all sections and questions.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.audits.duplicate-template', $audit) }}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="template_id" value="${templateId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function duplicateReviewType(reviewTypeId) {
    // Set the review type ID in the modal form
    document.getElementById('duplicate_review_type_id').value = reviewTypeId;
    
    // Set the form action URL
    const form = document.getElementById('duplicateLocationForm');
    form.action = `{{ route('admin.audits.duplicate-review-type', $audit) }}`;
    
    // Clear any previous input
    document.getElementById('location_name').value = '';
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('duplicateLocationModal'));
    modal.show();
}

function renameLocation(attachmentId, currentName) {
    // Set the attachment ID in the modal form
    document.getElementById('rename_attachment_id').value = attachmentId;
    
    // Set the form action URL
    const form = document.getElementById('renameLocationForm');
    form.action = `{{ route('admin.audits.rename-location', $audit) }}`;
    
    // Pre-fill with current name
    document.getElementById('rename_location_name').value = currentName || '';
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('renameLocationModal'));
    modal.show();
    
    // Focus on the input field when modal is shown
    document.getElementById('renameLocationModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('rename_location_name').focus();
        document.getElementById('rename_location_name').select();
    });
}

// Legacy function for backward compatibility
function renameFacility(attachmentId, currentName) {
    return renameLocation(attachmentId, currentName);
}

        function detachReviewType(reviewTypeId) {
            // Set the review type ID in the modal form
            document.getElementById('detach_review_type_id').value = reviewTypeId;
            
            // Set the form action
            const form = document.getElementById('detachReviewTypeForm');
            form.action = `/admin/audits/{{ $audit->id }}/detach-review-type`;
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('detachReviewTypeModal'));
            modal.show();
        }

        function removeDuplicate(attachmentId, locationName) {
            // Set the attachment ID in the modal form
            document.getElementById('remove_attachment_id').value = attachmentId;
            document.getElementById('remove_location_name').textContent = locationName;
            
            // Set the form action
            const form = document.getElementById('removeDuplicateForm');
            form.action = `/admin/audits/{{ $audit->id }}/remove-duplicate`;
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('removeDuplicateModal'));
            modal.show();
        }// IMPROVED Table manipulation for modals
function addRow(questionId) {
    const table = document.getElementById('editableTable-' + questionId).getElementsByTagName('tbody')[0];
    if (!table) return;
    const headerRow = table.rows[0];
    const colCount = headerRow ? headerRow.cells.length : 2;
    const rowCount = table.rows.length;
    const newRow = table.insertRow();
    for (let c = 0; c < colCount; c++) {
        let td = document.createElement('td');
        td.innerHTML = `<input type="text" class="form-control" name="answers[${questionId}][table][${rowCount}][${c}]">`;
        newRow.appendChild(td);
    }
}

function addColumn(questionId) {
    const table = document.getElementById('editableTable-' + questionId).getElementsByTagName('tbody')[0];
    if (!table) return;
    for (let r = 0; r < table.rows.length; r++) {
        const row = table.rows[r];
        const colIndex = row.cells.length;
        if (r === 0) {
            let th = document.createElement('th');
            th.innerText = 'Header ' + (colIndex + 1);
            row.appendChild(th);
        } else {
            let td = document.createElement('td');
            td.innerHTML = `<input type="text" class="form-control" name="answers[${questionId}][table][${r}][${colIndex}]">`;
            row.appendChild(td);
        }
    }
}

function deleteRow(questionId) {
    const table = document.getElementById('editableTable-' + questionId).getElementsByTagName('tbody')[0];
    if (!table) return;
    // Ensure at least header + 1 data row remain
    if (table.rows.length > 2) {
        table.deleteRow(table.rows.length - 1);
    }
}

function deleteColumn(questionId) {
    const table = document.getElementById('editableTable-' + questionId).getElementsByTagName('tbody')[0];
    if (!table) return;
    const colCount = table.rows[0].cells.length;
    // Ensure at least one column remains
    if (colCount > 1) {
        for (let r = 0; r < table.rows.length; r++) {
            table.rows[r].deleteCell(colCount - 1);
        }
    }
}

let currentTemplateIndex = {};

function showTemplatePanel(reviewTypeId, index, total) {
    for (let i = 0; i < total; i++) {
        const panel = document.getElementById(`template-panel-${reviewTypeId}-${i}`);
        if (panel) panel.style.display = 'none';
    }
    const activePanel = document.getElementById(`template-panel-${reviewTypeId}-${index}`);
    if (activePanel) activePanel.style.display = 'block';
    currentTemplateIndex[reviewTypeId] = index;
    const prevBtn = document.getElementById('prev-template-btn-' + reviewTypeId);
    const nextBtn = document.getElementById('next-template-btn-' + reviewTypeId);
    if (prevBtn) prevBtn.disabled = (index === 0);
    if (nextBtn) nextBtn.disabled = (index === total - 1);
}

function showNextTemplate(reviewTypeId, total) {
    if (typeof currentTemplateIndex[reviewTypeId] === 'undefined')
        currentTemplateIndex[reviewTypeId] = 0;
    let idx = currentTemplateIndex[reviewTypeId];
    if (idx < total - 1) idx++;
    showTemplatePanel(reviewTypeId, idx, total);
}

function showPrevTemplate(reviewTypeId, total) {
    if (typeof currentTemplateIndex[reviewTypeId] === 'undefined')
        currentTemplateIndex[reviewTypeId] = 0;
    let idx = currentTemplateIndex[reviewTypeId];
    if (idx > 0) idx--;
    showTemplatePanel(reviewTypeId, idx, total);
}

document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Optional improvement: Ensure modals don't blink by resetting scroll on modal show
    document.querySelectorAll('.modal').forEach(function(modalEl) {
        modalEl.addEventListener('shown.bs.modal', function (event) {
            modalEl.scrollTop = 0;
        });
    });
});
</script>