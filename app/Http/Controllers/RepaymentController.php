<?php

namespace App\Http\Controllers;

use App\Helpers\LoanHelper;
use App\Models\LoanSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RepaymentController extends Controller
{
    public function index(Request $request) {

        $loans = LoanSchedule::on();
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;

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
                $date = new Carbon($filters->due_date);
                $loans->where('due_date', $date->format('Y-m-d'));
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
            'amount_paid' => 'required',
            'payment_remarks' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'payment_channel' => 'required|string',
        ]);

        $loanRepayment = LoanHelper::updatePayment($loanRepayment, $request->amount_paid);
        $loanRepayment->payment_remarks = $request->payment_remarks;
        $loanRepayment->payment_reference = $request->payment_reference;
        $loanRepayment->payment_channel = $request->payment_channel;
        $loanRepayment->save();

        return response()->json($loanRepayment);
    }
}
