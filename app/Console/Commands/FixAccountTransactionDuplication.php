<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAccountTransactionDuplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account-transaction:duplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix account transactions that are duplicated';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::unprepared("
            DROP TABLE if exists TempTable;
            CREATE TEMPORARY TABLE TempTable ( SELECT w.*, ROW_NUMBER() OVER (PARTITION BY w.particular, w.transaction_date, w.amount ORDER BY w.id) AS row_num FROM account_transactions AS w JOIN account_transactions AS z ON z.particular = w.particular AND z.transaction_date = w.transaction_date AND z.amount = w.amount AND z.member_account_id = w.member_account_id AND z.id > w.id );
            update account_transactions w JOIN TempTable t ON t.id = w.id and t.row_num > 1 set w.deleted_at = now();
            DROP TABLE if exists TempTable;
        ");
        return Command::SUCCESS;
    }
}
