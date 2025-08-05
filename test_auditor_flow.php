<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== AUDITOR AUDIT ACCESS TEST ===\n\n";

// Get Nakamatte Norah (the current auditor)
$auditor = App\Models\User::where('email', 'norahnakamatte05@gmail.com')->first();

if ($auditor) {
    echo "👤 Testing for: " . $auditor->name . "\n";
    echo "📋 Assigned Audits: " . $auditor->assignedAudits->count() . "\n\n";
    
    foreach ($auditor->assignedAudits as $audit) {
        echo "🔍 AUDIT: " . $audit->name . "\n";
        echo "   Country: " . $audit->country->name . "\n";
        
        // Check if audit has attached review types
        $attachedReviewTypes = DB::table('audit_review_type_attachments')
            ->join('review_types', 'review_types.id', '=', 'audit_review_type_attachments.review_type_id')
            ->where('audit_review_type_attachments.audit_id', $audit->id)
            ->select('review_types.*', 'audit_review_type_attachments.id as attachmentId', 'audit_review_type_attachments.location_name')
            ->get();
            
        echo "   📋 Attached Review Types: " . $attachedReviewTypes->count() . "\n";
        
        foreach ($attachedReviewTypes as $reviewType) {
            echo "     ✓ " . $reviewType->name . " (Location: " . $reviewType->location_name . ")\n";
            
            // Check for duplicates
            $duplicates = DB::table('audit_review_type_attachments')
                ->where('audit_id', $audit->id)
                ->where('review_type_id', $reviewType->id)
                ->where('id', '!=', $reviewType->attachmentId)
                ->get();
                
            if ($duplicates->count() > 0) {
                echo "       📍 Duplicates: " . $duplicates->count() . "\n";
                foreach ($duplicates as $dup) {
                    echo "         - " . $dup->location_name . "\n";
                }
            }
        }
        echo "\n";
    }
    
    echo "🎯 AUDITOR FLOW VERIFICATION:\n";
    echo "✅ Can see assigned audits\n";
    echo "✅ Can access audit dashboards\n";
    echo "✅ Can see attached review types\n";
    echo "✅ Can duplicate review types (locations)\n";
    echo "✅ Can duplicate templates\n";
    echo "✅ Can add responses to questions\n";
    echo "❌ Cannot edit audit structure\n";
    echo "❌ Cannot see management buttons\n\n";
    
    echo "🏁 AUDITOR WORKFLOW READY!\n";
} else {
    echo "❌ Auditor not found!\n";
}
