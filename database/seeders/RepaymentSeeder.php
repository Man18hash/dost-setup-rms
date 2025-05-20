<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setup;
use App\Models\ExpectedSchedule;
use App\Models\Repayment;

class RepaymentSeeder extends Seeder
{
    public function run(): void
    {
        //
        // 1) Define repayment data for each setup, keyed by spin_number
        //    Each inner array has  nine elements in this order:
        //
        //    [0] OR number
        //    [1] OR date (Y-m-d)
        //    [2] payment_amount (decimal)
        //    [3] penalty_amount (decimal)
        //    [4] returned_check (0|1)
        //    [5] deferred (0|1)
        //    [6] deferred_date (Y-m-d|null)
        //    [7] pdc_number (string|null)
        //    [8] pdc_date   (Y-m-d|null)
        //    [9] remarks    (string|null)
        //
        $allData = [

            // ———————————— SPIN-0001: first 6 months normal ————————————
            'SPIN-0001' => [
                '2016-09-01' => ['OR6667215','2016-10-16',7139.00,   0, 0, 0, null, null,   null,       null],
                '2016-10-01' => ['OR6667328','2016-11-10',7139.00,   0, 0, 0, null, null,   null,       null],
                '2016-11-01' => ['OR6667392','2016-12-09',7139.00,   0, 0, 0, null, null,   null,       null],
                '2016-12-01' => ['OR6667561','2017-01-11',7139.00,   0, 0, 0, null, null,   null,       null],
                '2017-01-01' => ['OR6667684','2017-02-14',7139.00,   0, 0, 0, null, null,   null,       null],
                '2017-02-01' => ['OR6667806','2017-03-18',7139.00,   0, 0, 0, null, null,   null,       null],
            ],

            // ——— SPIN-0002: deferred then paid ———
            'SPIN-0002' => [
                // Month 1: deferred (no payment, deferred to March)
                '2016-09-01' => ['OR2001001','2016-10-16',   0.00, 0.00, 0, 1,  '2017-03-01', null,   null, 'Deferred to 01/03/2017'],
                // Month 1 retry: paid on deferred_date
                '2016-09-01-r'=> ['OR2001002','2017-03-05',7139.00,   0.00, 0, 0,  null,        null,   null,       null],
                // Month 2: normal
                '2016-10-01' => ['OR2002002','2016-11-10',7139.00,   0.00, 0, 0,  null,        null,   null,       null],
            ],

            // ——— “N/A”: returned check then retry ———
            '123' => [
                // Month 1: check bounced (100 penalty)
                '2025-05-01' => ['OR3003001','2025-05-10',   0.00,100.00, 1, 0,  null,        null,   null, 'Check bounced – ₱100.00'],
                // Month 1 retry: paid with PDC on same due date
                '2025-05-01-r'=> ['OR3003002','2025-06-05',5000.00,   0.00, 0, 0,  null,'PDC-1234','2025-06-05', null],
            ],

            // ——— Any other SPIN: fallback B-style first 2 months ———
            'FALLBACK' => [
                '2016-09-01' => ['ORXXXXX01','2016-10-01',7000.00,   0, 0, 0,  null,        null,   null,       null],
                '2016-10-01' => ['ORXXXXX02','2016-11-01',7000.00,   0, 0, 0,  null,        null,   null,       null],
            ],
        ];

        foreach ($allData as $spin => $scheduleData) {
            // 2) Lookup setup(s) by spin_number (or fallback)
            $setups = $spin === 'FALLBACK'
                ? Setup::whereNotIn('spin_number', array_keys($allData))->get()
                : Setup::where('spin_number', $spin)->get();

            if ($setups->isEmpty()) {
                $this->command->warn("Skipping “{$spin}” – no Setup found.");
                continue;
            }

            foreach ($setups as $setup) {
                // 3) Load schedules keyed by due_date (+ “-r” for retries)
                $schedules = ExpectedSchedule::where('setup_id', $setup->id)
                    ->orderBy('due_date')
                    ->get()
                    ->flatMap(function($s) {
                        $key = $s->due_date->format('Y-m-d');
                        return [
                            $key       => $s,
                            $key . '-r' => $s,
                        ];
                    });

                // 4) Seed repayments
                foreach ($scheduleData as $dueKey => $info) {
                    if (! isset($schedules[$dueKey])) {
                        $this->command->warn(" • No schedule for {$setup->spin_number} on {$dueKey}");
                        continue;
                    }

                    [
                      $orNumber,
                      $orDate,
                      $amt,
                      $penalty,
                      $returned,
                      $deferred,
                      $deferredDate,
                      $pdcNumber,
                      $pdcDate,
                      $remark
                    ] = $info;

                    Repayment::create([
                        'expected_schedule_id' => $schedules[$dueKey]->id,
                        'payment_amount'       => $amt,
                        'payment_date'         => $orDate,
                        'or_number'            => $orNumber,
                        'or_date'              => $orDate,
                        'penalty_amount'       => $penalty,
                        'returned_check'       => $returned,
                        'deferred'             => $deferred,
                        'deferred_date'        => $deferredDate,
                        'pdc_number'           => $pdcNumber,
                        'pdc_date'             => $pdcDate,
                        'remarks'              => $remark,
                    ]);

                    $this->command->info(" • Seeded {$setup->spin_number} on {$dueKey}");
                }
            }
        }
    }
}
