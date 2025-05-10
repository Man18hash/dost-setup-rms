<?php

use Illuminate\Support\Facades\Route;
use App\Models\Beneficiary;
use App\Models\Setup;
use App\Models\Repayment;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\RepaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1) Home → projects.index
Route::get('/', fn() => redirect()->route('projects.index'))->name('home');

// 2) Dashboard
Route::get('/dashboard', function () {
    $totalBeneficiaries = Beneficiary::count();
    $activeSetups       = Setup::whereDate('refund_end', '>=', now())->count();
    $setups             = Setup::withSum('repayments', 'payment_amount')->get();
    $fullyPaid          = $setups->filter(fn($s) =>
        $s->repayments_sum_payment_amount >= $s->amount_assisted
    )->count();

    $monthly = Repayment::selectRaw(
        "DATE_FORMAT(payment_date, '%b %Y') AS month, SUM(payment_amount) AS total"
    )
    ->groupBy('month')
    ->orderBy('payment_date')
    ->pluck('total', 'month');

    return view('dashboard', [
        'totalBeneficiaries' => $totalBeneficiaries,
        'activeSetups'       => $activeSetups,
        'fullyPaid'          => $fullyPaid,
        'chartLabels'        => $monthly->keys(),
        'chartData'          => $monthly->values(),
    ]);
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
Route::get('/setups/{setup}/export', [SetupController::class, 'export'])
     ->name('setups.export');
