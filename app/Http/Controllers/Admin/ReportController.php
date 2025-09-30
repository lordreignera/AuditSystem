<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Response;
use App\Models\ReviewType;
use App\Models\AuditReviewTypeAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Jobs\GenerateAiReportJob;

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
     * Generate AI-powered report with enhanced data collection
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

            // Collect comprehensive audit data using new method
            $auditData = $this->collectAuditData($audit, $request->selected_locations ?? []);
            
            // Log what data we collected for debugging
            \Log::info('AI Report Data Collection', [
                'audit_id' => $audit->id,
                'audit_name' => $audit->name,
                'review_types_count' => count($auditData['review_types_data'] ?? []),
                'total_responses' => $auditData['total_responses'] ?? 0,
                'total_questions' => $auditData['total_questions'] ?? 0,
                'selected_locations' => $request->selected_locations ?? 'all'
            ]);
            
            // Prepare AI request options
            $options = [
                'report_type' => $request->get('report_type'),
                'include_table_analysis' => $includeTables,
                'include_recommendations' => $includeRecommendations,
            ];
            
            // Generate AI report
            $aiReport = $this->callDeepSeekAI($auditData, $options);
            
            // Save the generated report
            $reportId = $this->saveGeneratedReport($audit, $aiReport, $request->all());

            return response()->json([
                'success' => true,
                'report_id' => $reportId,
                'report_content' => $aiReport,
                'message' => 'Report generated successfully',
                'data_summary' => [
                    'review_types_analyzed' => count($auditData['review_types_data'] ?? []),
                    'total_responses_analyzed' => $auditData['total_responses'] ?? 0,
                    'total_questions_analyzed' => $auditData['total_questions'] ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Report Generation Failed', [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage(),
                'debug_info' => [
                    'audit_id' => $audit->id,
                    'audit_name' => $audit->name,
                    'error_type' => get_class($e)
                ]
            ], 500);
        }
    } 

    /**
     * Debug endpoint to check what data is being collected
     */
    public function debugAuditData(Audit $audit)
    {
        try {
            // Collect all audit data using the enhanced method
            $auditData = $this->collectAuditData($audit, []);
            
            // Get raw database counts for comparison
            $rawCounts = [
                'attachments_in_db' => AuditReviewTypeAttachment::where('audit_id', $audit->id)->count(),
                'responses_in_db' => Response::where('audit_id', $audit->id)->count(),
                'review_types_via_attachments' => AuditReviewTypeAttachment::where('audit_id', $audit->id)
                    ->distinct('review_type_id')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'audit_info' => $auditData['audit_info'],
                'summary' => [
                    'total_review_types' => count($auditData['review_types_data']),
                    'total_responses' => $auditData['total_responses'],
                    'total_questions' => $auditData['total_questions'],
                ],
                'raw_database_counts' => $rawCounts,
                'review_types_preview' => array_map(function($rt) {
                    return [
                        'name' => $rt['review_type_name'],
                        'locations_count' => count($rt['locations']),
                        'templates_count' => count($rt['templates'] ?? []),
                        'templates_preview' => array_slice(array_map(function($template) {
                            return $template['template_name'] . ' (' . $template['total_sections'] . ' sections, ' . $template['total_questions'] . ' questions)';
                        }, $rt['templates'] ?? []), 0, 3),
                        'first_location_preview' => !empty($rt['locations']) ? [
                            'name' => $rt['locations'][0]['location_name'],
                            'completion_rate' => $rt['locations'][0]['response_summary']['completion_percentage'] . '%',
                            'sections_count' => count($rt['locations'][0]['sections_data']),
                            'responses_found' => $rt['locations'][0]['response_summary']['total_responses'],
                            'first_section_preview' => !empty($rt['locations'][0]['sections_data']) ? [
                                'name' => $rt['locations'][0]['sections_data'][0]['section_name'],
                                'template_name' => $rt['locations'][0]['sections_data'][0]['template_name'] ?? 'Unknown Template',
                                'questions_count' => count($rt['locations'][0]['sections_data'][0]['questions_data']),
                                'sample_question' => !empty($rt['locations'][0]['sections_data'][0]['questions_data']) ? 
                                    substr($rt['locations'][0]['sections_data'][0]['questions_data'][0]['question_text'], 0, 100) . '...' : 'No questions',
                                'sample_answer' => !empty($rt['locations'][0]['sections_data'][0]['questions_data']) && 
                                    $rt['locations'][0]['sections_data'][0]['questions_data'][0]['has_response'] ? 
                                    substr($rt['locations'][0]['sections_data'][0]['questions_data'][0]['formatted_answer'], 0, 100) . '...' : 'No answer'
                            ] : 'No sections'
                        ] : 'No locations'
                    ];
                }, $auditData['review_types_data']),
                'data_collection_method' => 'Enhanced method using AuditReviewTypeAttachment directly'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
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
     * Collect comprehensive audit data for AI processing with detailed template structure
     */
    private function collectAuditData(Audit $audit, array $selectedLocations = [])
    {
        $auditData = [
            'audit_info' => [
                'name' => $audit->name,
                'description' => $audit->description,
                'country' => $audit->country->name,
                'start_date' => $audit->start_date->format('Y-m-d'),
                'end_date' => $audit->end_date ? $audit->end_date->format('Y-m-d') : null,
                'review_code' => $audit->review_code,
                'status' => $audit->status,
            ],
            'review_types_data' => [],
            'total_responses' => 0,
            'total_questions' => 0,
        ];

        // Get attachments (locations) directly since audit->reviewTypes might be empty
        $attachmentsQuery = AuditReviewTypeAttachment::where('audit_id', $audit->id)
            ->with(['reviewType.templates.sections.questions', 'responses.question']);
            
        if (!empty($selectedLocations)) {
            $attachmentsQuery->whereIn('id', $selectedLocations);
        }
        
        $attachments = $attachmentsQuery->get();

        // Group attachments by review type
        $reviewTypeGroups = $attachments->groupBy('review_type_id');

        foreach ($reviewTypeGroups as $reviewTypeId => $typeAttachments) {
            $firstAttachment = $typeAttachments->first();
            $reviewType = $firstAttachment->reviewType;
            
            if (!$reviewType) {
                \Log::warning("Attachment {$firstAttachment->id} has no review type");
                continue;
            }

            $reviewTypeData = [
                'review_type_name' => $reviewType->name,
                'review_type_description' => $reviewType->description ?? '',
                'locations' => [],
                'templates' => [],
            ];

            // Process each location (attachment) for this review type
            foreach ($typeAttachments as $attachment) {
                $locationData = [
                    'location_id' => $attachment->id,
                    'location_name' => $attachment->getContextualLocationName(),
                    'is_master' => $attachment->isMaster(),
                    'duplicate_number' => $attachment->duplicate_number,
                    'sections_data' => [],
                    'response_summary' => [
                        'total_responses' => 0,
                        'answered_questions' => 0,
                        'unanswered_questions' => 0,
                        'completion_percentage' => 0
                    ]
                ];

                // Get all templates for this review type (not just the first one)
                $templates = $reviewType->templates()->with('sections.questions')->get();
                
                if ($templates->count() > 0) {
                    // Process each template
                    foreach ($templates as $template) {
                        // Process each section in the template
                        foreach ($template->sections()->with('questions')->orderBy('order')->get() as $section) {
                            $sectionData = [
                                'section_name' => $section->name,
                                'section_description' => $section->description ?? '',
                                'section_order' => $section->order,
                                'template_name' => $template->name,
                                'questions_data' => []
                            ];

                            // Process each question in the section
                            foreach ($section->questions()->orderBy('order')->get() as $question) {
                                // Get response for this question and location
                                $response = Response::where('audit_id', $audit->id)
                                    ->where('attachment_id', $attachment->id)
                                    ->where('question_id', $question->id)
                                    ->first();

                                $questionData = [
                                    'question_id' => $question->id,
                                    'question_text' => $question->question_text,
                                    'response_type' => $question->response_type,
                                    'is_required' => $question->is_required,
                                    'order' => $question->order,
                                    'template_name' => $template->name,
                                    'response' => null,
                                    'audit_note' => null,
                                    'has_response' => false,
                                    'formatted_answer' => null
                                ];

                                if ($response) {
                                    $questionData['response'] = $response->answer;
                                    $questionData['audit_note'] = $response->audit_note;
                                    $questionData['has_response'] = true;
                                    $questionData['formatted_answer'] = $this->formatResponseForAI($question, $response);
                                    
                                    $locationData['response_summary']['total_responses']++;
                                    $locationData['response_summary']['answered_questions']++;
                                } else {
                                    $locationData['response_summary']['unanswered_questions']++;
                                }

                                $sectionData['questions_data'][] = $questionData;
                                $auditData['total_questions']++;
                            }

                            // Only add section if it has questions
                            if (!empty($sectionData['questions_data'])) {
                                $locationData['sections_data'][] = $sectionData;
                            }
                        }
                    }

                    // Calculate completion percentage
                    $totalQuestions = $locationData['response_summary']['answered_questions'] + $locationData['response_summary']['unanswered_questions'];
                    if ($totalQuestions > 0) {
                        $locationData['response_summary']['completion_percentage'] = round(
                            ($locationData['response_summary']['answered_questions'] / $totalQuestions) * 100, 1
                        );
                    }

                    $auditData['total_responses'] += $locationData['response_summary']['total_responses'];
                } else {
                    // If no template, try to collect responses directly from the attachment
                    $responses = $attachment->responses()->with('question.section')->get();
                    $locationData['response_summary']['total_responses'] = $responses->count();
                    $auditData['total_responses'] += $responses->count();
                    
                    // Group responses by section
                    $responsesBySection = $responses->groupBy(function($response) {
                        return $response->question->section ? $response->question->section->name : 'No Section';
                    });
                    
                    foreach ($responsesBySection as $sectionName => $sectionResponses) {
                        $sectionData = [
                            'section_name' => $sectionName,
                            'section_description' => '',
                            'section_order' => 0,
                            'questions_data' => []
                        ];
                        
                        foreach ($sectionResponses as $response) {
                            $question = $response->question;
                            if ($question) {
                                $questionData = [
                                    'question_id' => $question->id,
                                    'question_text' => $question->question_text,
                                    'response_type' => $question->response_type,
                                    'is_required' => $question->is_required,
                                    'order' => $question->order,
                                    'response' => $response->answer,
                                    'audit_note' => $response->audit_note,
                                    'has_response' => true,
                                    'formatted_answer' => $this->formatResponseForAI($question, $response)
                                ];
                                
                                $sectionData['questions_data'][] = $questionData;
                                $auditData['total_questions']++;
                            }
                        }
                        
                        $locationData['sections_data'][] = $sectionData;
                    }
                }

                $reviewTypeData['locations'][] = $locationData;
            }

            // Add template information for all templates
            if ($templates && $templates->count() > 0) {
                foreach ($templates as $template) {
                    $reviewTypeData['templates'][] = [
                        'template_name' => $template->name,
                        'template_description' => $template->description ?? '',
                        'total_sections' => $template->sections()->count(),
                        'total_questions' => $template->questions()->count()
                    ];
                }
            }

            $auditData['review_types_data'][] = $reviewTypeData;
        }

        // Log what we found for debugging
        \Log::info('Data Collection Results', [
            'audit_id' => $audit->id,
            'attachments_found' => $attachments->count(),
            'review_type_groups' => count($reviewTypeGroups),
            'total_responses_collected' => $auditData['total_responses'],
            'total_questions_found' => $auditData['total_questions']
        ]);

        return $auditData;
    }

    /**
     * Format response answer for AI consumption
     */
    private function formatResponseForAI($question, $response)
    {
        $answer = $response->answer;
        $auditNote = $response->audit_note;

        // Handle different response types
        switch ($question->response_type) {
            case 'table':
                return $this->formatTableResponseForAI($question, $answer, $auditNote);
            
            case 'yes_no':
                $formatted = is_array($answer) ? $answer[0] ?? 'No response' : (string) $answer;
                break;
                
            case 'select':
                $formatted = is_array($answer) ? $answer[0] ?? 'No selection' : (string) $answer;
                break;
                
            case 'number':
                $formatted = is_array($answer) ? $answer[0] ?? '0' : (string) $answer;
                break;
                
            case 'date':
                $formatted = is_array($answer) ? $answer[0] ?? 'No date' : (string) $answer;
                break;
                
            case 'text':
            case 'textarea':
            default:
                $formatted = is_array($answer) ? $answer[0] ?? 'No response' : (string) $answer;
                break;
        }

        // Add audit note if present
        if (!empty($auditNote)) {
            $formatted .= " [Audit Note: " . $auditNote . "]";
        }

        return $formatted;
    }

    /**
     * Format table response for AI analysis
     */
    private function formatTableResponseForAI($question, $answer, $auditNote)
    {
        if (!is_array($answer) || empty($answer)) {
            $note = !empty($auditNote) ? " [Audit Note: " . $auditNote . "]" : "";
            return "No table data provided" . $note;
        }

        $formatted = "\nTable Data:\n";
        
        // Get table structure from question
        $tableStructure = $question->parseTableStructure();
        
        if ($tableStructure && !empty($tableStructure[0])) {
            // Use headers from table structure
            $headers = $tableStructure[0];
            $formatted .= "Headers: " . implode(' | ', $headers) . "\n";
            
            // Format each row of data
            $rowIndex = 1;
            foreach ($answer as $row) {
                if (is_array($row) && !empty(array_filter($row))) {
                    $formatted .= "Row {$rowIndex}: ";
                    foreach ($row as $cellIndex => $cellValue) {
                        $header = $headers[$cellIndex] ?? "Col" . ($cellIndex + 1);
                        if (!empty($cellValue)) {
                            $formatted .= "{$header}: {$cellValue} | ";
                        }
                    }
                    $formatted = rtrim($formatted, ' | ') . "\n";
                    $rowIndex++;
                }
            }
        } else {
            // Fallback formatting
            foreach ($answer as $rowIndex => $row) {
                if (is_array($row) && !empty(array_filter($row))) {
                    $formatted .= "Row " . ($rowIndex + 1) . ": " . implode(' | ', array_filter($row)) . "\n";
                }
            }
        }

        // Add audit note if present
        if (!empty($auditNote)) {
            $formatted .= "[Audit Note: " . $auditNote . "]\n";
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

            // Increase timeout to 300 seconds (5 minutes)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])
            ->withOptions([
                'verify' => $verifySSL,
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
     * Build comprehensive AI prompt based on detailed audit data and options
     */
    private function buildPromptForAI($auditData, $options)
    {
        $reportType = $options['report_type'];
        $includeRecommendations = $options['include_recommendations'] ?? false;
        $includeTableAnalysis = $options['include_table_analysis'] ?? false;
        
        $prompt = "Generate a comprehensive {$reportType} report for the following healthcare audit:\n\n";
        
        // Add audit basic info
        $prompt .= "AUDIT INFORMATION:\n";
        $prompt .= "Name: {$auditData['audit_info']['name']}\n";
        $prompt .= "Description: {$auditData['audit_info']['description']}\n";
        $prompt .= "Country: {$auditData['audit_info']['country']}\n";
        $prompt .= "Start Date: {$auditData['audit_info']['start_date']}\n";
        if ($auditData['audit_info']['end_date']) {
            $prompt .= "End Date: {$auditData['audit_info']['end_date']}\n";
        }
        $prompt .= "Review Code: {$auditData['audit_info']['review_code']}\n";
        $prompt .= "Status: {$auditData['audit_info']['status']}\n";
        if (!empty($auditData['audit_info']['participants'])) {
            $prompt .= "Participants: " . implode(', ', $auditData['audit_info']['participants']) . "\n";
        }
        $prompt .= "Total Questions: {$auditData['total_questions']}\n";
        $prompt .= "Total Responses: {$auditData['total_responses']}\n\n";

        // Process each review type with detailed structure
        $prompt .= "DETAILED AUDIT DATA:\n\n";
        foreach ($auditData['review_types_data'] as $reviewTypeIndex => $reviewType) {
            $prompt .= "═══ REVIEW TYPE " . ($reviewTypeIndex + 1) . ": {$reviewType['review_type_name']} ═══\n";
            $prompt .= "Description: {$reviewType['review_type_description']}\n";
            
            // Add template summary
            if (!empty($reviewType['templates'])) {
                $prompt .= "Templates Available: " . count($reviewType['templates']) . "\n";
                foreach ($reviewType['templates'] as $template) {
                    $prompt .= "  - {$template['template_name']}: {$template['total_sections']} sections, {$template['total_questions']} questions\n";
                }
                $prompt .= "\n";
            }

            // Process each location
            foreach ($reviewType['locations'] as $locationIndex => $location) {
                $prompt .= "--- LOCATION " . ($locationIndex + 1) . ": {$location['location_name']} ---\n";
                $prompt .= "Location Type: " . ($location['is_master'] ? 'Master Location' : 'Duplicate Location #' . $location['duplicate_number']) . "\n";
                $prompt .= "Completion Rate: {$location['response_summary']['completion_percentage']}%\n";
                $prompt .= "Answered Questions: {$location['response_summary']['answered_questions']}\n";
                $prompt .= "Unanswered Questions: {$location['response_summary']['unanswered_questions']}\n\n";

                // Group sections by template for better organization
                $templateSections = [];
                foreach ($location['sections_data'] as $section) {
                    $templateName = $section['template_name'] ?? 'Unknown Template';
                    if (!isset($templateSections[$templateName])) {
                        $templateSections[$templateName] = [];
                    }
                    $templateSections[$templateName][] = $section;
                }

                // Process each template's sections
                foreach ($templateSections as $templateName => $sections) {
                    // Find template description
                    $templateObj = null;
                    if (!empty($reviewType['templates'])) {
                        foreach ($reviewType['templates'] as $tpl) {
                            if ($tpl['template_name'] === $templateName) {
                                $templateObj = $tpl;
                                break;
                            }
                        }
                    }
                    $prompt .= "  TEMPLATE: {$templateName}\n";
                    if ($templateObj && !empty($templateObj['template_description'])) {
                        $prompt .= "  Description: {$templateObj['template_description']}\n";
                    }

                    // Check if any question in any section is tabular
                    $isTabular = false;
                    foreach ($sections as $section) {
                        foreach ($section['questions_data'] as $question) {
                            if (($question['response_type'] ?? '') === 'table') {
                                $isTabular = true;
                                break 2;
                            }
                        }
                    }

                    if ($isTabular) {
                        // Check if sections are present or if questions are directly under template
                        $hasSections = !empty($sections) && !empty($sections[0]['section_name']);
                        $prompt .= "  TABULAR RESPONSES:\n";
                        if ($hasSections) {
                            $prompt .= "  | Section | Question | Response |\n";
                            $prompt .= "  |---------|----------|----------|\n";
                            foreach ($sections as $section) {
                                $sectionName = $section['section_name'] ?? '';
                                foreach ($section['questions_data'] as $question) {
                                    if (($question['response_type'] ?? '') === 'table') {
                                        $qText = str_replace(["\n", "|"], [" ", " "], $question['question_text']);
                                        $resp = str_replace(["\n", "|"], [" ", " "], $question['response_value'] ?? '[No Response]');
                                        $prompt .= "  | $sectionName | $qText | $resp |\n";
                                    }
                                }
                            }
                        } else {
                            // No sections, just questions under template
                            $prompt .= "  | Question | Response |\n";
                            $prompt .= "  |----------|----------|\n";
                            foreach ($sections as $section) {
                                foreach ($section['questions_data'] as $question) {
                                    if (($question['response_type'] ?? '') === 'table') {
                                        $qText = str_replace(["\n", "|"], [" ", " "], $question['question_text']);
                                        $resp = str_replace(["\n", "|"], [" ", " "], $question['response_value'] ?? '[No Response]');
                                        $prompt .= "  | $qText | $resp |\n";
                                    }
                                }
                            }
                        }
                        $prompt .= "\n  [Template Summary: Please summarize the tabular responses and findings for this template in this location.]\n\n";
                    } else {
                        // Standard section/question listing
                        foreach ($sections as $section) {
                            $prompt .= "    SECTION: {$section['section_name']}\n";
                            if (!empty($section['section_description'])) {
                                $prompt .= "    Description: {$section['section_description']}\n";
                            }
                            $prompt .= "    Questions and Responses:\n";
                            foreach ($section['questions_data'] as $question) {
                                $prompt .= "      Q{$question['order']}: {$question['question_text']}\n";
                                $resp = isset($question['response_value']) && $question['response_value'] !== null && $question['response_value'] !== '' ? $question['response_value'] : '[No Response]';
                                $prompt .= "      Response: $resp\n";
                            }
                            $prompt .= "    [Section Summary: Please summarize the above responses for this section.]\n\n";
                        }
                        $prompt .= "\n  [Template Summary: Please summarize the responses and findings for this template in this location.]\n\n";
                    }
                }
                $prompt .= "\n";
            }
        }

        // Add specific instructions based on report type
        $prompt .= "\nREPORT REQUIREMENTS:\n";
        switch ($reportType) {
            case 'executive_summary':
                $prompt .= "Create an executive summary focusing on:\n";
                $prompt .= "- Key findings and critical issues\n";
                $prompt .= "- Overall compliance status\n";
                $prompt .= "- Major gaps and strengths\n";
                $prompt .= "- High-level insights and trends\n";
                $prompt .= "- Strategic recommendations\n";
                break;
                
            case 'detailed_analysis':
                $prompt .= "Provide a detailed analysis including:\n";
                $prompt .= "- Section-by-section analysis\n";
                $prompt .= "- Question-by-question findings where significant\n";
                $prompt .= "- Pattern identification across locations\n";
                $prompt .= "- Data quality assessment\n";
                $prompt .= "- Specific compliance issues\n";
                $prompt .= "- Detailed recommendations for each major finding\n";
                break;
                
            case 'compliance_check':
                $prompt .= "Focus on compliance analysis including:\n";
                $prompt .= "- Standards and regulations adherence\n";
                $prompt .= "- Non-compliance areas and severity\n";
                $prompt .= "- Risk assessment for each non-compliance\n";
                $prompt .= "- Required corrective actions\n";
                $prompt .= "- Timeline for compliance achievement\n";
                break;
                
            case 'comparative_analysis':
                $prompt .= "Compare responses across locations focusing on:\n";
                $prompt .= "- Variations between master and duplicate locations\n";
                $prompt .= "- Consistency in implementation\n";
                $prompt .= "- Best performing locations and their practices\n";
                $prompt .= "- Areas needing standardization\n";
                $prompt .= "- Location-specific recommendations\n";
                break;
        }

        if ($includeTableAnalysis) {
            $prompt .= "\nFor table data:\n";
            $prompt .= "- Analyze numerical trends and patterns\n";
            $prompt .= "- Identify data quality issues\n";
            $prompt .= "- Calculate key metrics where appropriate\n";
            $prompt .= "- Highlight significant variances\n";
        }

        if ($includeRecommendations) {
            $prompt .= "\nInclude specific recommendations:\n";
            $prompt .= "- Immediate actions required\n";
            $prompt .= "- Short-term improvements (3-6 months)\n";
            $prompt .= "- Long-term strategic changes (6-12 months)\n";
            $prompt .= "- Resource requirements\n";
            $prompt .= "- Success metrics\n";
        }

        $prompt .= "\nFormat the report professionally with:\n";
        $prompt .= "- Clear headings and subheadings\n";
        $prompt .= "- Executive summary at the beginning\n";
        $prompt .= "- Bullet points for key findings\n";
        $prompt .= "- Tables or lists where appropriate\n";
        $prompt .= "- Actionable insights and recommendations\n";
        $prompt .= "- Professional healthcare audit language\n";

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


    // Save the generated report
    
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
    /**
     * Export the AI-generated report as a PDF using Dompdf
     */
    public function exportAsPDF(Request $request, Audit $audit)
    {
        try {
            // Ensure Dompdf is available
            if (!class_exists('Dompdf\\Dompdf')) {
                \Log::error('PDF export failed: Dompdf not installed');
                abort(500, 'PDF export library not installed.');
            }

            // Prepare report content
            $reportContent = $request->report_content;
            $reportType = ucwords(str_replace('_', ' ', $request->report_type));
            $countryName = $audit->country ? $audit->country->name : 'Unknown';
            // Fix: Support both arrays and collections for participants
            if (!empty($audit->participants)) {
                if (is_array($audit->participants)) {
                    $participants = implode(', ', array_map(function($p) {
                        return is_object($p) ? $p->name : (isset($p['name']) ? $p['name'] : '');
                    }, $audit->participants));
                } else {
                    $participants = $audit->participants->pluck('name')->join(', ');
                }
            } else {
                $participants = 'N/A';
            }

            // Log incoming request for debugging
            \Log::info('PDF Export Request', [
                'audit_id' => $audit->id,
                'report_type' => $reportType,
                'country' => $countryName,
                'participants' => $participants,
                'report_content_length' => strlen($reportContent),
            ]);

            // Build HTML for PDF
            $html = view('admin.reports.pdf', [
                'audit' => $audit,
                'countryName' => $countryName,
                'reportType' => $reportType,
                'participants' => $participants,
                'reportContent' => $reportContent,
                'generatedAt' => now()->format('M j, Y H:i:s'),
            ])->render();

            // Log HTML length for debugging
            \Log::info('PDF Export HTML Length', ['length' => strlen($html)]);

            // Generate PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Audit_Report_' . str_replace([' ', '/', '\\'], '_', $audit->name) . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            \Log::info('PDF Export Success', ['filename' => $filename]);

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            \Log::error('PDF export failed', [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
