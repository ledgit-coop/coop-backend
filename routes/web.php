<?php

use App\Helpers\LoanHelper;
use App\Models\LoanSchedule;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
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
 
    $originalDate = new Carbon('2023-02-28'); // Replace with your original date

    $daysToAdd = 15;
                
    $end_of_month = $originalDate->clone()->endOfMonth();
    $daysDiff = $originalDate->diffInDays($end_of_month);
    $daysInMonth = $originalDate->daysInMonth;

    if($daysDiff > 0 && $daysDiff < 15) {
        if($daysInMonth > 30) {
            $daysToAdd += ($originalDate->daysInMonth - 30); // Excess of 30 days
        }
        else if($daysInMonth < 30)
        {
            $daysToAdd -= (30 - $originalDate->daysInMonth);
           
        }
    }

    // if ($originalDate->day >= 30 && $originalDate->daysInMonth > 30)
    //     $daysToAdd++;
    // else if($originalDate->daysInMonth > 30) {
     
    //     $end_of_month = $originalDate->clone()->endOfMonth();
    //     $daysDiff = $originalDate->diffInDays($end_of_month);

    //     if($daysDiff < 15)
    //         $daysToAdd++;
    // }
    // else if($originalDate->daysInMonth < 30) {
    //     $end_of_month = $originalDate->clone()->endOfMonth();
    //     $daysDiff = $originalDate->diffInDays($end_of_month);

    //     if($daysDiff > 15)
    //         $daysToAdd = $daysDiff + ($daysToAdd - $daysDiff);
    //     else if($daysDiff < 15 && $daysDiff != 0)
    //         $daysToAdd += $daysDiff;
    //     else if($daysDiff > 0)
    //         $daysToAdd = $daysDiff + ($daysToAdd - $daysDiff) - 1;

    //         dd($daysToAdd);
   
    // }            



    return ($originalDate->clone())->addDays($daysToAdd)->format('Y-m-d');


});
