<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianService extends Model
{
    use HasFactory;
    
    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'technician_profile_id',
        'service_name',
        'description',
        'price',
        'duration_minutes',
        'is_onsite', // Servicio a domicilio
        'is_remote', // Servicio remoto
        'is_active', // Servicio disponible
    ];
    
    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'duration_minutes' => 'integer',
        'is_onsite' => 'boolean',
        'is_remote' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    /**
     * Valores predeterminados para atributos
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
    ];
    
    /**
     * Obtiene el perfil técnico al que pertenece este servicio.
     */
    public function technicianProfile()
    {
        return $this->belongsTo(TechnicianProfile::class);
    }
}
