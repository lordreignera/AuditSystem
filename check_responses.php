<?php

use App\Models\Response;
use App\Models\AuditReviewTypeAttachment;

// Check for responses with null attachment_id
$nullAttachments = Response::whereNull('attachment_id')->count();
echo "Responses with null attachment_id: {$nullAttachments}\n";

// Check for responses with invalid attachment_id
$validAttachmentIds = AuditReviewTypeAttachment::pluck('id')->toArray();
$invalidAttachments = Response::whereNotNull('attachment_id')
    ->get()
    ->filter(function($response) use ($validAttachmentIds) {
        return !in_array($response->attachment_id, $validAttachmentIds);
    })
    ->count();
echo "Responses with invalid attachment_id: {$invalidAttachments}\n";

// Check total responses
$totalResponses = Response::count();
echo "Total responses: {$totalResponses}\n";

// Check total attachments
$totalAttachments = AuditReviewTypeAttachment::count();
echo "Total attachments: {$totalAttachments}\n";

// Show some sample data
echo "\nSample responses with attachment data:\n";
$sampleResponses = Response::with('attachment')->take(3)->get();
foreach ($sampleResponses as $response) {
    echo "Response ID: {$response->id}, Attachment ID: {$response->attachment_id}, ";
    echo "Attachment exists: " . ($response->attachment ? 'Yes' : 'No') . "\n";
}
