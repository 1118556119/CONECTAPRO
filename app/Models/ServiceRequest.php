<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'technician_id',
        'type',
        'description',
        'urgency',
        'equipment_type',
        'brand',
        'service_type_detail',
        'address',
        'city',
        'postal_code',
        'preferred_date',
        'preferred_time',
        'comments',
        'status'
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    // Relación con el usuario que hace la solicitud
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el técnico asignado
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Relación con las fotos
    public function photos()
    {
        return $this->hasMany(ServiceRequestPhoto::class);
    }
}