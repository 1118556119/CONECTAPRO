<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size'
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
    
    // Accesorio para obtener la URL completa
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
