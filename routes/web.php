<?php

use App\Helpers\LoanHelper;
use App\Models\LoanSchedule;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
 
    $amount_paid = 1679.16;
    //$amount_paid = 1716.66;
    $schedle = LoanSchedule::where('id', 39)->first();
    
    LoanHelper::updatePayment($schedle, $amount_paid);

        

});
