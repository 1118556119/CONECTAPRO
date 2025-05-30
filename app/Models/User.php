<?php


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'idNumber',
        'phone',
        'birthDate',
        'gender',
        'email',
        'password',
        'user_type', // Cambiado de 'role' a 'user_type'
        'photo_url', 
    ];

    /**
     * Campos que deben permanecer ocultos en las respuestas JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tipos de datos que deben ser casteados.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthDate' => 'date',
    ];

    /**
     * Relación con el perfil del técnico.
     * Un usuario puede tener un perfil de técnico.
     */
    public function technicianProfile()
    {
        return $this->hasOne(TechnicianProfile::class);
    }

    /**
     * Relación con las solicitudes de servicio.
     * Un usuario puede tener muchas solicitudes de servicio.
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Relación con las reseñas.
     * Un usuario puede tener muchas reseñas.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    /**
     * Verifica si el usuario es un técnico.
     *
     * @return bool
     */
    public function isTechnician()
    {
        return $this->user_type === 'technician';
    }
    
    /**
     * Verifica si el usuario es un cliente.
     *
     * @return bool
     */
    public function isClient()
    {
        return $this->user_type === 'client';
    }
}