<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditReviewTypeAttachment extends Model
{
    protected $fillable = [
        'audit_id',
        'review_type_id',
        'master_attachment_id',
        'duplicate_number',
        'location_name' // Renamed from facility_name for better context
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function reviewType(): BelongsTo
    {
        return $this->belongsTo(ReviewType::class);
    }

    /**
     * Get the master attachment (for duplicates)
     */
    public function masterAttachment(): BelongsTo
    {
        return $this->belongsTo(AuditReviewTypeAttachment::class, 'master_attachment_id');
    }

    /**
     * Get all duplicates of this master attachment
     */
    public function duplicates()
    {
        return $this->hasMany(AuditReviewTypeAttachment::class, 'master_attachment_id');
    }

    /**
     * Check if this is a master attachment
     */
    public function isMaster(): bool
    {
        return $this->master_attachment_id === null;
    }

    /**
     * Get all responses for this attachment
     */
    public function responses()
    {
        return $this->hasMany(Response::class, 'attachment_id');
    }

    /**
     * Check if this is a duplicate attachment
     */
    public function isDuplicate(): bool
    {
        return $this->master_attachment_id !== null;
    }

    /**
     * Get contextual location name based on review type
     */
    public function getContextualLocationName(): string
    {
        if ($this->location_name) {
            return $this->location_name;
        }

        return $this->getDefaultLocationName();
    }

    /**
     * Get default location name based on review type and duplicate number
     */
    public function getDefaultLocationName(): string
    {
        $reviewTypeName = strtolower($this->reviewType->name);
        
        // National is always singular
        if (str_contains($reviewTypeName, 'national')) {
            return 'National Level';
        }
        
        // Contextual naming based on review type
        if (str_contains($reviewTypeName, 'province') || str_contains($reviewTypeName, 'region')) {
            return "Province {$this->duplicate_number}";
        }
        
        if (str_contains($reviewTypeName, 'district')) {
            return "District {$this->duplicate_number}";
        }
        
        if (str_contains($reviewTypeName, 'health facility') || str_contains($reviewTypeName, 'facility')) {
            return "Facility {$this->duplicate_number}";
        }
        
        // Default fallback
        return "{$this->reviewType->name} {$this->duplicate_number}";
    }
}
