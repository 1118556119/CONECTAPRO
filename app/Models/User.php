<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use App\Helpers\ImageHelper;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'idNumber',      
        'phone',
        'gender',
        'birthDate',     
        'photo',
        'email',
        'password',
        'user_type',
        'address',
        'city',
        'postal_code',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthDate' => 'date',        
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Relación con experiencias laborales
     */
    public function experiences()
    {
        return $this->hasMany(UserExperience::class)->orderBy('start_date', 'desc');
    }

    /**
     * Relación con educación
     */
    public function educations()
    {
        return $this->hasMany(UserEducation::class)->orderBy('graduation_year', 'desc');
    }

    /**
     * Relación con perfil técnico
     */
    public function technicianProfile()
    {
        return $this->hasOne(TechnicianProfile::class);
    }

    /**
     * Obtener experiencias ordenadas
     */
    public function getOrderedExperiencesAttribute()
    {
        return $this->experiences()
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('currently_working', 'desc')
                    ->orderBy('start_date', 'desc')
                    ->get();
    }

    /**
     * Obtener educación ordenada
     */
    public function getOrderedEducationAttribute()
    {
        return $this->educations()
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('graduation_year', 'desc')
                    ->get();
    }

    // Accessors
    public function getIsClientAttribute()
    {
        return $this->user_type === 'client';
    }

    public function getIsTechnicianAttribute()
    {
        return $this->user_type === 'technician';
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getAverageRatingAttribute()
    {
        if ($this->is_technician) {
            return $this->reviews()->avg('overall_rating') ?? 0;
        }
        return 0;
    }

    // Accessor para obtener años de experiencia total
    public function getTotalExperienceYearsAttribute()
    {
        $totalMonths = 0;
        
        foreach ($this->experience as $exp) {
            if ($exp->start_date) {
                $end = $exp->currently_working ? Carbon::now() : $exp->end_date;
                if ($end) {
                    $totalMonths += $exp->start_date->diffInMonths($end);
                }
            }
        }
        
        return floor($totalMonths / 12);
    }

    /**
     * Accessor para obtener la URL completa de la foto
     */
    public function getPhotoUrlAttribute()
    {
        return ImageHelper::getPhotoUrl($this->photo);
    }

    // Scopes
    public function scopeClients($query)
    {
        return $query->where('user_type', 'client');
    }

    public function scopeTechnicians($query)
    {
        return $query->where('user_type', 'technician');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerifiedTechnicians($query)
    {
        return $query->where('user_type', 'technician')
                    ->whereHas('technicianProfile', function($q) {
                        $q->where('is_verified', true);
                    });
    }

    public function scopeAvailableTechnicians($query)
    {
        return $query->where('user_type', 'technician')
                    ->whereHas('technicianProfile', function($q) {
                        $q->where('is_available', true);
                    });
    }
}