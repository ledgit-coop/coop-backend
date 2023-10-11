<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanFeeTemplateController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/password-reset', [PasswordResetController::class, 'reset'])->name('password.reset')->middleware('guest');
Route::post('/password-reset/request', [PasswordResetController::class, 'request'])->name('password.request')->middleware('guest');

Route::get('/loans/download/{loan}', [LoanController::class, 'download'])->name('loans.download');

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    Route::get('/dashboard/counts', [DashboardController::class, 'counts'])->name('dashboard.counts');
    Route::get('/dashboard/cash-flow', [DashboardController::class, 'cashFlow'])->name('dashboard.cash-flow');
    Route::get('/dashboard/recent-loans', [DashboardController::class, 'recentLoans'])->name('dashboard.recent-loans');

    Route::resource('users', UserController::class)->except(['create', 'edit']);
    Route::resource('accounts', AccountController::class)->except(['create', 'edit']);
    Route::resource('expenses', ExpensesController::class)->except(['create', 'edit']);
    Route::resource('loan-products', LoanProductController::class)->except(['create', 'edit']);
    Route::resource('loan-fees', LoanFeeTemplateController::class)->except(['create', 'edit']);
    Route::post('/loan-fees/{loanFee}/toggle', [LoanFeeTemplateController::class, 'toggle'])->name('loan-fees.toggle');

    Route::resource('loans', LoanController::class)->except(['create', 'edit']);
    Route::get('/loans/active-loans/{member}', [LoanController::class, 'activeLoans'])->name('loans.active');
    Route::post('/loans/status/{loan}', [LoanController::class, 'updateStatus'])->name('loans.status');
    Route::get('/loans/schedule/{loan}', [LoanController::class, 'loanSchedule'])->name('loans.schedule');

    Route::get('/loan-repayments', [RepaymentController::class, 'index'])->name('loan-repayments');
    Route::post('/loan-repayments/{loanRepayment}', [RepaymentController::class, 'store'])->name('loan-repayments.store');

    Route::resource('logs', LogController::class)->except(['create', 'edit']);

    Route::resource('members', MemberController::class)->except(['create', 'edit']);
    Route::post('/members/accounts/add/{id}/{account_id}', [MemberController::class, 'addAccount'])->name('members.account.add');
    Route::post('/members/update/orientation/{member}', [MemberController::class, 'attendedOrientation'])->name('members.update.orientation');
    Route::get('/members/accounts/transaction/{member}', [MemberController::class, 'getAccountTransactions'])->name('members.accounts.transactions.list');
    Route::post('/members/accounts/transaction/{member}', [MemberController::class, 'addAccountTransaction'])->name('members.accounts.transactions.post');
    Route::delete('/members/accounts/transaction/{transaction}', [MemberController::class, 'deleteAccountTransaction'])->name('members.accounts.transactions.destroy');
    Route::delete('/members/accounts/{account}', [MemberController::class, 'deleteAccount'])->name('members.accounts.destroy');
    Route::get('/members/accounts/{member}', [MemberController::class, 'getMemberAccounts'])->name('members.accounts');
    Route::post('/members/accounts/status/{member}', [MemberController::class, 'updateStatus'])->name('members.accounts.status.update');    

    Route::group(['prefix' => 'utility', 'name' => 'utility.'], function() {
        Route::get('/members/dropdown', [UtilityController::class, 'memberDropdown'])->name('members.dropdown');
        Route::get('/members/{member}/account-holders/dropdown', [UtilityController::class, 'memberAccountHolder'])->name('members.account-holder.dropdown');

        Route::get('/accounts/dropdown', [UtilityController::class, 'accountDropdown'])->name('accounts.dropdown');
        Route::get('/accounts/members/dropdown/{member_id}', [UtilityController::class, 'memberAccountDropdown'])->name('accounts.members.dropdown');
        Route::get('/work-industries/dropdown', [UtilityController::class, 'workIndustryDropdown'])->name('work-industries.dropdown');
        Route::get('/loan-products/dropdown', [UtilityController::class, 'loanProductions'])->name('loan-products.dropdown');
        Route::get('/guarantors/dropdown', [UtilityController::class, 'guarantorDropdown'])->name('guarantors.dropdown');
        Route::get('/loan-calculator', [UtilityController::class, 'loanCalculator'])->name('loan-calculator.calculate');
        Route::get('/loan-fee-templates', [UtilityController::class, 'loanFees'])->name('loan-fee-templates');
    });


});