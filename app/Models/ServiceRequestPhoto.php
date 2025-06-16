<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'file_name',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'photo_type',
        'description',
        'sort_order',
        'is_public',
        'metadata',
        'uploaded_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
    ];

    // Relaciones
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors
    public function getPhotoTypeLabelAttribute()
    {
        $labels = [
            'problem' => 'Problema',
            'equipment' => 'Equipo',
            'diagnostic' => 'Diagnóstico',
            'progress' => 'Progreso',
            'solution' => 'Solución',
            'before' => 'Antes',
            'after' => 'Después',
            'invoice' => 'Factura',
            'other' => 'Otro',
        ];

        return $labels[$this->photo_type] ?? $this->photo_type;
    }

    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('photo_type', $type);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}