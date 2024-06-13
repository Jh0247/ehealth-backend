<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'pharmacist_id',
        'medication_id',
        'date_purchase',
        'quantity',
        'total_payment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
