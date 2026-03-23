<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationsArchive extends Model
{
    protected $fillable = [
        'original_violation_id',
        'vehicle_id',
        'school_year',
        'violation_type',
        'location_notes',
        'gps_lat',
        'gps_lng',
        'sanction_applied',
        'logged_by',
        'archived_at',
    ];

    protected $casts = [
        'gps_lat' => 'decimal:8',
        'gps_lng' => 'decimal:8',
        'sanction_applied' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
