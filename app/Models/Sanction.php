<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sanction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'violation_id',
        'sanction_type',
        'start_date',
        'end_date',
        'is_active',
        'source',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }
}
