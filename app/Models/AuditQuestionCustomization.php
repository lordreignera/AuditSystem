<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditQuestionCustomization extends Model
{
    protected $fillable = [
        'audit_id',
        'default_question_id',
        'question_text',
        'description',
        'options',
        'order',
        'is_required',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function defaultQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'default_question_id');
    }
}
