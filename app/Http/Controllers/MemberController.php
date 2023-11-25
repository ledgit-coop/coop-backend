<?php

namespace App\Http\Controllers;

use App\Constants\AccountType;
use App\Constants\ActionTransaction;
use App\Constants\MemberAccountTransactionType;
use App\Constants\MemberLoanStatus;
use App\Constants\Pagination;
use App\Helpers\AccountHelper;
use App\Helpers\Helper;
use App\Helpers\LogHelper;
use App\Helpers\MemberAccounHelper;
use App\Helpers\MemberHelper;
use App\Helpers\TransactionHelper;
use App\Helpers\Uploading;
use App\Http\Requests\AddAccountTransactionRequest;
use App\Http\Requests\MemberRequest;
use App\Http\Requests\MemberUpdateRequest;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Loan;
use App\Models\Log as ModelsLog;
use App\Models\Member;
use App\Models\MemberAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;
        
        $members = Member::select([
            'id',
            'member_number',
            'surname', 
            'first_name',
            'middle_name',
            'email_address', 
            'gender', 
            'member_at', 
            'status',
            'profile_picture_url',
        ]);
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $members->where(function($members) use($filters) {
                    $members->orWhere('member_number', 'like', "%$filters->keyword%")
                    ->orWhere('surname', 'like', "%$filters->keyword%")
                    ->orWhere('first_name', 'like', "%$filters->keyword%")
                    ->orWhere('middle_name', 'like', "%$filters->keyword%")
                    ->orWhere('email_address', 'like', "%$filters->keyword%");
                });
            if(isset($filters->status))
                $members->where('status', $filters->status);

            if(isset($filters->others)) {
                switch ($filters->others) {
                    case 'not-paid-membership':
                        $members->where('paid_membership', false);
                        break;
                    case 'not-attended-orientation':
                        $members->where('oriented', false);
                        break;
                    case 'already-paid-membership':
                        $members->where('paid_membership', true);
                        break;
                    case 'already-attended-orientation':
                        $members->where('paid_membership', true);
                        break;
                    case 'guarantor-twice':
                        $members->whereRaw(
                            DB::raw("
                                (select count(*) from loans where loans.status <> '". MemberLoanStatus::CLOSED ."' and (loans.guarantor_first_id = members.id or loans.guarantor_second_id = members.id)) >= 2
                            ")
                        );
                    break;
                    case 'guarantor-once':
                        $members->whereRaw(
                            DB::raw("
                                (select count(*) from loans where loans.status <> '". MemberLoanStatus::CLOSED ."' and (loans.guarantor_first_id = members.id or loans.guarantor_second_id = members.id)) >= 1
                            ")
                        );
                    break;
                }
            }
        }

        if($request->sortField && $request->sortOrder)
            $members->orderBy($request->sortField, $request->sortOrder);

        return $members->paginate($limit);
    }
    
    public function exportMembersCSV(Request $request)
    {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;
        
        $members = Member::on();
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $members->where(function($members) use($filters) {
                    $members->orWhere('member_number', 'like', "%$filters->keyword%")
                    ->orWhere('surname', 'like', "%$filters->keyword%")
                    ->orWhere('first_name', 'like', "%$filters->keyword%")
                    ->orWhere('middle_name', 'like', "%$filters->keyword%")
                    ->orWhere('email_address', 'like', "%$filters->keyword%");
                });
            if(isset($filters->status))
                $members->where('status', $filters->status);

            if(isset($filters->others)) {
                switch ($filters->others) {
                    case 'not-paid-membership':
                        $members->where('paid_membership', false);
                        break;
                    case 'not-attended-orientation':
                        $members->where('oriented', false);
                        break;
                    case 'already-paid-membership':
                        $members->where('paid_membership', true);
                        break;
                    case 'already-attended-orientation':
                        $members->where('paid_membership', true);
                        break;
                    case 'gurantor-twice':
                        $members->whereRaw(
                            DB::raw("
                                (select count(*) from loans where loans.status <> '". MemberLoanStatus::CLOSED ."' and (guarantor_first_id = members.id or guarantor_second_id = members.id)) > 2
                            ")
                        );
                        break;
                    case 'guarantor-once':
                        $members->whereRaw(
                            DB::raw("
                                (select count(*) from loans where loans.status <> '". MemberLoanStatus::CLOSED ."' and (loans.guarantor_first_id = members.id or loans.guarantor_second_id = members.id)) >= 1
                            ")
                        );
                        break;
                }
            }
        }

        if($request->sortField && $request->sortOrder)
            $members->orderBy($request->sortField, $request->sortOrder);

        $members = $members->limit($limit)->get();

        $columnNames = [
            'Member Number',
            'Surname',
            'First Name',
            'Middle Name',
            'Name Extension',
            'Civil Status',
            'Status',
            'Date of Birth',
            'Place of Birth',
            'Gender',
            'Date Hired',
            'Department',
            'Position',
            'Employee No',
            'TIN No',
            'Email Address',
            'Mobile Number',
            'Telephone Number',
            'Present Address',
            'Permanent Address',
            'Spouse Name',
            'Father\'s Name',
            'Mothers\'s Name',
            'In Case of Emergency Person',
            'In Case of Emergency Address',
            'In Case of Emergency Contact',
            'Member At',
        ];

        $members = $members->map(function($member) {
            return [
                $member->member_number,
                $member->surname,
                $member->first_name,
                $member->middle_name,
                $member->name_extension,
                $member->civil_status,
                $member->status,
                $member->date_of_birth->format("Y-m-d"),
                $member->place_of_birth,
                $member->gender,
                $member->date_hired->format("Y-m-d"),
                $member->department,
                $member->position,
                $member->employee_no,
                $member->tin_no,
                $member->email_address,
                $member->mobile_number,
                $member->telephone_number,
                $member->full_present_address,
                $member->full_permanent_address,
                $member->spouse->full_name,
                $member->father->full_name,
                $member->mother->full_name,
                $member->in_case_emergency_person,
                $member->in_case_emergency_address,
                $member->in_case_emergency_contact,
                $member->member_at->format("Y-m-d"),
            ];
        })->values();

        return response()->json([
            $columnNames,
            ...$members,
        ]);
    }
    
    public function store(MemberRequest $request)
    {
        $data = $request->only([
            'surname',
            'first_name',
            'middle_name',
            'name_extension',
            'date_of_birth',
            'place_of_birth',
            'gender',
            'date_hired',
            'department',
            'position',
            'employee_no',
            'tin_no',
            'email_address',
            'member_at',
            'oriented',
            'paid_membership',
            'mobile_number',
            'telephone_number',
            'in_case_emergency_person',
            'in_case_emergency_address',
            'in_case_emergency_contact',
            'civil_status',
        ]);

        $data['member_number'] = MemberHelper::generateID();

        try {
            DB::beginTransaction();

            $member = Member::create($data);
        
            // Upload image
            if(!empty($request->profile_picture_url) && Helper::isDataImageValid($request->profile_picture_url)) {
                $member->profile_picture_url = Uploading::memberImage($member, $request->profile_picture_url);
                $member->save();
            }
    
            $member->beneficiaries()->createMany([...$request->beneficiaries]);
    
            $member->member_addresses()->createMany([
                [
                    ...$request->permanent_address,
                    'type' => 'permanent',
                ],
                [
                    ...$request->present_address,
                    'type' => 'present',
                ]
            ]);
    
            $member->member_related_people()->createMany([
                [
                    ...$request->father,
                    'type' => 'father',
                ],
                [
                    ...$request->mother,
                    'type' => 'mother',
                ],
                [
                    ...$request->spouse,
                    'type' => 'spouse',
                ]
            ]);
            

            if(isset($request->record_membership_payment) && isset($request->membership_fee_amount)) {
                $transaction = TransactionHelper::makeMembershipTransaction($member, new Carbon($request->member_at), $request->membership_fee_amount);
                LogHelper::logMembeshipPayment($member, $transaction);
                $member->paid_membership = true;
                $member->save();
            }

            if($request->record_orientation_fee && $request->orientation_fee_amount && $request->orientation_date) {
                $transaction = TransactionHelper::makeOrientationPaymentTransaction($member, new Carbon($request->orientation_date), $request->orientation_fee_amount);
                LogHelper::logOrientationPayment($member, $transaction);
                $member->oriented = true;
                $member->save();
            }

            DB::commit();

            return response()->json(Member::find($member->id));


        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(MemberUpdateRequest $request, Member $member)
    {
        $data = $request->only([
            'surname',
            'first_name',
            'middle_name',
            'name_extension',
            'date_of_birth',
            'place_of_birth',
            'gender',
            'date_hired',
            'department',
            'position',
            'employee_no',
            'tin_no',
            'email_address',
            'member_at',
            'oriented',
            'mobile_number',
            'telephone_number',
            'member_number',
            'in_case_emergency_person',
            'in_case_emergency_address',
            'in_case_emergency_contact',
            'civil_status',
        ]);

        try {
            DB::beginTransaction();


            foreach ($data as $key => $value) {
                Log::info("$key : $value");
                $member->{$key} = $value;
            }

            // Upload image
            if(!empty($request->profile_picture_url) && Helper::isDataImageValid($request->profile_picture_url)) {
                $member->profile_picture_url = Uploading::memberImage($member, $request->profile_picture_url);
            }

            $member->save();

            $member->member_addresses()->updateOrCreate([
                'type' => 'permanent'
            ],
            [
                ...$request->permanent_address,
            ],);

            $member->member_addresses()->updateOrCreate([
                'type' => 'present'
            ],
            [
                ...$request->present_address,
            ],);

            $member->member_related_people()->updateOrCreate(
                [
                    'type' => 'father',
                ],
                [
                    
                    ...$request->father,
                ] 
            );


            $member->member_related_people()->updateOrCreate(
                [
                    'type' => 'mother',
                ],
                [
                    
                    ...$request->mother,
                ] 
            );


            $member->member_related_people()->updateOrCreate(
                [
                    'type' => 'spouse',
                ],
                [
                    
                    ...$request->spouse,
                ] 
            );

            $member->beneficiaries()->delete();

            $member->beneficiaries()->createMany([...$request->beneficiaries]);
            
            DB::commit();

            return response()->json(Member::find($member->id));
            
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Member $member)
    {
        return [
        
            'id' => $member->id,
            'full_name' => $member->full_name,
            'full_present_address' => $member->full_present_address,
            'full_permanent_address' => $member->full_permanent_address,
            
            'present_address' => $member->present_address,
            'permanent_address' => $member->permanent_address,

            'mother' => $member->mother,
            'father' => $member->father,
            'spouse' => $member->spouse,
            'beneficiaries' => $member->beneficiaries,
            'member_year' => $member->member_at->format('Y'),
            'residency_status' => $member->residency_status,
            'share_capital' => $member->share_capital_account ? [
                'id' => $member->share_capital_account->id,
                'balance' => $member->share_capital_account->balance,
                'latest_transaction' => $member->share_capital_account->latest_transaction,
            ] : null,
            'mortuary' => $member->mortuary_account ? [
                'id' => $member->mortuary_account->id,
                'balance' => $member->mortuary_account->balance,
                'latest_transaction' => $member->mortuary_account->latest_transaction,
            ] : null,
            'savings_accounts' => $member->savings_accounts->map(function($account) {
                return [
                    'id' => $account->id,
                    "name" => $account->account->name,
                    'interest_per_anum' => $account->interest_per_anum,
                    'balance' => $account->balance,
                    'latest_transaction' => $account->latest_transaction,
                ];
            }),
            ...$member->toArray(),
        ];
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return true;
    }

    public function addAccount(Request $request, $id, $account_id) {

        $this->validate($request, ['account_holder' => 'required']);

        $member = Member::findOrFail($id);
        $account = Account::findOrFail($account_id);

        $is_holder_member = strtolower(trim($member->full_name)) == strtolower(trim($request->account_holder));

        $member_account = MemberAccount::where([
            'member_id' => $member->id,
            'account_id' => $account->id,
        ])->first();

        if($member_account && $member_account->account->type == AccountType::SHARE_CAPITAL)
            return response()->json([
                "message" => "Account already exists."
            ], 422);

        $holder_exists = MemberAccount::where([
            'account_holder' => $request->account_holder,
            'account_id' => $account->id,
        ])->first();

        if($holder_exists)
            return response()->json([
                "message" => "Cannot open an account with the same holder name."
            ], 422);

        MemberHelper::makeAccount($member, $account, $request->account_holder, $is_holder_member);
        
        return response('Account created.');
    }

    public function attendedOrientation(Member $member) {
        
        $member->oriented = true;
        $member->save();

        return response('Account updated.');
    }

    public function updateStatus(Request $request, Member $member) {
        
        $this->validate($request, [
            'status' => 'required',
        ]);

        $member->status = $request->status;
        $member->save();

        return response('Account updated.');
    }

    public function addAccountTransaction(AddAccountTransactionRequest $request, Member $member) {
        switch ($request->transaction_type) {

            case ActionTransaction::DepositShareCapital:

                $account = $member->share_capital_account;

                if(!$account)
                    throw new Exception("No share capital account.", 422);

                $account->transactions()->createMany([
                    [
                        'transaction_number' => AccountHelper::generateTransactionNumber(),
                        'particular' => "Share Capital Deposit",
                        'transaction_date' => $request->transaction_date,
                        'amount' => $request->amount,
                        'type' => MemberAccountTransactionType::SHARE_CAPITAL,
                    ]
                ]);
                break;
            case ActionTransaction::WithdrawShareCapital:

                $account = $member->share_capital_account;

                if(!$account)
                    throw new Exception("No share capital account.", 422);

                $account->transactions()->createMany([
                    [
                        'transaction_number' => AccountHelper::generateTransactionNumber(),
                        'particular' => "Share Capital Withdrawal",
                        'transaction_date' => $request->transaction_date,
                        'amount' => (-$request->amount),
                        'type' => MemberAccountTransactionType::SHARE_CAPITAL,
                    ]
                ]);
                break;
            case ActionTransaction::PayMortuary:

                $account = $member->mortuary_account;

                if(!$account)
                    throw new Exception("No mortuary account.", 422);

                $account->transactions()->createMany([
                    [
                        'transaction_number' => AccountHelper::generateTransactionNumber(),
                        'particular' => "Mortuary Deposit",
                        'transaction_date' => $request->transaction_date,
                        'amount' => $request->amount,
                        'type' => MemberAccountTransactionType::MORTUARY,
                    ]
                ]);

                break;
            case ActionTransaction::DepositSavings:

                $account = $member->savings_accounts()->where('id', $request->member_account_id)->first();

                if(!$account)
                    throw new Exception("Account not exists", 422);

                $account->transactions()->createMany([
                    [
                        'transaction_number' => AccountHelper::generateTransactionNumber(),
                        'particular' => $request->particular,
                        'transaction_date' => $request->transaction_date,
                        'amount' => $request->amount,
                    ]
                ]);
                break;

            case ActionTransaction::WithdrawSavings:

                $account = $member->savings_accounts()->where('id', $request->member_account_id)->first();

                if(!$account)
                    throw new Exception("Account not exists", 422);

                $account->transactions()->createMany([
                    [
                        'transaction_number' => AccountHelper::generateAccount(),
                        'particular' => $request->particular,
                        'transaction_date' => $request->transaction_date,
                        'amount' => (-$request->amount),
                    ]
                ]);
                break;
            
            case ActionTransaction::PayMembership:
                
                $transaction = TransactionHelper::makeMembershipTransaction($member, new Carbon($request->transaction_date), $request->amount);
                LogHelper::logMembeshipPayment($member, $transaction);
                $member->paid_membership = true;
                $member->save();

            break;

            case ActionTransaction::PayOrientation:
                
                $transaction = TransactionHelper::makeOrientationPaymentTransaction($member, new Carbon($request->transaction_date), $request->amount);
                LogHelper::logOrientationPayment($member, $transaction);

            break;
            
            default:
            throw new Exception("Transaction not supported.");
            break;
        }

       
        
        return response('Transaction created.');
    }

    public function getMemberAccounts(Request $request, Member $member) {
        $accounts = $member->member_accounts()->with('account');

   
        if(!empty($request->year))
            $accounts->whereYear('created_at', $request->year);

        if(!empty($request->status))
            $accounts->where('status', $request->status);


        return response()->json($accounts->get());
    }

    public function getAccountTransactions(Request $request, Member $member) {
        $transactions = AccountTransaction::whereHas('member_account', function($account) use($member) {
            $account->where('member_id', $member->id);
        });
        
        if(isset($request->member_account_id) && $request->member_account_id !== null)
            $transactions->where('member_account_id', $request->member_account_id);

        if(!empty($request->year))
            $transactions->whereYear('transaction_date', $request->year);

        if(!empty($request->type))
            $transactions->whereHas('member_account', function($account) use($request) {
                $account->whereHas('account', function($account) use($request) {
                    $account->where('type', $request->type);
                });
            });

        $transactions->orderBy('transaction_date', 'asc')->orderBy('created_at', 'asc');

        return response()->json($transactions->get());
    }

    public function deleteAccountTransaction(AccountTransaction $transaction) {
        
        try {
            $member_account = $transaction->member_account;

            DB::beginTransaction();
            
            $transaction->delete();

            MemberAccounHelper::fixAccounBalance($member_account);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        
        return true;
    }

    public function deleteAccount(MemberAccount $account) {
        if($account->has_balance) throw new Exception("Cannot delete the account that has already a balance.", 1);

        $account->delete();
        
        return true;
    }

    public function download(Request $request, Member $member) {
        $this->validate($request, [
            'document' => 'required|in:profile',
        ]);
        
        if($request->document == 'profile') {
            $view = view('exports.members.profile', compact('member'))->render();
            return response()->json(['view' => $view]);
        } else {
            throw new Exception("Document not supported.", 1);
        }
    }

    public function overview(Member $member) {
        $recentActivities = ModelsLog::where(function($logs) use($member) {
            $logs->where('model', Member::class)
            ->where('model_id', $member->id);
            // Checks if parent from other logs
            $logs->orWhere(function($logs) use($member) {
                $logs->where('parent_model', Member::class)
                ->where('parent_model_id', $member->id);
            });
        })
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();

        $activeLoans = $member->loans()->where('released', true)->with('loan_product')->get()->map(function($loan) {
            return [
                'interest' => $loan->loan_interest,
                'period' => $loan->loan_interest_period,
                'amount' => $loan->principal_amount,
                'name' => $loan->loan_product->name,
            ];
        });

        $pendingLoans = $member->loans()->where('released', false)->with('loan_product')->get()->map(function($loan) {
            return [
                'interest' => $loan->loan_interest,
                'applied_date' => $loan->applied_date,
                'period' => $loan->loan_interest_period,
                'status' => $loan->status,
                'amount' => $loan->principal_amount,
                'name' => $loan->loan_product->name,
            ];
        });

        $savingsAccounts = $member->member_accounts()->whereHas('account', function($account) {
            $account->where('type', AccountType::SAVINGS);
        })->with('account')->get();
        
        $gauranteeLoans = Loan::with('loan_product')->with('member')->where('status', '<>', MemberLoanStatus::CLOSED)->where(function($guarantor) use($member) {
            $guarantor->orWhere('guarantor_first_id', $member->id)
            ->orWhere('guarantor_second_id', $member->id);
        })->get();
        

        return response()->json([
            'recent_activities' => $recentActivities,
            'active_loans' => $activeLoans,
            'pending_loans' => $pendingLoans,
            'savings_accounts' => $savingsAccounts,
            'gaurantee_loans' => $gauranteeLoans,
        ]);
    }
}
