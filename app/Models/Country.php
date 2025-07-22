<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'iso_code',
        'phone_code',
        'currency',
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
     * Scope a query to only include active countries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted phone code with plus sign.
     */
    public function getFormattedPhoneCodeAttribute()
    {
        return $this->phone_code ? '+' . $this->phone_code : null;
    }

    /**
     * Get the audits for this country.
     */
    public function audits()
    {
        return $this->hasMany(Audit::class);
    }
}
