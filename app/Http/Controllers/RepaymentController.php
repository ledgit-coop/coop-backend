<?php

namespace App\Http\Controllers;

use App\Constants\Pagination;
use App\Helpers\LoanHelper;
use App\Helpers\LogHelper;
use App\Helpers\MemberAccounHelper;
use App\Models\LoanSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    public function index(Request $request) {

        $loans = LoanSchedule::on();
        $filters = ($request->filters ? (object) $request->filters : null)  ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;

        if($request->member_id)
            $loans->where('member_id', $request->member_id);

        $loans->whereHas('loan', function($loan) {
            $loan->where('released', true);
        });
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $loans->where(function($loans) use($filters) {
                    $loans->orWhereHas('loan.member', function($member) use($filters) {
                        $member->where(function($member) use($filters) {
                            $member->orWhere('surname', 'like', "%$filters->keyword%")
                            ->orWhere('first_name', 'like', "%$filters->keyword%")
                            ->orWhere('middle_name', 'like', "%$filters->keyword%");
                        });
                    })
                    ->orWhereHas('loan', function($member) use($filters) {
                        $member->where(function($member) use($filters) {
                            $member->orWhere('loan_number', 'like', "%$filters->keyword%");
                        });
                    });
                });
            if(isset($filters->due_date)) {
                $dates = [
                    (new Carbon($filters->due_date[0]))->format('Y-m-d'),
                    (new Carbon($filters->due_date[1]))->format('Y-m-d'),
                ];
                $loans->whereBetween('due_date', $dates);
            }

            if(isset($filters->status)) {
                $date = Carbon::now();
                switch ($filters->status) {
                    case 'paid':
                        $loans->where('paid',true);
                    break;
                    case 'past-due':
                        $loans->where('paid',false)->where('overdue',true);
                    break;
                    break;
                    case 'due-today':
                        $loans->where('paid',false)->where('due_date', $date->format('Y-m-d'));
                    break;
                    case 'due-4-days':
                        $date->addDays(4);
                        $loans->where('paid',false)->where('due_date', $date->format('Y-m-d'));
                    break;
                    case 'due-next-month':
                        $date->addMonth(1);
                        $loans->where('paid',false)->whereMonth('due_date', $date->format('m'));
                    break;
                }
            }
        }
        
        if($request->sortField && $request->sortOrder)
            $loans->orderBy($request->sortField, $request->sortOrder);
        else {
            $loans->orderBy('paid', 'asc')->orderBy('due_date', 'asc');
        }

        $loans->with('loan.loanProduct');
        $loans->with('loan.member');

        return response()->json($loans->paginate($limit));
    }
    
    public function store(Request $request, LoanSchedule $loanRepayment) {
        $this->validate($request, [
            'amount_paid' => 'required|numeric|gt:0',
            'payment_remarks' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'payment_channel' => 'required|string',
            'payment_date' => 'required',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            $paymentDate = new Carbon($request->payment_date);

            $loanRepayment = LoanHelper::updatePayment(
                $loanRepayment,
                $request->amount_paid,
                $paymentDate,
                $user,
                $request->payment_remarks,
                $request->payment_reference,
                $request->payment_channel

            );
    
            // Record transaction
            MemberAccounHelper::recordPayment($loanRepayment, $request->amount_paid, $paymentDate);

            // Log Payment
            LogHelper::logLoanPayment($loanRepayment);
    
            DB::commit();

            return response()->json($loanRepayment);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
