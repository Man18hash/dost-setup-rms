<?php
// database/seeders/BeneficiarySetupSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Beneficiary;
use App\Models\Setup;
use App\Models\ExpectedSchedule;
use Carbon\Carbon;

class BeneficiarySetupSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            // —————————— Client #1 ——————————
            [
                'spin'            => 'SPIN-0001',
                'name'            => 'Delicioso Bakeshop',
                'owner'           => 'Ms. Olive Laccay',
                'address'         => '123 Bakeshop St., Tuguegarao City',
                'province_id'     => 2,
                'project_title'   => 'Upgrading of the Production Processes of Delicioso Bakeshop',
                'check_number'    => '97185',
                'amount_assisted' => 257000,
                'check_date'      => '2015-01-28',
                'refund_start'    => '2016-09-01',
                'refund_end'      => '2019-08-01',
            ],

            // —————————— Client #2 ——————————
            [
                'spin'            => 'SPIN-0002',
                'name'            => 'JW Wood Industry',
                'owner'           => 'Mr & Mrs. Edward M. Salvador',
                'address'         => '456 Lumber Rd., Tuguegarao City',
                'province_id'     => 2,
                'project_title'   => 'Upgrading the Production Process of JW Wood Industry',
                'check_number'    => '97186',
                'amount_assisted' => 440000,
                'check_date'      => '2015-01-28',
                'refund_start'    => '2016-09-01',
                'refund_end'      => '2019-08-01',
            ],
           // —————————— Client #3 ——————————
            [
                'spin'            => 'N/A',
                'name'            => 'JW Wood Industry',
                'owner'           => 'Mr & Mrs. Edward M. Salvador',
                'address'         => '456 Lumber Rd., Tuguegarao City',
                'province_id'     => 2,
                'project_title'   => 'Upgrading the Production Process of JW Wood Industry',
                'check_number'    => '97186',
                'amount_assisted' => 440000,
                'check_date'      => '2015-01-28',
                'refund_start'    => '2016-09-01',
                'refund_end'      => '2019-08-01',
            ],

            // … add as many more clients here …
            // … add as many more clients here …
        ];

        foreach ($clients as $c) {
            // 1) Create Beneficiary
            $b = Beneficiary::create([
                'spin_number' => $c['spin'],
                'name'        => $c['name'],
                'owner'       => $c['owner'],
                'address'     => $c['address'],
            ]);

            // 2) Create Setup
            $setup = Setup::create([
                'spin_number'    => $c['spin'],
                'beneficiary_id' => $b->id,
                'province_id'    => $c['province_id'],
                'project_title'  => $c['project_title'],
                'check_number'   => $c['check_number'],
                'amount_assisted'=> $c['amount_assisted'],
                'check_date'     => $c['check_date'],
                'refund_start'   => $c['refund_start'],
                'refund_end'     => $c['refund_end'],
            ]);

            // 3) Generate every month’s schedule exactly as in your controller
            $this->generateMonthlySchedule($setup);
        }
    }

    private function generateMonthlySchedule(Setup $setup)
    {
        $start  = Carbon::parse($setup->refund_start)->startOfMonth();
        $end    = Carbon::parse($setup->refund_end)->startOfMonth();
        $months = $start->diffInMonths($end) + 1;

        $per   = floor($setup->amount_assisted / $months);
        $last  = $setup->amount_assisted - $per * ($months - 1);

        for ($i = 0; $i < $months; $i++) {
            ExpectedSchedule::create([
                'setup_id'   => $setup->id,
                'due_date'   => $start->copy()->addMonths($i)->format('Y-m-d'),
                'amount_due' => $i < $months - 1 ? $per : $last,
            ]);
        }
    }
}
