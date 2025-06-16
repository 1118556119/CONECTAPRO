<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserEducation extends Model
{
    use HasFactory;

    protected $table = 'user_education';

    protected $fillable = [
        'user_id',
        'education_level',
        'institution',
        'degree',
        'start_year',
        'graduation_year',
        'country',
        'department',
        'city',
        'certificate_file_path',
        'certificate_original_name',
        'certificate_mime_type',
        'certificate_file_size',
        'sort_order',
        'is_primary',
        'is_verified'
    ];

    protected $casts = [
        'start_year' => 'integer',
        'graduation_year' => 'integer',
        'certificate_file_size' => 'integer',
        'is_primary' => 'boolean',
        'is_verified' => 'boolean'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getCertificateUrlAttribute()
    {
        if (!$this->certificate_file_path) {
            return null;
        }
        
        return Storage::url($this->certificate_file_path);
    }

    public function getFormattedPeriodAttribute()
    {
        if ($this->start_year && $this->graduation_year) {
            return "{$this->start_year} - {$this->graduation_year}";
        }
        
        if ($this->graduation_year) {
            return "Graduado en {$this->graduation_year}";
        }
        
        if ($this->start_year) {
            return "Desde {$this->start_year}";
        }
        
        return 'Período no especificado';
    }

    public function getLocationAttribute()
    {
        $location = [];
        
        if ($this->city) {
            $location[] = $this->city;
        }
        
        if ($this->department) {
            $location[] = $this->department;
        }
        
        if ($this->country) {
            $location[] = $this->country;
        }
        
        return implode(', ', $location);
    }

    // Scopes
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_primary', 'desc')
                    ->orderBy('graduation_year', 'desc')
                    ->orderBy('sort_order', 'asc');
    }
}