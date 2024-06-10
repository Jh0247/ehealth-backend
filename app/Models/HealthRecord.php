<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'health_condition',
        'blood_type',
        'allergic',
        'diseases',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
