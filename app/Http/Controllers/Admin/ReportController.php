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
        // DISABLE FORCE MODE - User wants detailed reports
        $FORCE_CLOUD_MODE = false; // Disabled to get detailed reports
        
        if ($FORCE_CLOUD_MODE) {
            \Log::emergency('FORCING CLOUD MODE FOR TESTING', [
                'audit_id' => $audit->id,
                'forced_mode' => true
            ]);
        }
        
        // Cloud-optimized settings for Laravel Cloud hosting
        @ini_set('max_execution_time', 180); // 3 minutes for cloud
        @ini_set('memory_limit', '256M'); // Conservative for cloud
        @set_time_limit(180);
        
        // Force performance mode for cloud hosting
        $forcePerformanceMode = true;
        
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

            // Cloud-optimized data collection with performance mode
            \Log::info('Starting cloud-optimized AI report generation', [
                'audit_id' => $audit->id,
                'performance_mode' => $forcePerformanceMode,
                'environment' => app()->environment()
            ]);
            
            $startTime = microtime(true);
            
            // Force performance mode for cloud hosting
            $auditData = $this->collectAuditData($audit, $request->selected_locations ?? [], $forcePerformanceMode);
            
            $dataCollectionTime = round(microtime(true) - $startTime, 2);
            
            // Log what data we collected for debugging
            \Log::info('AI Report Data Collection Completed', [
                'audit_id' => $audit->id,
                'collection_time' => $dataCollectionTime . 's',
                'review_types_count' => count($auditData['review_types_data'] ?? []),
                'total_responses' => $auditData['total_responses'] ?? 0,
                'total_questions' => $auditData['total_questions'] ?? 0,
                'selected_locations' => $request->selected_locations ?? 'all'
            ]);
            
            // Get report type first
            $reportType = $request->get('report_type');
            
            // Smart cloud optimization: Detect environment first
            $isCloudEnvironment = $this->isCloudEnvironment();
            \Log::info('Environment Detection Results', [
                'audit_id' => $audit->id,
                'is_cloud' => $isCloudEnvironment,
                'report_type' => $reportType,
                'detection_indicators' => [
                    'LARAVEL_CLOUD' => env('LARAVEL_CLOUD'),
                    'DYNO' => env('DYNO'),
                    'RAILWAY_ENVIRONMENT' => env('RAILWAY_ENVIRONMENT'),
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
                ]
            ]);
            
            if ($isCloudEnvironment) {
                // Cloud: Allow detailed reports but with cloud-optimized timeouts
                if ($reportType === 'executive_summary') {
                    $useUltraFast = true;  // Ultra-fast for summaries only
                    $cloudTimeout = 25;    // Quick timeout for summaries
                } else {
                    $useUltraFast = false; // Allow detailed mode for other reports
                    $cloudTimeout = match($reportType) {
                        'detailed_analysis' => 120,     // Increased to 120s as requested for full detail
                        'compliance_check' => 75,       // Increased timeout for compliance
                        'comparative_analysis' => 90,   // Increased timeout for comparisons
                        default => 60
                    };
                }
                \Log::info('Cloud environment detected - using cloud-optimized settings', [
                    'audit_id' => $audit->id,
                    'report_type' => $reportType,
                    'timeout' => $cloudTimeout,
                    'ultra_fast' => $useUltraFast,
                    'allows_detailed' => !$useUltraFast
                ]);
            } else {
                // Local: Only use ultra-fast for executive summaries
                $useUltraFast = ($reportType === 'executive_summary');
                $cloudTimeout = match($reportType) {
                    'executive_summary' => 25,      // Fast for summaries
                    'compliance_check' => 40,       // Medium for compliance
                    'comparative_analysis' => 50,   // More time for comparisons
                    'detailed_analysis' => 120,     // Much longer for complex detailed reports
                    default => 25
                };
                \Log::info('Local environment detected - using standard timeouts', [
                    'audit_id' => $audit->id,
                    'report_type' => $reportType,
                    'timeout' => $cloudTimeout,
                    'ultra_fast' => $useUltraFast
                ]);
            }
            
            // Cloud-optimized AI request options with automatic environment detection
            $options = [
                'report_type' => $reportType,
                'include_table_analysis' => $includeTables,
                'include_recommendations' => $includeRecommendations,
                'performance_mode' => $forcePerformanceMode,
                'cloud_environment' => $isCloudEnvironment,
                'max_questions_detail' => $isCloudEnvironment ? 20 : ($reportType === 'detailed_analysis' ? 100 : 50), // Drastically reduce for cloud
                'timeout' => $cloudTimeout,
                'ultra_fast_mode' => $useUltraFast,
            ];
            
            // FORCE OVERRIDE for testing
            if ($FORCE_CLOUD_MODE) {
                $options['cloud_environment'] = true;
                $options['ultra_fast_mode'] = true;
                $options['timeout'] = 15; // Super aggressive for testing
                \Log::emergency('FORCING CLOUD OPTIONS', $options);
            }
            
            \Log::info('Starting AI analysis with environment detection', [
                'audit_id' => $audit->id, 
                'environment' => $isCloudEnvironment ? 'cloud' : 'local',
                'timeout' => $cloudTimeout,
                'ultra_fast_mode' => $useUltraFast,
                'options' => $options
            ]);
            $aiStartTime = microtime(true);
            
            // Generate AI report
            $aiReport = $this->callDeepSeekAI($auditData, $options);
            
            $aiTime = round(microtime(true) - $aiStartTime, 2);
            \Log::info('AI analysis completed', ['audit_id' => $audit->id, 'ai_time' => $aiTime . 's']);
            
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
     * Debug endpoint to test cloud environment detection
     */
    public function debugCloudDetection()
    {
        $isCloud = $this->isCloudEnvironment();
        
        return response()->json([
            'is_cloud_environment' => $isCloud,
            'environment' => app()->environment(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Not Set',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'env_variables_checked' => [
                'LARAVEL_CLOUD' => getenv('LARAVEL_CLOUD') ?: 'Not Set',
                'DYNO' => getenv('DYNO') ?: 'Not Set',
                'RAILWAY_ENVIRONMENT' => getenv('RAILWAY_ENVIRONMENT') ?: 'Not Set',
                'VERCEL' => getenv('VERCEL') ?: 'Not Set',
                'APP_ENV' => getenv('APP_ENV') ?: 'Not Set',
            ],
            'recommended_settings' => $isCloud ? [
                'timeout' => 20,
                'ultra_fast_mode' => true,
                'max_tokens' => 600
            ] : [
                'timeout' => 60,
                'ultra_fast_mode' => false,
                'max_tokens' => 4000
            ]
        ]);
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
     * Collect audit data optimized for cloud hosting
     */
    private function collectAuditData(Audit $audit, array $selectedLocations = [], $performanceMode = false)
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
            'total_questions' => 0, // This will be the sum of questions across all locations
        ];

        // Cloud-optimized data collection with performance mode
        if ($performanceMode) {
            \Log::info('Using performance mode for cloud hosting', ['audit_id' => $audit->id]);
            
            // Lighter query for performance mode
            $attachmentsQuery = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->with(['reviewType', 'responses' => function($query) {
                    $query->limit(50); // Limit responses for cloud performance
                }]);
        } else {
            // Full query for detailed analysis
            $attachmentsQuery = AuditReviewTypeAttachment::where('audit_id', $audit->id)
                ->with(['reviewType.templates.sections.questions', 'responses.question']);
        }
            
        if (!empty($selectedLocations)) {
            $attachmentsQuery->whereIn('id', $selectedLocations);
        }
        
        $attachments = $attachmentsQuery->get();

        // Smart limiting for cloud hosting based on context
        if ($performanceMode) {
            // Allow more attachments for detailed analysis
            $maxAttachments = 3; // More reasonable for cloud
            
            if ($attachments->count() > $maxAttachments) {
                \Log::info('Limiting attachments for cloud performance', [
                    'original_count' => $attachments->count(),
                    'limited_to' => $maxAttachments
                ]);
                $attachments = $attachments->take($maxAttachments);
            }
        }

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
                        'completion_percentage' => 0,
                        'location_total_questions' => 0 // Questions specific to this location
                    ]
                ];

                // Get all templates for this review type (prefer audit-specific, fallback to default)
                $templates = $reviewType->templates()
                    ->where('audit_id', $audit->id) // First try audit-specific templates
                    ->with('sections.questions')
                    ->get();
                
                // If no audit-specific templates found, use default templates
                if ($templates->isEmpty()) {
                    $templates = $reviewType->templates()
                        ->whereNull('audit_id') // Fallback to default templates
                        ->with('sections.questions')
                        ->get();
                }
                
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
                                // Count this question for this location
                                $locationData['response_summary']['location_total_questions']++;
                                $auditData['total_questions']++; // Count for overall total (per location)

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
                            }

                            // Only add section if it has questions
                            if (!empty($sectionData['questions_data'])) {
                                $locationData['sections_data'][] = $sectionData;
                            }
                        }
                    }

                    // Calculate completion percentage based on this location's questions
                    if ($locationData['response_summary']['location_total_questions'] > 0) {
                        $locationData['response_summary']['completion_percentage'] = round(
                            ($locationData['response_summary']['answered_questions'] / $locationData['response_summary']['location_total_questions']) * 100, 1
                        );
                    }

                    $auditData['total_responses'] += $locationData['response_summary']['total_responses'];
                } else {
                    // If no template, try to collect responses directly from the attachment
                    $responses = $attachment->responses()->with('question.section')->get();
                    $locationData['response_summary']['total_responses'] = $responses->count();
                    $auditData['total_responses'] += $responses->count();
                    
                    // Count unique questions for this location
                    $uniqueQuestionIdsForLocation = [];
                    foreach ($responses as $response) {
                        if ($response->question && !in_array($response->question->id, $uniqueQuestionIdsForLocation)) {
                            $uniqueQuestionIdsForLocation[] = $response->question->id;
                            $locationData['response_summary']['location_total_questions']++;
                            $auditData['total_questions']++; // Count for overall total (per location)
                        }
                    }
                    
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
                                $locationData['response_summary']['answered_questions']++;
                            }
                        }
                        
                        $locationData['sections_data'][] = $sectionData;
                    }
                    
                    // Calculate completion percentage for this location
                    if ($locationData['response_summary']['location_total_questions'] > 0) {
                        $locationData['response_summary']['completion_percentage'] = round(
                            ($locationData['response_summary']['answered_questions'] / $locationData['response_summary']['location_total_questions']) * 100, 1
                        );
                    }
                }

                $reviewTypeData['locations'][] = $locationData;
            }

            // Add template information for the templates we actually used (audit-specific or default)
            $usedTemplates = $reviewType->templates()
                ->where('audit_id', $audit->id)
                ->with('sections.questions')
                ->get();
                
            if ($usedTemplates->isEmpty()) {
                $usedTemplates = $reviewType->templates()
                    ->whereNull('audit_id')
                    ->with('sections.questions')
                    ->get();
            }
                
            if ($usedTemplates && $usedTemplates->count() > 0) {
                foreach ($usedTemplates as $template) {
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
     * Format table response for AI analysis with enhanced structure
     */
    private function formatTableResponseForAI($question, $answer, $auditNote)
    {
        if (!$answer) {
            $note = !empty($auditNote) ? " [Audit Note: " . $auditNote . "]" : "";
            return "No table data provided" . $note;
        }

        // Handle JSON string responses
        if (is_string($answer)) {
            $decodedAnswer = json_decode($answer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $answer = $decodedAnswer;
            }
        }

        // Extract table data from the response structure
        $tableData = null;
        if (is_array($answer)) {
            // Check if response has 'value' key (common format)
            if (isset($answer['value']) && is_array($answer['value'])) {
                $tableData = $answer['value'];
            } else {
                // Direct array format
                $tableData = $answer;
            }
        }

        if (!$tableData || empty($tableData)) {
            $note = !empty($auditNote) ? " [Audit Note: " . $auditNote . "]" : "";
            return "Empty table data" . $note;
        }

        $formatted = "\n=== TABLE DATA ANALYSIS ===\n";
        
        // Get table structure and headers from question options
        $tableOptions = $question->options;
        if (is_string($tableOptions)) {
            $tableOptions = json_decode($tableOptions, true);
        }
        
        $headers = [];
        if (isset($tableOptions['headers']) && is_array($tableOptions['headers'])) {
            $headers = $tableOptions['headers'];
        } elseif (isset($tableOptions['rows']) && is_array($tableOptions['rows']) && !empty($tableOptions['rows'])) {
            // Use first row as headers if available
            $headers = $tableOptions['rows'][0] ?? [];
        }

        // If we have headers, show them
        if (!empty($headers)) {
            $formatted .= "TABLE HEADERS: " . implode(' | ', array_filter($headers)) . "\n";
            $formatted .= str_repeat('-', 60) . "\n";
        }

        // Format each row of data with meaningful structure
        $rowCount = 0;
        foreach ($tableData as $rowIndex => $row) {
            if (!is_array($row)) continue;
            
            // Skip empty rows (all cells empty or null)
            $hasData = false;
            foreach ($row as $cell) {
                if (!empty($cell) && $cell !== "" && $cell !== null) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) continue;
            
            $rowCount++;
            $formatted .= "ROW {$rowCount}:\n";
            
            // Map cells to headers if available
            foreach ($row as $cellIndex => $cellValue) {
                if (!empty($cellValue) && $cellValue !== "" && $cellValue !== null) {
                    $headerName = isset($headers[$cellIndex]) && !empty($headers[$cellIndex]) 
                        ? $headers[$cellIndex] 
                        : "Column " . ($cellIndex + 1);
                    $formatted .= "  {$headerName}: {$cellValue}\n";
                }
            }
            $formatted .= "\n";
        }

        // Add summary statistics
        $totalRows = count($tableData);
        $dataRows = $rowCount;
        $formatted .= "TABLE SUMMARY:\n";
        $formatted .= "- Total rows in response: {$totalRows}\n";
        $formatted .= "- Rows with data: {$dataRows}\n";
        $formatted .= "- Completion rate: " . round(($dataRows / max($totalRows, 1)) * 100, 1) . "%\n";

        // Add audit note if present
        if (!empty($auditNote)) {
            $formatted .= "\n[AUDIT NOTE: " . $auditNote . "]\n";
        }

        $formatted .= "=== END TABLE DATA ===\n";

        return $formatted;
    }

    /**
     * Call DeepSeek AI API for report generation with cloud optimization
     */
    private function callDeepSeekAI($auditData, $options)
    {
        $performanceMode = $options['performance_mode'] ?? false;
        $cloudEnvironment = $options['cloud_environment'] ?? $this->isCloudEnvironment();
        
        // Use the configured timeout from the options instead of overriding
        if ($cloudEnvironment) {
            // Use the timeout that was carefully configured based on report type
            $customTimeout = $options['timeout'] ?? 45; // Default fallback for cloud
            \Log::info('Cloud environment confirmed - using configured timeout', [
                'timeout' => $customTimeout,
                'report_type' => $options['report_type'] ?? 'unknown',
                'ultra_fast_mode' => $options['ultra_fast_mode'] ?? false
            ]);
        } else {
            $customTimeout = $options['timeout'] ?? 60;
            \Log::info('Local environment - using standard timeout', [
                'timeout' => $customTimeout
            ]);
        }
        
        // Try multiple ways to get the API key
        $apiKey = config('services.deepseek.api_key') 
                  ?? env('DEEPSEEK_API_KEY') 
                  ?? $_ENV['DEEPSEEK_API_KEY'] 
                  ?? null;
        
        if (!$apiKey || empty($apiKey)) {
            throw new \Exception('DeepSeek API key is not configured. Please add DEEPSEEK_API_KEY to your .env file.');
        }

        // Force ultra-fast mode for cloud environments to ensure completion
        if ($cloudEnvironment) {
            $options['ultra_fast_mode'] = true;
            $options['cloud_environment'] = true;
            \Log::info('Forcing ultra-fast mode for cloud environment', [
                'original_ultra_fast' => $options['ultra_fast_mode'] ?? 'not set',
                'forced_ultra_fast' => true
            ]);
        }

        // Use smart prompt for faster, more intelligent analysis
        $prompt = $this->buildSmartPromptForAI($auditData, $options);
        
        // Log prompt size for monitoring
        $promptSize = strlen($prompt);
        \Log::info('AI Prompt Size', [
            'size_bytes' => $promptSize,
            'size_kb' => round($promptSize / 1024, 2),
            'report_type' => $options['report_type'],
            'performance_mode' => $performanceMode,
            'cloud_environment' => $cloudEnvironment,
            'total_questions' => $auditData['total_questions'],
            'total_responses' => $auditData['total_responses']
        ]);

        try {
            $verifySSL = config('services.deepseek.verify_ssl', false);

            // Use the properly configured timeout from options instead of hardcoded override
            $finalTimeout = $options['timeout'] ?? $customTimeout;
            
            \Log::info('Using configured timeout settings', [
                'cloud_environment' => $cloudEnvironment,
                'configured_timeout_from_options' => $options['timeout'] ?? 'not_set',
                'custom_timeout_fallback' => $customTimeout,
                'final_timeout' => $finalTimeout,
                'ultra_fast_forced' => $options['ultra_fast_mode'] ?? false,
                'report_type' => $options['report_type'] ?? 'unknown'
            ]);
            
            \Log::info('Making DeepSeek API request', [
                'api_endpoint' => 'https://api.deepseek.com/chat/completions',
                'verify_ssl' => $verifySSL,
                'timeout' => $finalTimeout,
                'prompt_size_kb' => round(strlen($prompt) / 1024, 2),
                'cloud_environment' => $cloudEnvironment
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])
            ->withOptions([
                'verify' => $verifySSL,
                'timeout' => $finalTimeout,
                'connect_timeout' => 5, // Very quick connection for cloud
            ])
            ->timeout($finalTimeout)
            ->post('https://api.deepseek.com/v1/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $options['ultra_fast_mode'] ?? false ? 
                            'You are a healthcare audit expert. Provide concise, actionable audit insights. Be brief but comprehensive.' :
                            'You are an expert healthcare auditor and data analyst. Generate intelligent, data-driven audit reports with actionable insights. Focus on patterns, compliance gaps, and practical recommendations based on the provided metrics.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->getCloudOptimizedTokens($options),
                'temperature' => 0.05 // Ultra-low temperature for fastest processing
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Enhanced cloud environment error handling
            if (strpos($errorMessage, 'SSL certificate') !== false) {
                \Log::error('SSL Error in cloud', ['error' => $errorMessage]);
                throw new \Exception('SSL certificate error in cloud environment. Please check network connectivity.');
            }
            
            // Handle timeout errors specifically for cloud
            if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'Operation timed out') !== false || strpos($errorMessage, 'cURL error 28') !== false) {
                \Log::warning('AI API Timeout in Cloud', [
                    'prompt_size_kb' => round(strlen($prompt) / 1024, 2),
                    'timeout_duration' => $finalTimeout . ' seconds',
                    'cloud_environment' => $cloudEnvironment,
                    'performance_mode' => $performanceMode,
                    'report_type' => $options['report_type'],
                    'error' => $errorMessage
                ]);
                
                $timeoutMessage = $cloudEnvironment ? 
                    'Cloud processing timeout (' . $finalTimeout . 's). The audit data is too complex for cloud hosting limits. Try these solutions: 1) Generate an Executive Summary instead (much faster), 2) Select fewer locations to analyze, 3) Use fewer review types. Cloud hosting has strict time limits for processing.' :
                    'Processing timeout (' . $finalTimeout . 's). The audit data is complex and requires more processing time. Try: 1) Generate an Executive Summary instead, 2) Select fewer locations, 3) Reduce the scope of analysis.';
                
                throw new \Exception($timeoutMessage);
            }
            
            // Handle connection errors
            if (strpos($errorMessage, 'cURL error') !== false || strpos($errorMessage, 'Connection') !== false) {
                \Log::error('API Connection Error', [
                    'error' => $errorMessage,
                    'cloud_environment' => $cloudEnvironment,
                    'timeout_used' => $finalTimeout
                ]);
                throw new \Exception('Network connectivity issue with DeepSeek API. Error: ' . $errorMessage . '. Please try again in a moment.');
            }
            
            \Log::error('Cloud AI Processing Error', ['error' => $errorMessage]);
            throw new \Exception('Cloud processing error: ' . $errorMessage);
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
     * Get cloud-optimized token limits based on report type and environment
     */
    private function getCloudOptimizedTokens($options)
    {
        $cloudEnvironment = $options['cloud_environment'] ?? false;
        $ultraFastMode = $options['ultra_fast_mode'] ?? false;
        $reportType = $options['report_type'] ?? 'executive_summary';
        
        if ($ultraFastMode) {
            // Ultra-fast mode: Very aggressive limits for cloud speed
            if ($cloudEnvironment) {
                return 600; // Super minimal for cloud ultra-fast
            }
            return 1000; // Standard ultra-fast
        }
        
        if ($cloudEnvironment) {
            // Increased tokens for cloud to allow comprehensive detailed reports
            return match($reportType) {
                'executive_summary' => 800,      // Keep executive summary short
                'compliance_check' => 2000,      // Increased from 1200
                'comparative_analysis' => 2500,  // Increased from 1500
                'detailed_analysis' => 8000,     // MASSIVELY increased from 2000 to match local detail
                default => 800
            };
        }
        
        // Local/non-cloud defaults
        return 4000;
    }

    /**
     * Build comprehensive transparent AI prompt with full data visibility
     */
    private function buildSmartPromptForAI($auditData, $options)
    {
        $reportType = $options['report_type'];
        $includeRecommendations = $options['include_recommendations'] ?? false;
        $includeTableAnalysis = $options['include_table_analysis'] ?? false;
        $ultraFastMode = $options['ultra_fast_mode'] ?? false;
        $cloudEnvironment = $options['cloud_environment'] ?? false;
        
        // Ultra-fast mode: Minimal prompt for cloud speed
        if ($ultraFastMode) {
            return $this->buildUltraFastPrompt($auditData, $options);
        }
        
        $prompt = "You are an expert healthcare auditor and data analyst. Analyze this audit data and provide intelligent insights with complete transparency, showing all data sources.\n\n";
        
        if ($cloudEnvironment && $reportType === 'detailed_analysis') {
            $prompt .= "**CRITICAL INSTRUCTION FOR CLOUD ENVIRONMENT**: You MUST provide the IDENTICAL detailed analysis format as local environment:\n";
            $prompt .= "- Complete '# COMPREHENSIVE HEALTHCARE AUDIT ANALYSIS REPORT' format\n";
            $prompt .= "- Full '## 1. DATA TRANSPARENCY SECTION' with complete template breakdown table\n";
            $prompt .= "- Comprehensive '## 2. DETAILED SECTION-BY-SECTION ANALYSIS' covering ALL sections\n"; 
            $prompt .= "- Complete '## 3. TABLE DATA ANALYSIS' for all table data\n";
            $prompt .= "- Full '## 4. CRITICAL COMPLIANCE GAPS' analysis\n";
            $prompt .= "- Detailed '## 5. ACTIONABLE RECOMMENDATIONS' with timelines\n";
            $prompt .= "- Complete '## 6. DATA SOURCES ANALYZED' transparency section\n";
            $prompt .= "**DO NOT provide summary-style reports. Provide the FULL detailed transparency format.**\n\n";
        }
        
        // Basic audit context
        $prompt .= "AUDIT CONTEXT:\n";
        $prompt .= "- Audit: {$auditData['audit_info']['name']}\n";
        $prompt .= "- Country: {$auditData['audit_info']['country']}\n";
        $prompt .= "- Total Questions: {$auditData['total_questions']}\n";
        $prompt .= "- Total Responses: {$auditData['total_responses']}\n";
        $prompt .= "- Completion Rate: " . round(($auditData['total_responses'] / max($auditData['total_questions'], 1)) * 100, 1) . "%\n\n";
        
        // For detailed analysis, include COMPLETE data structure with cloud optimization
        if ($reportType === 'detailed_analysis') {
            $prompt .= "COMPLETE AUDIT DATA STRUCTURE FOR FULL ANALYSIS:\n";
            
            // Cloud optimization: For detailed analysis, provide FULL data like local environment
            if ($reportType === 'detailed_analysis') {
                // NO LIMITS for detailed analysis - full transparency required
                $maxQuestionsPerSection = $cloudEnvironment ? 999 : 50;  // Unlimited for detailed reports
                $maxSectionsPerLocation = $cloudEnvironment ? 999 : 100; // Unlimited for detailed reports
            } else {
                // Other report types can have reasonable limits
                $maxQuestionsPerSection = $cloudEnvironment ? 40 : 50;
                $maxSectionsPerLocation = $cloudEnvironment ? 50 : 100;
            }
            
            foreach ($auditData['review_types_data'] as $reviewType) {
                $prompt .= "═══════════════════════════════════════\n";
                $prompt .= "REVIEW TYPE: {$reviewType['review_type_name']}\n";
                $prompt .= "Description: {$reviewType['review_type_description']}\n";
                
                // Show all templates with complete structure
                if (!empty($reviewType['templates'])) {
                    $prompt .= "\nCOMPLETE TEMPLATES STRUCTURE:\n";
                    foreach ($reviewType['templates'] as $template) {
                        $prompt .= "• {$template['template_name']} - {$template['total_sections']} sections, {$template['total_questions']} questions\n";
                    }
                }
                
                foreach ($reviewType['locations'] as $location) {
                    $prompt .= "\n─────────────────────────────────────\n";
                    $prompt .= "LOCATION: {$location['location_name']}\n";
                    $prompt .= "Type: " . ($location['is_master'] ? 'Master Location' : 'Duplicate Location') . "\n";
                    $totalQuestions = $location['response_summary']['location_total_questions'] ?? ($location['response_summary']['answered_questions'] + $location['response_summary']['unanswered_questions']);
                    $prompt .= "Overall Completion: {$location['response_summary']['completion_percentage']}% ({$location['response_summary']['answered_questions']}/{$totalQuestions})\n\n";
                    
                    // Show sections with cloud-optimized detail level
                    $sectionCount = 0;
                    foreach ($location['sections_data'] as $section) {
                        if ($sectionCount >= $maxSectionsPerLocation) {
                            $prompt .= "[Additional sections omitted for cloud efficiency - structure preserved]\n";
                            break;
                        }
                        
                        $prompt .= "TEMPLATE: {$section['template_name']}\n";
                        $prompt .= "SECTION: {$section['section_name']}\n";
                        if (!empty($section['section_description'])) {
                            $prompt .= "Description: {$section['section_description']}\n";
                        }
                        $prompt .= "Order: {$section['section_order']}\n";
                        $prompt .= "Questions in section: " . count($section['questions_data']) . "\n\n";
                        
                        // Show questions with cloud-aware limits but preserve transparency
                        $questionCount = 0;
                        foreach ($section['questions_data'] as $qIndex => $question) {
                            if ($questionCount >= $maxQuestionsPerSection) {
                                $prompt .= "[Additional questions in section omitted for cloud efficiency]\n\n";
                                break;
                            }
                            
                            $prompt .= "Q{$question['order']}: {$question['question_text']}\n";
                            $prompt .= "Type: {$question['response_type']} | Required: " . ($question['is_required'] ? 'Yes' : 'No') . "\n";
                            
                            if (!empty($question['response'])) {
                                // Smart formatting based on response type and environment
                                if ($question['response_type'] === 'table') {
                                    // For tables, show structured summary
                                    $responseText = $question['formatted_answer'] ?? $question['response'];
                                    if (is_array($question['response'])) {
                                        $responseText = "[TABLE DATA - See formatted analysis]";
                                    }
                                    $prompt .= "ANSWER: {$responseText}\n";
                                } else {
                                    // Handle other response types efficiently for cloud
                                    $responseText = $question['formatted_answer'] ?? $question['response'];
                                    if (is_array($responseText)) {
                                        $responseText = implode(', ', $responseText);
                                    }
                                    // Allow full responses for detailed analysis, reasonable limits for others
                                    $maxResponseLength = $cloudEnvironment ? 
                                        ($reportType === 'detailed_analysis' ? 1000 : 400) : 500;
                                    
                                    if (is_string($responseText) && strlen($responseText) > $maxResponseLength) {
                                        $responseText = substr($responseText, 0, $maxResponseLength) . "... [TRUNCATED for " . ($cloudEnvironment ? "cloud efficiency" : "analysis efficiency") . "]";
                                    }
                                    $prompt .= "ANSWER: {$responseText}\n";
                                }
                                
                                if (!empty($question['audit_note'])) {
                                    $prompt .= "AUDIT NOTE: {$question['audit_note']}\n";
                                }
                            } else {
                                $prompt .= "ANSWER: [NO RESPONSE]\n";
                            }
                            $prompt .= "---\n";
                            $questionCount++;
                        }
                        $prompt .= "\n";
                        $sectionCount++;
                    }
                }
            }
            
            // Complete data analysis note with optimization info
            $optimizationNote = $cloudEnvironment ? 
                "[COMPLETE DATA SET with CLOUD OPTIMIZATION: This includes ALL review types, ALL locations, ALL templates, and comprehensive section/question coverage. Data is optimized for cloud processing while preserving complete transparency and audit structure.]" :
                "[COMPLETE DATA SET: This includes ALL review types, ALL locations, ALL templates, ALL sections, and ALL questions with their responses. Table responses are formatted for analysis efficiency while maintaining full data integrity.]";
            $prompt .= "\n{$optimizationNote}\n\n";
        } else {
            // For other report types, use summary with key examples
            $prompt .= "AUDIT DATA SUMMARY:\n";
            foreach ($auditData['review_types_data'] as $reviewType) {
                $prompt .= "REVIEW TYPE: {$reviewType['review_type_name']}\n";
                
                foreach ($reviewType['locations'] as $location) {
                    $totalQuestions = ($location['response_summary']['answered_questions'] ?? 0) + ($location['response_summary']['unanswered_questions'] ?? 0);
                    $prompt .= "• {$location['location_name']}: {$location['response_summary']['completion_percentage']}% complete ({$location['response_summary']['answered_questions']}/{$totalQuestions})\n";
                    
                    // Show a few key questions for context
                    if (!empty($location['sections_data'])) {
                        $questionCount = 0;
                        foreach ($location['sections_data'] as $section) {
                            foreach ($section['questions_data'] as $question) {
                                if ($questionCount >= 3) break 2; // Limit for summary reports
                                
                                $prompt .= "  - {$question['question_text']}: ";
                                if (!empty($question['response'])) {
                                    $responseText = is_array($question['response']) ? implode(', ', $question['response']) : $question['response'];
                                    $prompt .= substr($responseText, 0, 50) . (strlen($responseText) > 50 ? '...' : '') . "\n";
                                } else {
                                    $prompt .= "[No Response]\n";
                                }
                                $questionCount++;
                            }
                        }
                    }
                }
                $prompt .= "\n";
            }
        }
        
        // Analysis instructions for complete transparency including table data
        $prompt .= "\nANALYSIS REQUIREMENTS FOR COMPLETE TRANSPARENCY:\n";
        $prompt .= "Your report MUST include:\n";
        $prompt .= "1. **DATA TRANSPARENCY SECTION**: Show the complete data structure you analyzed:\n";
        $prompt .= "   - List ALL templates with their names and question counts\n";
        $prompt .= "   - List ALL sections within each template\n";
        $prompt .= "   - Show total questions per template and per section\n";
        $prompt .= "   - Display completion rates per location for each template\n";
        $prompt .= "2. **DETAILED FINDINGS**: Reference specific questions by number and text\n";
        $prompt .= "3. **SECTION-BY-SECTION REVIEW**: Analyze each section with its questions and responses\n";
        $prompt .= "4. **TABLE DATA ANALYSIS**: For table-format questions, analyze the structured data:\n";
        $prompt .= "   - Summarize table contents and completion patterns\n";
        $prompt .= "   - Identify missing data in table rows/columns\n";
        $prompt .= "   - Analyze trends and patterns in tabular responses\n";
        $prompt .= "   - Reference specific table headers and row data\n";
        $prompt .= "5. **EVIDENCE-BASED CONCLUSIONS**: Cite exact question numbers and response data\n";
        $prompt .= "6. **ACTIONABLE RECOMMENDATIONS**: Based on specific gaps found in the complete dataset\n\n";
        
        switch ($reportType) {
            case 'executive_summary':
                $prompt .= "EXECUTIVE SUMMARY FORMAT:\n";
                $prompt .= "- Overview of data analyzed (mention templates and sections covered)\n";
                $prompt .= "- Key findings with specific completion percentages\n";
                $prompt .= "- Critical issues with question/section references\n";
                $prompt .= "- Strategic recommendations with data justification\n";
                break;
                
            case 'detailed_analysis':
                $prompt .= "DETAILED ANALYSIS FORMAT:\n";
                if ($cloudEnvironment) {
                    $prompt .= "**IMPORTANT**: Even in cloud environment, provide COMPLETE TRANSPARENCY with full data structure analysis.\n";
                }
                $prompt .= "- **COMPLETE DATA STRUCTURE**: List ALL templates analyzed with their full section breakdown and question counts\n";
                $prompt .= "- **TEMPLATE-BY-TEMPLATE BREAKDOWN**: Show each template's sections and their specific question coverage\n";
                $prompt .= "- **LOCATION-BY-LOCATION ANALYSIS**: Show completion rates per location for each template/section\n";
                $prompt .= "- **SECTION-BY-SECTION REVIEW**: Analyze performance in each section with specific question references\n";
                $prompt .= "- **TABLE DATA ANALYSIS**: For questions with table responses, provide detailed analysis:\n";
                $prompt .= "  * Summarize table structure (headers, rows, data patterns)\n";
                $prompt .= "  * Identify data completeness in table cells\n";
                $prompt .= "  * Analyze trends across table rows/columns\n";
                $prompt .= "  * Highlight critical missing data in tables\n";
                $prompt .= "- **QUESTION ANALYSIS**: Highlight specific questions with poor response rates or concerning answers\n";
                $prompt .= "- **COMPLIANCE GAPS**: Identify specific areas needing attention with exact question numbers and section names\n";
                $prompt .= "- **RECOMMENDATIONS**: Provide actionable steps for each identified issue with template/section references\n";
                if ($cloudEnvironment) {
                    $prompt .= "**CLOUD OPTIMIZATION NOTE**: While data is optimized for cloud processing, maintain complete transparency structure and provide comprehensive analysis of ALL available data.\n";
                }
                break;
                
            case 'compliance_check':
                $prompt .= "COMPLIANCE CHECK FORMAT:\n";
                $prompt .= "- Data coverage assessment (what templates/sections were reviewed)\n";
                $prompt .= "- Compliance scoring based on completion rates and response quality\n";
                $prompt .= "- Non-compliance areas with specific question references\n";
                $prompt .= "- Risk assessment with evidence from responses\n";
                break;
                
            case 'comparative_analysis':
                $prompt .= "COMPARATIVE ANALYSIS FORMAT:\n";
                $prompt .= "- Data comparison framework (what was compared across locations)\n";
                $prompt .= "- Performance differences with specific metrics\n";
                $prompt .= "- Best practices identified from high-performing areas\n";
                $prompt .= "- Standardization needs with specific examples\n";
                break;
        }
        
        $prompt .= "\n**CRITICAL REQUIREMENT**: Your analysis must show COMPLETE TRANSPARENCY of the audit structure including table data. Users need to see:\n";
        $prompt .= "- The exact templates you analyzed (with names and question counts)\n";
        $prompt .= "- All sections within each template (with section names and question counts)\n";
        $prompt .= "- Specific question numbers and text when referencing findings\n";
        $prompt .= "- Response patterns and completion rates per template/section\n";
        $prompt .= "- **TABLE DATA INSIGHTS**: For table-format questions, show:\n";
        $prompt .= "  * Table structure analysis (headers, row/column patterns)\n";
        $prompt .= "  * Data completeness assessment per table\n";
        $prompt .= "  * Specific missing data points in table cells\n";
        $prompt .= "  * Trends and patterns found in tabular data\n";
        $prompt .= "- A comprehensive 'Data Sources' section listing all audit components analyzed\n";
        $prompt .= "\nDo NOT truncate or abbreviate the template/section structure. Show the complete data architecture including table analysis.\n";
        
        return $prompt;
    }

    /**
     * Build ultra-fast prompt for cloud hosting with maximum efficiency
     */
    private function buildUltraFastPrompt($auditData, $options)
    {
        $reportType = $options['report_type'];
        $cloudEnvironment = $options['cloud_environment'] ?? false;
        
        // Minimal prompt for maximum cloud speed
        $prompt = "Healthcare Audit Analysis - Be concise and actionable.\n\n";
        
        // Essential context only
        $prompt .= "AUDIT: {$auditData['audit_info']['name']} ({$auditData['audit_info']['country']})\n";
        $prompt .= "COMPLETION: " . round(($auditData['total_responses'] / max($auditData['total_questions'], 1)) * 100, 1) . "% ({$auditData['total_responses']}/{$auditData['total_questions']})\n\n";
        
        // For cloud environments, use ultra-minimal data
        if ($cloudEnvironment) {
            $prompt .= "REVIEW TYPES: " . count($auditData['review_types_data']) . "\n";
            $totalLocations = 0;
            foreach ($auditData['review_types_data'] as $reviewType) {
                $totalLocations += count($reviewType['locations']);
            }
            $prompt .= "LOCATIONS: {$totalLocations}\n\n";
        } else {
            // Summary data only - no detailed questions
            $prompt .= "LOCATIONS:\n";
            foreach ($auditData['review_types_data'] as $reviewType) {
                $prompt .= "• {$reviewType['review_type_name']}\n";
                foreach ($reviewType['locations'] as $location) {
                    $prompt .= "  - {$location['location_name']}: {$location['response_summary']['completion_percentage']}% complete\n";
                }
            }
        }
        
        // Minimal requirements based on report type
        $prompt .= "GENERATE: ";
        switch ($reportType) {
            case 'executive_summary':
                $prompt .= "Executive summary with key findings and 3 main recommendations.";
                break;
            case 'compliance_check':
                $prompt .= "Compliance status with critical gaps and required actions.";
                break;
            case 'comparative_analysis':
                $prompt .= "Location comparison with performance differences and best practices.";
                break;
            case 'detailed_analysis':
                $prompt .= "Detailed findings by location with specific improvement areas.";
                break;
        }
        
        $prompt .= "\nFormat: Clear headings, bullet points, actionable insights. Keep concise.";
        
        return $prompt;
    }

    /**
     * Build comprehensive AI prompt based on detailed audit data and options (LEGACY - for fallback)
     */
    private function buildDetailedPromptForAI($auditData, $options)
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

    /**
     * Detect if running in cloud environment
     */
    private function isCloudEnvironment()
    {
        // Check for common cloud environment indicators
        $cloudIndicators = [
            // Laravel Cloud specific
            'LARAVEL_CLOUD',
            // Heroku
            'DYNO',
            // Railway
            'RAILWAY_ENVIRONMENT',
            // Vercel
            'VERCEL',
            // AWS Lambda
            'AWS_LAMBDA_FUNCTION_NAME',
            // Google Cloud
            'GOOGLE_CLOUD_PROJECT',
            // Azure
            'WEBSITE_SITE_NAME',
            // Generic cloud indicators
            'CLOUD_PLATFORM',
            'HOSTING_ENVIRONMENT'
        ];
        
        $detectedIndicators = [];
        foreach ($cloudIndicators as $indicator) {
            $value = getenv($indicator) ?: $_ENV[$indicator] ?? null;
            if ($value) {
                $detectedIndicators[$indicator] = $value;
            }
        }
        
        // Additional cloud detection based on common cloud patterns
        $additionalChecks = [
            'document_root_cloud' => strpos($_SERVER['DOCUMENT_ROOT'] ?? '', '/var/www/html') !== false,
            'server_software_cloud' => strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false,
            'cloud_paths' => file_exists('/opt/cloud/') || file_exists('/var/www/html/'),
            'app_env' => (env('APP_ENV') === 'production' && php_sapi_name() !== 'cli'),
        ];
        
        $isCloud = !empty($detectedIndicators) || array_filter($additionalChecks);
        
        // FORCE CLOUD DETECTION - Based on logs showing /var/www/html paths
        if (!$isCloud && strpos($_SERVER['DOCUMENT_ROOT'] ?? '', '/var/www/html') !== false) {
            $isCloud = true;
            \Log::info('FORCED CLOUD DETECTION based on /var/www/html path');
        }
        
        // Enhanced logging for debugging
        \Log::info('Cloud Environment Detection Results', [
            'detected_indicators' => $detectedIndicators,
            'additional_checks' => $additionalChecks,
            'is_cloud_final' => $isCloud,
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'not_set',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'not_set',
            'app_env' => env('APP_ENV'),
            'sapi_name' => php_sapi_name()
        ]);
        
        return $isCloud;
            }
        }
        
        // Check if app is in production and has cloud-like characteristics
        if (app()->environment('production')) {
            // Check for limited execution time (typical cloud constraint)
            $maxExecutionTime = ini_get('max_execution_time');
            if ($maxExecutionTime > 0 && $maxExecutionTime <= 60) {
                return true;
            }
            
            // Check if running under a web server with cloud characteristics
            $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
            if (strpos($serverSoftware, 'nginx') !== false || 
                strpos($serverSoftware, 'Apache') !== false) {
    }
}
