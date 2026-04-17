<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'old_vehicle_id',
        'old_registration_id',
        'new_make',
        'new_model',
        'new_color',
        'new_plate_number',
        'reason',
        'document_paths',
        'image_data',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'document_paths' => 'array',
        'image_data'     => 'array',
        'reviewed_at'    => 'datetime',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function oldVehicle() { return $this->belongsTo(Vehicle::class, 'old_vehicle_id'); }
    public function oldRegistration() { return $this->belongsTo(Registration::class, 'old_registration_id'); }
    public function reviewedBy() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
