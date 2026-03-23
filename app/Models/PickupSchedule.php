<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupSchedule extends Model
{
    protected $fillable = [
        'registration_id',
        'pickup_date',
        'pickup_time',
        'location',
        'is_completed',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
