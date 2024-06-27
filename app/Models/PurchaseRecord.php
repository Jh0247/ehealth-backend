<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseRecord
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $pharmacist_id
 * @property int $medication_id
 * @property \Illuminate\Support\Carbon $date_purchase
 * @property int $quantity
 * @property float $total_payment
 *
 * @property User $user
 * @property User $pharmacist
 * @property Medication $medication
 */
class PurchaseRecord extends Model
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
        'pharmacist_id',
        'medication_id',
        'date_purchase',
        'quantity',
        'total_payment'
    ];

    /**
     * Get the user that owns the purchase record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pharmacist that handled the purchase.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    /**
     * Get the medication for the purchase.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
