@extends('admin.admin_layout')

@section('title', 'AI Chat - ' . $audit->name)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">AI Chat - {{ $audit->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Audit Information Header -->
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="card">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-white">
                            <i class="mdi mdi-robot me-2"></i>AI Assistant - {{ $audit->name }}
                        </h3>
                        <p class="mb-0 text-white-50">
                            <i class="mdi mdi-map-marker me-1"></i>{{ $audit->country->name }} | 
                            <i class="mdi mdi-calendar me-1"></i>{{ $audit->start_date->format('M j, Y') }}
                            @if($audit->end_date)
                                - {{ $audit->end_date->format('M j, Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-light">{{ $audit->review_code }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $auditStats['total_responses'] }}</h4>
                            <small class="text-muted">Total Responses</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">{{ $auditStats['review_types_count'] }}</h4>
                            <small class="text-muted">Review Types</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">{{ $auditStats['completion_rate'] }}%</h4>
                            <small class="text-muted">Completion Rate</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <span class="badge badge-info">AI Powered</span><br>
                            <small class="text-muted">DeepSeek Analysis</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chat Interface -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="mdi mdi-chat me-2"></i>AI Assistant Chat
                </h5>
                <small class="text-muted">Ask questions about your audit data, request analysis, tables, or charts</small>
            </div>
            <div class="card-body d-flex flex-column" style="min-height: 600px;">
                <!-- Chat Messages Container -->
                <div id="chatMessages" class="flex-grow-1 mb-3" style="max-height: 450px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 15px; background-color: #fafafa;">
                    <div class="message ai-message mb-3">
                        <div class="d-flex align-items-start">
                            <div class="avatar bg-primary text-white rounded-circle me-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-robot"></i>
                            </div>
                            <div class="message-content">
                                <div class="bg-white p-3 rounded shadow-sm">
                                    <strong>AI Assistant:</strong><br>
                                    Hello! I'm your AI assistant for audit analysis. I can help you with:
                                    <ul class="mb-0 mt-2">
                                        <li>📊 Generating charts and visualizations</li>
                                        <li>📋 Creating data tables and summaries</li>
                                        <li>🔍 Analyzing patterns and trends</li>
                                        <li>💡 Providing recommendations</li>
                                        <li>❓ Answering questions about your audit data</li>
                                    </ul>
                                    <br>What would you like to explore about the <strong>{{ $audit->name }}</strong> audit?
                                </div>
                                <small class="text-muted">Just now</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Input -->
                <div id="chatInput">
                    <form id="chatForm" class="d-flex flex-column">
                        <div class="input-group mb-2">
                            <select class="form-select" id="contextType" name="context_type" style="max-width: 150px;">
                                <option value="general">General</option>
                                <option value="tables">Tables</option>
                                <option value="charts">Charts</option>
                                <option value="analysis">Analysis</option>
                                <option value="recommendations">Recommendations</option>
                            </select>
                            <input type="text" class="form-control" id="messageInput" name="message" placeholder="Ask me anything about your audit data..." required>
                            <button class="btn btn-primary" type="submit" id="sendButton">
                                <i class="mdi mdi-send"></i>
                            </button>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary quick-question" data-message="Show me completion rates by location">Completion Rates</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-question" data-message="Generate a summary table of all responses">Response Summary</button>
                            <button type="button" class="btn btn-sm btn-outline-info quick-question" data-message="Create a chart showing audit progress">Progress Chart</button>
                            <button type="button" class="btn btn-sm btn-outline-warning quick-question" data-message="What are the key findings and recommendations?">Key Findings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="mdi mdi-flash me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="generateQuickChart('completion_rates')">
                        <i class="mdi mdi-chart-bar me-2"></i>Completion Rate Chart
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="generateQuickTable('response_summary')">
                        <i class="mdi mdi-table me-2"></i>Response Summary Table
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="generateQuickChart('location_comparison')">
                        <i class="mdi mdi-chart-pie me-2"></i>Location Comparison
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="askQuickQuestion('What patterns do you see in the data?')">
                        <i class="mdi mdi-lightbulb me-2"></i>Pattern Analysis
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="askQuickQuestion('What are the main compliance issues?')">
                        <i class="mdi mdi-alert me-2"></i>Compliance Issues
                    </button>
                    <hr>
                    <button class="btn btn-outline-purple btn-sm" onclick="compareTemplates()">
                        <i class="mdi mdi-compare me-2"></i>Compare Templates
                    </button>
                </div>
            </div>
        </div>

        <!-- Export Panel -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="mdi mdi-download me-2"></i>Export Options</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger btn-sm" onclick="exportToPdf()" id="exportPdfBtn">
                        <i class="mdi mdi-file-pdf me-2"></i>Export to PDF
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="exportToWord()" id="exportWordBtn">
                        <i class="mdi mdi-file-word me-2"></i>Export to Word
                    </button>
                    <small class="text-muted">Includes conversation, charts, and tables</small>
                </div>
            </div>
        </div>

        <!-- Audit Context Panel -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="mdi mdi-information me-2"></i>Audit Context</h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p><strong>Audit:</strong> {{ $audit->name }}</p>
                    <p><strong>Country:</strong> {{ $audit->country->name }}</p>
                    <p><strong>Review Types:</strong> {{ $auditStats['review_types_count'] }}</p>
                    <p><strong>Data Source:</strong> This audit only</p>
                    <div class="alert alert-info alert-sm">
                        <i class="mdi mdi-information me-1"></i>
                        All analysis is specific to this audit's data.
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart/Table Output -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="mdi mdi-chart-line me-2"></i>Generated Visualizations</h6>
            </div>
            <div class="card-body">
                <div id="chartContainer" style="display: none;">
                    <canvas id="generatedChart" width="400" height="300"></canvas>
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="saveChartAsImage()">
                            <i class="mdi mdi-download me-1"></i>Save Chart
                        </button>
                    </div>
                </div>
                <div id="tableContainer" style="display: none;">
                    <div class="table-responsive">
                        <table id="generatedTable" class="table table-sm table-striped">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportTableToCSV()">
                            <i class="mdi mdi-download me-1"></i>Export CSV
                        </button>
                    </div>
                </div>
                <div id="comparisonContainer" style="display: none;">
                    <div id="comparisonContent"></div>
                </div>
                <div id="noVisualization" class="text-center text-muted py-4">
                    <i class="mdi mdi-chart-line" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="mb-0">Generated charts and tables will appear here</p>
                    <small>All data is specific to <strong>{{ $audit->name }}</strong></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">AI is analyzing your request...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentChart = null;
const auditId = {{ $audit->id }};

$(document).ready(function() {
    // Initialize chat functionality
    initializeChat();
    
    // Quick question buttons
    $('.quick-question').click(function() {
        const message = $(this).data('message');
        const contextType = $(this).text().toLowerCase().includes('chart') ? 'charts' : 
                           $(this).text().toLowerCase().includes('table') ? 'tables' : 'general';
        
        $('#messageInput').val(message);
        $('#contextType').val(contextType);
        $('#chatForm').submit();
    });
});

function initializeChat() {
    $('#chatForm').on('submit', function(e) {
        e.preventDefault();
        
        const message = $('#messageInput').val().trim();
        const contextType = $('#contextType').val();
        
        if (!message) return;
        
        // Add user message to chat
        addMessageToChat('user', message);
        
        // Clear input
        $('#messageInput').val('');
        
        // Show loading
        showLoadingModal();
        
        // Send to AI
        sendChatMessage(message, contextType);
    });
}

function addMessageToChat(sender, message, isError = false) {
    const timestamp = new Date().toLocaleTimeString();
    const isUser = sender === 'user';
    
    // Convert markdown-like formatting for AI responses
    let formattedMessage = message;
    if (!isUser) {
        formattedMessage = formatAIResponse(message);
    }
    
    const messageHtml = `
        <div class="message ${isUser ? 'user-message' : 'ai-message'} mb-3">
            <div class="d-flex align-items-start ${isUser ? 'justify-content-end' : ''}">
                ${!isUser ? `
                    <div class="avatar bg-${isError ? 'danger' : 'primary'} text-white rounded-circle me-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                        <i class="mdi mdi-${isError ? 'alert' : 'robot'}"></i>
                    </div>
                ` : ''}
                <div class="message-content ${isUser ? 'me-3' : ''}">
                    <div class="bg-${isUser ? 'primary text-white' : 'white'} p-3 rounded shadow-sm ${isError ? 'border-danger' : ''}">
                        ${isUser ? '' : `<strong>${isError ? 'Error' : 'AI Assistant'}:</strong><br>`}
                        ${formattedMessage}
                    </div>
                    <small class="text-muted">${timestamp}</small>
                </div>
                ${isUser ? `
                    <div class="avatar bg-secondary text-white rounded-circle ms-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                        <i class="mdi mdi-account"></i>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    $('#chatMessages').append(messageHtml);
    scrollToBottom();
}

function formatAIResponse(message) {
    // Convert basic markdown formatting
    let formatted = message;
    
    // Headers
    formatted = formatted.replace(/^## (.+)$/gm, '<h5 class="text-primary mb-2">$1</h5>');
    formatted = formatted.replace(/^### (.+)$/gm, '<h6 class="text-secondary mb-2">$1</h6>');
    
    // Bold text
    formatted = formatted.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    
    // Lists
    formatted = formatted.replace(/^- (.+)$/gm, '<li class="mb-1">$1</li>');
    formatted = formatted.replace(/^(\d+)\. (.+)$/gm, '<li class="mb-1">$2</li>');
    
    // Wrap consecutive list items in ul tags
    formatted = formatted.replace(/(<li.*?<\/li>\s*)+/g, function(match) {
        return '<ul class="mb-2">' + match + '</ul>';
    });
    
    // Line breaks
    formatted = formatted.replace(/\n\n/g, '<br><br>');
    formatted = formatted.replace(/\n/g, '<br>');
    
    // Emojis and status indicators
    formatted = formatted.replace(/⚠️/g, '<span class="text-warning">⚠️</span>');
    formatted = formatted.replace(/✅/g, '<span class="text-success">✅</span>');
    formatted = formatted.replace(/🔴/g, '<span class="text-danger">🔴</span>');
    formatted = formatted.replace(/🟡/g, '<span class="text-warning">🟡</span>');
    formatted = formatted.replace(/🟢/g, '<span class="text-success">🟢</span>');
    
    return formatted;
}

function sendChatMessage(message, contextType) {
    $.ajax({
        url: `/admin/ai-chat/${auditId}/chat`,
        method: 'POST',
        data: {
            message: message,
            context_type: contextType,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoadingModal();
            
            if (response.success) {
                // Add the AI response with proper formatting
                addMessageToChat('ai', response.response);
                
                // If this was a chart or table request, suggest generation
                if (contextType === 'charts' || response.response.toLowerCase().includes('chart')) {
                    setTimeout(() => suggestChartGeneration(message), 1000);
                } else if (contextType === 'tables' || response.response.toLowerCase().includes('table')) {
                    setTimeout(() => suggestTableGeneration(message), 1000);
                }
            } else {
                addMessageToChat('ai', response.fallback || 'Sorry, I encountered an error processing your request.', true);
            }
        },
        error: function(xhr) {
            hideLoadingModal();
            console.error('Chat error:', xhr);
            addMessageToChat('ai', 'Sorry, I\'m having trouble connecting right now. Please try again.', true);
        }
    });
}

function generateQuickChart(dataFocus) {
    showLoadingModal();
    
    $.ajax({
        url: `/admin/ai-chat/${auditId}/generate-chart`,
        method: 'POST',
        data: {
            chart_type: 'bar',
            data_focus: dataFocus,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoadingModal();
            
            if (response.success) {
                displayChart(response.chart_config, response.chart_data);
                addMessageToChat('ai', `I've generated a ${dataFocus.replace('_', ' ')} chart for you. You can see it in the visualization panel.`);
            } else {
                addMessageToChat('ai', 'Sorry, I couldn\'t generate the chart right now.', true);
            }
        },
        error: function() {
            hideLoadingModal();
            addMessageToChat('ai', 'Error generating chart. Please try again.', true);
        }
    });
}

function generateQuickTable(tableFocus) {
    showLoadingModal();
    
    $.ajax({
        url: `/admin/ai-chat/${auditId}/generate-table`,
        method: 'POST',
        data: {
            table_focus: tableFocus,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoadingModal();
            
            if (response.success) {
                displayTable(response.table_data);
                addMessageToChat('ai', `I've generated a ${tableFocus.replace('_', ' ')} table for you. You can see it in the visualization panel.`);
            } else {
                addMessageToChat('ai', 'Sorry, I couldn\'t generate the table right now.', true);
            }
        },
        error: function() {
            hideLoadingModal();
            addMessageToChat('ai', 'Error generating table. Please try again.', true);
        }
    });
}

function askQuickQuestion(question) {
    $('#messageInput').val(question);
    $('#contextType').val('analysis');
    $('#chatForm').submit();
}

function displayChart(config, data) {
    // Destroy existing chart
    if (currentChart) {
        currentChart.destroy();
    }
    
    // Show chart container
    $('#noVisualization').hide();
    $('#tableContainer').hide();
    $('#chartContainer').show();
    
    // Create new chart
    const ctx = document.getElementById('generatedChart').getContext('2d');
    currentChart = new Chart(ctx, config);
}

function displayTable(tableData) {
    // Hide chart, show table
    $('#noVisualization').hide();
    $('#chartContainer').hide();
    $('#tableContainer').show();
    
    // Populate table
    const table = $('#generatedTable');
    const thead = table.find('thead');
    const tbody = table.find('tbody');
    
    // Clear existing content
    thead.empty();
    tbody.empty();
    
    if (tableData.headers) {
        const headerRow = '<tr>' + tableData.headers.map(h => `<th>${h}</th>`).join('') + '</tr>';
        thead.append(headerRow);
    }
    
    if (tableData.rows) {
        tableData.rows.forEach(row => {
            const rowHtml = '<tr>' + row.map(cell => `<td>${cell}</td>`).join('') + '</tr>';
            tbody.append(rowHtml);
        });
    }
}

function suggestChartGeneration(message) {
    const suggestion = `
        <div class="mt-2 p-2 bg-light rounded">
            <small class="text-muted">
                <i class="mdi mdi-lightbulb me-1"></i>
                Would you like me to generate a chart based on this analysis? 
                <button class="btn btn-sm btn-outline-primary ms-2" onclick="generateQuickChart('analysis_based')">
                    Generate Chart
                </button>
            </small>
        </div>
    `;
    
    $('#chatMessages .message:last .message-content div').append(suggestion);
}

function suggestTableGeneration(message) {
    const suggestion = `
        <div class="mt-2 p-2 bg-light rounded">
            <small class="text-muted">
                <i class="mdi mdi-lightbulb me-1"></i>
                Would you like me to create a data table for this? 
                <button class="btn btn-sm btn-outline-success ms-2" onclick="generateQuickTable('analysis_based')">
                    Generate Table
                </button>
            </small>
        </div>
    `;
    
    $('#chatMessages .message:last .message-content div').append(suggestion);
}

function showLoadingModal() {
    $('#loadingModal').modal('show');
}

function hideLoadingModal() {
    $('#loadingModal').modal('hide');
}

function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Export Functions
function exportToPdf() {
    const btn = document.getElementById('exportPdfBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i>Generating PDF...';
    
    fetch(`/admin/ai-chat/${auditId}/export-pdf`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            conversation: getConversationData(),
            chartData: currentChart ? currentChart.data : null
        })
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'audit-ai-analysis-{{ $audit->name }}.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        console.error('Export error:', error);
        alert('Error generating PDF. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-file-pdf me-2"></i>Export to PDF';
    });
}

function exportToWord() {
    const btn = document.getElementById('exportWordBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i>Generating Word...';
    
    fetch(`/admin/ai-chat/${auditId}/export-word`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            conversation: getConversationData(),
            chartData: currentChart ? currentChart.data : null
        })
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'audit-ai-analysis-{{ $audit->name }}.docx';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        console.error('Export error:', error);
        alert('Error generating Word document. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-file-word me-2"></i>Export to Word';
    });
}

function compareTemplates() {
    const message = 'Please compare audit responses across different templates and review types for this audit, highlighting similarities, differences, and patterns. Show me a detailed comparison analysis.';
    $('#messageInput').val(message);
    $('#contextType').val('comparison');
    $('#chatForm').submit();
}

function saveChartAsImage() {
    if (currentChart) {
        const url = currentChart.toBase64Image();
        const a = document.createElement('a');
        a.href = url;
        a.download = 'audit-chart-{{ $audit->name }}.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}

function exportTableToCSV() {
    const table = document.getElementById('generatedTable');
    if (!table || table.rows.length === 0) return;
    
    let csv = [];
    
    // Get headers
    if (table.rows[0]) {
        const headers = Array.from(table.rows[0].cells).map(cell => cell.textContent);
        csv.push(headers.join(','));
    }
    
    // Get data rows (skip header row)
    for (let i = 1; i < table.rows.length; i++) {
        const row = Array.from(table.rows[i].cells).map(cell => `"${cell.textContent}"`);
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'audit-table-{{ $audit->name }}.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function getConversationData() {
    const messages = [];
    document.querySelectorAll('.message').forEach(msg => {
        const isUser = msg.classList.contains('user-message');
        const content = msg.querySelector('.message-content div').textContent;
        messages.push({
            type: isUser ? 'user' : 'assistant',
            content: content,
            timestamp: new Date().toISOString()
        });
    });
    return messages;
}
</script>
@endsection
