<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpectedSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'setup_id',
        'due_date',
        'amount_due',
        'months_lapsed',
    ];

    // ✅ Automatically cast due_date to Carbon object
    protected $casts = [
        'due_date' => 'date',
    ];

    public function setup()
    {
        return $this->belongsTo(Setup::class);
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class);
    }

    public function beneficiary()
    {
        return $this->hasOneThrough(
            Beneficiary::class,
            Setup::class,
            'id',           // local key on ExpectedSchedule (setup_id)
            'id',           // local key on Setup (beneficiary_id)
            'setup_id',     // foreign key on ExpectedSchedule
            'beneficiary_id'
        );
    }
}
