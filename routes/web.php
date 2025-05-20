<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Beneficiary;
use App\Models\Setup;
use App\Models\Repayment;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1) Home → redirect straight to dashboard
Route::redirect('/', '/dashboard')->name('home');

// 2) Dashboard (with optional province filter)
Route::get('/dashboard', function (Request $request) {
    // -- Province dropdown & filter value
    $provinces  = Province::orderBy('name')->get();
    $provinceId = $request->get('province_id');

    // -- Base setups query (summing repayments)
    $setupsQuery = Setup::withSum('repayments', 'payment_amount')
                        ->when($provinceId, fn($q) => $q->where('province_id', $provinceId));

    // 1) Summary counts
    $totalBeneficiaries = Beneficiary::when($provinceId, fn($q) =>
        $q->whereHas('setups', fn($q) =>
            $q->where('province_id', $provinceId)
        )
    )->count();

    $activeSetups = (clone $setupsQuery)
                        ->whereDate('refund_end', '>=', now())
                        ->count();

    $allSetups = $setupsQuery->get();
    $fullyPaid = $allSetups->filter(fn($s) =>
        $s->repayments_sum_payment_amount >= $s->amount_assisted
    )->count();

    // 2) Monthly collection data
    $monthlyRaw = Repayment::selectRaw(
            "DATE_FORMAT(payment_date, '%b %Y') AS month, SUM(payment_amount) AS total"
        )
        ->when($provinceId, fn($q) =>
            $q->whereHas('setup', fn($q) =>
                $q->where('province_id', $provinceId)
            )
        )
        ->groupBy('month')
        ->orderBy('payment_date')
        ->get();

    $chartLabels = $monthlyRaw->pluck('month');
    $chartData   = $monthlyRaw->pluck('total');

    // 3) Build list of all payers with totals, sorted desc
    $payers = Repayment::with('setup.beneficiary')
        ->when($provinceId, fn($q) =>
            $q->whereHas('setup', fn($q) =>
                $q->where('province_id', $provinceId)
            )
        )
        ->get()
        ->groupBy(fn($r) => $r->setup->beneficiary->name)
        ->map(fn($group, $name) => [
            'name' => $name,
            'paid' => $group->sum('payment_amount'),
        ])
        ->sortByDesc('paid')
        ->values();

    return view('dashboard', compact(
        'provinces','provinceId',
        'totalBeneficiaries','activeSetups','fullyPaid',
        'chartLabels','chartData',
        'payers'
    ));
})->name('dashboard');

// 3) Combined Beneficiary & Setup landing
Route::get('/beneficiary-setup', [BeneficiaryController::class, 'index'])
     ->name('beneficiary-setup');

// 4) CRUD for Provinces & Beneficiaries
Route::resource('provinces', ProvinceController::class);
Route::resource('beneficiaries', BeneficiaryController::class);

// 5) Full Setup Projects CRUD at /setups
Route::resource('setups', SetupController::class);

// 6) Alias full Projects CRUD at /projects
Route::resource('projects', SetupController::class)
     ->parameters(['projects' => 'setup']);

// 7) Repayment routes (manual to support ExpectedSchedule binding)
Route::get   ('repayments/create/{expectedSchedule}', [RepaymentController::class, 'create'])
     ->name('repayments.create');
Route::post  ('repayments',                           [RepaymentController::class, 'store'])
     ->name('repayments.store');
Route::get   ('repayments/{repayment}/edit',          [RepaymentController::class, 'edit'])
     ->name('repayments.edit');
Route::patch ('repayments/{repayment}',               [RepaymentController::class, 'update'])
     ->name('repayments.update');
Route::delete('repayments/{repayment}',               [RepaymentController::class, 'destroy'])
     ->name('repayments.destroy');

// 8) Export Setup
Route::get('/setups/{setup}/export',     [SetupController::class, 'export'])
     ->name('setups.export');
Route::get('/setups/{setup}/export-pdf', [SetupController::class, 'exportPdf'])
     ->name('setups.exportPdf');

// 9) Backup download (protected)
Route::get('backup/download', [BackupController::class, 'download'])
     ->name('backup.download')
     ->middleware('auth');
// in routes/web.php
Route::resource('setups', SetupController::class);
// step 2 – the custom schedule form & submit
Route::get('setups/{setup}/schedule/customize', [SetupController::class, 'showCustomizeSchedule'])
     ->name('setups.schedule.customize');
Route::post('setups/{setup}/schedule/customize', [SetupController::class, 'saveCustomizeSchedule'])
     ->name('setups.schedule.customize.save');
