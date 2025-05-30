<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TechnicianProfile extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        // Información profesional
        'specialty',
        'experience',
        'bio',
        'photo_url',
        'current_company',
        'position',
        'verified', // Indica si el perfil está verificado
        'available', // Indica disponibilidad para trabajar
        
        // Formación académica
        'education_level',
        'education',
        'institution',
        'graduation_year',
        'start_year',
        'education_status',
        'thesis_title',
        'education_country',
        'education_city',
        
        // Habilidades y otros estudios
        'skills',
        'certifications',
        'languages',
        'additional_courses',
        
        // Información de negocios
        'hourly_rate',
        'service_area',
        'availability_schedule'
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'skills' => 'array',
        'experience' => 'integer',
        'graduation_year' => 'integer',
        'start_year' => 'integer',
        'verified' => 'boolean',
        'available' => 'boolean',
        'hourly_rate' => 'float',
        'availability_schedule' => 'array',
    ];

    /**
     * Valores predeterminados para atributos
     *
     * @var array
     */
    protected $attributes = [
        'verified' => false,
        'available' => true,
    ];

    /**
     * Obtiene el usuario asociado con el perfil técnico.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relación con los servicios ofrecidos por el técnico
     */
    public function services()
    {
        return $this->hasMany(TechnicianService::class);
    }

    /**
     * Calcula el porcentaje de completitud del perfil.
     *
     * @return int
     */
    public function getCompletenessPercentage()
    {
        $requiredFields = [
            'specialty', 'experience', 'education_level', 
            'education', 'institution', 'current_company',
            'bio', 'skills'
        ];
        
        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }
        
        return round(($completedFields / count($requiredFields)) * 100);
    }
}