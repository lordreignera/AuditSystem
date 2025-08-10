<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Response;
use App\Models\AuditReviewTypeAttachment;

class CleanupOrphanedResponses extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cleanup:orphaned-responses {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up orphaned responses that reference deleted attachments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for orphaned responses...');
        
        // Find responses that have attachment_id but the attachment doesn't exist
        $orphanedResponses = Response::whereNotNull('attachment_id')
            ->whereDoesntHave('attachment')
            ->get();
        
        $this->info("Found {$orphanedResponses->count()} orphaned responses");
        
        if ($orphanedResponses->count() === 0) {
            $this->info('✅ No orphaned responses found!');
            return 0;
        }
        
        // Show details
        $this->table(
            ['Response ID', 'Attachment ID', 'Audit ID', 'Question ID', 'User ID'],
            $orphanedResponses->map(function ($response) {
                return [
                    $response->id,
                    $response->attachment_id,
                    $response->audit_id,
                    $response->question_id,
                    $response->user_id
                ];
            })->toArray()
        );
        
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN - No data was deleted. Run without --dry-run to actually delete.');
            return 0;
        }
        
        if ($this->confirm('Do you want to delete these orphaned responses?')) {
            $deleted = 0;
            foreach ($orphanedResponses as $response) {
                $response->delete();
                $deleted++;
            }
            $this->info("✅ Deleted {$deleted} orphaned responses.");
        } else {
            $this->info('❌ Cleanup cancelled.');
        }
        
        // Check for responses without attachment_id (legacy)
        $legacyResponses = Response::whereNull('attachment_id')->count();
        if ($legacyResponses > 0) {
            $this->warn("⚠️  Found {$legacyResponses} responses without attachment_id (legacy format)");
            $this->info("These may need manual review if they cause issues.");
        }
        
        return 0;
    }
}
