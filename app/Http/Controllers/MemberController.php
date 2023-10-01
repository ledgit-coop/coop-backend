<?php

namespace App\Http\Controllers;

use App\Constants\AccountType;
use App\Constants\ActionTransaction;
use App\Helpers\AccountHelper;
use App\Helpers\Helper;
use App\Helpers\MemberHelper;
use App\Helpers\Uploading;
use App\Http\Requests\AddAccountTransactionRequest;
use App\Http\Requests\MemberRequest;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Member;
use App\Models\MemberAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;
        
        $members = Member::select([
            'id',
            'member_number',
            'surname', 
            'first_name',
            'middle_name',
            'email_address', 
            'gender', 
            'member_at', 
            'status'
        ]);
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $members->where(function($members) use($filters) {
                    $members->orWhere('member_number', 'like', "%$filters->keyword%")
                    ->orWhere('surname', 'like', "%$filters->keyword%")
                    ->orWhere('email_address', 'like', "%$filters->keyword%");
                });
            if(isset($filters->status))
                $members->where('status', $filters->status);
        }

        if($request->sortField && $request->sortOrder)
            $members->orderBy($request->sortField, $request->sortOrder);


        return $members->paginate($limit);
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
            'mobile_number',
            'telephone_number',
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
            
            DB::commit();

            return response()->json(Member::find($member->id));


        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(MemberRequest $request, Member $member)
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
        ]);

        try {
            DB::beginTransaction();


            foreach ($data as $key => $value) {
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

        $member_account = MemberAccount::where([
            'member_id' => $member->id,
            'account_id' => $account->id,
        ])->first();

        if($member_account && $member_account->account->type == AccountType::SHARE_CAPITAL)
            return response()->json([
                "message" => "Account already exists."
            ], 422);

        MemberHelper::makeAccount($member, $account, $request->account_holder);
        
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

        return response()->json($transactions->get());
    }
}
