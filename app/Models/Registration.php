<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'full_name',
        'age',
        'gender', // Male, Female
        'email',
        'travelers',
        'destination',
        'stay_duration',
        'travel_package', // Economic, Comfortable, VIP
        'status', // Pending, Processing, Approved, Rejected
        'admin_notes',
    ];

    protected $casts = [
        'age' => 'integer',
        'travelers' => 'integer',
        'stay_duration' => 'integer',
    ];

    // Relationships
    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
