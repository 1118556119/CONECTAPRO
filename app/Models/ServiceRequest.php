<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'client_id',
        'technician_id',
        'service_type',
        'urgency_level',
        'description',
        'equipment_type',
        'equipment_brand',
        'equipment_model',
        'service_details',
        'service_address',
        'service_city',
        'service_postal_code',
        'location_notes',
        'preferred_date',
        'preferred_time',
        'scheduling_notes',
        'status',
        'estimated_cost',
        'quoted_price',
        'final_cost',
        'cost_breakdown',
        'client_notes',
        'technician_notes',
        'admin_notes',
        'cancellation_reason',
        'rejection_reason',      
        'rejection_comments',
        'rejected_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'quoted_at' => 'datetime',
        'accepted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relaciones
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function photos()
    {
        return $this->hasMany(ServiceRequestPhoto::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Boot para generar número de solicitud
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($serviceRequest) {
            $serviceRequest->request_number = 'SR-' . date('Y') . '-' . str_pad(static::max('id') + 1, 6, '0', STR_PAD_LEFT);
        });
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendiente',
            'quoted' => 'Cotizado',
            'accepted' => 'Aceptado',
            'assigned' => 'Asignado',
            'in_progress' => 'En Progreso',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            'rejected' => 'Rechazado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getUrgencyLabelAttribute()
    {
        $labels = [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'critica' => 'Crítica',
        ];

        return $labels[$this->urgency_level] ?? $this->urgency_level;
    }

    public function getServiceTypeLabelAttribute()
    {
        $labels = [
            'correctivo' => 'Mantenimiento Correctivo',
            'preventivo' => 'Mantenimiento Preventivo',
            'instalacion' => 'Instalación',
            'limpieza' => 'Limpieza',
            'reparacion' => 'Reparación',
            'asesoria' => 'Asesoría',
            'diagnostico' => 'Diagnóstico',
            'otro' => 'Otro',
        ];

        return $labels[$this->service_type] ?? $this->service_type;
    }

    public function getEquipmentTypeLabelAttribute()
    {
        $labels = [
            'desktop' => 'Computadora de Escritorio',
            'laptop' => 'Laptop',
            'server' => 'Servidor',
            'printer' => 'Impresora',
            'network' => 'Equipos de Red',
            'mobile' => 'Dispositivo Móvil',
            'tablet' => 'Tablet',
            'scanner' => 'Escáner',
            'projector' => 'Proyector',
            'other' => 'Otro',
        ];

        return $labels[$this->equipment_type] ?? $this->equipment_type;
    }

    public function getFormattedUrgencyAttribute()
    {
        return $this->getUrgencyLabelAttribute();
    }

    public function getFormattedServiceTypeAttribute()
    {
        return $this->getServiceTypeLabelAttribute();
    }

    public function getFormattedEquipmentTypeAttribute()
    {
        return $this->getEquipmentTypeLabelAttribute();
    }

    public function getClientInfoAttribute()
    {
        return [
            'name' => $this->client->name,
            'phone' => $this->client->phone,
            'city' => $this->client->city,
        ];
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUrgency($query, $urgency)
    {
        return $query->where('urgency_level', $urgency);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }
}