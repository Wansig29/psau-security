<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'plate_number',
        'make',
        'model',
        'color',
        'photo_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }
}
