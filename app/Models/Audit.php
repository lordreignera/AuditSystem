<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'country_id',
        'review_code',
        'template_id',
        'created_by',
        'participants',
        'start_date',
        'duration_value',
        'duration_unit',
        'end_date',
    ];

    protected $casts = [
        'participants' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationship with Country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Relationship with Template
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    // Relationship with Creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Many-to-many relationship with ReviewTypes
    public function reviewTypes()
    {
        return $this->belongsToMany(ReviewType::class, 'audit_review_types')
                    ->withPivot('template_id')
                    ->withTimestamps();
    }

    // Relationship with AuditReviewTypeAttachments
    public function attachments()
    {
        return $this->hasMany(AuditReviewTypeAttachment::class);
    }

    // Get responses for this audit
    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    // Automatically calculate end date when start_date, duration_value, or duration_unit changes
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($audit) {
            if ($audit->start_date && $audit->duration_value && $audit->duration_unit) {
                $audit->end_date = $audit->calculateEndDate();
            }
        });
    }

    // Calculate end date based on start date and duration
    public function calculateEndDate()
    {
        if (!$this->start_date || !$this->duration_value || !$this->duration_unit) {
            return null;
        }

        $startDate = Carbon::parse($this->start_date);

        switch ($this->duration_unit) {
            case 'days':
                return $startDate->addDays($this->duration_value);
            case 'months':
                return $startDate->addMonths($this->duration_value);
            case 'years':
                return $startDate->addYears($this->duration_value);
            default:
                return null;
        }
    }

    // Generate unique review code
    public static function generateReviewCode()
    {
        do {
            $code = 'AUD-' . strtoupper(uniqid());
        } while (self::where('review_code', $code)->exists());

        return $code;
    }
}
