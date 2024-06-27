<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Medication
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $ingredient
 * @property string $form
 * @property string $usage
 * @property string $strength
 * @property string $manufacturer
 * @property float $price
 *
 * @property \Illuminate\Database\Eloquent\Collection|Prescription[] $prescriptions
 * @property \Illuminate\Database\Eloquent\Collection|PurchaseRecord[] $purchaseRecords
 */
class Medication extends Model
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
        'description',
        'ingredient',
        'form',
        'usage',
        'strength',
        'manufacturer',
        'price',
    ];

    /**
     * Get the prescriptions for the medication.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the purchase records for the medication.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseRecords()
    {
        return $this->hasMany(PurchaseRecord::class);
    }
}
