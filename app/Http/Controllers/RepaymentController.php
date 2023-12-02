<?php

namespace App\Http\Controllers;

use App\Constants\MemberLoanStatus;
use App\Constants\Pagination;
use App\Helpers\LoanHelper;
use App\Helpers\LogHelper;
use App\Helpers\MemberAccounHelper;
use App\Models\LoanPayment;
use App\Models\LoanSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    public function index(Request $request) {
        
        $limit = $request->limit ?? Pagination::PER_PAGE;
        $filters = ($request->filters ? (object) $request->filters : null)  ?? [];

        $loans = LoanPayment::select('loans.*', DB::raw('IFNULL(MaxDueDate.due_date, "") AS due_date'))
            ->with('member')
            ->with('loan_product')
            ->where('status', '<>', MemberLoanStatus::CLOSED)
            ->where('released', true);

        if(!empty($filters)) {
            if(isset($filters->keyword))
                $loans->where(function($loans) use($filters) {
                    $loans->orWhereHas('member', function($member) use($filters) {
                        $member->where(function($member) use($filters) {
                            $member->orWhere('surname', 'like', "%$filters->keyword%")
                            ->orWhere('first_name', 'like', "%$filters->keyword%")
                            ->orWhere('middle_name', 'like', "%$filters->keyword%");
                        });
                    })
                    ->orWhere(function($member) use($filters) {
                        $member->orWhere('loan_number', 'like', "%$filters->keyword%");
                    });
                });

            if(isset($filters->due_date)) {
                $dates = [
                    (new Carbon($filters->due_date[0]))->format('Y-m-d'),
                    (new Carbon($filters->due_date[1]))->format('Y-m-d'),
                ];
                $loans->whereBetween('MaxDueDate.due_date', $dates);
            }

            if(isset($filters->status)) {
                $date = Carbon::now();
                switch ($filters->status) {
                    case 'paid':
                        $loans->leftJoin(DB::raw('(SELECT loan_id, MIN(due_date) AS due_date FROM loan_schedules WHERE paid = true GROUP BY loan_id ORDER BY due_date desc) AS MaxDueDate'), 'loans.id', '=', 'MaxDueDate.loan_id');
                    break;
                    case 'past-due':
                        $loans->leftJoin(DB::raw('(SELECT loan_id, MIN(due_date) AS due_date FROM loan_schedules WHERE paid = false and overdue = true GROUP BY loan_id ORDER BY due_date desc) AS MaxDueDate'), 'loans.id', '=', 'MaxDueDate.loan_id');
                    break;
                    case 'due-today':
                        $date = $date->format('Y-m-d');
                        $loans->leftJoin(DB::raw("(SELECT loan_id, MIN(due_date) AS due_date FROM loan_schedules WHERE paid = false and due_date = '$date' GROUP BY loan_id ORDER BY due_date desc) AS MaxDueDate"), 'loans.id', '=', 'MaxDueDate.loan_id');
                    break;
                    case 'due-4-days':
                        $date->addDays(4);
                        $date = $date->format('Y-m-d');
                        $loans->leftJoin(DB::raw("(SELECT loan_id, MIN(due_date) AS due_date FROM loan_schedules WHERE paid = false and due_date = '$date' GROUP BY loan_id ORDER BY due_date desc) AS MaxDueDate"), 'loans.id', '=', 'MaxDueDate.loan_id');
                    break;
                    case 'due-next-month':
                        $date->addMonth(1);
                        $date = $date->format('Y-m-d');
                        $loans->leftJoin(DB::raw("(SELECT loan_id, MIN(due_date) AS due_date FROM loan_schedules WHERE paid = false and month(due_date) = '$date' GROUP BY loan_id ORDER BY due_date desc) AS MaxDueDate"), 'loans.id', '=', 'MaxDueDate.loan_id');
                    break;
                }
            }
        }

        if(!isset($filters->status))
            $loans->leftJoin(DB::raw('(SELECT loan_id, MIN(due_date) AS due_date FROM loan_schedules WHERE paid = false GROUP BY loan_id ORDER BY due_date desc) AS MaxDueDate'), 'loans.id', '=', 'MaxDueDate.loan_id');

        $loans->with('loan_schedules', function($schedule) use($filters) {

            if(isset($filters->due_date)) {
                $dates = [
                    (new Carbon($filters->due_date[0]))->format('Y-m-d'),
                    (new Carbon($filters->due_date[1]))->format('Y-m-d'),
                ];
                $schedule->whereBetween('due_date', $dates);
            } else if (!isset($filters->status)){
                $schedule->where(function($schedule) {
                    $schedule->orWhere('overdue', true)
                    ->orWhereRaw(DB::raw("
                        id = 
                        (select id from loan_schedules ls2 where 
                        ls2.loan_id = loan_schedules.loan_id and ls2.paid = false and ls2.overdue = false order by due_date asc limit 1)
                    "));
                })
                ->where('paid',false);
            } else {
                if(isset($filters->status)) {
                    $date = Carbon::now();
                    switch ($filters->status) {
                        case 'paid':
                            $schedule->where('paid',true);
                        break;
                        case 'past-due':
                            $schedule->where('paid',false)->where('overdue',true);
                        break;
                        break;
                        case 'due-today':
                            $schedule->where('paid',false)->where('due_date', $date->format('Y-m-d'));
                        break;
                        case 'due-4-days':
                            $date->addDays(4);
                            $schedule->where('paid',false)->where('due_date', $date->format('Y-m-d'));
                        break;
                        case 'due-next-month':
                            $date->addMonth(1);
                            $schedule->where('paid',false)->whereMonth('due_date', $date->format('m'));
                        break;
                    }
                }
            }

            $schedule->orderBy('due_date', 'asc');

        });

                
        if($request->sortField && $request->sortOrder)
            $loans->orderBy($request->sortField, $request->sortOrder);
        else {
            $loans->orderBy('MaxDueDate.due_date', 'asc');
        }
        
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
