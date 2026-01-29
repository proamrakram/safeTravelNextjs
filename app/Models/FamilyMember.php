<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $fillable = [
        'registration_id',
        'name',
        'age',
        'gender', // Male, Female
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
