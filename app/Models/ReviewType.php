<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the templates associated with this review type.
     */
    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    /**
     * Get the audits associated with this review type.
     */
    public function audits()
    {
        return $this->belongsToMany(Audit::class, 'audit_review_types')
                    ->withPivot('template_id')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active review types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
