<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'organization_id',
        'address',
        'latitude',
        'longitude',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
