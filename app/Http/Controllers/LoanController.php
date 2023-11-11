<?php

namespace App\Http\Controllers;

use App\Constants\MemberLoanStatus;
use App\Constants\Pagination;
use App\Helpers\Exports\ExportFile;
use App\Helpers\LoanHelper;
use App\Helpers\LogHelper;
use App\Helpers\MemberAccounHelper;
use App\Helpers\TransactionHelper;
use App\Http\Requests\LoanApplicationRequest;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\Member;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index(Request $request) {

        $loans = Loan::on();
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;

        if($request->member_id)
            $loans->where('member_id', $request->member_id);

        if(!empty($filters)) {
            if(isset($filters->keyword))
                $loans->where(function($loans) use($filters) {
                    $loans->whereHas('member', function($member) use($filters) {
                        $member->where(function($member) use($filters) {
                            $member->orWhere('surname', 'like', "%$filters->keyword%")
                            ->orWhere('first_name', 'like', "%$filters->keyword%")
                            ->orWhere('middle_name', 'like', "%$filters->keyword%");
                        });
                    })
                    ->orWhere('loan_number', 'like', "%$filters->keyword%");
                });

            if(isset($filters->status))
                $loans->where('status', $filters->status);
            if(isset($filters->loan_product_id))
                $loans->where('loan_product_id', $filters->loan_product_id);
            if(isset($filters->year))
                $loans->whereYear('applied_date', $filters->year);
        }
        if($request->sortField && $request->sortOrder)
            $loans->orderBy($request->sortField, $request->sortOrder);
        else
            $loans->orderByRaw(DB::raw("
                CASE
                    WHEN status = 'pending' THEN 2
                    WHEN status = 'released' THEN 1
                    WHEN status = 'closed' THEN 0
                    ELSE 3
                END desc
            "))
            ->orderBy('applied_date', 'asc');

        $loans->with('loanProduct');
        $loans->with('member');
        $loans->with('loan_fees.loan_fee_template');

        return response()->json($loans->paginate($limit));
    }

    public function store(LoanApplicationRequest $request)
    {
        $data = $request->only([
            'email',
            'member_id',
            'loan_product_id',
            'contact_number',
            'age',
            'civil_status',
            'present_address',
            'home_address',
            'valid_id',
            'tin_number',
            'number_of_children',
            'application_type',
            'employer_name',
            'occupation',
            'work_address',
            'work_industry',
            'loan_purpose',
            'salary_range',
            'applied_amount',
            'principal_amount',
            'disbursed_channel',
            'interest_method',
            'interest_type',
            'loan_interest',
            'loan_interest_period',
            'loan_duration',
            'repayment_cycle',
            'number_of_repayments',
            're_payment_mode',
            're_payment_method',
            'member_account_id',
            'applied_date',
            'loan_duration_type',
            'guarantor_first_id',
            'guarantor_second_id',
            'repayment_mode',
            'released_date',
            'penalty',
            'penalty_duration',
            'penalty_grace_period',
            'penalty_method',

            'pre_termination_panalty',
            'pre_termination_panalty_method',
            'next_payroll_date',
        ]);

        $data['loan_number'] = LoanHelper::generateUniqueLoanNumber();

        if($request->is_draft == true)
            $data['status'] = MemberLoanStatus::DRAFT;

        
        $loan_product = LoanProduct::findOrFail($request->loan_product_id);

        if(Loan::where('loan_product_id', $request->loan_product_id)->where('member_id', $request->member_id)
            ->where('status', '<>', MemberLoanStatus::CLOSED)->exists())
            abort(422, "($loan_product->name) Member has already an existing loan.");
            
        try {
            DB::beginTransaction();

            $loan = Loan::create($data);

            if($request->loan_fees)
                $loan->loan_fees()->createMany($request->loan_fees);
    
            LogHelper::logLoanCreated($loan);
            LoanHelper::makeSchedule($loan);
            DB::commit();

            return response()->json($loan);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Loan $loan)
    {
        $loan = Loan::where('id', $loan->id)
                ->with('loanProduct')
                ->with('member.share_capital_account')
                ->with('member.savings_accounts')
                ->with('guarantor_first')
                ->with('guarantor_second')
                ->with('member_account.account')
                ->with('loan_fees.loan_fee_template')
                ->firstOrFail(); 

        return response()->json($loan);
    }

    public function update(LoanApplicationRequest $request, Loan $loan)
    {
        $data = $request->only([
            'email',
            'loan_product_id',
            'contact_number',
            'age',
            'civil_status',
            'present_address',
            'home_address',
            'valid_id',
            'tin_number',
            'number_of_children',
            'application_type',
            'employer_name',
            'occupation',
            'work_address',
            'work_industry',
            'loan_purpose',
            'salary_range',
            'applied_amount',
            'principal_amount',
            'disbursed_channel',
            'interest_method',
            'interest_type',
            'loan_interest',
            'loan_interest_period',
            'loan_duration',
            'repayment_cycle',
            'number_of_repayments',
            're_payment_mode',
            're_payment_method',
            'member_account_id',
            'applied_date',
            'loan_duration_type',
            'guarantor_first_id',
            'guarantor_second_id',
            'repayment_mode',
            'released_date',
            'penalty',
            'penalty_duration',
            'penalty_grace_period',
            'penalty_method',

            'pre_termination_panalty',
            'pre_termination_panalty_method',
            'next_payroll_date'
        ]);

        try {
            DB::beginTransaction();

            foreach ($data as $key => $value) {
                $loan->{$key} = $value;
            }
    
            if(!$request->is_draft && $loan->status = MemberLoanStatus::DRAFT)
                $loan->status = MemberLoanStatus::PENDING;
            else if($request->is_draft == true)
                $loan->status = MemberLoanStatus::DRAFT;
    
            $loan->save();
    
            if($request->loan_fees)
            {
                foreach ($request->loan_fees as $fee) {
                    $loan->loan_fees()->updateOrCreate([
                        'loan_fee_template_id' => $fee['loan_fee_template_id']
                    ],
                    [
                        ...$fee,
                    ],);
                }
            }
    
            LoanHelper::reComputeSchedule($loan);
    
            DB::commit();

            return response()->json($loan);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateStatus(Request $request, Loan $loan)
    {
        $this->validate($request,[
            'status' => 'required|in:'. implode(",", MemberLoanStatus::LIST),
            'pre_termination_fee' => 'nullable|numeric',
            'pre_termination_date' => 'nullable|date|date_format:Y-m-d',
            'approved_amount' => 'required_if:status,'. MemberLoanStatus::APPROVED .'|numeric'
        ]);

        if($loan->released && !in_array($request->status,[
            MemberLoanStatus::REQUEST_PRE_TERMINATION,
            MemberLoanStatus::PRE_TERMINATED
        ])) return response()->json(['message' => 'Cannot update status of a released loan.'], 422);

        try {
            DB::beginTransaction();

            if($request->status === MemberLoanStatus::REQUEST_PRE_TERMINATION)
                TransactionHelper::makeLoanPreTerminationFee(
                    $loan,
                    new Carbon($request->pre_termination_date),
                    $request->pre_termination_fee,
                );
            else if($request->status === MemberLoanStatus::RELEASED)
                $loan->released = true;
            else if($request->status == MemberLoanStatus::APPROVED)
                $loan->principal_amount = $request->approved_amount; // Change the approved amount
    
            $loan->status = $request->status;
            $loan->save();

            if($request->status == MemberLoanStatus::APPROVED)
                LoanHelper::reComputeSchedule($loan); // Recompute if approved

            else if($request->status == MemberLoanStatus::RELEASED) {
                MemberAccounHelper::recordLoan($loan);
            }

            LogHelper::logLoanStatusChange($loan);
            
            DB::commit();
            
            return response()->json($loan);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy(Loan $loan)
    {
        if($loan->released) return response()->json(['message' => 'Cannot delete released loan.'], 422);

        $loan->delete();

        return response(true);
    }

    public function activeLoans(Member $member) {
        
        $loanSchedules = Loan::where('member_id', $member->id)
            ->with(['loan_schedules', 'loanProduct'])
            ->where('status', '<>', MemberLoanStatus::CLOSED)
            ->get();
        $loanSchedules->each(function ($loanSchedule) {
            $loanSchedule->append(['outstanding']);
        });

        return response()->json($loanSchedules);
    }

    public function loanSchedule(Loan $loan) {
        
        $schedules = $loan->loan_schedules()->orderBy('due_date');

        return response()->json($schedules->get());
    }

    public function download(Request $request, Loan $loan) {
        $this->validate($request, [
            'document' => 'required|in:agreement,terms',
        ]);

        if($request->document == 'agreement') {
            $css = file_get_contents(realpath(public_path('bootstrap-5.0.2/css/bootstrap.min.css')));
            return response()->json(['view' => ExportFile::exportAgreement($loan), 'css' => $css]);
        } else if($request->document == 'terms') {
            return response()->json(['view' => ExportFile::exportLoanTerms($loan)]);
        } else {
            throw new Exception("Document not supported.", 1);
        }
    }


    public function updateLoanSchedule(Request $request, LoanSchedule $schedule)
    {
        $this->validate($request, [
            'due_date' => 'required|date|date_format:Y-m-d',
            'principal_amount' => 'required|numeric|between:0.00,9999999.99',
            'interest_amount' => 'required|numeric|between:0.00,9999999.99',
            'penalty_amount' => 'required|numeric|between:0.00,9999999.99',
            'amount_paid' => 'required|numeric|between:0.00,9999999.99',
        ]);

        try {
            DB::beginTransaction();

            $schedule->due_date = $request->due_date;
            $schedule->principal_amount = $request->principal_amount;
            $schedule->interest_amount = $request->interest_amount;
            $schedule->penalty_amount = $request->penalty_amount;
            $schedule->amount_paid = $request->amount_paid;
            $schedule->due_amount = (
                $schedule->principal_amount + $schedule->interest_amount + $schedule->penalty_amount
            );

            // @Note: this must be excuted first before saving
            LogHelper::logLoanScheduleUpdate($schedule);

            $schedule->save();

            DB::commit();
     
            return response()->json($schedule);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
