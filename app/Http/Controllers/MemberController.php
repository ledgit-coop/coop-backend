<?php

namespace App\Http\Controllers;

use App\Helpers\AccountHelper;
use App\Helpers\MemberHelper;
use App\Http\Requests\AddAccountTransactionRequest;
use App\Http\Requests\MemberRequest;
use App\Models\Account;
use App\Models\Member;
use App\Models\MemberAccount;
use Exception;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;
        
        $members = Member::select([
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
                    $members->orWhere('id', 'like', "%$filters->keyword%")
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

        $member = Member::create($data);
        
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
        
        return response()->json(Member::find($member->id));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $member = Member::where('member_number', $id)->firstOrFail();
        return [
            'id' => $member->id,
            'member_number' => $member->member_number,
            'surname' => $member->surname,
            'full_name' => $member->full_name,
            'age' => $member->age,
            'full_present_address' => $member->full_present_address,
            'full_permanent_address' => $member->full_permanent_address,
            'member_year' => $member->member_at->format('Y'),
            'oriented' => $member->oriented,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function addAccount($id, $account_id) {
        $member = Member::findOrFail($id);
        $account = Account::findOrFail($account_id);

        $member_account = MemberAccount::where([
            'member_id' => $member->id,
            'account_id' => $account->id
        ])->first();

        if($member_account)
            return response()->json([
                "message" => "Account already exists."
            ], 422);

        MemberAccount::create([
            'account_number' => AccountHelper::generateAccount(),
            'passbook_count' => 1,
            'member_id' => $member->id,
            'account_id' => $account->id,
            'balance' => 0,
        ]);
        
        return response('Account created.');
    }

    public function attendedOrientation($member_number) {
        
        $member = Member::where('member_number', $member_number)->firstOrFail();
        $member->oriented = true;
        $member->save();

        return response('Account updated.');
    }
    

    public function addAccountTransaction(AddAccountTransactionRequest $request, MemberAccount $member_account) {
        
        $member_account->transactions()->createMany([
            [
                'particular' => $request->particular,
                'amount' => $request->transaction_type == 'deposit' ? $request->amount : -$request->amount,
            ]
        ]);
        
        return response('Transaction created.');
    }
}
