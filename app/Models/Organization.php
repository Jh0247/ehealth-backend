<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Organization
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property string $address
 * @property float $latitude
 * @property float $longitude
 *
 * @property \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property \Illuminate\Database\Eloquent\Collection|Appointment[] $appointments
 */
class Organization extends Model
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
        'name',
        'code',
        'type',
        'address',
        'latitude',
        'longitude',
    ];

    /**
     * Get the users for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the appointments for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
