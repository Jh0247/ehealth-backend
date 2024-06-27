<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HealthRecord
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $health_condition
 * @property string|null $blood_type
 * @property string|null $allergic
 * @property string|null $diseases
 *
 * @property User $user
 */
class HealthRecord extends Model
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
        'health_condition',
        'blood_type',
        'allergic',
        'diseases',
    ];

    /**
     * Get the user that owns the health record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
