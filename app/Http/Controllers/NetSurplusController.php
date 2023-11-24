<?php

namespace App\Http\Controllers;

use App\Constants\Pagination;
use App\Constants\Settings;
use App\Helpers\Exports\ExportFile;
use App\Helpers\Helper;
use App\Models\AnnualReturn;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NetSurplusController extends Controller
{
    public function index(Request $request) {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;
        
        $types = AnnualReturn::on();

        if($request->sortField && $request->sortOrder)
            $types->orderBy($request->sortField, $request->sortOrder);


        return $types->paginate($limit);
    }

    public function show($id)
    {
        $netSurplus = AnnualReturn::findOrFail($id);

        $memberShareCapitals = DB::table('account_transactions')->select([
            'members.id',
            'members.member_number',
            'members.first_name',
            'members.middle_name',
            'members.surname',
            DB::raw("MONTH(account_transactions.transaction_date) AS `month`"),
            DB::raw("YEAR(account_transactions.transaction_date) AS `year`"),
            DB::raw("SUM(`account_transactions`.`amount`) AS `total`")
        ])
        ->groupBy([
            'members.id',
            'members.member_number',
            'members.first_name',
            'members.middle_name',
            'members.surname',
            DB::raw("MONTH(account_transactions.transaction_date)"),
            DB::raw("YEAR(account_transactions.transaction_date)"),
        ])
        ->join('member_accounts', function (JoinClause $join) {
            $join->on('member_accounts.id', '=', 'account_transactions.member_account_id')
                 ->whereRaw(DB::raw("EXISTS(SELECT 1 FROM `accounts` WHERE  `accounts`.`id` = `member_accounts`.`account_id`  AND `accounts`.`type` = 'share-capital')"));
        })
        ->join('members', 'members.id', '=', 'member_accounts.member_id')
        ->whereBetween('account_transactions.transaction_date', [$netSurplus->from_date->format("Y-m-d"), $netSurplus->to_date->format("Y-m-d")])
        ->whereNull('deleted_at')
        ->get()
        ->groupBy('id');
 
        $memberLoanInterest = DB::table('loan_schedules')
            ->select([
                'members.id',
                DB::raw("SUM(`loan_schedules`.`interest_amount`) AS `total`")
            ])
            ->join('loans', function (JoinClause $join) {
                $join->on('loans.id', '=', 'loan_schedules.loan_id')
                    ->whereNull('loans.deleted_at')
                    ->where('loans.released', true);
            })
            ->join('members', function (JoinClause $join) {
                $join->on('members.id', '=', 'loans.member_id');
            })
            ->where('loan_schedules.paid', true)
            ->groupBy([
                'members.id',
            ])
            ->whereBetween('loan_schedules.due_date', [
                $netSurplus->from_date->format("Y-m-d"),
                $netSurplus->to_date->format("Y-m-d")
            ])
            ->get();

        $dates = Helper::generateMonthYearArray($netSurplus->from_date, $netSurplus->to_date);
        
        return response()->json(['view' => ExportFile::exportNetSurplus($netSurplus, $memberShareCapitals, $memberLoanInterest, $dates)]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'from_date' => 'required|date',
            'to_date'   => 'required|date',
        ]);

        $from = (Carbon::parse($request->from_date))->format("Y-m-d");
        $to = (Carbon::parse($request->to_date))->format("Y-m-d");
        $user = Auth::user();

        if(AnnualReturn::where('from_date', $from)->where('to_date', $to)->exists())
            abort(422, "Selected dates for net surplus report is already exist.");

        try {

            DB::unprepared("SET @p0='$from'; SET @p1='$to'; CALL `net_surplus_allocation_proc`(@p0, @p1);");
            $net_surplus = DB::table('net_surplus_allocation_proc_temp')->first();
    
            DB::unprepared("SET @p0='$from'; SET @p1='$to'; CALL `statutory_funds_proc`(@p0, @p1);");
            $statutory_funds = DB::table('statutory_funds_proc_temp')->first();
    
            $settings = DB::table('settings')->select('key','value')->whereIn('key', [
                Settings::ALLOCATION_RESERVE_FUND,
                Settings::EDUCATIONAL_TRAINING_FUND,
                Settings::OPTIONAL_FUND,
                Settings::REMAINDER_INTEREST_SHARE_CAPITAL,
                Settings::REMAINDER_PATRONAGE_REFUND,
            ])->get()->pluck('value', 'key')->toArray();
    
            // @Note: Necessary to drop the `statutory_funds_total` and `net_surplus_allocation_proc_temp`
            DB::unprepared("drop table if exists statutory_funds_total;");
            DB::unprepared("drop table if exists net_surplus_allocation_proc_temp;");
            
            $allocated = collect(DB::select("SELECT 
                `statutory_funds_total`('$from', '$to') as statutory_funds_total, 
                `net_surplus_total`('$from', '$to') as net_surplus_total,
                `share_capital_interest_allocation`('$from', '$to') as share_capital_interest_allocation,
                `share_capital_rate_interest`('$from', '$to') as share_capital_rate_interest,
                `patronage_refund_rate_interest`('$from', '$to') as patronage_refund_rate_interest,
                `patronage_refund_allocation`('$from', '$to') as patronage_refund_allocation
    
            "))->first();
    
            $data = [
                'to_date' => $to,
                'from_date' => $from,
                'interest_income_on_loan' => $net_surplus->loan_interest,
                'membership_fees' => $net_surplus->membership_fee,
                'service_fees' => $net_surplus->service_fee,
                'gross_surplus' => $net_surplus->gross_surplus,
                'operating_expenses' => $net_surplus->operating_expenses,
                'net_suprplus_allocation_distribution' => $net_surplus->net_surplus,
    
            
                'reserve_fund' => $statutory_funds->allocation_reserve_fund,
                'educational_training_fund' => $statutory_funds->educational_training_fund,
    
                'educational_training_fund_due_cetf' => $statutory_funds->educational_training_fund_due_cetf,
                'educational_training_fund_due_etf' => $statutory_funds->educational_training_fund_due_etf,
                'optional_fund' => $statutory_funds->optional_fund,
    
                'reserve_fund_percent' => $settings[Settings::ALLOCATION_RESERVE_FUND],
                'educational_training_fund_percent' => $settings[Settings::EDUCATIONAL_TRAINING_FUND],
                'optional_fund_percent' => $settings[Settings::OPTIONAL_FUND],
     
                'interest_on_share_capital_allocation_percent' => $settings[Settings::REMAINDER_INTEREST_SHARE_CAPITAL],
                'patronage_refund_allocation_percent' => $settings[Settings::REMAINDER_PATRONAGE_REFUND],

                'interest_on_share_capital' => $allocated->share_capital_interest_allocation,
                'interest_on_share_capital_rate_interest' => $allocated->share_capital_rate_interest,
                'net_surplus_allocated_distributed' => $allocated->net_surplus_total - $allocated->statutory_funds_total,
    
                'patronage_refund' => $allocated->patronage_refund_allocation,
                'patronage_refund_rate_interest' => $allocated->patronage_refund_rate_interest,
                'created_by' => $user->id,
            ];
    
            DB::beginTransaction();
            
            $annual = AnnualReturn::create($data);

            DB::commit();

            return response()->json($annual);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    { 
        $netSurplus = AnnualReturn::findOrFail($id);
        $netSurplus->delete();
        
        return response(true);
    }
}
