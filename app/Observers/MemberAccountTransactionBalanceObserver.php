<?php

namespace App\Observers;

use App\Helpers\AccountHelper;
use App\Models\AccountTransaction;

class MemberAccountTransactionBalanceObserver
{
    /**
     * Handle the Member "created" event.
     *
     * @param  \App\Models\Member  $member
     * @return void
     */
    public function created(AccountTransaction $transaction)
    {
        $account = $transaction->member_account;
        $account->balance += $transaction->amount;   
        $account->save();
        
        AccountHelper::updateTransactionBalance($transaction, $account); 
    }

    /**
     * Handle the Member "updated" event.
     *
     * @param  \App\Models\Member  $member
     * @return void
     */
    public function updated(AccountTransaction $transaction)
    {
        $account = $transaction->member_account;
        $account->balance += $transaction->amount;   
        $account->save();

        AccountHelper::updateTransactionBalance($transaction, $account);
    }

    /**
     * Handle the Member "deleted" event.
     *
     * @param  \App\Models\Member  $member
     * @return void
     */
    public function deleted(AccountTransaction $transaction)
    {
        $account = $transaction->member_account;
        $account->balance -= $transaction->amount;   
        $account->save();

        AccountHelper::updateTransactionBalance($transaction, $account);
    }

    /**
     * Handle the Member "restored" event.
     *
     * @param  \App\Models\Member  $member
     * @return void
     */
    public function restored(AccountTransaction $transaction)
    {
        $account = $transaction->member_account;
        $account->balance += $transaction->amount;   
        $account->save();

        AccountHelper::updateTransactionBalance($transaction, $account);
    }

    /**
     * Handle the Member "force deleted" event.
     *
     * @param  \App\Models\Member  $member
     * @return void
     */
    public function forceDeleted(AccountTransaction $transaction)
    {
        $account = $transaction->member_account;
        $account->balance -= $transaction->amount;   
        $account->save();

        AccountHelper::updateTransactionBalance($transaction, $account);
    }
}
