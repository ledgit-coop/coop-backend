<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SQLSeeder extends Seeder
{
    protected $sqls = [
        # SQL Functions
        'sql/functions/net_surplus_total.sql',
        'sql/functions/patronage_refund_allocation.sql',
        'sql/functions/patronage_refund_rate_interest.sql',
        'sql/functions/share_capital_interest_allocation.sql',
        'sql/functions/share_capital_rate_interest.sql',
        'sql/functions/statutory_funds_total.sql',

        # SQL Procedures
        'sql/procedures/member_share_capital_shares.sql',
        'sql/procedures/net_surplus_allocation_proc.sql',
        'sql/procedures/statutory_funds_proc.sql',

        # SQL Views
        'sql/views/loan_audit_view.sql',
        'sql/views/loan_transactions_view.sql',
        'sql/views/member_summary_view.sql',

    ];

    public function run(): void
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS net_surplus_total;
            DROP FUNCTION IF EXISTS patronage_refund_allocation;
            DROP FUNCTION IF EXISTS patronage_refund_rate_interest;
            DROP FUNCTION IF EXISTS share_capital_interest_allocation;
            DROP FUNCTION IF EXISTS statutory_funds_total;
            DROP FUNCTION IF EXISTS share_capital_rate_interest;

            DROP PROCEDURE IF EXISTS member_share_capital_shares;
            DROP PROCEDURE IF EXISTS net_surplus_allocation_proc;
            DROP PROCEDURE IF EXISTS statutory_funds_proc;

            DROP VIEW IF EXISTS loan_audit_view;
            DROP VIEW IF EXISTS loan_transactions_view;
            DROP VIEW IF EXISTS member_summary_view;
        ");

        foreach ($this->sqls as $sql) {
            $path = database_path($sql);
            DB::unprepared(file_get_contents($path));
        }
    }
}
