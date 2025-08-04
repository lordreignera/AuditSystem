<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_id',
        'attachment_id',
        'question_id',
        'answer',
        'audit_note',
        'created_by',
    ];

    // app/Models/Response.php
// app/Models/Response.php
    protected $casts = [
        'answer' => 'array',
    ];

    /**
     * Get the audit review type attachment that owns the response.
     */
    public function attachment()
    {
        return $this->belongsTo(AuditReviewTypeAttachment::class, 'attachment_id');
    }

    /**
     * Get the audit that owns the response.
     */
    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    /**
     * Get the question that owns the response.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the user who created the response.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
