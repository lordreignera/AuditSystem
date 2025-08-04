<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'audit_id',
        'name',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the template that owns the section.
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the audit that owns the section (for audit-specific sections).
     */
    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    /**
     * Get the questions for the section.
     */
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Scope a query to only include active sections.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
