<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'technician_id',
        'service_request_id',
        'overall_rating',
        'quality_rating',
        'punctuality_rating',
        'communication_rating',
        'price_rating',
        'comment',
        'pros',
        'cons',
        'is_public',
        'is_verified',
        'is_recommended',
        'technician_response',
        'technician_response_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'is_recommended' => 'boolean',
        'technician_response_at' => 'datetime',
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

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    // Accessors
    public function getAverageRatingAttribute()
    {
        $ratings = array_filter([
            $this->quality_rating,
            $this->punctuality_rating,
            $this->communication_rating,
            $this->price_rating,
        ]);

        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : $this->overall_rating;
    }

    public function getRatingStarsAttribute()
    {
        $stars = '';
        $rating = $this->overall_rating;
        
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating ? '★' : '☆';
        }
        
        return $stars;
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }
}