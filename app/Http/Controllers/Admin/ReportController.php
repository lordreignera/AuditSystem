<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Response;
use App\Models\ReviewType;
use App\Models\AuditReviewTypeAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view reports']);
    }

    /**
     * Display the reports dashboard
     */
    public function index()
    {
        $audits = Audit::with(['country', 'attachedReviewTypes.reviewType'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.reports.index', compact('audits'));
    }

    /**
     * Show audit report generation page
     */
    public function show(Audit $audit)
    {
        $audit->load([
            'attachedReviewTypes.reviewType',
            'responses.question.section.template',
            'responses.user'
        ]);

        // Get all review types with their locations (master + duplicates)
        $reviewTypesData = $this->getReviewTypesWithLocations($audit);
        
        // Get response statistics
        $responseStats = $this->getResponseStatistics($audit);

        return view('admin.reports.show', compact('audit', 'reviewTypesData', 'responseStats'));
    }

    /**
     * Generate AI-powered report
     */
    public function generateAiReport(Request $request, Audit $audit)
    {
        $request->validate([
            'report_type' => 'required|in:executive_summary,detailed_analysis,compliance_check,comparative_analysis',
            'selected_locations' => 'array',
            'include_tables' => 'sometimes|in:0,1,true,false,on,off',
            'include_recommendations' => 'sometimes|in:0,1,true,false,on,off'
        ]);

        try {
            // Convert checkbox values to boolean
            $includeTablesValue = $request->get('include_tables');
            $includeRecommendationsValue = $request->get('include_recommendations');
            
            $includeTables = in_array($includeTablesValue, ['1', 'true', 'on', true], true);
            $includeRecommendations = in_array($includeRecommendationsValue, ['1', 'true', 'on', true], true);
            
            // Override request values with converted booleans
            $request->merge([
                'include_tables' => $includeTables,
                'include_recommendations' => $includeRecommendations
            ]);

            // Collect audit data
            $auditData = $this->collectAuditData($audit, $request->selected_locations ?? []);
            
            // Generate AI report
            $aiReport = $this->callDeepSeekAI($auditData, $request->all());
            
            // Save the generated report
            $reportId = $this->saveGeneratedReport($audit, $aiReport, $request->all());

            return response()->json([
                'success' => true,
                'report_id' => $reportId,
                'report_content' => $aiReport,
                'message' => 'Report generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get review types with their locations and response counts
     */
    private function getReviewTypesWithLocations(Audit $audit)
    {
        $reviewTypesData = [];
        
        $attachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->with(['reviewType.templates.sections.questions', 'responses.question'])
            ->get()
            ->groupBy('review_type_id');

        foreach ($attachments as $reviewTypeId => $locationAttachments) {
            $firstAttachment = $locationAttachments->first();
            
            // Skip if no valid attachment or review type
            if (!$firstAttachment || !$firstAttachment->reviewType) {
                continue;
            }
            
            $reviewType = $firstAttachment->reviewType;
            $locations = [];

            foreach ($locationAttachments as $attachment) {
                try {
                    $responseCount = $attachment->responses->count();
                    $totalQuestions = $reviewType->templates()
                        ->with('sections.questions')
                        ->get()
                        ->sum(function($template) {
                            return $template->sections->sum(function($section) {
                                return $section->questions->count();
                            });
                        });

                    $locations[] = [
                        'attachment_id' => $attachment->id,
                        'location_name' => $attachment->getContextualLocationName(),
                        'is_master' => $attachment->is_master,
                        'duplicate_number' => $attachment->duplicate_number,
                        'response_count' => $responseCount,
                        'total_questions' => $totalQuestions,
                        'completion_percentage' => $totalQuestions > 0 ? round(($responseCount / $totalQuestions) * 100, 1) : 0
                    ];
                } catch (\Exception $e) {
                    // Log the error but continue processing other attachments
                    \Log::warning("Error processing attachment {$attachment->id}: " . $e->getMessage());
                }
            }

            if (!empty($locations)) {
                $reviewTypesData[] = [
                    'review_type' => $reviewType,
                    'locations' => $locations
                ];
            }
        }

        return $reviewTypesData;
    }

    /**
     * Get response statistics for the audit
     */
    private function getResponseStatistics(Audit $audit)
    {
        $responses = Response::where('audit_id', $audit->id)
            ->with(['question', 'attachment.reviewType'])
            ->whereHas('attachment') // Only get responses with valid attachments
            ->get();

        $stats = [
            'total_responses' => $responses->count(),
            'by_type' => [],
            'by_location' => [],
            'completion_rate' => 0
        ];

        // Group by response type
        $responsesByType = $responses->groupBy(function($response) {
            return $response->question ? $response->question->response_type : 'unknown';
        });

        foreach ($responsesByType as $type => $typeResponses) {
            $stats['by_type'][$type] = $typeResponses->count();
        }

        // Group by location with better error handling
        $responsesByLocation = $responses->groupBy('attachment_id');
        foreach ($responsesByLocation as $attachmentId => $locationResponses) {
            $attachment = $locationResponses->first()->attachment;
            if ($attachment && $attachment->reviewType) {
                try {
                    $locationName = $attachment->getContextualLocationName();
                    $stats['by_location'][$locationName] = $locationResponses->count();
                } catch (\Exception $e) {
                    // Fallback if getContextualLocationName fails
                    $stats['by_location']['Location ' . $attachmentId] = $locationResponses->count();
                }
            } else {
                // Handle case where attachment or reviewType is null
                $stats['by_location']['Unknown Location'] = $locationResponses->count();
            }
        }

        return $stats;
    }

    /**
     * Collect comprehensive audit data for AI processing
     */
    private function collectAuditData(Audit $audit, array $selectedLocations = [])
    {
        $data = [
            'audit_info' => [
                'name' => $audit->name,
                'description' => $audit->description,
                'country' => $audit->country->name,
                'start_date' => $audit->start_date->format('Y-m-d'),
                'end_date' => $audit->end_date ? $audit->end_date->format('Y-m-d') : null,
                'review_code' => $audit->review_code
            ],
            'review_types' => []
        ];

        $attachments = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->with(['reviewType.templates.sections.questions', 'responses.question'])
            ->get();

        // Filter by selected locations if specified
        if (!empty($selectedLocations)) {
            $attachments = $attachments->whereIn('id', $selectedLocations);
        }

        foreach ($attachments as $attachment) {
            // Skip invalid attachments
            if (!$attachment || !$attachment->reviewType) {
                continue;
            }

            try {
                $reviewTypeData = [
                    'name' => $attachment->reviewType->name,
                    'location' => $attachment->getContextualLocationName(),
                    'is_master' => $attachment->is_master,
                    'sections' => []
                ];

                // Get responses for this attachment
                $responses = $attachment->responses->keyBy('question_id');

                foreach ($attachment->reviewType->templates as $template) {
                    foreach ($template->sections as $section) {
                        $sectionData = [
                            'name' => $section->name,
                            'description' => $section->description,
                            'questions' => []
                        ];

                        foreach ($section->questions as $question) {
                            $response = $responses->get($question->id);
                            
                            $questionData = [
                                'question_text' => $question->question_text,
                                'description' => $question->description,
                                'response_type' => $question->response_type,
                                'is_required' => $question->is_required,
                                'answer' => null,
                                'audit_note' => null
                            ];

                            if ($response) {
                                $questionData['answer'] = $this->formatResponseAnswer($response, $question);
                                $questionData['audit_note'] = $response->audit_note;
                            }

                            $sectionData['questions'][] = $questionData;
                        }

                        $reviewTypeData['sections'][] = $sectionData;
                    }
                }

                $data['review_types'][] = $reviewTypeData;
            } catch (\Exception $e) {
                // Log error but continue processing other attachments
                \Log::warning("Error processing attachment {$attachment->id} in collectAuditData: " . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * Format response answer based on question type
     */
    private function formatResponseAnswer($response, $question)
    {
        if (!$response->answer) {
            return null;
        }

        switch ($question->response_type) {
            case 'table':
                // Format table data for AI understanding
                $tableData = is_array($response->answer) ? $response->answer : json_decode($response->answer, true);
                if (is_array($tableData)) {
                    return [
                        'type' => 'table',
                        'data' => $tableData,
                        'formatted' => $this->formatTableForAI($tableData)
                    ];
                }
                return $response->answer;

            case 'yes_no':
                return [
                    'type' => 'yes_no',
                    'value' => $response->answer,
                    'formatted' => "Answer: " . $response->answer
                ];

            case 'textarea':
                return [
                    'type' => 'textarea',
                    'value' => $response->answer,
                    'formatted' => "Response: " . $response->answer
                ];

            default:
                return [
                    'type' => 'text',
                    'value' => $response->answer,
                    'formatted' => "Answer: " . $response->answer
                ];
        }
    }

    /**
     * Format table data for AI processing
     */
    private function formatTableForAI($tableData)
    {
        if (!is_array($tableData) || empty($tableData)) {
            return "Empty table";
        }

        $formatted = "Table Data:\n";
        foreach ($tableData as $rowIndex => $row) {
            if (is_array($row)) {
                $formatted .= "Row " . ($rowIndex + 1) . ": " . implode(" | ", $row) . "\n";
            }
        }

        return $formatted;
    }

    /**
     * Call DeepSeek AI API for report generation
     */
    private function callDeepSeekAI($auditData, $options)
    {
        // Try multiple ways to get the API key
        $apiKey = config('services.deepseek.api_key') 
                  ?? env('DEEPSEEK_API_KEY') 
                  ?? $_ENV['DEEPSEEK_API_KEY'] 
                  ?? null;
        
        if (!$apiKey || empty($apiKey)) {
            throw new \Exception('DeepSeek API key is not configured. Please add DEEPSEEK_API_KEY to your .env file.');
        }

        $prompt = $this->buildPromptForAI($auditData, $options);

        try {
            $verifySSL = config('services.deepseek.verify_ssl', false);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])
            ->withOptions([
                'verify' => $verifySSL, // Use configuration setting
                'timeout' => 120
            ])
            ->timeout(120)
            ->post('https://api.deepseek.com/v1/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert audit report analyst. Generate comprehensive, professional audit reports based on the provided data.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.3
            ]);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'SSL certificate') !== false) {
                throw new \Exception('SSL certificate error. This is common in development environments. The request has been configured to bypass SSL verification.');
            }
            throw new \Exception('Network error: ' . $e->getMessage());
        }

        if (!$response->successful()) {
            $errorMessage = 'DeepSeek API request failed. ';
            $errorMessage .= 'Status: ' . $response->status() . '. ';
            $responseBody = $response->body();
            
            // Handle specific error cases
            if ($response->status() === 402) {
                // Insufficient balance error
                $errorData = json_decode($responseBody, true);
                if (isset($errorData['error']['message']) && strpos($errorData['error']['message'], 'Insufficient Balance') !== false) {
                    // Return a demo report instead of failing
                    return $this->generateDemoReport($auditData, $options);
                }
            }
            
            $errorMessage .= 'Response: ' . $responseBody;
            throw new \Exception($errorMessage);
        }

        $result = $response->json();
        return $result['choices'][0]['message']['content'] ?? 'No report generated';
    }

    /**
     * Build the AI prompt based on audit data and options
     */
    private function buildPromptForAI($auditData, $options)
    {
        $reportType = $options['report_type'];
        $includeRecommendations = $options['include_recommendations'] ?? false;
        
        $prompt = "Generate a {$reportType} report for the following audit:\n\n";
        
        // Add audit basic info
        $prompt .= "AUDIT INFORMATION:\n";
        $prompt .= "Name: {$auditData['audit_info']['name']}\n";
        $prompt .= "Country: {$auditData['audit_info']['country']}\n";
        $prompt .= "Start Date: {$auditData['audit_info']['start_date']}\n";
        if ($auditData['audit_info']['end_date']) {
            $prompt .= "End Date: {$auditData['audit_info']['end_date']}\n";
        }
        $prompt .= "Review Code: {$auditData['audit_info']['review_code']}\n\n";

        // Add review types and responses
        $prompt .= "AUDIT DATA:\n";
        foreach ($auditData['review_types'] as $reviewType) {
            $prompt .= "\nREVIEW TYPE: {$reviewType['name']}\n";
            $prompt .= "Location: {$reviewType['location']}\n";
            $prompt .= "Type: " . ($reviewType['is_master'] ? 'Master' : 'Duplicate') . "\n";

            foreach ($reviewType['sections'] as $section) {
                $prompt .= "\nSECTION: {$section['name']}\n";
                if ($section['description']) {
                    $prompt .= "Description: {$section['description']}\n";
                }

                foreach ($section['questions'] as $question) {
                    $prompt .= "\nQ: {$question['question_text']}\n";
                    if ($question['answer']) {
                        if (is_array($question['answer']) && isset($question['answer']['formatted'])) {
                            $prompt .= "A: {$question['answer']['formatted']}\n";
                        } else {
                            $prompt .= "A: {$question['answer']}\n";
                        }
                    } else {
                        $prompt .= "A: [No response provided]\n";
                    }
                    
                    if ($question['audit_note']) {
                        $prompt .= "Note: {$question['audit_note']}\n";
                    }
                }
            }
        }

        // Add specific instructions based on report type
        $prompt .= "\n\nREPORT REQUIREMENTS:\n";
        switch ($reportType) {
            case 'executive_summary':
                $prompt .= "Create an executive summary focusing on key findings, major issues, and high-level insights.";
                break;
            case 'detailed_analysis':
                $prompt .= "Provide a detailed analysis of all responses, identifying patterns, trends, and specific areas of concern.";
                break;
            case 'compliance_check':
                $prompt .= "Focus on compliance-related findings, highlighting areas where standards are met or not met.";
                break;
            case 'comparative_analysis':
                $prompt .= "Compare responses across different locations (master vs duplicates) and identify discrepancies or consistencies.";
                break;
        }

        if ($includeRecommendations) {
            $prompt .= " Include specific recommendations for improvement based on the findings.";
        }

        $prompt .= "\n\nFormat the report professionally with clear headings, bullet points where appropriate, and actionable insights.";

        return $prompt;
    }

    /**
     * Generate a demo report when API credits are insufficient
     */
    private function generateDemoReport($auditData, $options)
    {
        $reportType = $options['report_type'];
        $auditName = $auditData['audit_info']['name'];
        $country = $auditData['audit_info']['country'];
        
        $demoReport = "# COMPREHENSIVE AUDIT REPORT - {$auditName}\n\n";
        $demoReport .= "**Generated using Built-in Analysis Engine**\n";
        $demoReport .= "**Note: For AI-powered insights, add credits to your DeepSeek account**\n\n";
        
        $demoReport .= "## Executive Summary\n";
        $demoReport .= "- **Audit Name**: {$auditName}\n";
        $demoReport .= "- **Country**: {$country}\n";
        $demoReport .= "- **Report Type**: " . ucwords(str_replace('_', ' ', $reportType)) . "\n";
        $demoReport .= "- **Generated**: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        // Analyze the actual data
        $analysis = $this->analyzeAuditData($auditData);
        
        $demoReport .= "## Audit Statistics\n";
        $demoReport .= "- **Review Types**: {$analysis['review_type_count']}\n";
        $demoReport .= "- **Total Questions**: {$analysis['total_questions']}\n";
        $demoReport .= "- **Answered Questions**: {$analysis['answered_questions']}\n";
        $demoReport .= "- **Completion Rate**: {$analysis['completion_rate']}%\n";
        $demoReport .= "- **Response Types**: " . implode(', ', array_keys($analysis['response_types'])) . "\n\n";
        
        $demoReport .= "## Review Type Analysis\n";
        foreach ($auditData['review_types'] as $reviewType) {
            $demoReport .= "### {$reviewType['name']} - {$reviewType['location']}\n";
            $demoReport .= "- **Type**: " . ($reviewType['is_master'] ? 'Master Location' : 'Duplicate Location') . "\n";
            
            $sectionCount = count($reviewType['sections']);
            $demoReport .= "- **Sections**: {$sectionCount}\n";
            
            // Count questions and responses for this review type
            $rtQuestions = 0;
            $rtAnswered = 0;
            foreach ($reviewType['sections'] as $section) {
                foreach ($section['questions'] as $question) {
                    $rtQuestions++;
                    if ($question['answer']) {
                        $rtAnswered++;
                    }
                }
            }
            $rtCompletion = $rtQuestions > 0 ? round(($rtAnswered / $rtQuestions) * 100, 1) : 0;
            $demoReport .= "- **Questions**: {$rtQuestions} ({$rtAnswered} answered, {$rtCompletion}% complete)\n\n";
        }
        
        $demoReport .= "## Key Findings\n";
        $demoReport .= $this->generateKeyFindings($auditData, $analysis);
        
        $demoReport .= "\n## Recommendations\n";
        $demoReport .= $this->generateRecommendations($analysis);
        
        $demoReport .= "\n## Data Quality Assessment\n";
        $demoReport .= $this->assessDataQuality($analysis);
        
        $demoReport .= "\n---\n";
        $demoReport .= "**💡 Want AI-Powered Insights?**\n";
        $demoReport .= "DeepSeek API costs only ~$0.01-0.05 per report!\n";
        $demoReport .= "1. Visit: https://platform.deepseek.com/\n";
        $demoReport .= "2. Add $5-10 credits (gets you 100+ reports)\n";
        $demoReport .= "3. Get advanced AI analysis, patterns, and insights\n";
        
        return $demoReport;
    }
    
    /**
     * Analyze audit data for statistics
     */
    private function analyzeAuditData($auditData)
    {
        $totalQuestions = 0;
        $answeredQuestions = 0;
        $responseTypes = [];
        
        foreach ($auditData['review_types'] as $reviewType) {
            foreach ($reviewType['sections'] as $section) {
                foreach ($section['questions'] as $question) {
                    $totalQuestions++;
                    if ($question['answer']) {
                        $answeredQuestions++;
                        $type = $question['response_type'];
                        $responseTypes[$type] = ($responseTypes[$type] ?? 0) + 1;
                    }
                }
            }
        }
        
        return [
            'review_type_count' => count($auditData['review_types']),
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'completion_rate' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 1) : 0,
            'response_types' => $responseTypes
        ];
    }
    
    /**
     * Generate key findings based on data analysis
     */
    private function generateKeyFindings($auditData, $analysis)
    {
        $findings = "";
        
        // Completion analysis
        if ($analysis['completion_rate'] >= 90) {
            $findings .= "✅ **Excellent Response Rate**: {$analysis['completion_rate']}% completion indicates thorough audit coverage.\n\n";
        } elseif ($analysis['completion_rate'] >= 70) {
            $findings .= "⚠️ **Good Response Rate**: {$analysis['completion_rate']}% completion is acceptable but could be improved.\n\n";
        } else {
            $findings .= "🔴 **Low Response Rate**: {$analysis['completion_rate']}% completion indicates significant data gaps.\n\n";
        }
        
        // Response type diversity
        $typeCount = count($analysis['response_types']);
        if ($typeCount >= 3) {
            $findings .= "📊 **Diverse Data Collection**: {$typeCount} different response types provide comprehensive insights.\n\n";
        }
        
        // Multi-location analysis
        $locations = [];
        foreach ($auditData['review_types'] as $reviewType) {
            $locations[] = $reviewType['location'];
        }
        $uniqueLocations = array_unique($locations);
        
        if (count($uniqueLocations) > 1) {
            $findings .= "🌍 **Multi-Location Coverage**: Data collected from " . count($uniqueLocations) . " locations enables comparative analysis.\n\n";
        }
        
        return $findings;
    }
    
    /**
     * Generate recommendations based on analysis
     */
    private function generateRecommendations($analysis)
    {
        $recommendations = "";
        
        if ($analysis['completion_rate'] < 80) {
            $recommendations .= "1. **Improve Response Rates**: Focus on completing unanswered questions to reach 80%+ completion.\n";
        }
        
        if ($analysis['completion_rate'] >= 80) {
            $recommendations .= "1. **Data Analysis**: With good completion rates, focus on analyzing patterns and trends.\n";
        }
        
        $recommendations .= "2. **Quality Assurance**: Review responses for consistency and accuracy.\n";
        $recommendations .= "3. **Stakeholder Engagement**: Share preliminary findings with relevant stakeholders.\n";
        $recommendations .= "4. **Action Planning**: Develop specific action plans based on identified issues.\n";
        
        return $recommendations;
    }
    
    /**
     * Assess data quality
     */
    private function assessDataQuality($analysis)
    {
        $assessment = "";
        
        if ($analysis['completion_rate'] >= 90) {
            $assessment .= "**Data Quality: Excellent** - High completion rate enables reliable analysis.\n";
        } elseif ($analysis['completion_rate'] >= 70) {
            $assessment .= "**Data Quality: Good** - Adequate data for most analytical purposes.\n";
        } else {
            $assessment .= "**Data Quality: Needs Improvement** - Consider data collection enhancement.\n";
        }
        
        $assessment .= "\n**Response Distribution:**\n";
        foreach ($analysis['response_types'] as $type => $count) {
            $percentage = round(($count / $analysis['answered_questions']) * 100, 1);
            $assessment .= "- {$type}: {$count} responses ({$percentage}%)\n";
        }
        
        return $assessment;
    }

    /**
     * Save the generated report
     */
    private function saveGeneratedReport($audit, $reportContent, $options)
    {
        // You can implement report saving logic here
        // For now, we'll store it in session or create a reports table
        
        $reportData = [
            'audit_id' => $audit->id,
            'report_type' => $options['report_type'],
            'content' => $reportContent,
            'options' => json_encode($options),
            'generated_at' => now(),
            'generated_by' => auth()->id()
        ];

        // Store in session for now (you might want to create a reports table)
        session(['generated_report_' . $audit->id => $reportData]);

        return 'report_' . $audit->id . '_' . time();
    }
}
