<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Violation extends Model
{
    use HasFactory;
    protected $fillable = [
        'vehicle_id',
        'registration_id',
        'school_year',
        'violation_type',
        'location_notes',
        'gps_lat',
        'gps_lng',
        'photo_path',
        'logged_by',
        'sanction_applied',
    ];

    protected $casts = [
        'gps_lat' => 'decimal:8',
        'gps_lng' => 'decimal:8',
        'sanction_applied' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    public function appeals()
    {
        return $this->hasMany(Appeal::class);
    }
}
