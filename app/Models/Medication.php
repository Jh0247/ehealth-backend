<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function purchaseRecords()
    {
        return $this->hasMany(PurchaseRecord::class);
    }
}
