<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'registration_id',
        'type',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
