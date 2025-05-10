<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'spin_number',
        'name',
        'owner',
        'address',
    ];

    /**
     * A beneficiary can have multiple setup projects.
     */
    public function setups()
    {
        return $this->hasMany(Setup::class);
    }
}
