<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Appointment
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $doctor_id
 * @property int $organization_id
 * @property string $appointment_datetime
 * @property string $type
 * @property string|null $purpose
 * @property int|null $duration
 * @property string|null $note
 * @property string $status
 *
 * @property User $user
 * @property User $doctor
 * @property Organization $organization
 * @property \Illuminate\Database\Eloquent\Collection|Prescription[] $prescriptions
 */
class Appointment extends Model
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

    /**
     * Get the user that owns the appointment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the doctor for the appointment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the organization for the appointment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the prescriptions for the appointment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
