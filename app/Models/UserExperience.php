<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserExperience extends Model
{
    use HasFactory;

    protected $table = 'user_experience';

    protected $fillable = [
        'user_id',
        'company_name',
        'position',
        'start_date',
        'end_date',
        'currently_working',
        'country',
        'department',
        'city',
        'certificate_file_path',
        'certificate_original_name',
        'certificate_mime_type',
        'certificate_file_size',
        'sort_order',
        'is_featured',
        'is_verified'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'currently_working' => 'boolean',
        'certificate_file_size' => 'integer',
        'is_featured' => 'boolean',
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
        $start = $this->start_date ? $this->start_date->format('M Y') : '';
        $end = $this->currently_working ? 'Presente' : ($this->end_date ? $this->end_date->format('M Y') : '');
        
        return $start . ($end ? " - {$end}" : '');
    }

    public function getDurationAttribute()
    {
        if (!$this->start_date) {
            return null;
        }
        
        $endDate = $this->currently_working ? Carbon::now() : $this->end_date;
        
        if (!$endDate) {
            return null;
        }
        
        $diff = $this->start_date->diff($endDate);
        
        $years = $diff->y;
        $months = $diff->m;
        
        $duration = [];
        
        if ($years > 0) {
            $duration[] = $years . ($years == 1 ? ' año' : ' años');
        }
        
        if ($months > 0) {
            $duration[] = $months . ($months == 1 ? ' mes' : ' meses');
        }
        
        return implode(' y ', $duration) ?: 'Menos de un mes';
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
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('currently_working', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_featured', 'desc')
                    ->orderBy('currently_working', 'desc')
                    ->orderBy('start_date', 'desc')
                    ->orderBy('sort_order', 'asc');
    }
}