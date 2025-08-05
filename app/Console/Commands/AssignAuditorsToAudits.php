<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Audit;
use App\Models\UserAuditAssignment;

class AssignAuditorsToAudits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:auditors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign auditors to existing audits';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find the auditor
        $auditor = User::where('email', 'norahnakamatte05@gmail.com')->first();
        
        if (!$auditor) {
            $this->error('Auditor not found!');
            return;
        }
        
        $this->info("Found auditor: {$auditor->name}");
        
        // Get available audits
        $audits = Audit::take(3)->get(); // Take first 3 audits
        
        if ($audits->isEmpty()) {
            $this->error('No audits found!');
            return;
        }
        
        // Find superadmin for assigned_by
        $superadmin = User::role('Super Admin')->first();
        
        foreach ($audits as $audit) {
            // Check if assignment already exists
            $existingAssignment = UserAuditAssignment::where('user_id', $auditor->id)
                                                   ->where('audit_id', $audit->id)
                                                   ->first();
                                                   
            if (!$existingAssignment) {
                UserAuditAssignment::create([
                    'user_id' => $auditor->id,
                    'audit_id' => $audit->id,
                    'assigned_by' => $superadmin ? $superadmin->id : null,
                    'assigned_at' => now(),
                ]);
                
                $this->info("Assigned audit: {$audit->title} to {$auditor->name}");
            } else {
                $this->warn("Assignment already exists for audit: {$audit->title}");
            }
        }
        
        $this->info('Assignment completed!');
    }
}
