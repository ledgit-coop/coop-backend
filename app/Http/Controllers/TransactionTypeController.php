<?php

namespace App\Http\Controllers;

use App\Constants\Pagination;
use App\Models\TransactionSubType;
use Exception;
use Illuminate\Http\Request;

class TransactionTypeController extends Controller
{
    public function index(Request $request) {
        $filters = (object) $request->filters ?? [];
        $limit = $request->limit ?? Pagination::PER_PAGE;
        
        $types = TransactionSubType::on();
        
        if(!empty($filters)) {
            if(isset($filters->keyword))
                $types->where(function($types) use($filters) {
                    $types->orWhere('name', 'like', "%$filters->keyword%")
                    ->orWhere('type', 'like', "%$filters->keyword%");
                });
        }

        if($request->sortField && $request->sortOrder)
            $types->orderBy($request->sortField, $request->sortOrder);


        return $types->paginate($limit);
    }

    public function show(TransactionSubType $transactionType)
    {
        return response()->json($transactionType);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'key' => 'required|unique:transaction_sub_types,key',
            'name' => 'required',
            'type' => 'required',
        ]);

        $type = TransactionSubType::create($validatedData);

        return response()->json($type);
    }

    public function update(Request $request, TransactionSubType $transactionType)
    {
        $validatedData = $request->validate([
            'key' => 'required|unique:transaction_sub_types,key,' . $transactionType->id,
            'name' => 'required',
            'type' => 'required',
        ]);

        $transactionType->update($validatedData);

        return response()->json($transactionType);
    }

    public function destroy(TransactionSubType $transactionType)
    {
        if($transactionType->transactions()->exists())
            throw new Exception("Cannot delete since currently being in used in the system.");  

        $transactionType->delete();
        
        return response(true);
    }
}
