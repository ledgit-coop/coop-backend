<?php

namespace App\Http\Controllers;

use App\Models\LoanGuarantor;
use Illuminate\Http\Request;

class GuarantorController extends Controller
{
    public function index(Request $request) {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? 10;
        
        $loanGuarantors = LoanGuarantor::on();
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $loanGuarantors->where(function($loanGuarantors) use($filters) {
                    $loanGuarantors->orWhere('first_name', 'like', "%$filters->keyword%")
                    ->orWhere('first_name', 'like', "%$filters->keyword%");
                });
        }

        if($request->sortField && $request->sortOrder)
            $loanGuarantors->orderBy($request->sortField, $request->sortOrder);


        return $loanGuarantors->paginate($limit);
    }

    public function show(LoanGuarantor $loanGuarantor)
    {
        return response()->json($loanGuarantor);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact' => 'required|string',
        ]);

        $loanGuarantor = LoanGuarantor::create($validatedData);

        return response()->json($loanGuarantor);
    }

    public function update(Request $request, LoanGuarantor $loanGuarantor)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact' => 'required|string',
        ]);

        $loanGuarantor->update($validatedData);

        return response()->json($loanGuarantor);
    }

    public function destroy(LoanGuarantor $loanGuarantor)
    {
        $loanGuarantor->delete();
        
        return response(true);
    }
}
