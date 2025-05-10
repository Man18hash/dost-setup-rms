<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setup extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiary_id',
        'province_id',
        'spin_number',
        'project_title',
        'check_number',
        'amount_assisted',
        'check_date',
        'refund_start',
        'refund_end',
    ];

    /**
     * The beneficiary for this setup.
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    /**
     * The province for this setup.
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * The expected monthly schedules for this setup.
     */
    public function expectedSchedules()
    {
        return $this->hasMany(ExpectedSchedule::class);
    }

    /**
     * All repayments across all schedules of this setup.
     */
    public function repayments()
    {
        return $this->hasManyThrough(
            Repayment::class,
            ExpectedSchedule::class,
            'setup_id',             // FK on expected_schedules
            'expected_schedule_id', // FK on repayments
            'id',                   // Local key on setups
            'id'                    // Local key on expected_schedules
        );
    }
}
