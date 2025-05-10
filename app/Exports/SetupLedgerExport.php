<?php

namespace App\Exports;

use App\Models\Setup;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SetupLedgerExport implements FromView
{
    protected $setup;

    public function __construct(Setup $setup)
    {
        $this->setup = $setup->load('expectedSchedules.repayments', 'beneficiary');
    }

    public function view(): View
    {
        return view('exports.setup-ledger', [
            'setup' => $this->setup
        ]);
    }
}
