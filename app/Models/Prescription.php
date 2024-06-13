<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'appointment_id',
        'medication_id',
        'dosage',
        'frequency',
        'duration',
        'prescription_date',
        'start_date',
        'end_date'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
