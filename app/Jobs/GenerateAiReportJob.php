<?php

namespace App\Jobs;

use App\Models\Audit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAiReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $auditId;
    protected $requestData;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($auditId, $requestData, $userId)
    {
        $this->auditId = $auditId;
        $this->requestData = $requestData;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $audit = \App\Models\Audit::find($this->auditId);

        // Convert checkbox values to boolean
        $includeTablesValue = $this->requestData['include_tables'] ?? false;
        $includeRecommendationsValue = $this->requestData['include_recommendations'] ?? false;

        $includeTables = in_array($includeTablesValue, ['1', 'true', 'on', true], true);
        $includeRecommendations = in_array($includeRecommendationsValue, ['1', 'true', 'on', true], true);

        // Prepare options
        $options = [
            'report_type' => $this->requestData['report_type'],
            'include_table_analysis' => $includeTables,
            'include_recommendations' => $includeRecommendations,
        ];

        // Collect audit data
        $controller = new \App\Http\Controllers\Admin\ReportController();
        $auditData = $controller->collectAuditData($audit, $this->requestData['selected_locations'] ?? []);

        // Log for debugging
        \Log::info('AI Report Data Collection (Queued)', [
            'audit_id' => $audit->id,
            'audit_name' => $audit->name,
            'review_types_count' => count($auditData['review_types_data'] ?? []),
            'total_responses' => $auditData['total_responses'] ?? 0,
            'total_questions' => $auditData['total_questions'] ?? 0,
            'selected_locations' => $this->requestData['selected_locations'] ?? 'all'
        ]);

        // Generate AI report
        $aiReport = $controller->callDeepSeekAI($auditData, $options);

        // Save the generated report
        $controller->saveGeneratedReport($audit, $aiReport, $this->requestData);

        // Optionally: notify the user here
    }
}