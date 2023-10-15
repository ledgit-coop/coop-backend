<?php

namespace App\Console\Commands;

use App\Helpers\MemberAccounHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ComputeAccountEarnInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:interest {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute earn interest per day';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date_arg = $this->argument('date');

        $now = $date_arg ? new Carbon($date_arg) : Carbon::now();

        MemberAccounHelper::computeSavingsEarnInterest($now);

        return Command::SUCCESS;
    }
}
