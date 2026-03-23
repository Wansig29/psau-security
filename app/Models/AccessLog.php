<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'details',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
