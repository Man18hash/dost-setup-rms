<?php

namespace App\Http\Controllers;

use App\Models\Setup;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // 1) Filter dropdown data
        $years    = Setup::selectRaw('YEAR(refund_start) as year')
                         ->distinct()
                         ->orderBy('year','desc')
                         ->pluck('year');
        $quarters = [1, 2, 3, 4];

        // 2) Selected filters (default to current)
        $year    = $request->get('year', now()->year);
        $quarter = $request->get('quarter', ceil(now()->month / 3));

        // 3) Fetch active setups in that period
        $setups = Setup::where('active', true)
            ->whereYear('refund_start', $year)
            ->whereRaw('CEIL(MONTH(refund_start)/3) = ?', [$quarter])
            ->with('beneficiary')
            ->get()
            ->map(function($s) {
                $checkDate = Carbon::parse($s->check_date);
                $dueDate   = Carbon::parse($s->refund_end);
                $paid      = $s->repayments->sum('payment_amount');
                $status    = $paid >= $s->amount_assisted
                             ? 'Fully Paid'
                             : 'W/ past due';

                return [
                    'spin'            => $s->spin_number,                          // Account Used
                    'owner'           => $s->beneficiary->name,                    // Name of AO/Employee
                    'title'           => $s->project_title,                        // Purpose
                    'date_granted'    => $checkDate->format('Y-m-d'),              // Date Granted
                    'amount_assisted' => $s->amount_assisted,                      // Unliquidated Amount
                    'due_date'        => $dueDate->format('Y-m-d'),                // Due Date for Liquidation
                    'age_months'      => $checkDate->diffInMonths(now()),          // Age of Cash Advance
                    'status'          => $status,                                  // Status
                ];
            });

        // 4) Count deactivated in same period
        $deactivatedCount = Setup::where('active', false)
            ->whereYear('refund_start', $year)
            ->whereRaw('CEIL(MONTH(refund_start)/3) = ?', [$quarter])
            ->count();

        return view('reports.index', compact(
            'years', 'quarters', 'year', 'quarter',
            'setups', 'deactivatedCount'
        ));
    }

    public function exportPdf(Request $request)
    {
        // rebuild same data
        $year    = $request->get('year', now()->year);
        $quarter = $request->get('quarter', ceil(now()->month / 3));

        $setups = Setup::where('active', true)
            ->whereYear('refund_start', $year)
            ->whereRaw('CEIL(MONTH(refund_start)/3) = ?', [$quarter])
            ->with('beneficiary')
            ->get()
            ->map(function($s) {
                $checkDate = Carbon::parse($s->check_date);
                $dueDate   = Carbon::parse($s->refund_end);
                $paid      = $s->repayments->sum('payment_amount');
                $status    = $paid >= $s->amount_assisted
                             ? 'Fully Paid'
                             : 'W/ past due';

                return [
                    'spin'            => $s->spin_number,
                    'owner'           => $s->beneficiary->name,
                    'title'           => $s->project_title,
                    'date_granted'    => $checkDate->format('Y-m-d'),
                    'amount_assisted' => $s->amount_assisted,
                    'due_date'        => $dueDate->format('Y-m-d'),
                    'age_months'      => $checkDate->diffInMonths(now()),
                    'status'          => $status,
                ];
            });

        $deactivatedCount = Setup::where('active', false)
            ->whereYear('refund_start', $year)
            ->whereRaw('CEIL(MONTH(refund_start)/3) = ?', [$quarter])
            ->count();

        $pdf = PDF::loadView('reports.pdf', compact(
            'year', 'quarter', 'setups', 'deactivatedCount'
        ))
        ->setPaper('a4', 'landscape');

        return $pdf->download("Report_{$year}_Q{$quarter}.pdf");
    }
}
