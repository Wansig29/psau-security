<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'registration_id',
        'document_type',
        'image_path',
        'image_data',
        'ocr_extracted_text',
        'match_score',
        'is_flagged',
        'flagged_fields',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'flagged_fields' => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
