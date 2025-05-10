<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ExpectedSchedule;
use App\Models\Setup;
use App\Models\Beneficiary;

class Repayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'expected_schedule_id',
        'payment_amount',
        'payment_date',
        'or_number',
        'or_date',
        'penalty_amount',
        'returned_check',
        'deferred',
        'deferred_date',
        'pdc_number',
        'pdc_date',
        'remarks',
    ];

    /**
     * Cast dates and booleans so you can call format() on dates
     */
    protected $casts = [
        'payment_date'   => 'date',
        'or_date'        => 'date',
        'pdc_date'       => 'date',
        'deferred_date'  => 'date',
        'returned_check' => 'boolean',
        'deferred'       => 'boolean',
    ];

    /**
     * The schedule entry this repayment applies to.
     */
    public function expectedSchedule()
    {
        return $this->belongsTo(ExpectedSchedule::class);
    }

    /**
     * Shortcut to the setup via the schedule.
     */
    public function setup()
    {
        return $this->hasOneThrough(
            Setup::class,
            ExpectedSchedule::class,
            'id',                     // local key on Repayment (expected_schedule_id)
            'id',                     // local key on ExpectedSchedule (setup_id)
            'expected_schedule_id',   // foreign key on Repayment
            'setup_id'
        );
    }

    /**
     * Shortcut to the beneficiary via the setup.
     */
    public function beneficiary()
    {
        return $this->hasOneThrough(
            Beneficiary::class,
            Setup::class,
            'id',                     // local key on ExpectedSchedule (setup_id)
            'id',                     // local key on Setup (beneficiary_id)
            'expected_schedule_id',   // foreign key on Repayment
            'beneficiary_id'
        );
    }
}
