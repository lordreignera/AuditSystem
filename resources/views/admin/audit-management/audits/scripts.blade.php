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
    const optionsContainer = document.getElementById('options_container');
    
    if (responseType === 'select' || responseType === 'yes_no') {
        optionsContainer.style.display = 'block';
        if (responseType === 'yes_no') {
            document.getElementById('options').value = '["Yes", "No"]';
        }
    } else {
        optionsContainer.style.display = 'none';
        document.getElementById('options').value = '';
    }
}

function toggleEditQuestionOptions() {
    const responseType = document.getElementById('edit_response_type').value;
    const optionsContainer = document.getElementById('edit_options_container');
    
    if (responseType === 'select' || responseType === 'yes_no') {
        optionsContainer.style.display = 'block';
        if (responseType === 'yes_no') {
            document.getElementById('edit_options').value = '["Yes", "No"]';
        }
    } else {
        optionsContainer.style.display = 'none';
        document.getElementById('edit_options').value = '';
    }
}

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
            document.getElementById('edit_question_id').value = question.id;
            document.getElementById('edit_question_text').value = question.question_text;
            document.getElementById('edit_response_type').value = question.response_type;
            document.getElementById('edit_is_required').checked = question.is_required;
            document.getElementById('edit_is_active').checked = question.is_active;
            document.getElementById('edit_order').value = question.order;
            if (question.options && question.options.length > 0) {
                document.getElementById('edit_options').value = JSON.stringify(question.options);
            } else {
                document.getElementById('edit_options').value = '';
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

// IMPROVED Table manipulation for modals
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