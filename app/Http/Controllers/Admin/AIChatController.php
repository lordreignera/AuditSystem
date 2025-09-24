<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Response;
use App\Models\ReviewType;
use App\Models\AuditReviewTypeAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIChatController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view reports']);
    }

    /**
     * Display the AI Chat interface for an audit
     */
    public function show(Audit $audit)
    {
        $audit->load([
            'attachedReviewTypes.reviewType',
            'responses.question.section.template',
            'responses.user'
        ]);

        // Get audit statistics for context
        $auditStats = $this->getAuditStatistics($audit);
        
        return view('admin.ai-chat.show', compact('audit', 'auditStats'));
    }

    /**
     * Handle AI chat messages
     */
    public function chat(Request $request, Audit $audit)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'context_type' => 'in:general,tables,charts,analysis,recommendations'
        ]);

        try {
            // Collect comprehensive audit data for context
            $auditData = $this->collectAuditData($audit);
            
            // Check if this is a quick action request and generate immediate insights
            $quickResponse = $this->generateQuickInsight($request->message, $auditData, $audit);
            
            if ($quickResponse) {
                return response()->json([
                    'success' => true,
                    'response' => $quickResponse,
                    'context_type' => $request->context_type,
                    'has_data' => true
                ]);
            }
            
            // Build AI prompt based on message and context
            $prompt = $this->buildChatPrompt($request->message, $request->context_type, $auditData);
            
            // Call AI API
            $response = $this->callDeepSeekAI($prompt, $request->context_type);
            
            return response()->json([
                'success' => true,
                'response' => $response,
                'context_type' => $request->context_type,
                'has_data' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('AI Chat Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to process your request. Please try again.',
                'fallback' => $this->generateFallbackResponse($request->message, $request->context_type, $audit)
            ], 500);
        }
    }

    /**
     * Generate chart data based on AI analysis
     */
    public function generateChart(Request $request, Audit $audit)
    {
        $request->validate([
            'chart_type' => 'required|in:bar,line,pie,doughnut,area',
            'data_focus' => 'required|string',
            'question_ids' => 'array'
        ]);

        try {
            // Collect specific data based on request - AUDIT SPECIFIC
            $chartData = $this->collectChartData($audit, $request->data_focus, $request->question_ids);
            
            // Use AI to generate intelligent chart configuration
            $chartConfig = $this->generateChartConfig($chartData, $request->chart_type, $request->data_focus);
            
            return response()->json([
                'success' => true,
                'chart_data' => $chartData,
                'chart_config' => $chartConfig,
                'chart_type' => $request->chart_type,
                'audit_context' => [
                    'audit_id' => $audit->id,
                    'audit_name' => $audit->name,
                    'country' => $audit->country->name
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Chart Generation Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to generate chart. Please try again.'
            ], 500);
        }
    }

    /**
     * Export chat conversation with charts and tables to PDF
     */
    public function exportToPdf(Request $request, Audit $audit)
    {
        $request->validate([
            'conversation' => 'required|array',
            'charts' => 'array',
            'tables' => 'array'
        ]);

        try {
            // Generate PDF with conversation, charts, and tables
            $pdf = $this->generatePdfReport($audit, $request->conversation, $request->charts ?? [], $request->tables ?? []);
            
            $filename = 'AI_Chat_Report_' . $audit->name . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf;
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to export PDF. Please try again.'
            ], 500);
        }
    }

    /**
     * Export to Word document
     */
    public function exportToWord(Request $request, Audit $audit)
    {
        $request->validate([
            'conversation' => 'required|array',
            'charts' => 'array',
            'tables' => 'array'
        ]);

        try {
            // Generate Word document
            $wordDocument = $this->generateWordReport($audit, $request->conversation, $request->charts ?? [], $request->tables ?? []);
            
            $filename = 'AI_Chat_Report_' . $audit->name . '_' . now()->format('Y-m-d_H-i-s') . '.docx';
            
            return response()->streamDownload(function() use ($wordDocument) {
                $wordDocument->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Word Export Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to export Word document. Please try again.'
            ], 500);
        }
    }

    /**
     * Compare templates and responses across different review types
     */
    public function compareTemplates(Request $request, Audit $audit)
    {
        $request->validate([
            'template_ids' => 'array',
            'comparison_type' => 'in:completion_rates,response_patterns,question_analysis'
        ]);

        try {
            $comparison = $this->performTemplateComparison($audit, $request->template_ids ?? [], $request->comparison_type ?? 'completion_rates');
            
            return response()->json([
                'success' => true,
                'comparison_data' => $comparison,
                'audit_context' => [
                    'audit_id' => $audit->id,
                    'audit_name' => $audit->name
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Template Comparison Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to perform template comparison. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate table data based on AI analysis
     */
    public function generateTable(Request $request, Audit $audit)
    {
        $request->validate([
            'table_focus' => 'required|string',
            'columns' => 'array',
            'question_ids' => 'array'
        ]);

        try {
            // Collect data for table
            $tableData = $this->collectTableData($audit, $request->table_focus, $request->question_ids);
            
            // Use AI to structure and analyze the table
            $structuredTable = $this->generateTableStructure($tableData, $request->table_focus, $request->columns);
            
            return response()->json([
                'success' => true,
                'table_data' => $structuredTable,
                'table_focus' => $request->table_focus
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Table Generation Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to generate table. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate quick insights based on actual audit data
     */
    private function generateQuickInsight($message, $auditData, $audit)
    {
        $message = strtolower($message);
        
        // Check for specific quick action patterns
        if (strpos($message, 'summary') !== false && strpos($message, 'response') !== false) {
            return $this->generateResponseSummaryInsight($auditData, $audit);
        }
        
        if (strpos($message, 'progress') !== false && strpos($message, 'chart') !== false) {
            return $this->generateProgressInsight($auditData, $audit);
        }
        
        if (strpos($message, 'key findings') !== false || strpos($message, 'recommendations') !== false) {
            return $this->generateKeyFindingsInsight($auditData, $audit);
        }
        
        if (strpos($message, 'pattern') !== false) {
            return $this->generatePatternAnalysisInsight($auditData, $audit);
        }
        
        if (strpos($message, 'compliance') !== false) {
            return $this->generateComplianceInsight($auditData, $audit);
        }
        
        if (strpos($message, 'template') !== false && strpos($message, 'comparison') !== false) {
            return $this->generateTemplateComparisonInsight($auditData, $audit);
        }
        
        return null; // Let AI handle if no quick pattern match
    }

    /**
     * Generate response summary insight based on audit structure
     */
    private function generateResponseSummaryInsight($auditData, $audit)
    {
        $reviewTypeStructure = $this->analyzeAuditStructure($auditData);
        $totalResponses = collect($auditData['review_types'])->sum('responses_count');
        
        $insight = "## Audit Response Summary for {$audit->name}\n\n";
        $insight .= "**📊 Audit Completion Overview:**\n";
        $insight .= "- Total Responses: {$totalResponses}\n";
        $insight .= "- Review Types Progress: {$reviewTypeStructure['completed_types']}/4 types completed\n";
        $insight .= "- Overall Audit Status: " . $this->getAuditCompletionStatus($reviewTypeStructure) . "\n\n";
        
        $insight .= "**🏥 Review Type Breakdown:**\n";
        
        // Health Review Type
        if (isset($reviewTypeStructure['health'])) {
            $healthData = $reviewTypeStructure['health'];
            $insight .= "- **Health Review:** {$healthData['locations']} location(s), avg {$healthData['avg_completion']}% complete\n";
            foreach ($healthData['details'] as $location) {
                $status = $this->getLocationStatus($location['completion_rate']);
                $insight .= "  {$status} {$location['location']}: {$location['completion_rate']}% ({$location['responses']} responses)\n";
            }
        } else {
            $insight .= "- **Health Review:** ❌ Not started - Required for audit completion\n";
        }
        
        // District Review Type
        if (isset($reviewTypeStructure['district'])) {
            $districtData = $reviewTypeStructure['district'];
            $insight .= "- **District Review:** {$districtData['locations']} location(s), avg {$districtData['avg_completion']}% complete\n";
            foreach ($districtData['details'] as $location) {
                $status = $this->getLocationStatus($location['completion_rate']);
                $insight .= "  {$status} {$location['location']}: {$location['completion_rate']}% ({$location['responses']} responses)\n";
            }
        } else {
            $insight .= "- **District Review:** ❌ Not started - Required for audit completion\n";
        }
        
        // Province/Region Review Type
        if (isset($reviewTypeStructure['province']) || isset($reviewTypeStructure['region'])) {
            $provinceData = $reviewTypeStructure['province'] ?? $reviewTypeStructure['region'];
            $typeName = isset($reviewTypeStructure['province']) ? 'Province' : 'Region';
            $insight .= "- **{$typeName} Review:** {$provinceData['locations']} location(s), avg {$provinceData['avg_completion']}% complete\n";
            foreach ($provinceData['details'] as $location) {
                $status = $this->getLocationStatus($location['completion_rate']);
                $insight .= "  {$status} {$location['location']}: {$location['completion_rate']}% ({$location['responses']} responses)\n";
            }
        } else {
            $insight .= "- **Province/Region Review:** ❌ Not started - Required for audit completion\n";
        }
        
        // National Review Type
        if (isset($reviewTypeStructure['national'])) {
            $nationalData = $reviewTypeStructure['national'];
            $status = $this->getLocationStatus($nationalData['completion_rate']);
            $insight .= "- **National Review (Master):** {$status} {$nationalData['completion_rate']}% ({$nationalData['responses']} responses)\n";
        } else {
            $insight .= "- **National Review (Master):** ❌ Not started - Required for audit completion\n";
        }
        
        $insight .= "\n**🎯 Action Items:**\n";
        $insight .= $this->generateActionItems($reviewTypeStructure);
        
        return $insight;
    }

    /**
     * Analyze audit structure based on review types
     */
    private function analyzeAuditStructure($auditData)
    {
        $structure = [
            'completed_types' => 0,
            'total_types' => 4,
            'missing_types' => []
        ];
        
        $reviewTypes = collect($auditData['review_types']);
        
        // Group by review type name (case insensitive)
        $groupedTypes = $reviewTypes->groupBy(function($item) {
            $name = strtolower($item['name']);
            if (strpos($name, 'health') !== false) return 'health';
            if (strpos($name, 'district') !== false) return 'district';
            if (strpos($name, 'province') !== false) return 'province';
            if (strpos($name, 'region') !== false) return 'region';
            if (strpos($name, 'national') !== false) return 'national';
            return 'other';
        });
        
        // Analyze each required review type
        foreach (['health', 'district', 'province', 'region', 'national'] as $type) {
            if ($groupedTypes->has($type)) {
                $typeData = $groupedTypes[$type];
                
                if ($type === 'national') {
                    // National should have only one entry
                    $national = $typeData->first();
                    $structure['national'] = [
                        'completion_rate' => round($national['completion_rate'], 1),
                        'responses' => $national['responses_count'],
                        'location' => $national['location']
                    ];
                } else {
                    // Other types can have multiple locations
                    $structure[$type] = [
                        'locations' => $typeData->count(),
                        'avg_completion' => round($typeData->avg('completion_rate'), 1),
                        'total_responses' => $typeData->sum('responses_count'),
                        'details' => $typeData->map(function($item) {
                            return [
                                'location' => $item['location'],
                                'completion_rate' => round($item['completion_rate'], 1),
                                'responses' => $item['responses_count']
                            ];
                        })->values()->toArray()
                    ];
                }
                
                // Count as completed if average completion > 70%
                $avgCompletion = $type === 'national' ? 
                    $structure['national']['completion_rate'] : 
                    $structure[$type]['avg_completion'];
                    
                if ($avgCompletion > 70) {
                    $structure['completed_types']++;
                }
            } else {
                if (!in_array($type, ['province', 'region']) || 
                    (!$groupedTypes->has('province') && !$groupedTypes->has('region'))) {
                    $structure['missing_types'][] = ucfirst($type);
                }
            }
        }
        
        // Handle province/region as one requirement
        if ($groupedTypes->has('province') || $groupedTypes->has('region')) {
            // Already counted above
        } else {
            $structure['missing_types'][] = 'Province/Region';
        }
        
        return $structure;
    }

    /**
     * Get audit completion status
     */
    private function getAuditCompletionStatus($structure)
    {
        $completedTypes = $structure['completed_types'];
        
        if ($completedTypes == 4) {
            return "✅ Complete - All review types finished";
        } elseif ($completedTypes >= 3) {
            return "🟡 Nearly Complete - " . (4 - $completedTypes) . " type(s) remaining";
        } elseif ($completedTypes >= 2) {
            return "🟠 In Progress - " . (4 - $completedTypes) . " type(s) remaining";
        } elseif ($completedTypes >= 1) {
            return "🔶 Started - " . (4 - $completedTypes) . " type(s) remaining";
        } else {
            return "🔴 Not Started - All 4 review types required";
        }
    }

    /**
     * Get location status indicator
     */
    private function getLocationStatus($completionRate)
    {
        if ($completionRate >= 90) return "🟢";
        if ($completionRate >= 70) return "🟡";
        if ($completionRate >= 50) return "🟠";
        return "🔴";
    }

    /**
     * Generate action items based on audit structure
     */
    private function generateActionItems($structure)
    {
        $actions = [];
        
        if (!empty($structure['missing_types'])) {
            $actions[] = "**CRITICAL:** Start missing review types: " . implode(', ', $structure['missing_types']);
        }
        
        // Check for low completion rates
        foreach (['health', 'district', 'province', 'region'] as $type) {
            if (isset($structure[$type])) {
                $lowCompletionLocations = collect($structure[$type]['details'])
                    ->filter(function($location) { return $location['completion_rate'] < 70; });
                
                if ($lowCompletionLocations->count() > 0) {
                    $locations = $lowCompletionLocations->pluck('location')->join(', ');
                    $actions[] = "**" . ucfirst($type) . ":** Follow up on low completion locations: {$locations}";
                }
            }
        }
        
        // Check national review
        if (isset($structure['national']) && $structure['national']['completion_rate'] < 70) {
            $actions[] = "**National:** Complete master review (currently {$structure['national']['completion_rate']}%)";
        }
        
        if (empty($actions)) {
            $actions[] = "✅ All review types are progressing well";
            $actions[] = "📈 Focus on completing remaining responses to reach 100%";
        }
        
        return "1. " . implode("\n2. ", $actions);
    }

    /**
     * Generate progress insight based on audit structure
     */
    private function generateProgressInsight($auditData, $audit)
    {
        $reviewTypeStructure = $this->analyzeAuditStructure($auditData);
        
        $insight = "## Audit Progress Analysis for {$audit->name}\n\n";
        
        $insight .= "**🎯 Audit Completion Progress:**\n";
        $insight .= "- Review Types Completed: {$reviewTypeStructure['completed_types']}/4 (";
        $insight .= round(($reviewTypeStructure['completed_types'] / 4) * 100, 1) . "%)\n";
        $insight .= "- Overall Status: " . $this->getAuditCompletionStatus($reviewTypeStructure) . "\n\n";
        
        $insight .= "**📈 Review Type Progress:**\n";
        
        // Health Review Progress
        if (isset($reviewTypeStructure['health'])) {
            $health = $reviewTypeStructure['health'];
            $insight .= "- **🏥 Health Review:** {$health['avg_completion']}% avg completion across {$health['locations']} location(s)\n";
            $bestHealth = collect($health['details'])->sortByDesc('completion_rate')->first();
            $worstHealth = collect($health['details'])->sortBy('completion_rate')->first();
            if ($bestHealth && $worstHealth && count($health['details']) > 1) {
                $insight .= "  Best: {$bestHealth['location']} ({$bestHealth['completion_rate']}%) | Worst: {$worstHealth['location']} ({$worstHealth['completion_rate']}%)\n";
            }
        } else {
            $insight .= "- **🏥 Health Review:** ❌ Not started\n";
        }
        
        // District Review Progress
        if (isset($reviewTypeStructure['district'])) {
            $district = $reviewTypeStructure['district'];
            $insight .= "- **🏢 District Review:** {$district['avg_completion']}% avg completion across {$district['locations']} location(s)\n";
            $bestDistrict = collect($district['details'])->sortByDesc('completion_rate')->first();
            $worstDistrict = collect($district['details'])->sortBy('completion_rate')->first();
            if ($bestDistrict && $worstDistrict && count($district['details']) > 1) {
                $insight .= "  Best: {$bestDistrict['location']} ({$bestDistrict['completion_rate']}%) | Worst: {$worstDistrict['location']} ({$worstDistrict['completion_rate']}%)\n";
            }
        } else {
            $insight .= "- **🏢 District Review:** ❌ Not started\n";
        }
        
        // Province/Region Review Progress
        if (isset($reviewTypeStructure['province']) || isset($reviewTypeStructure['region'])) {
            $provincial = $reviewTypeStructure['province'] ?? $reviewTypeStructure['region'];
            $typeName = isset($reviewTypeStructure['province']) ? 'Province' : 'Region';
            $insight .= "- **🗺️ {$typeName} Review:** {$provincial['avg_completion']}% avg completion across {$provincial['locations']} location(s)\n";
            $bestProvincial = collect($provincial['details'])->sortByDesc('completion_rate')->first();
            $worstProvincial = collect($provincial['details'])->sortBy('completion_rate')->first();
            if ($bestProvincial && $worstProvincial && count($provincial['details']) > 1) {
                $insight .= "  Best: {$bestProvincial['location']} ({$bestProvincial['completion_rate']}%) | Worst: {$worstProvincial['location']} ({$worstProvincial['completion_rate']}%)\n";
            }
        } else {
            $insight .= "- **�️ Province/Region Review:** ❌ Not started\n";
        }
        
        // National Review Progress
        if (isset($reviewTypeStructure['national'])) {
            $national = $reviewTypeStructure['national'];
            $status = $this->getLocationStatus($national['completion_rate']);
            $insight .= "- **🏛️ National Review (Master):** {$status} {$national['completion_rate']}% completion\n";
        } else {
            $insight .= "- **🏛️ National Review (Master):** ❌ Not started\n";
        }
        
        $insight .= "\n**📊 Progress Priorities:**\n";
        
        // Determine next priorities
        $priorities = [];
        if (!empty($reviewTypeStructure['missing_types'])) {
            $priorities[] = "**HIGH:** Start " . implode(', ', $reviewTypeStructure['missing_types']) . " review type(s)";
        }
        
        // Find lowest performing areas
        $allLocations = [];
        foreach (['health', 'district', 'province', 'region'] as $type) {
            if (isset($reviewTypeStructure[$type])) {
                foreach ($reviewTypeStructure[$type]['details'] as $detail) {
                    $allLocations[] = [
                        'type' => ucfirst($type),
                        'location' => $detail['location'],
                        'completion' => $detail['completion_rate']
                    ];
                }
            }
        }
        
        if (isset($reviewTypeStructure['national'])) {
            $allLocations[] = [
                'type' => 'National',
                'location' => 'Master',
                'completion' => $reviewTypeStructure['national']['completion_rate']
            ];
        }
        
        $lowPerformers = collect($allLocations)->filter(function($loc) { 
            return $loc['completion'] < 70; 
        })->sortBy('completion');
        
        if ($lowPerformers->count() > 0) {
            $priorities[] = "**MEDIUM:** Focus on low completion areas:";
            foreach ($lowPerformers->take(3) as $performer) {
                $priorities[] = "  - {$performer['type']}: {$performer['location']} ({$performer['completion']}%)";
            }
        }
        
        if (empty($priorities)) {
            $priorities[] = "✅ All review types are progressing well";
            $priorities[] = "🎯 Continue current pace to achieve 100% completion";
        }
        
        $insight .= implode("\n", $priorities);
        
        return $insight;
    }

    /**
     * Generate key findings insight
     */
    private function generateKeyFindingsInsight($auditData, $audit)
    {
        $insight = "## Key Findings & Recommendations for {$audit->name}\n\n";
        
        $completionRates = collect($auditData['review_types'])->pluck('completion_rate');
        $avgCompletion = $completionRates->avg();
        $variation = $completionRates->max() - $completionRates->min();
        
        $insight .= "**🔍 Key Findings:**\n";
        
        if ($variation > 30) {
            $insight .= "- **High Variation:** Completion rates vary significantly between locations ({$variation}% difference)\n";
        }
        
        if ($avgCompletion < 60) {
            $insight .= "- **Incomplete Coverage:** Overall completion rate ({$avgCompletion}%) suggests systematic issues\n";
        }
        
        // Analyze sample responses for patterns
        if (!empty($auditData['response_summary'])) {
            $sampleResponses = collect($auditData['response_summary']);
            $emptyResponses = $sampleResponses->filter(function($response) {
                return empty($response['answer']) || $response['answer'] === 'No response provided';
            })->count();
            
            if ($emptyResponses > 0) {
                $insight .= "- **Missing Responses:** {$emptyResponses} empty responses found in sample data\n";
            }
        }
        
        $insight .= "\n**💡 Recommendations:**\n";
        $insight .= "1. **Priority Focus:** Target locations with completion rates below 70%\n";
        $insight .= "2. **Process Review:** Investigate reasons for incomplete responses\n";
        $insight .= "3. **Follow-up Actions:** Schedule additional review sessions for incomplete areas\n";
        $insight .= "4. **Quality Check:** Review responses for completeness and accuracy\n";
        
        return $insight;
    }

    /**
     * Generate pattern analysis insight
     */
    private function generatePatternAnalysisInsight($auditData, $audit)
    {
        $insight = "## Data Pattern Analysis for {$audit->name}\n\n";
        
        $insight .= "**🔍 Identified Patterns:**\n";
        
        // Analyze completion patterns
        $completionRates = collect($auditData['review_types'])->pluck('completion_rate');
        $highPerformers = $completionRates->filter(function($rate) { return $rate >= 80; })->count();
        $lowPerformers = $completionRates->filter(function($rate) { return $rate < 50; })->count();
        
        if ($highPerformers > 0) {
            $insight .= "- **Strong Performance Pattern:** {$highPerformers} location(s) with excellent completion rates (80%+)\n";
        }
        
        if ($lowPerformers > 0) {
            $insight .= "- **Performance Gap Pattern:** {$lowPerformers} location(s) with low completion rates (<50%)\n";
        }
        
        // Analyze template usage patterns
        $templateUsage = collect($auditData['templates']);
        if ($templateUsage->count() > 1) {
            $insight .= "- **Template Diversity:** Using " . $templateUsage->count() . " different templates across locations\n";
        }
        
        // Response type patterns from sample data
        if (!empty($auditData['response_summary'])) {
            $insight .= "\n**📝 Response Patterns:**\n";
            $responseTypes = collect($auditData['response_summary'])->groupBy('template');
            foreach ($responseTypes as $template => $responses) {
                $insight .= "- **{$template}:** " . $responses->count() . " sample responses analyzed\n";
            }
        }
        
        $insight .= "\n**🎯 Pattern Insights:**\n";
        $insight .= "- Consistent high performers likely have better processes or training\n";
        $insight .= "- Low performers may need additional support or clarification\n";
        $insight .= "- Template complexity may affect completion rates\n";
        
        return $insight;
    }

    /**
     * Generate compliance insight based on audit structure
     */
    private function generateComplianceInsight($auditData, $audit)
    {
        $reviewTypeStructure = $this->analyzeAuditStructure($auditData);
        
        $insight = "## Compliance Analysis for {$audit->name}\n\n";
        
        $auditCompletionPercentage = round(($reviewTypeStructure['completed_types'] / 4) * 100, 1);
        
        $insight .= "**⚖️ Audit Compliance Status:**\n";
        $insight .= "- **Overall Audit Compliance:** {$auditCompletionPercentage}% ({$reviewTypeStructure['completed_types']}/4 required review types)\n";
        
        if ($auditCompletionPercentage >= 100) {
            $insight .= "- **Status:** ✅ **FULLY COMPLIANT** - All required review types completed\n";
        } elseif ($auditCompletionPercentage >= 75) {
            $insight .= "- **Status:** 🟡 **SUBSTANTIALLY COMPLIANT** - Minor gaps remaining\n";
        } elseif ($auditCompletionPercentage >= 50) {
            $insight .= "- **Status:** 🟠 **PARTIALLY COMPLIANT** - Significant work required\n";
        } else {
            $insight .= "- **Status:** 🔴 **NON-COMPLIANT** - Major audit requirements missing\n";
        }
        
        $insight .= "\n**📋 Review Type Compliance:**\n";
        
        // Health Review Compliance
        $healthCompliant = isset($reviewTypeStructure['health']) && $reviewTypeStructure['health']['avg_completion'] >= 70;
        $insight .= "- **🏥 Health Review:** " . ($healthCompliant ? "✅ Compliant" : "❌ Non-Compliant");
        if (isset($reviewTypeStructure['health'])) {
            $insight .= " ({$reviewTypeStructure['health']['avg_completion']}% avg across {$reviewTypeStructure['health']['locations']} location(s))";
        } else {
            $insight .= " (Not started)";
        }
        $insight .= "\n";
        
        // District Review Compliance
        $districtCompliant = isset($reviewTypeStructure['district']) && $reviewTypeStructure['district']['avg_completion'] >= 70;
        $insight .= "- **🏢 District Review:** " . ($districtCompliant ? "✅ Compliant" : "❌ Non-Compliant");
        if (isset($reviewTypeStructure['district'])) {
            $insight .= " ({$reviewTypeStructure['district']['avg_completion']}% avg across {$reviewTypeStructure['district']['locations']} location(s))";
        } else {
            $insight .= " (Not started)";
        }
        $insight .= "\n";
        
        // Province/Region Review Compliance
        $provincialCompliant = (isset($reviewTypeStructure['province']) && $reviewTypeStructure['province']['avg_completion'] >= 70) ||
                              (isset($reviewTypeStructure['region']) && $reviewTypeStructure['region']['avg_completion'] >= 70);
        $provincialData = $reviewTypeStructure['province'] ?? $reviewTypeStructure['region'] ?? null;
        $provincialType = isset($reviewTypeStructure['province']) ? 'Province' : 'Region';
        
        $insight .= "- **🗺️ {$provincialType} Review:** " . ($provincialCompliant ? "✅ Compliant" : "❌ Non-Compliant");
        if ($provincialData) {
            $insight .= " ({$provincialData['avg_completion']}% avg across {$provincialData['locations']} location(s))";
        } else {
            $insight .= " (Not started)";
        }
        $insight .= "\n";
        
        // National Review Compliance
        $nationalCompliant = isset($reviewTypeStructure['national']) && $reviewTypeStructure['national']['completion_rate'] >= 70;
        $insight .= "- **🏛️ National Review (Master):** " . ($nationalCompliant ? "✅ Compliant" : "❌ Non-Compliant");
        if (isset($reviewTypeStructure['national'])) {
            $insight .= " ({$reviewTypeStructure['national']['completion_rate']}% completion)";
        } else {
            $insight .= " (Not started)";
        }
        $insight .= "\n";
        
        $insight .= "\n**🔴 Critical Compliance Issues:**\n";
        $criticalIssues = [];
        
        if (!empty($reviewTypeStructure['missing_types'])) {
            $criticalIssues[] = "**MISSING REVIEW TYPES:** " . implode(', ', $reviewTypeStructure['missing_types']) . " - Required for audit completion";
        }
        
        // Check for low compliance in existing types
        foreach (['health', 'district', 'province', 'region'] as $type) {
            if (isset($reviewTypeStructure[$type])) {
                $lowComplianceLocations = collect($reviewTypeStructure[$type]['details'])
                    ->filter(function($location) { return $location['completion_rate'] < 50; });
                
                if ($lowComplianceLocations->count() > 0) {
                    $locations = $lowComplianceLocations->pluck('location')->join(', ');
                    $criticalIssues[] = "**" . strtoupper($type) . " REVIEW:** Critical compliance gaps in: {$locations}";
                }
            }
        }
        
        if (isset($reviewTypeStructure['national']) && $reviewTypeStructure['national']['completion_rate'] < 50) {
            $criticalIssues[] = "**NATIONAL REVIEW:** Master review severely incomplete ({$reviewTypeStructure['national']['completion_rate']}%)";
        }
        
        if (empty($criticalIssues)) {
            $insight .= "- No critical compliance issues identified\n";
        } else {
            foreach ($criticalIssues as $issue) {
                $insight .= "- {$issue}\n";
            }
        }
        
        $insight .= "\n**✅ Compliance Action Plan:**\n";
        $actions = [];
        
        if (!empty($reviewTypeStructure['missing_types'])) {
            $actions[] = "**IMMEDIATE:** Initialize missing review types: " . implode(', ', $reviewTypeStructure['missing_types']);
        }
        
        $actions[] = "**PRIORITY 1:** Achieve minimum 70% completion in all review types";
        $actions[] = "**PRIORITY 2:** Focus on locations/types below 50% completion";
        $actions[] = "**PRIORITY 3:** Document and verify all responses for audit trail";
        $actions[] = "**FINAL STEP:** Complete all 4 review types to 100% for full compliance";
        
        $insight .= "1. " . implode("\n2. ", $actions);
        
        return $insight;
    }

    /**
     * Generate template comparison insight
     */
    private function generateTemplateComparisonInsight($auditData, $audit)
    {
        $insight = "## Template Comparison Analysis for {$audit->name}\n\n";
        
        $templates = collect($auditData['templates']);
        $reviewTypes = collect($auditData['review_types']);
        
        $insight .= "**📋 Template Overview:**\n";
        foreach ($templates as $templateId => $template) {
            $insight .= "- **{$template['name']}**: {$template['questions_count']} questions, {$template['sections_count']} sections\n";
            $insight .= "  Used in: " . implode(', ', $template['locations']) . "\n";
        }
        
        $insight .= "\n**📊 Performance Comparison:**\n";
        $templatePerformance = $reviewTypes->groupBy('name')->map(function($types) {
            return [
                'avg_completion' => $types->avg('completion_rate'),
                'locations' => $types->pluck('location')->unique()->count(),
                'total_responses' => $types->sum('responses_count')
            ];
        });
        
        foreach ($templatePerformance as $templateName => $performance) {
            $insight .= "- **{$templateName}**: {$performance['avg_completion']}% avg completion across {$performance['locations']} location(s)\n";
        }
        
        $insight .= "\n**🔍 Comparison Insights:**\n";
        $bestTemplate = $templatePerformance->sortByDesc('avg_completion')->first();
        $insight .= "- Highest performing template structure appears to be more user-friendly\n";
        $insight .= "- Consider standardizing successful template features across all locations\n";
        $insight .= "- Review template complexity versus completion rates for optimization opportunities\n";
        
        return $insight;
    }
    private function collectAuditData(Audit $audit)
    {
        $data = [
            'audit_info' => [
                'id' => $audit->id,
                'name' => $audit->name,
                'country' => $audit->country->name,
                'start_date' => $audit->start_date->format('Y-m-d'),
                'end_date' => $audit->end_date?->format('Y-m-d'),
                'description' => $audit->description
            ],
            'review_types' => [],
            'response_summary' => [],
            'templates' => []
        ];

        foreach ($audit->attachedReviewTypes as $attachment) {
            $reviewType = $attachment->reviewType;
            
            // Get responses SPECIFIC to this audit and attachment
            $responses = Response::where('audit_id', $audit->id)
                ->where('attachment_id', $attachment->id)
                ->with(['question.section.template', 'user'])
                ->get();

            $data['review_types'][] = [
                'id' => $reviewType->id,
                'name' => $reviewType->name,
                'location' => $attachment->location_name,
                'attachment_id' => $attachment->id,
                'responses_count' => $responses->count(),
                'completion_rate' => $this->calculateCompletionRate($responses)
            ];

            // Add template information for comparison
            foreach ($reviewType->templates as $template) {
                if (!isset($data['templates'][$template->id])) {
                    $data['templates'][$template->id] = [
                        'name' => $template->name,
                        'sections_count' => $template->sections->count(),
                        'questions_count' => $template->sections->sum(function($section) {
                            return $section->questions->count();
                        }),
                        'locations' => []
                    ];
                }
                $data['templates'][$template->id]['locations'][] = $attachment->location_name;
            }

            // Add sample responses for context (AUDIT SPECIFIC)
            foreach ($responses->take(5) as $response) {
                $data['response_summary'][] = [
                    'question_id' => $response->question->id,
                    'question' => $response->question->question_text,
                    'answer' => $this->formatResponseForAI($response),
                    'location' => $attachment->location_name,
                    'template' => $response->question->section->template->name,
                    'section' => $response->question->section->title
                ];
            }
        }

        return $data;
    }

    /**
     * Generate PDF report with conversation, charts, and tables
     */
    private function generatePdfReport($audit, $conversation, $charts, $tables)
    {
        // Using TCPDF or similar library
        require_once(app_path('Libraries/tcpdf/tcpdf.php'));
        
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Audit System AI Chat');
        $pdf->SetAuthor('AI Assistant');
        $pdf->SetTitle('AI Chat Report - ' . $audit->name);
        $pdf->SetSubject('Audit Analysis Report');
        
        // Set default font
        $pdf->SetFont('helvetica', '', 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Header
        $html = '<h1 style="color: #2c3e50;">AI Chat Analysis Report</h1>';
        $html .= '<h2 style="color: #34495e;">Audit: ' . $audit->name . '</h2>';
        $html .= '<p><strong>Country:</strong> ' . $audit->country->name . '</p>';
        $html .= '<p><strong>Date Range:</strong> ' . $audit->start_date->format('M j, Y');
        if ($audit->end_date) {
            $html .= ' - ' . $audit->end_date->format('M j, Y');
        }
        $html .= '</p>';
        $html .= '<p><strong>Generated:</strong> ' . now()->format('M j, Y H:i:s') . '</p>';
        $html .= '<hr>';
        
        // Conversation
        $html .= '<h3 style="color: #2980b9;">Chat Conversation</h3>';
        foreach ($conversation as $message) {
            $sender = $message['sender'] ?? 'Unknown';
            $content = $message['content'] ?? '';
            $timestamp = $message['timestamp'] ?? now()->format('H:i:s');
            
            if ($sender === 'user') {
                $html .= '<div style="margin: 10px 0; padding: 8px; background-color: #ecf0f1; border-left: 3px solid #3498db;">';
                $html .= '<strong>You (' . $timestamp . '):</strong><br>' . htmlspecialchars($content);
                $html .= '</div>';
            } else {
                $html .= '<div style="margin: 10px 0; padding: 8px; background-color: #e8f5e8; border-left: 3px solid #27ae60;">';
                $html .= '<strong>AI Assistant (' . $timestamp . '):</strong><br>' . htmlspecialchars($content);
                $html .= '</div>';
            }
        }
        
        // Tables
        if (!empty($tables)) {
            $html .= '<h3 style="color: #e67e22;">Generated Tables</h3>';
            foreach ($tables as $table) {
                $html .= '<h4>' . ($table['title'] ?? 'Data Table') . '</h4>';
                $html .= '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%;">';
                
                if (isset($table['headers'])) {
                    $html .= '<thead><tr style="background-color: #34495e; color: white;">';
                    foreach ($table['headers'] as $header) {
                        $html .= '<th>' . htmlspecialchars($header) . '</th>';
                    }
                    $html .= '</tr></thead>';
                }
                
                if (isset($table['rows'])) {
                    $html .= '<tbody>';
                    foreach ($table['rows'] as $row) {
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</tbody>';
                }
                
                $html .= '</table><br>';
            }
        }
        
        // Charts (as text descriptions for now, can be enhanced with image generation)
        if (!empty($charts)) {
            $html .= '<h3 style="color: #8e44ad;">Generated Charts</h3>';
            foreach ($charts as $chart) {
                $html .= '<h4>' . ($chart['title'] ?? 'Chart') . '</h4>';
                $html .= '<p><strong>Type:</strong> ' . ($chart['type'] ?? 'Unknown') . '</p>';
                if (isset($chart['description'])) {
                    $html .= '<p>' . htmlspecialchars($chart['description']) . '</p>';
                }
                $html .= '<hr>';
            }
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        return $pdf->Output('', 'S'); // Return as string
    }

    /**
     * Generate Word document
     */
    private function generateWordReport($audit, $conversation, $charts, $tables)
    {
        // Using PhpWord library
        require_once(app_path('Libraries/PhpWord/autoload.php'));
        
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Add section
        $section = $phpWord->addSection();
        
        // Title
        $section->addTitle('AI Chat Analysis Report', 1);
        $section->addTitle('Audit: ' . $audit->name, 2);
        
        // Audit info
        $section->addText('Country: ' . $audit->country->name, ['bold' => true]);
        $section->addText('Date Range: ' . $audit->start_date->format('M j, Y') . 
                         ($audit->end_date ? ' - ' . $audit->end_date->format('M j, Y') : ''));
        $section->addText('Generated: ' . now()->format('M j, Y H:i:s'));
        $section->addTextBreak(2);
        
        // Conversation
        $section->addTitle('Chat Conversation', 2);
        foreach ($conversation as $message) {
            $sender = $message['sender'] ?? 'Unknown';
            $content = $message['content'] ?? '';
            $timestamp = $message['timestamp'] ?? now()->format('H:i:s');
            
            if ($sender === 'user') {
                $section->addText('You (' . $timestamp . '):', ['bold' => true, 'color' => '2980b9']);
                $section->addText($content);
            } else {
                $section->addText('AI Assistant (' . $timestamp . '):', ['bold' => true, 'color' => '27ae60']);
                $section->addText($content);
            }
            $section->addTextBreak();
        }
        
        // Tables
        if (!empty($tables)) {
            $section->addTitle('Generated Tables', 2);
            foreach ($tables as $tableData) {
                $section->addTitle($tableData['title'] ?? 'Data Table', 3);
                
                if (isset($tableData['headers']) && isset($tableData['rows'])) {
                    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '006699']);
                    
                    // Add header row
                    $table->addRow();
                    foreach ($tableData['headers'] as $header) {
                        $table->addCell(2000)->addText($header, ['bold' => true]);
                    }
                    
                    // Add data rows
                    foreach ($tableData['rows'] as $row) {
                        $table->addRow();
                        foreach ($row as $cell) {
                            $table->addCell(2000)->addText($cell);
                        }
                    }
                }
                $section->addTextBreak();
            }
        }
        
        return $phpWord;
    }

    /**
     * Perform template comparison across different review types
     */
    private function performTemplateComparison($audit, $templateIds, $comparisonType)
    {
        $comparison = [
            'audit_info' => [
                'id' => $audit->id,
                'name' => $audit->name,
                'country' => $audit->country->name
            ],
            'templates' => [],
            'comparison_type' => $comparisonType,
            'summary' => []
        ];

        // Get all templates for this audit
        $auditTemplates = [];
        foreach ($audit->attachedReviewTypes as $attachment) {
            foreach ($attachment->reviewType->templates as $template) {
                if (empty($templateIds) || in_array($template->id, $templateIds)) {
                    $auditTemplates[$template->id] = $template;
                }
            }
        }

        // Analyze each template
        foreach ($auditTemplates as $template) {
            $templateData = [
                'id' => $template->id,
                'name' => $template->name,
                'sections' => [],
                'statistics' => []
            ];

            // Get responses for this template in this audit
            $templateResponses = Response::where('audit_id', $audit->id)
                ->whereHas('question.section.template', function($q) use ($template) {
                    $q->where('id', $template->id);
                })
                ->with(['question.section', 'attachment'])
                ->get();

            // Analyze by sections
            foreach ($template->sections as $section) {
                $sectionResponses = $templateResponses->filter(function($response) use ($section) {
                    return $response->question->section_id === $section->id;
                });

                $sectionData = [
                    'id' => $section->id,
                    'title' => $section->title,
                    'questions_count' => $section->questions->count(),
                    'responses_count' => $sectionResponses->count(),
                    'completion_rate' => $this->calculateCompletionRate($sectionResponses),
                    'locations' => $sectionResponses->pluck('attachment.location_name')->unique()->values()
                ];

                // Response pattern analysis
                if ($comparisonType === 'response_patterns') {
                    $sectionData['response_patterns'] = [
                        'text_responses' => $sectionResponses->where('answer_text', '!=', null)->where('answer_text', '!=', '')->count(),
                        'boolean_responses' => $sectionResponses->where('answer_boolean', '!=', null)->count(),
                        'table_responses' => $sectionResponses->where('answer_table', '!=', null)->where('answer_table', '!=', '')->count(),
                        'empty_responses' => $sectionResponses->filter(function($response) {
                            return empty($response->answer_text) && is_null($response->answer_boolean) && empty($response->answer_table);
                        })->count()
                    ];
                }

                $templateData['sections'][] = $sectionData;
            }

            // Template-level statistics
            $templateData['statistics'] = [
                'total_questions' => $template->sections->sum(function($section) {
                    return $section->questions->count();
                }),
                'total_responses' => $templateResponses->count(),
                'overall_completion_rate' => $this->calculateCompletionRate($templateResponses),
                'locations_using' => $templateResponses->pluck('attachment.location_name')->unique()->count(),
                'last_response' => $templateResponses->max('updated_at')
            ];

            $comparison['templates'][] = $templateData;
        }

        // Generate comparison summary
        $comparison['summary'] = $this->generateComparisonSummary($comparison['templates'], $comparisonType);

        return $comparison;
    }

    /**
     * Generate comparison summary
     */
    private function generateComparisonSummary($templates, $comparisonType)
    {
        $summary = [
            'total_templates' => count($templates),
            'highest_completion' => 0,
            'lowest_completion' => 100,
            'average_completion' => 0,
            'insights' => []
        ];

        $completionRates = [];
        foreach ($templates as $template) {
            $rate = $template['statistics']['overall_completion_rate'];
            $completionRates[] = $rate;
            
            if ($rate > $summary['highest_completion']) {
                $summary['highest_completion'] = $rate;
                $summary['best_template'] = $template['name'];
            }
            
            if ($rate < $summary['lowest_completion']) {
                $summary['lowest_completion'] = $rate;
                $summary['needs_attention'] = $template['name'];
            }
        }

        $summary['average_completion'] = count($completionRates) > 0 ? 
            round(array_sum($completionRates) / count($completionRates), 2) : 0;

        // Generate insights
        if ($summary['highest_completion'] - $summary['lowest_completion'] > 30) {
            $summary['insights'][] = "Significant variation in completion rates between templates (difference: " . 
                round($summary['highest_completion'] - $summary['lowest_completion'], 1) . "%)";
        }

        if ($summary['average_completion'] < 50) {
            $summary['insights'][] = "Overall completion rate is below 50% - consider reviewing template complexity";
        }

        return $summary;
    }

    /**
     * Build AI prompt for chat interaction
     */
    private function buildChatPrompt($message, $contextType, $auditData)
    {
        $systemPrompt = "You are an AI assistant specialized in audit data analysis and reporting. ";
        
        switch ($contextType) {
            case 'tables':
                $systemPrompt .= "Focus on creating comprehensive tables that organize audit data effectively. Provide structured data analysis with clear columns and rows.";
                break;
            case 'charts':
                $systemPrompt .= "Focus on recommending appropriate chart types and data visualizations. Suggest specific chart configurations and data groupings.";
                break;
            case 'analysis':
                $systemPrompt .= "Provide deep analytical insights, identify patterns, trends, and correlations in the audit data.";
                break;
            case 'recommendations':
                $systemPrompt .= "Focus on actionable recommendations and improvement suggestions based on audit findings.";
                break;
            default:
                $systemPrompt .= "Provide helpful responses about audit data, analysis, and reporting.";
        }

        $prompt = $systemPrompt . "\n\n";
        $prompt .= "Audit Context:\n";
        $prompt .= "- Audit Name: " . $auditData['audit_info']['name'] . "\n";
        $prompt .= "- Country: " . $auditData['audit_info']['country'] . "\n";
        $prompt .= "- Review Types: " . count($auditData['review_types']) . "\n";
        $prompt .= "- Total Responses: " . collect($auditData['review_types'])->sum('responses_count') . "\n\n";

        if (!empty($auditData['response_summary'])) {
            $prompt .= "Sample Audit Data:\n";
            foreach (array_slice($auditData['response_summary'], 0, 3) as $sample) {
                $prompt .= "- Q: " . substr($sample['question'], 0, 100) . "\n";
                $prompt .= "  A: " . substr($sample['answer'], 0, 150) . "\n";
                $prompt .= "  Location: " . $sample['location'] . "\n\n";
            }
        }

        $prompt .= "User Question: " . $message . "\n\n";
        $prompt .= "Please provide a helpful, specific response based on the audit context above.";

        return $prompt;
    }

    /**
     * Call DeepSeek AI API
     */
    private function callDeepSeekAI($prompt, $contextType = 'general')
    {
        $apiKey = config('services.deepseek.api_key') ?? env('DEEPSEEK_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('DeepSeek API key not configured');
        }

        $maxTokens = $contextType === 'tables' ? 3000 : 2000;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])
        ->timeout(60)
        ->post('https://api.deepseek.com/v1/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert audit data analyst and AI assistant.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.3
        ]);

        if (!$response->successful()) {
            throw new \Exception('AI API request failed');
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? 'No response generated';
    }

    /**
     * Generate fallback response when AI fails
     */
    private function generateFallbackResponse($message, $contextType, $audit)
    {
        $responses = [
            'general' => "I understand you're asking about the audit data. While I'm temporarily unable to access the AI service, I can tell you that this audit '{$audit->name}' contains valuable information that can be analyzed in various ways.",
            'tables' => "To create tables from your audit data, consider organizing responses by location, question category, or completion status. The system has tools to export and structure this data effectively.",
            'charts' => "For visualizing your audit data, consider using bar charts for completion rates, pie charts for response distributions, or line charts for trends over time. The dashboard includes Chart.js capabilities for this.",
            'analysis' => "Your audit '{$audit->name}' contains important insights. Consider reviewing completion rates, identifying pattern in responses, and comparing data across different locations or time periods.",
            'recommendations' => "Based on typical audit best practices, focus on areas with low completion rates, inconsistent responses across locations, and any identified compliance gaps."
        ];

        return $responses[$contextType] ?? $responses['general'];
    }

    /**
     * Collect data for chart generation
     */
    private function collectChartData($audit, $dataFocus, $questionIds = [])
    {
        $chartData = [
            'labels' => [],
            'datasets' => [],
            'statistics' => []
        ];

        switch ($dataFocus) {
            case 'completion_rates':
                $chartData = $this->getCompletionRatesData($audit);
                break;
                
            case 'location_comparison':
                $chartData = $this->getLocationComparisonData($audit);
                break;
                
            case 'response_distribution':
                $chartData = $this->getResponseDistributionData($audit);
                break;
                
            case 'analysis_based':
                $chartData = $this->getAnalysisBasedData($audit);
                break;
                
            default:
                $chartData = $this->getCompletionRatesData($audit);
        }

        return $chartData;
    }

    /**
     * Get completion rates data for charts based on audit structure
     */
    private function getCompletionRatesData($audit)
    {
        $auditData = $this->collectAuditData($audit);
        $reviewTypeStructure = $this->analyzeAuditStructure($auditData);
        
        $reviewTypes = [];
        $completionRates = [];
        $colors = [
            'rgba(75, 192, 192, 0.8)',   // Health - Teal
            'rgba(54, 162, 235, 0.8)',   // District - Blue  
            'rgba(255, 206, 86, 0.8)',   // Province/Region - Yellow
            'rgba(153, 102, 255, 0.8)'   // National - Purple
        ];
        
        // Health Review Type
        if (isset($reviewTypeStructure['health'])) {
            $reviewTypes[] = '🏥 Health Review';
            $completionRates[] = $reviewTypeStructure['health']['avg_completion'];
        } else {
            $reviewTypes[] = '🏥 Health Review (Missing)';
            $completionRates[] = 0;
        }
        
        // District Review Type
        if (isset($reviewTypeStructure['district'])) {
            $reviewTypes[] = '🏢 District Review';
            $completionRates[] = $reviewTypeStructure['district']['avg_completion'];
        } else {
            $reviewTypes[] = '🏢 District Review (Missing)';
            $completionRates[] = 0;
        }
        
        // Province/Region Review Type
        if (isset($reviewTypeStructure['province']) || isset($reviewTypeStructure['region'])) {
            $provincialData = $reviewTypeStructure['province'] ?? $reviewTypeStructure['region'];
            $typeName = isset($reviewTypeStructure['province']) ? 'Province' : 'Region';
            $reviewTypes[] = "🗺️ {$typeName} Review";
            $completionRates[] = $provincialData['avg_completion'];
        } else {
            $reviewTypes[] = '🗺️ Province/Region Review (Missing)';
            $completionRates[] = 0;
        }
        
        // National Review Type
        if (isset($reviewTypeStructure['national'])) {
            $reviewTypes[] = '🏛️ National Review (Master)';
            $completionRates[] = $reviewTypeStructure['national']['completion_rate'];
        } else {
            $reviewTypes[] = '🏛️ National Review (Missing)';
            $completionRates[] = 0;
        }

        return [
            'labels' => $reviewTypes,
            'datasets' => [
                [
                    'label' => 'Completion Rate (%)',
                    'data' => $completionRates,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('0.8', '1', $color);
                    }, $colors),
                    'borderWidth' => 2
                ]
            ],
            'statistics' => [
                'review_types_completed' => $reviewTypeStructure['completed_types'],
                'total_review_types' => 4,
                'audit_completion_percentage' => round(($reviewTypeStructure['completed_types'] / 4) * 100, 1),
                'average_completion' => count($completionRates) > 0 ? round(array_sum($completionRates) / count($completionRates), 2) : 0,
                'highest_completion' => count($completionRates) > 0 ? max($completionRates) : 0,
                'lowest_completion' => count($completionRates) > 0 ? min($completionRates) : 0
            ]
        ];
    }

    /**
     * Get location comparison data
     */
    private function getLocationComparisonData($audit)
    {
        $locations = [];
        $responseCounts = [];
        $colors = [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 205, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)'
        ];

        $locationData = $audit->attachedReviewTypes
            ->groupBy('location_name')
            ->map(function ($attachments, $location) use ($audit) {
                $totalResponses = 0;
                foreach ($attachments as $attachment) {
                    $totalResponses += Response::where('audit_id', $audit->id)
                        ->where('attachment_id', $attachment->id)
                        ->count();
                }
                return $totalResponses;
            });

        $colorIndex = 0;
        foreach ($locationData as $location => $count) {
            $locations[] = $location;
            $responseCounts[] = $count;
        }

        return [
            'labels' => $locations,
            'datasets' => [
                [
                    'label' => 'Responses by Location',
                    'data' => $responseCounts,
                    'backgroundColor' => array_slice($colors, 0, count($locations)),
                    'borderWidth' => 2
                ]
            ],
            'statistics' => [
                'total_locations' => count($locations),
                'total_responses' => array_sum($responseCounts),
                'average_per_location' => count($responseCounts) > 0 ? round(array_sum($responseCounts) / count($responseCounts), 2) : 0
            ]
        ];
    }

    /**
     * Get response distribution data
     */
    private function getResponseDistributionData($audit)
    {
        $responses = Response::where('audit_id', $audit->id)->get();
        
        $textResponses = $responses->where('answer_text', '!=', null)->where('answer_text', '!=', '')->count();
        $booleanResponses = $responses->where('answer_boolean', '!=', null)->count();
        $tableResponses = $responses->where('answer_table', '!=', null)->where('answer_table', '!=', '')->count();
        $emptyResponses = $responses->count() - ($textResponses + $booleanResponses + $tableResponses);

        return [
            'labels' => ['Text Responses', 'Yes/No Responses', 'Table Responses', 'Empty Responses'],
            'datasets' => [
                [
                    'label' => 'Response Types',
                    'data' => [$textResponses, $booleanResponses, $tableResponses, $emptyResponses],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 205, 86, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    'borderWidth' => 2
                ]
            ],
            'statistics' => [
                'total_responses' => $responses->count(),
                'completion_rate' => $responses->count() > 0 ? round((($responses->count() - $emptyResponses) / $responses->count()) * 100, 2) : 0
            ]
        ];
    }

    /**
     * Get analysis-based chart data
     */
    private function getAnalysisBasedData($audit)
    {
        // For now, return completion rates data
        // This could be enhanced based on specific analysis requirements
        return $this->getCompletionRatesData($audit);
    }

    /**
     * Generate chart configuration using AI
     */
    private function generateChartConfig($data, $chartType, $focus)
    {
        $title = $this->getChartTitle($focus);
        
        $config = [
            'type' => $chartType,
            'data' => $data,
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => $title,
                        'font' => [
                            'size' => 16,
                            'weight' => 'bold'
                        ]
                    ],
                    'legend' => [
                        'display' => true,
                        'position' => 'top'
                    ]
                ],
                'scales' => $this->getScalesConfig($chartType, $focus)
            ]
        ];

        // Add specific options based on chart type
        if ($chartType === 'pie' || $chartType === 'doughnut') {
            unset($config['options']['scales']);
            $config['options']['plugins']['legend']['position'] = 'right';
        }

        return $config;
    }

    /**
     * Get chart title based on focus
     */
    private function getChartTitle($focus)
    {
        $titles = [
            'completion_rates' => 'Audit Completion Rates by Location',
            'location_comparison' => 'Response Distribution by Location',
            'response_distribution' => 'Response Type Distribution',
            'analysis_based' => 'Audit Data Analysis',
            'default' => 'Audit Data Visualization'
        ];

        return $titles[$focus] ?? $titles['default'];
    }

    /**
     * Get scales configuration for chart
     */
    private function getScalesConfig($chartType, $focus)
    {
        if ($chartType === 'pie' || $chartType === 'doughnut') {
            return null;
        }

        $config = [
            'y' => [
                'beginAtZero' => true,
                'grid' => [
                    'color' => 'rgba(200, 200, 200, 0.3)'
                ]
            ],
            'x' => [
                'grid' => [
                    'color' => 'rgba(200, 200, 200, 0.3)'
                ],
                'ticks' => [
                    'maxRotation' => 45
                ]
            ]
        ];

        // Add specific configurations based on focus
        if ($focus === 'completion_rates') {
            $config['y']['max'] = 100;
            $config['y']['title'] = [
                'display' => true,
                'text' => 'Completion Percentage'
            ];
        }

        return $config;
    }

    /**
     * Collect data for table generation
     */
    private function collectTableData($audit, $tableFocus, $questionIds = [])
    {
        switch ($tableFocus) {
            case 'response_summary':
                return $this->getResponseSummaryTable($audit);
                
            case 'completion_status':
                return $this->getCompletionStatusTable($audit);
                
            case 'location_overview':
                return $this->getLocationOverviewTable($audit);
                
            case 'analysis_based':
                return $this->getAnalysisBasedTable($audit);
                
            default:
                return $this->getResponseSummaryTable($audit);
        }
    }

    /**
     * Get response summary table data
     */
    private function getResponseSummaryTable($audit)
    {
        $headers = ['Location', 'Review Type', 'Total Questions', 'Completed', 'Completion %', 'Last Updated'];
        $rows = [];

        foreach ($audit->attachedReviewTypes as $attachment) {
            $responses = Response::where('audit_id', $audit->id)
                ->where('attachment_id', $attachment->id)
                ->get();

            $completed = $responses->filter(function ($response) {
                return !empty($response->answer_text) || 
                       !empty($response->answer_boolean) || 
                       !empty($response->answer_table);
            })->count();

            $total = $responses->count();
            $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
            
            $lastUpdated = $responses->max('updated_at');
            $lastUpdatedFormatted = $lastUpdated ? 
                \Carbon\Carbon::parse($lastUpdated)->format('M j, Y H:i') : 
                'Never';

            $rows[] = [
                $attachment->location_name,
                $attachment->reviewType->name,
                $total,
                $completed,
                $completionRate . '%',
                $lastUpdatedFormatted
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'metadata' => [
                'total_locations' => count($rows),
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Get completion status table data
     */
    private function getCompletionStatusTable($audit)
    {
        $headers = ['Question', 'Location', 'Status', 'Response Type', 'Last Updated'];
        $rows = [];

        $responses = Response::where('audit_id', $audit->id)
            ->with(['question', 'attachment.reviewType'])
            ->orderBy('updated_at', 'desc')
            ->take(50) // Limit to recent 50 responses
            ->get();

        foreach ($responses as $response) {
            $status = 'Empty';
            $responseType = 'None';

            if (!empty($response->answer_text)) {
                $status = 'Completed';
                $responseType = 'Text';
            } elseif (!empty($response->answer_boolean)) {
                $status = 'Completed';
                $responseType = 'Yes/No';
            } elseif (!empty($response->answer_table)) {
                $status = 'Completed';
                $responseType = 'Table';
            }

            $rows[] = [
                substr($response->question->question_text, 0, 80) . (strlen($response->question->question_text) > 80 ? '...' : ''),
                $response->attachment->location_name,
                $status,
                $responseType,
                $response->updated_at->format('M j, Y H:i')
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'metadata' => [
                'showing_recent' => min(50, count($rows)),
                'total_responses' => Response::where('audit_id', $audit->id)->count()
            ]
        ];
    }

    /**
     * Get location overview table data
     */
    private function getLocationOverviewTable($audit)
    {
        $headers = ['Location', 'Review Types', 'Total Responses', 'Avg Completion %', 'Status'];
        $rows = [];

        $locationData = $audit->attachedReviewTypes
            ->groupBy('location_name')
            ->map(function ($attachments, $location) use ($audit) {
                $reviewTypes = $attachments->pluck('reviewType.name')->unique()->count();
                $totalResponses = 0;
                $totalCompletions = 0;
                $totalQuestions = 0;

                foreach ($attachments as $attachment) {
                    $responses = Response::where('audit_id', $audit->id)
                        ->where('attachment_id', $attachment->id)
                        ->get();

                    $totalResponses += $responses->count();
                    $totalQuestions += $responses->count();
                    
                    $completed = $responses->filter(function ($response) {
                        return !empty($response->answer_text) || 
                               !empty($response->answer_boolean) || 
                               !empty($response->answer_table);
                    })->count();
                    
                    $totalCompletions += $completed;
                }

                $avgCompletion = $totalQuestions > 0 ? round(($totalCompletions / $totalQuestions) * 100, 1) : 0;
                $status = $avgCompletion >= 80 ? 'Good' : ($avgCompletion >= 50 ? 'Fair' : 'Needs Attention');

                return [
                    'review_types' => $reviewTypes,
                    'total_responses' => $totalResponses,
                    'avg_completion' => $avgCompletion,
                    'status' => $status
                ];
            });

        foreach ($locationData as $location => $data) {
            $rows[] = [
                $location,
                $data['review_types'],
                $data['total_responses'],
                $data['avg_completion'] . '%',
                $data['status']
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'metadata' => [
                'total_locations' => count($rows),
                'audit_name' => $audit->name
            ]
        ];
    }

    /**
     * Get analysis-based table data
     */
    private function getAnalysisBasedTable($audit)
    {
        // For now, return response summary
        // This could be enhanced based on specific analysis requirements
        return $this->getResponseSummaryTable($audit);
    }

    /**
     * Generate table structure using AI
     */
    private function generateTableStructure($data, $focus, $columns = [])
    {
        // The data is already structured from the collection methods above
        return $data;
    }

    /**
     * Get audit statistics
     */
    private function getAuditStatistics($audit)
    {
        $totalResponses = Response::where('audit_id', $audit->id)->count();
        $reviewTypesCount = $audit->attachedReviewTypes->count();
        
        return [
            'total_responses' => $totalResponses,
            'review_types_count' => $reviewTypesCount,
            'completion_rate' => $this->calculateOverallCompletionRate($audit)
        ];
    }

    /**
     * Calculate completion rate for responses
     */
    private function calculateCompletionRate($responses)
    {
        if ($responses->isEmpty()) return 0;
        
        $completedCount = $responses->filter(function ($response) {
            return !empty($response->answer_text) || 
                   !empty($response->answer_boolean) || 
                   !empty($response->answer_table);
        })->count();
        
        return round(($completedCount / $responses->count()) * 100, 2);
    }

    /**
     * Calculate overall completion rate for audit
     */
    private function calculateOverallCompletionRate($audit)
    {
        $responses = Response::where('audit_id', $audit->id)->get();
        return $this->calculateCompletionRate($responses);
    }

    /**
     * Format response for AI consumption
     */
    private function formatResponseForAI($response)
    {
        if (!empty($response->answer_text)) {
            return $response->answer_text;
        } elseif (!empty($response->answer_boolean)) {
            return $response->answer_boolean ? 'Yes' : 'No';
        } elseif (!empty($response->answer_table)) {
            $table = json_decode($response->answer_table, true);
            if (is_array($table)) {
                return 'Table with ' . count($table) . ' rows of data';
            }
        }
        
        return 'No response provided';
    }
}
