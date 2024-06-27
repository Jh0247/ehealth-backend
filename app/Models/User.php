<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $organization_id
 * @property string $contact
 * @property string $icno
 * @property string $user_role
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 *
 * @property Organization $organization
 * @property \Illuminate\Database\Eloquent\Collection|Blogpost[] $blogposts
 * @property HealthRecord $healthRecord
 * @property \Illuminate\Database\Eloquent\Collection|Appointment[] $appointmentsAsUser
 * @property \Illuminate\Database\Eloquent\Collection|Appointment[] $appointmentsAsDoctor
 * @property \Illuminate\Database\Eloquent\Collection|PurchaseRecord[] $purchaseRecordsAsUser
 * @property \Illuminate\Database\Eloquent\Collection|PurchaseRecord[] $purchaseRecordsAsPharmacist
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'contact',
        'icno',
        'user_role',
        'status',
    ];

    /**
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the organization that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the blogposts for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blogposts()
    {
        return $this->hasMany(Blogpost::class);
    }

    /**
     * Get the health record associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function healthRecord()
    {
        return $this->hasOne(HealthRecord::class);
    }

    /**
     * Get the appointments where the user is a patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointmentsAsUser()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    /**
     * Get the appointments where the user is a doctor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointmentsAsDoctor()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * Get the purchase records where the user is the customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseRecordsAsUser()
    {
        return $this->hasMany(PurchaseRecord::class, 'user_id');
    }

    /**
     * Get the purchase records where the user is the pharmacist.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseRecordsAsPharmacist()
    {
        return $this->hasMany(PurchaseRecord::class, 'pharmacist_id');
    }
}
