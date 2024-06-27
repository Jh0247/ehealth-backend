<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Prescription
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $appointment_id
 * @property int $medication_id
 * @property string $dosage
 * @property string $frequency
 * @property int $duration
 * @property \Illuminate\Support\Carbon $prescription_date
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 *
 * @property Appointment $appointment
 * @property Medication $medication
 */
class Prescription extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
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

    /**
     * Get the appointment that owns the prescription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the medication that belongs to the prescription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
