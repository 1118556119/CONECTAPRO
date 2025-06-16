<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty',
        'experience_years',
        'bio',
        'profile_photo',
        'education_level',
        'institution',
        'degree',
        'graduation_year',
        'certifications',
        'skills',
        'languages',
        'current_company',
        'current_position',
        'hourly_rate',
        'is_verified',
        'is_available',
        'service_areas',
        'availability_schedule',
        'rating_average',
        'total_jobs',
        'profile_completeness',
    ];

    protected $casts = [
        'certifications' => 'array',
        'skills' => 'array',
        'languages' => 'array',
        'service_areas' => 'array',
        'availability_schedule' => 'array',
        'hourly_rate' => 'decimal:2',
        'rating_average' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_available' => 'boolean',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'technician_id', 'user_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'technician_id', 'user_id');
    }

    // Accessors
    public function getEducationLevelLabelAttribute()
    {
        $labels = [
            'primaria' => 'Primaria',
            'secundaria' => 'Secundaria',
            'tecnico' => 'Técnico',
            'tecnologo' => 'Tecnólogo',
            'universitario' => 'Universitario',
            'postgrado' => 'Postgrado',
        ];

        return $labels[$this->education_level] ?? $this->education_level;
    }

    public function getExperienceLabelAttribute()
    {
        if ($this->experience_years == 0) {
            return 'Sin experiencia';
        } elseif ($this->experience_years == 1) {
            return '1 año';
        } else {
            return $this->experience_years . ' años';
        }
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', $specialty);
    }

    public function scopeInServiceArea($query, $city)
    {
        return $query->whereJsonContains('service_areas', $city);
    }
}