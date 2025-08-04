<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_type_id',
        'audit_id',
        'name',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the review type that owns the template.
     */
    public function reviewType()
    {
        return $this->belongsTo(ReviewType::class);
    }

    /**
     * Get the audit that owns the template (for audit-specific templates).
     */
    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    /**
     * Get the sections for the template.
     */
    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    /**
     * Get all questions through sections.
     */
    public function questions()
    {
        return $this->hasManyThrough(Question::class, Section::class);
    }

    /**
     * Get the audits using this template.
     */
    public function audits()
    {
        return $this->hasMany(Audit::class);
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default templates.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
