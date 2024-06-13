<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'doctor_id',
        'organization_id',
        'appointment_datetime',
        'type',
        'purpose',
        'duration',
        'note',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
