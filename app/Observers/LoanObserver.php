<?php

namespace App\Observers;

use App\Helpers\LoanHelper;
use App\Models\Loan;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     *
     * @param  \App\Models\Loan  $loan
     * @return void
     */
    public function created(Loan $loan)
    {
    
    }

    /**
     * Handle the Loan "updated" event.
     *
     * @param  \App\Models\Loan  $loan
     * @return void
     */
    public function updated(Loan $loan)
    {
        //
    }

    /**
     * Handle the Loan "deleted" event.
     *
     * @param  \App\Models\Loan  $loan
     * @return void
     */
    public function deleted(Loan $loan)
    {
        //
    }

    /**
     * Handle the Loan "restored" event.
     *
     * @param  \App\Models\Loan  $loan
     * @return void
     */
    public function restored(Loan $loan)
    {
        //
    }

    /**
     * Handle the Loan "force deleted" event.
     *
     * @param  \App\Models\Loan  $loan
     * @return void
     */
    public function forceDeleted(Loan $loan)
    {
        //
    }
}
